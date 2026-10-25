<?php
/** Read-only layout previews use the selected item's real frontend request. */
defined('ABSPATH') || exit;

/** Resolve only explicitly changed zones, retaining the saved layout elsewhere. */
function modfarm_settings_content_preview_plan($post, array $patterns): array {
    $blocks = parse_blocks((string) $post->post_content);
    $summary = modfarm_get_ppb_zone_summary_for_post($post->ID, $post->post_type);
    $zoned = ($summary['content_state'] ?? '') === 'Zoned';
    $hybrid = str_starts_with((string) ($summary['layout_mode'] ?? ''), 'Hybrid');
    $plan = ['replacements' => [], 'chrome' => [], 'messages' => []];
    foreach (['header', 'body', 'footer'] as $zone) {
        if (!isset($patterns[$zone]) || !is_string($patterns[$zone])) continue;
        $slug = $patterns[$zone];
        if ($slug !== '' && !modfarm_ppb_pattern_exists($slug)) {
            $plan['messages'][] = ucfirst($zone) . ' pattern is unavailable; the saved layout is retained.';
            continue;
        }
        if ($zoned) {
            $block = modfarm_find_zone_block_by_slot($blocks, $zone);
            if (!$block) {
                $plan['messages'][] = ucfirst($zone) . ' zone is absent; the saved content is retained.';
                continue;
            }
            if (!empty($block['attrs']['locked'])) {
                $plan['messages'][] = ucfirst($zone) . ' zone is locked and is retained.';
                continue;
            }
            $incoming = parse_blocks(modfarm_ppb_get_pattern_content_by_slug($slug));
            $payloads = modfarm_ppb_extract_slot_payloads_from_blocks($block['innerBlocks'] ?? [], $zone);
            $changed = false;
            $incoming = modfarm_ppb_hydrate_empty_slots_in_blocks($incoming, $payloads, $changed);
            $plan['replacements'][$zone] = [
                'slug' => $slug,
                'content' => serialize_blocks($incoming),
            ];
            if ($zone === 'body') {
                $plan['messages'][] = 'Replacing the body can remove blocks outside matching Content Slots. This preview does not save changes.';
            }
        } elseif ($hybrid && $zone !== 'body') {
            if (modfarm_ppb_get_local_chrome_override_slug($post->ID, $zone) !== '') {
                $plan['messages'][] = ucfirst($zone) . ' has a local override and is retained.';
                continue;
            }
            $plan['chrome'][$zone] = $slug;
        } else {
            $plan['messages'][] = $zone === 'body' && $hybrid
                ? 'The Hybrid body is authored content and is retained.'
                : 'This content is not zoned; its saved content is retained. Convert it to PPB before replacing zones.';
        }
    }
    return $plan;
}

/** Replace only the target item's saved zones, never other posts or stored data. */
function modfarm_settings_content_preview_replace(string $content, array $replacements): string {
    if (!$replacements) return $content;
    $walk = static function (array $blocks) use (&$walk, $replacements): array {
        foreach ($blocks as &$block) {
            $zone = ($block['blockName'] ?? '') === 'modfarm/zone'
                ? modfarm_get_zone_slot_from_block_attrs($block['attrs'] ?? []) : '';
            if (isset($replacements[$zone]) && empty($block['attrs']['locked'])) {
                $replacement = $replacements[$zone];
                $block['attrs']['pattern'] = $replacement['slug'];
                $block['innerBlocks'] = parse_blocks($replacement['content']);
                $block['innerHTML'] = '';
                $block['innerContent'] = array_fill(0, count($block['innerBlocks']), null);
            } elseif (!empty($block['innerBlocks'])) {
                $block['innerBlocks'] = $walk($block['innerBlocks']);
            }
        }
        unset($block);
        return $blocks;
    };
    return serialize_blocks($walk(parse_blocks($content)));
}

/** Frontend execution is restricted to an authenticated editor and a signed item URL. */
function modfarm_settings_content_preview_request(): void {
    if (!isset($_GET['mf_settings_preview'])) return;
    $id = absint($_GET['mf_settings_preview']);
    $nonce = $_GET['mf_settings_nonce'] ?? '';
    if (!is_string($nonce) || !current_user_can('edit_theme_options') || !current_user_can('edit_post', $id)
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), 'modfarm_settings_content_preview_' . $id)
        || !is_singular() || get_queried_object_id() !== $id) {
        wp_die('This layout preview has expired or is unavailable.', '', ['response' => 403]);
    }
    if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
    nocache_headers();
    show_admin_bar(false);
    $raw = $_GET['mf_settings_patterns'] ?? '';
    $patterns = is_string($raw) ? json_decode(wp_unslash($raw), true) : [];
    $plan = modfarm_settings_content_preview_plan(get_post($id), is_array($patterns) ? $patterns : []);
    if ($plan['replacements']) {
        add_filter('the_content', static function ($content) use ($id, $plan) {
            if (get_the_ID() !== $id || !in_the_loop() || !is_main_query()) return $content;
            return modfarm_settings_content_preview_replace($content, $plan['replacements']);
        }, 8);
    }
    if ($plan['chrome']) {
        add_filter('modfarm_ppb_hybrid_chrome_slugs', static function ($slugs, $post_id) use ($id, $plan) {
            return $post_id === $id ? array_merge($slugs, $plan['chrome']) : $slugs;
        }, 10, 2);
    }
}
add_action('template_redirect', 'modfarm_settings_content_preview_request', 1);
