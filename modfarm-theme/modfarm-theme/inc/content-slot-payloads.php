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
 * Build a valid parsed content-slot block carrying hydrated inner blocks.
 */
function modfarm_ppb_build_hydrated_content_slot_block(array $block, string $payload_markup): array {
    $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
    $json = wp_json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $open = $json ? "<!-- wp:modfarm/content-slot {$json} -->" : '<!-- wp:modfarm/content-slot -->';
    $markup = $open . "\n" . $payload_markup . "\n<!-- /wp:modfarm/content-slot -->";
    $parsed = parse_blocks($markup);

    if (!empty($parsed[0]) && is_array($parsed[0])) {
        return modfarm_ppb_normalize_parsed_blocks([$parsed[0]])[0];
    }

    return $block;
}

/**
 * Hydrate active empty content-slot blocks from stored slot payloads.
 *
 * Only empty live slots are hydrated. Existing visible slot content always wins.
 * Stored slot payloads remain preserved in meta even after hydration.
 */
function modfarm_ppb_hydrate_empty_slots_in_blocks(array $blocks, array $stored_payloads, bool &$changed): array {
    $blocks = modfarm_ppb_normalize_parsed_blocks($blocks);
    $hydrated = [];

    foreach ($blocks as $block) {
        $name = $block['blockName'] ?? null;
        $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
        $inner_blocks = is_array($block['innerBlocks'] ?? null) ? $block['innerBlocks'] : [];

        if ($name === 'modfarm/content-slot') {
            $slot_id = isset($attrs['slot']) && is_string($attrs['slot']) ? trim($attrs['slot']) : 'main';
            if ($slot_id === '') {
                $slot_id = 'main';
            }

            $serialized_inner = function_exists('serialize_blocks') ? serialize_blocks($inner_blocks) : '';
            $is_empty = modfarm_ppb_is_slot_markup_empty($serialized_inner);
            $payload = $stored_payloads[$slot_id] ?? null;

            if ($is_empty && is_array($payload) && !empty($payload['blocks']) && is_string($payload['blocks'])) {
                $block = modfarm_ppb_build_hydrated_content_slot_block($block, $payload['blocks']);
                $changed = true;
            }

            $hydrated[] = $block;
            continue;
        }

        if (!empty($inner_blocks)) {
            $block['innerBlocks'] = modfarm_ppb_hydrate_empty_slots_in_blocks($inner_blocks, $stored_payloads, $changed);
        }

        $hydrated[] = $block;
    }

    return $hydrated;
}

/**
 * Hydrate active empty content slots in post_content using stored payloads.
 */
function modfarm_ppb_hydrate_empty_slots_in_content(string $content, array $stored_payloads, bool &$changed): string {
    if (trim($content) === '' || empty($stored_payloads)) {
        return $content;
    }

    $blocks = parse_blocks($content);
    if (empty($blocks)) {
        return $content;
    }

    $hydrated = modfarm_ppb_hydrate_empty_slots_in_blocks($blocks, $stored_payloads, $changed);
    if (!$changed || !function_exists('serialize_blocks')) {
        return $content;
    }

    return serialize_blocks($hydrated);
}

/**
 * Resolve a zone slot from parsed attrs while honoring the block default.
 */
function modfarm_ppb_get_zone_slot_from_attrs(array $attrs): string {
    if (function_exists('modfarm_get_zone_slot_from_block_attrs')) {
        return modfarm_get_zone_slot_from_block_attrs($attrs);
    }

    $slot = isset($attrs['slot']) && is_string($attrs['slot']) ? trim($attrs['slot']) : '';
    return $slot !== '' ? $slot : 'body';
}

/**
 * Replace one zoned PPB region while preserving matching portable slot payloads.
 *
 * Returns true only when the post content was actually updated.
 */
function modfarm_ppb_replace_post_zone_with_pattern(int $post_id, string $target_zone, string $pattern_slug): bool {
    if ($post_id <= 0 || !in_array($target_zone, ['header', 'body', 'footer'], true)) {
        return false;
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return false;
    }

    $pattern_content = function_exists('modfarm_ppb_get_pattern_content_by_slug')
        ? modfarm_ppb_get_pattern_content_by_slug($pattern_slug)
        : '';
    if ($pattern_content === '') {
        return false;
    }

    $original_content = (string) $post->post_content;
    if (trim($original_content) === '') {
        return false;
    }

    $blocks = parse_blocks($original_content);
    if (empty($blocks) || !function_exists('serialize_blocks') || !function_exists('serialize_block')) {
        return false;
    }

    $changed = false;
    $harvested_payloads = [];
    $segments = [];

    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        $name = $block['blockName'] ?? null;
        $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
        $inner_blocks = is_array($block['innerBlocks'] ?? null) ? $block['innerBlocks'] : [];

        if ($name === 'modfarm/zone' && modfarm_ppb_get_zone_slot_from_attrs($attrs) === $target_zone) {
            $harvested_payloads = modfarm_ppb_merge_slot_payloads(
                $harvested_payloads,
                modfarm_ppb_extract_slot_payloads_from_blocks($inner_blocks, $target_zone)
            );

            $incoming_blocks = parse_blocks($pattern_content);
            $incoming_changed = false;
            $incoming_blocks = modfarm_ppb_hydrate_empty_slots_in_blocks($incoming_blocks, $harvested_payloads, $incoming_changed);
            $incoming_markup = serialize_blocks($incoming_blocks);
            $zone_markup = function_exists('modfarm_ppb_build_zone_markup')
                ? modfarm_ppb_build_zone_markup($target_zone, $incoming_markup, [
                    'origin' => isset($attrs['origin']) && is_string($attrs['origin']) ? $attrs['origin'] : 'ppb',
                    'pattern' => $pattern_slug,
                    'locked' => !empty($attrs['locked']),
                    'version' => isset($attrs['version']) ? (int) $attrs['version'] : 1,
                ])
                : '';

            if ($zone_markup === '') {
                return false;
            }

            $segments[] = $zone_markup;
            $changed = true;
            continue;
        }

        $segments[] = serialize_block($block);
    }

    if (!$changed || empty($segments)) {
        return false;
    }

    if (!empty($harvested_payloads)) {
        $meta_key = modfarm_ppb_slot_payload_meta_key();
        $existing = get_post_meta($post_id, $meta_key, true);
        $merged = modfarm_ppb_merge_slot_payloads(is_array($existing) ? $existing : [], $harvested_payloads);
        update_post_meta($post_id, $meta_key, $merged);
    }

    $updated_content = implode("\n\n", array_filter($segments, static function ($segment) {
        return is_string($segment) && $segment !== '';
    }));
    if ($updated_content === $original_content) {
        return false;
    }

    remove_action('save_post', 'modfarm_ppb_sync_slot_payloads_on_save', 20);
    wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content,
    ]);
    add_action('save_post', 'modfarm_ppb_sync_slot_payloads_on_save', 20, 3);

    return true;
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

    $changed = false;
    $hydrated_content = modfarm_ppb_hydrate_empty_slots_in_content((string) $post->post_content, $merged, $changed);

    if ($changed && $hydrated_content !== (string) $post->post_content) {
        remove_action('save_post', 'modfarm_ppb_sync_slot_payloads_on_save', 20);
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $hydrated_content,
        ]);
        add_action('save_post', 'modfarm_ppb_sync_slot_payloads_on_save', 20, 3);
    }
}
add_action('save_post', 'modfarm_ppb_sync_slot_payloads_on_save', 20, 3);
