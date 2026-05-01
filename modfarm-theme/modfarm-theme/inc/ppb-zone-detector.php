<?php
if (!defined('ABSPATH')) exit;

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
                $slot = isset($attrs['slot']) && is_string($attrs['slot']) ? $attrs['slot'] : '';
                if ($slot !== '') {
                    $seen_zones[$slot] = true;
                    if ($slot === 'body' && modfarm_zone_tree_contains_content_slot($block['innerBlocks'] ?? [])) {
                        $body_has_slot = true;
                    }
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
 * Build a read-only PPB zone summary for editor/admin UI.
 */
function modfarm_get_ppb_zone_summary_for_post(int $post_id, string $post_type = ''): array {
    $post_type = $post_type !== '' ? $post_type : (string) get_post_type($post_id);
    $content = (string) get_post_field('post_content', $post_id);
    $detected = modfarm_detect_ppb_zones_in_content($content);
    $blocks = parse_blocks($content);

    $zone_details = [
        'header' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
        ],
        'body' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
        ],
        'footer' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
        ],
        'data' => [
            'present' => false,
            'pattern' => '',
            'locked' => false,
            'contains_content_slot' => false,
        ],
    ];

    $walk = function(array $blocks) use (&$walk, &$zone_details): void {
        foreach ($blocks as $block) {
            $name = $block['blockName'] ?? null;
            $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];

            if ($name === 'modfarm/zone') {
                $slot = isset($attrs['slot']) && is_string($attrs['slot']) ? $attrs['slot'] : '';
                if (isset($zone_details[$slot])) {
                    $zone_details[$slot] = [
                        'present' => true,
                        'pattern' => isset($attrs['pattern']) && is_string($attrs['pattern']) ? $attrs['pattern'] : '',
                        'locked' => !empty($attrs['locked']),
                        'contains_content_slot' => $slot === 'body'
                            ? modfarm_zone_tree_contains_content_slot($block['innerBlocks'] ?? [])
                            : false,
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

    return [
        'content_state' => $detected['is_zoned']
            ? 'Zoned'
            : ($detected['appears_legacy_ppb'] ? 'Legacy PPB' : 'Plain'),
        'layout_mode' => modfarm_get_ppb_layout_mode_for_post($post_id, $post_type, $detected),
        'zones' => $zone_details,
    ];
}

/**
 * Read-only layout mode summary for the local PPB manager.
 */
function modfarm_get_ppb_layout_mode_for_post(int $post_id, string $post_type, array $detected = []): string {
    unset($post_id);

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
