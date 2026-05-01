<?php
if (!defined('ABSPATH')) exit;

/**
 * Resolve a ModFarm zone slot from parsed attrs, honoring the block default.
 */
function modfarm_get_zone_slot_from_block_attrs(array $attrs): string {
    $slot = isset($attrs['slot']) && is_string($attrs['slot']) ? trim($attrs['slot']) : '';
    return $slot !== '' ? $slot : 'body';
}

/**
 * Parse post content and report whether it already uses explicit ModFarm zones.
 */
function modfarm_detect_ppb_zones_in_content(string $content): array {
    $report = [
        'is_zoned' => false,
        'zones' => [],
        'body_contains_content_slot' => false,
        'appears_legacy_ppb' => false,
        'appears_plain' => true,
    ];

    if (trim($content) === '') {
        return $report;
    }

    $blocks = parse_blocks($content);
    if (empty($blocks)) {
        $report['appears_plain'] = true;
        return $report;
    }

    $seen_zones = [];
    $body_has_slot = false;
    $has_modfarm_blocks = false;
    $has_content_slot = false;

    $walk = function(array $blocks) use (&$walk, &$seen_zones, &$body_has_slot, &$has_modfarm_blocks, &$has_content_slot): void {
        foreach ($blocks as $block) {
            $name = $block['blockName'] ?? null;
            $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];

            if (is_string($name) && str_starts_with($name, 'modfarm/')) {
                $has_modfarm_blocks = true;
            }

            if ($name === 'modfarm/content-slot') {
                $has_content_slot = true;
            }

            if ($name === 'modfarm/zone') {
                $slot = modfarm_get_zone_slot_from_block_attrs($attrs);
                $seen_zones[$slot] = true;
                if ($slot === 'body' && modfarm_zone_tree_contains_content_slot($block['innerBlocks'] ?? [])) {
                    $body_has_slot = true;
                }
            }

            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $walk($block['innerBlocks']);
            }
        }
    };

    $walk($blocks);

    $report['zones'] = array_values(array_keys($seen_zones));
    sort($report['zones']);
    $report['is_zoned'] = !empty($report['zones']);
    $report['body_contains_content_slot'] = $body_has_slot;

    if ($report['is_zoned']) {
        $report['appears_plain'] = false;
        return $report;
    }

    $report['appears_legacy_ppb'] = $has_modfarm_blocks || $has_content_slot;
    $report['appears_plain'] = !$report['appears_legacy_ppb'];

    return $report;
}

/**
 * Convenience wrapper for post IDs.
 */
function modfarm_detect_ppb_zones_for_post(int $post_id): array {
    $content = (string) get_post_field('post_content', $post_id);
    return modfarm_detect_ppb_zones_in_content($content);
}

/**
 * Check whether a zone tree contains a ModFarm content slot.
 */
function modfarm_zone_tree_contains_content_slot(array $blocks): bool {
    foreach ($blocks as $block) {
        $name = $block['blockName'] ?? null;
        if ($name === 'modfarm/content-slot') {
            return true;
        }
        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            if (modfarm_zone_tree_contains_content_slot($block['innerBlocks'])) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Collect all content-slot identifiers from a parsed block tree.
 */
function modfarm_collect_content_slot_ids(array $blocks): array {
    $slot_ids = [];

    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        $name = $block['blockName'] ?? null;
        $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
        if ($name === 'modfarm/content-slot') {
            $slot_id = isset($attrs['slot']) && is_string($attrs['slot']) ? trim($attrs['slot']) : '';
            if ($slot_id !== '') {
                $slot_ids[] = $slot_id;
            }
        }

        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            $slot_ids = array_merge($slot_ids, modfarm_collect_content_slot_ids($block['innerBlocks']));
        }
    }

    return $slot_ids;
}

/**
 * Return duplicate content-slot IDs within a block tree.
 */
function modfarm_get_duplicate_content_slot_ids(array $blocks): array {
    $counts = array_count_values(modfarm_collect_content_slot_ids($blocks));
    $duplicates = [];

    foreach ($counts as $slot_id => $count) {
        if ($count > 1) {
            $duplicates[] = (string) $slot_id;
        }
    }

    sort($duplicates);

    return $duplicates;
}

/**
 * Find the first zone block for a given slot.
 */
