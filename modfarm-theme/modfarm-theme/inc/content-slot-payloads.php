<?php
if (!defined('ABSPATH')) exit;

/**
 * Portable content-slot payload storage.
 *
 * This layer mirrors manual slot content into post meta by slot ID so future
 * PPB pattern replacement can preserve and rehydrate content across layout
 * changes. It intentionally does not alter current rendering behavior yet.
 */

function modfarm_ppb_slot_payload_meta_key(): string {
    return '_modfarm_ppb_slot_payloads';
}

/**
 * Register slot payload meta for supported PPB post types.
 */
add_action('init', function () {
    $meta_key = modfarm_ppb_slot_payload_meta_key();
    $meta_args = [
        'single' => true,
        'type' => 'object',
        'show_in_rest' => false,
        'auth_callback' => static function (): bool {
            return current_user_can('edit_posts');
        },
        'sanitize_callback' => static function ($value) {
            return is_array($value) ? $value : [];
        },
    ];

    foreach (['page', 'post', 'book', 'offer'] as $post_type) {
        if (post_type_exists($post_type)) {
            register_post_meta($post_type, $meta_key, $meta_args);
        }
    }
});

/**
 * Determine whether serialized block HTML is effectively empty.
 */
function modfarm_ppb_is_slot_markup_empty(string $html): bool {
    if ($html === '') {
        return true;
    }

    $clean = preg_replace('/<!--.*?-->/s', '', $html);
    $clean = is_string($clean) ? $clean : $html;
    $clean = str_replace("\xC2\xA0", ' ', $clean);
    $clean = preg_replace('/&nbsp;?/i', ' ', $clean);
    $clean = is_string($clean) ? $clean : '';
    $clean = trim(strip_tags(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

    return $clean === '';
}

/**
 * Normalize parsed block trees so downstream serialize/walk operations never
 * receive null or malformed block entries.
 */
function modfarm_ppb_normalize_parsed_blocks(array $blocks): array {
    $normalized = [];

    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        $normalized_block = $block;
        $normalized_block['blockName'] = $block['blockName'] ?? null;
        $normalized_block['attrs'] = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
        $normalized_block['innerBlocks'] = !empty($block['innerBlocks']) && is_array($block['innerBlocks'])
            ? modfarm_ppb_normalize_parsed_blocks($block['innerBlocks'])
            : [];
        $normalized_block['innerHTML'] = isset($block['innerHTML']) && is_string($block['innerHTML'])
            ? $block['innerHTML']
            : '';
        $normalized_block['innerContent'] = !empty($block['innerContent']) && is_array($block['innerContent'])
            ? array_values(array_filter($block['innerContent'], static function ($item) {
                return $item === null || is_string($item);
            }))
            : [];

        $normalized[] = $normalized_block;
    }

    return $normalized;
}

/**
 * Extract non-empty portable slot payloads from a parsed block tree.
 *
 * Returns an array keyed by slot ID.
 */
function modfarm_ppb_extract_slot_payloads_from_blocks(array $blocks, string $current_zone = ''): array {
    $blocks = modfarm_ppb_normalize_parsed_blocks($blocks);
    $payloads = [];

    foreach ($blocks as $block) {
        $name = $block['blockName'] ?? null;
        $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
        $inner_blocks = is_array($block['innerBlocks'] ?? null) ? $block['innerBlocks'] : [];

        $zone_context = $current_zone;
        if ($name === 'modfarm/zone') {
            $zone_context = isset($attrs['slot']) && is_string($attrs['slot']) ? $attrs['slot'] : $current_zone;
        }

        if ($name === 'modfarm/content-slot') {
            $slot_id = isset($attrs['slot']) && is_string($attrs['slot']) ? trim($attrs['slot']) : 'main';
            if ($slot_id === '') {
                $slot_id = 'main';
            }

            $serialized = function_exists('serialize_blocks') ? serialize_blocks($inner_blocks) : '';
            if (!modfarm_ppb_is_slot_markup_empty($serialized)) {
                $payloads[$slot_id] = [
                    'blocks' => $serialized,
                    'updated_at' => gmdate('c'),
                    'source' => 'manual',
                    'last_zone' => $zone_context,
                ];
            }

            continue;
        }

        if (!empty($inner_blocks)) {
            $nested = modfarm_ppb_extract_slot_payloads_from_blocks($inner_blocks, $zone_context);
            foreach ($nested as $slot_id => $payload) {
                $payloads[$slot_id] = $payload;
            }
        }
    }

    return $payloads;
}

/**
 * Extract portable slot payloads from post content.
 */
function modfarm_ppb_extract_slot_payloads_from_content(string $content): array {
    if (trim($content) === '') {
        return [];
    }

    $blocks = parse_blocks($content);
    if (empty($blocks)) {
        return [];
    }

    return modfarm_ppb_extract_slot_payloads_from_blocks(modfarm_ppb_normalize_parsed_blocks($blocks));
}

/**
 * Merge newly harvested slot payloads into the stored slot payload map.
 *
 * Important preservation rule:
 * Never auto-delete slot payloads when a content-slot block is removed.
 */
function modfarm_ppb_merge_slot_payloads(array $existing, array $harvested): array {
    $merged = is_array($existing) ? $existing : [];

    foreach ($harvested as $slot_id => $payload) {
        if (!is_string($slot_id) || $slot_id === '' || !is_array($payload)) {
            continue;
        }
        $merged[$slot_id] = $payload;
    }

    return $merged;
}

/**
 * Fetch one stored portable slot payload by slot ID.
 */
function modfarm_ppb_get_slot_payload_for_post(int $post_id, string $slot_id): array {
    if ($post_id <= 0 || $slot_id === '') {
        return [];
    }

    $all_payloads = get_post_meta($post_id, modfarm_ppb_slot_payload_meta_key(), true);
    if (!is_array($all_payloads)) {
        return [];
    }

    $payload = $all_payloads[$slot_id] ?? null;
    if (!is_array($payload)) {
        return [];
    }

    $blocks = isset($payload['blocks']) && is_string($payload['blocks']) ? $payload['blocks'] : '';
    if ($blocks === '' || modfarm_ppb_is_slot_markup_empty($blocks)) {
        return [];
    }

    return [
        'blocks' => $blocks,
        'updated_at' => isset($payload['updated_at']) && is_string($payload['updated_at']) ? $payload['updated_at'] : '',
        'source' => isset($payload['source']) && is_string($payload['source']) ? $payload['source'] : '',
        'last_zone' => isset($payload['last_zone']) && is_string($payload['last_zone']) ? $payload['last_zone'] : '',
    ];
}

/**
 * Save portable content-slot payloads for supported post types.
 */
function modfarm_ppb_sync_slot_payloads_on_save(int $post_id, WP_Post $post, bool $update): void {
    unset($update);

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!in_array($post->post_type, ['page', 'post', 'book', 'offer'], true)) {
        return;
    }

    $harvested = modfarm_ppb_extract_slot_payloads_from_content((string) $post->post_content);
    if (empty($harvested)) {
        return;
    }

    $meta_key = modfarm_ppb_slot_payload_meta_key();
    $existing = get_post_meta($post_id, $meta_key, true);
    $merged = modfarm_ppb_merge_slot_payloads(is_array($existing) ? $existing : [], $harvested);

    update_post_meta($post_id, $meta_key, $merged);
}
add_action('save_post', 'modfarm_ppb_sync_slot_payloads_on_save', 20, 3);