function modfarm_find_zone_block_by_slot(array $blocks, string $target_slot): ?array {
    foreach ($blocks as $block) {
        if (!is_array($block)) {
            continue;
        }

        $name = $block['blockName'] ?? null;
        $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];

        if ($name === 'modfarm/zone') {
            $slot = modfarm_get_zone_slot_from_block_attrs($attrs);
            if ($slot === $target_slot) {
                return $block;
            }
        }

        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            $found = modfarm_find_zone_block_by_slot($block['innerBlocks'], $target_slot);
            if (is_array($found)) {
                return $found;
            }
        }
    }

    return null;
}

/**
 * Build a read-only PPB zone summary for editor/admin UI.
 */
function modfarm_get_ppb_zone_summary_for_post(int $post_id, string $post_type = ''): array {
    $post_type = $post_type !== '' ? $post_type : (string) get_post_type($post_id);
    $content = (string) get_post_field('post_content', $post_id);
    $detected = modfarm_detect_ppb_zones_in_content($content);
    $blocks = parse_blocks($content);
    $is_hybrid_template = function_exists('modfarm_ppb_is_hybrid_template_for_post')
        ? modfarm_ppb_is_hybrid_template_for_post($post_id, $post_type)
        : false;

    $zone_details = [
        'header' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
            'local_override_active' => false,
            'default_pattern' => '',
        ],
        'body' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
            'local_override_active' => false,
            'default_pattern' => '',
        ],
        'footer' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
            'local_override_active' => false,
            'default_pattern' => '',
        ],
        'data' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
            'local_override_active' => false,
            'default_pattern' => '',
        ],
    ];

    $walk = function(array $blocks) use (&$walk, &$zone_details): void {
        foreach ($blocks as $block) {
            $name = $block['blockName'] ?? null;
            $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];

            if ($name === 'modfarm/zone') {
                $slot = modfarm_get_zone_slot_from_block_attrs($attrs);
                if (isset($zone_details[$slot])) {
                    $zone_details[$slot] = [
                        'present' => true,
                        'pattern' => isset($attrs['pattern']) && is_string($attrs['pattern']) ? $attrs['pattern'] : '',
                        'locked' => !empty($attrs['locked']),
                        'contains_content_slot' => modfarm_zone_tree_contains_content_slot($block['innerBlocks'] ?? []),
                        'local_override_active' => false,
                        'default_pattern' => '',
                    ];
                }
            }

            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $walk($block['innerBlocks']);
            }
        }
    };

    if (!empty($blocks)) {
        $walk($blocks);
    }

    if (!$detected['is_zoned'] && $is_hybrid_template && function_exists('modfarm_ppb_get_effective_hybrid_chrome_slugs_for_post')) {
        $hybrid_defaults = [];
        if (function_exists('modfarm_ppb_get_local_chrome_override_meta_keys') && function_exists('modfarm_ppb_resolve_pattern_slug')) {
            $opts = get_option('modfarm_theme_settings', []);
            $hybrid_defaults = $post_type === 'page'
                ? [
                    'header' => modfarm_ppb_resolve_pattern_slug('page_header_pattern', $opts['page_header_pattern'] ?? null, $opts),
                    'footer' => modfarm_ppb_resolve_pattern_slug('page_footer_pattern', $opts['page_footer_pattern'] ?? null, $opts),
                ]
                : [
                    'header' => modfarm_ppb_resolve_pattern_slug('post_header_pattern', $opts['post_header_pattern'] ?? null, $opts),
                    'footer' => modfarm_ppb_resolve_pattern_slug('post_footer_pattern', $opts['post_footer_pattern'] ?? null, $opts),
                ];
        }

        $hybrid_slugs = modfarm_ppb_get_effective_hybrid_chrome_slugs_for_post($post_id, $post_type);
        foreach (['header', 'footer'] as $slot) {
            if (!empty($hybrid_slugs[$slot])) {
                $zone_details[$slot]['pattern'] = $hybrid_slugs[$slot];
            }
            if (!empty($hybrid_defaults[$slot])) {
                $zone_details[$slot]['default_pattern'] = $hybrid_defaults[$slot];
            }

            $override_slug = function_exists('modfarm_ppb_get_local_chrome_override_slug')
                ? modfarm_ppb_get_local_chrome_override_slug($post_id, $slot)
                : '';
            $zone_details[$slot]['local_override_active'] = ($override_slug !== '');
        }
    }

    return [
        'content_state' => $detected['is_zoned']
            ? 'Zoned'
            : ($detected['appears_legacy_ppb'] ? 'Legacy PPB' : 'Plain'),
        'layout_mode' => modfarm_get_ppb_layout_mode_for_post($post_id, $post_type, $detected),
        'zones' => $zone_details,
    ];
}

/**
 * Build a read-only Apply All preview item report for a single post.
 */
function modfarm_get_ppb_apply_all_item_preview(int $post_id, string $post_type, string $target_zone): array {
    $summary = modfarm_get_ppb_zone_summary_for_post($post_id, $post_type);
    $content = (string) get_post_field('post_content', $post_id);
    $blocks = parse_blocks($content);
    $zone_details = $summary['zones'][$target_zone] ?? [
        'present' => false,
        'pattern' => '',
        'locked' => false,
        'contains_content_slot' => false,
    ];
    $layout_mode = (string) ($summary['layout_mode'] ?? '');
    $is_hybrid = str_starts_with($layout_mode, 'Hybrid');
    $is_zoned = $summary['content_state'] === 'Zoned';
    $has_slot_content = false;
    $duplicate_slot_ids = [];
    $action = 'skip_legacy';
    $notes = [];

    if ($is_zoned) {
        $zone_block = modfarm_find_zone_block_by_slot($blocks, $target_zone);
        if (is_array($zone_block)) {
            $zone_inner_blocks = is_array($zone_block['innerBlocks'] ?? null) ? $zone_block['innerBlocks'] : [];
            $has_slot_content = modfarm_zone_tree_contains_content_slot($zone_inner_blocks);
            $duplicate_slot_ids = modfarm_get_duplicate_content_slot_ids($zone_inner_blocks);
        }

        if (!empty($zone_details['locked'])) {
            $action = 'skip_locked';
            $notes[] = 'Zone is locked.';
        } else {
            $action = 'will_update';
        }
    } else {
        if ($is_hybrid) {
            $notes[] = in_array($target_zone, ['header', 'footer'], true)
                ? 'Hybrid items are not eligible for Apply All. Use the central selector or local reset-to-default flow.'
                : 'Hybrid body is not PPB-managed.';
        } else {
            $notes[] = 'Content is not zoned.';
        }
    }

    if ($has_slot_content) {
        $notes[] = 'Content-slot payloads would be preserved.';
    }

    if (!empty($duplicate_slot_ids)) {
        $notes[] = 'Duplicate slot IDs: ' . implode(', ', $duplicate_slot_ids);
    }

    return [
        'post_id' => $post_id,
        'title' => get_the_title($post_id) ?: sprintf('#%d', $post_id),
        'status' => (string) get_post_status($post_id),
        'edit_link' => get_edit_post_link($post_id, ''),
        'content_state' => $summary['content_state'],
        'layout_mode' => $layout_mode,
        'zone' => [
            'present' => !empty($zone_details['present']),
            'pattern' => (string) ($zone_details['pattern'] ?? ''),
            'locked' => !empty($zone_details['locked']),
            'contains_content_slot' => $has_slot_content,
            'duplicate_slot_ids' => $duplicate_slot_ids,
        ],
        'action' => $action,
        'notes' => $notes,
    ];
}

/**
 * Build a read-only Apply All preview report for one content type + zone.
 */
function modfarm_get_ppb_apply_all_preview_report(string $post_type, string $target_zone, string $pattern_slug): array {
    $report = [
        'content_type' => $post_type,
        'zone' => $target_zone,
        'pattern' => $pattern_slug,
        'totals' => [
            'items' => 0,
            'will_update' => 0,
            'skipped_locked' => 0,
            'skipped_legacy' => 0,
            'slot_content_detected' => 0,
            'potential_conflicts' => 0,
        ],
        'items' => [],
    ];

    $posts = get_posts([
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => false,
    ]);

    foreach ($posts as $post_id) {
        $item = modfarm_get_ppb_apply_all_item_preview((int) $post_id, $post_type, $target_zone);
        $report['items'][] = $item;
        $report['totals']['items']++;

        switch ($item['action']) {
            case 'will_update':
                $report['totals']['will_update']++;
                break;
            case 'skip_locked':
                $report['totals']['skipped_locked']++;
                break;
            case 'skip_legacy':
            default:
                $report['totals']['skipped_legacy']++;
                break;
        }

        if (!empty($item['zone']['contains_content_slot'])) {
            $report['totals']['slot_content_detected']++;
        }

        if (!empty($item['zone']['duplicate_slot_ids'])) {
            $report['totals']['potential_conflicts']++;
        }
    }

    return $report;
}

/**
 * Read-only layout mode summary for the local PPB manager.
 */
function modfarm_get_ppb_layout_mode_for_post(int $post_id, string $post_type, array $detected = []): string {
    $hybrid_template = $post_id > 0 && function_exists('modfarm_ppb_is_hybrid_template_for_post')
        ? modfarm_ppb_is_hybrid_template_for_post($post_id, $post_type)
        : false;

    if ($hybrid_template) {
        $template_slug = (string) get_page_template_slug($post_id);
        if ($template_slug === 'singular-hybrid-sidebar.php') {
            return 'Hybrid: Right Sidebar';
        }
        return 'Hybrid: No Sidebar';
    }

    if ($post_type === 'post') {
        if (!empty($detected['is_zoned'])) {
            return 'Full PPB';
        }
        return 'Hybrid (Default)';
    }

    if (in_array($post_type, ['page', 'book', 'offer'], true)) {
        return 'Full PPB';
    }

    return 'Not managed';
}

/**
 * Build selector payloads for the local PPB manager from the existing lane-based pattern list.
 */
function modfarm_get_ppb_pattern_payloads_for_field(string $field_id): array {
    if (!function_exists('modfarm_get_registered_patterns_for_field') || !function_exists('modfarm_ppb_get_pattern_content_by_slug')) {
        return [];
    }

    $payloads = [];
    foreach (modfarm_get_registered_patterns_for_field($field_id) as $slug => $title) {
        $content = modfarm_ppb_get_pattern_content_by_slug($slug);
        if ($content === '') {
            continue;
        }

        $payloads[] = [
            'value' => $slug,
            'label' => $title,
            'content' => $content,
        ];
    }

    return $payloads;
}

/**
 * Phase 2 local PPB manager config, including safe local zone eligibility.
 */
function modfarm_get_local_ppb_manager_config_for_post(int $post_id, string $post_type = ''): array {
    $post_type = $post_type !== '' ? $post_type : (string) get_post_type($post_id);
    $summary = modfarm_get_ppb_zone_summary_for_post($post_id, $post_type);
    $is_hybrid_template = function_exists('modfarm_ppb_is_hybrid_template_for_post')
        ? modfarm_ppb_is_hybrid_template_for_post($post_id, $post_type)
        : false;
    $is_zoned = $summary['content_state'] === 'Zoned';
    $actions_mode = $is_zoned ? 'zoned' : ($is_hybrid_template ? 'hybrid' : 'disabled');
    $meta_keys = function_exists('modfarm_ppb_local_chrome_override_meta_keys')
        ? modfarm_ppb_local_chrome_override_meta_keys()
        : ['header' => '', 'footer' => ''];

    $actions = [
        'mode' => $actions_mode,
        'zones' => [
            'header' => [
                'enabled' => false,
                'meta_key' => $meta_keys['header'] ?? '',
                'patterns' => [],
            ],
            'body' => [
                'enabled' => false,
                'meta_key' => '',
                'patterns' => [],
            ],
            'footer' => [
                'enabled' => false,
                'meta_key' => $meta_keys['footer'] ?? '',
                'patterns' => [],
            ],
        ],
    ];

    foreach (['header', 'body', 'footer'] as $slot) {
        $field_id = function_exists('modfarm_ppb_get_field_id_for_post_zone')
            ? modfarm_ppb_get_field_id_for_post_zone($post_type, $slot)
            : '';
        if ($field_id === '') {
            continue;
        }

        $patterns = modfarm_get_ppb_pattern_payloads_for_field($field_id);
        $has_zone = !empty($summary['zones'][$slot]['present']);
        $is_locked = !empty($summary['zones'][$slot]['locked']);
        $actions['zones'][$slot]['patterns'] = $patterns;
        $actions['zones'][$slot]['enabled'] = !empty($patterns) && (
            ($actions_mode === 'zoned' && $has_zone && !$is_locked) ||
            ($actions_mode === 'hybrid' && in_array($slot, ['header', 'footer'], true))
        );
    }

    $summary['actions'] = $actions;

    return $summary;
}
