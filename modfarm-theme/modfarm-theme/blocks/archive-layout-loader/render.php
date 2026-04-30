<?php

function modfarm_render_archive_layout_loader_block($attributes) {
    ob_start();

    //error_log('[ModFarm] Archive layout block triggered');

    $options = get_option('modfarm_theme_settings', []);

    // Fresh or unused PPB settings must still render a usable archive layout.
    $header = function_exists('modfarm_ppb_resolve_pattern_slug')
        ? modfarm_ppb_resolve_pattern_slug('archive_header_pattern', $options['archive_header_pattern'] ?? null, $options)
        : ($options['archive_header_pattern'] ?? null);
    $body = function_exists('modfarm_ppb_resolve_pattern_slug')
        ? modfarm_ppb_resolve_pattern_slug('archive_body_pattern', $options['archive_body_pattern'] ?? null, $options)
        : ($options['archive_body_pattern'] ?? null);
    $footer = function_exists('modfarm_ppb_resolve_pattern_slug')
        ? modfarm_ppb_resolve_pattern_slug('archive_footer_pattern', $options['archive_footer_pattern'] ?? null, $options)
        : ($options['archive_footer_pattern'] ?? null);

    if (is_tax()) {
        $taxonomy = get_queried_object()->taxonomy;
        $tax_key = 'archive_body_pattern_' . $taxonomy;
        if (!empty($options[$tax_key])) {
            $body = function_exists('modfarm_ppb_resolve_pattern_slug')
                ? modfarm_ppb_resolve_pattern_slug($tax_key, $options[$tax_key], $options)
                : $options[$tax_key];
            error_log("[ModFarm] ✅ Taxonomy-specific override found for $taxonomy");
        }
    }

    if (!$header && !$body && !$footer) {
        error_log('[ModFarm] ⚠️ No archive layout patterns defined');
        echo '<p>No archive layout patterns defined. Please assign them in Page Pattern Builder.</p>';
        return ob_get_clean();
    }

    $get_content = function($slug) {
        if (!$slug) return '';
    
        // 1) Support user/* patterns stored in wp_block
        if (str_starts_with($slug, 'user/')) {
            $post_name = substr($slug, 5);
            $post = get_page_by_path($post_name, OBJECT, 'wp_block');
            if ($post && has_blocks($post->post_content)) {
                return $post->post_content;
            } else {
                // error_log("[ModFarm] ❌ User pattern not found or empty: {$slug}");
                return '';
            }
        }
    
        // 2) Support registered/core pattern slugs (stock patterns)
        if (function_exists('get_block_pattern')) {
            $p = get_block_pattern($slug); // returns array with ['content'] when found
            if (is_array($p) && !empty($p['content'])) {
                return $p['content'];
            }
        }
    
        // Registry fallback (older WP or custom registration)
        if (class_exists('WP_Block_Patterns_Registry')) {
            $reg = WP_Block_Patterns_Registry::get_instance();
            if ($reg && method_exists($reg, 'get_registered')) {
                $p = $reg->get_registered($slug);
                if (is_array($p) && !empty($p['content'])) return $p['content'];
                if (is_object($p) && !empty($p->content))   return $p->content;
            }
        }
    
        // error_log("[ModFarm] ⚠️ Pattern slug not resolved: {$slug}");
        return '';
    };


    echo do_blocks(
        $get_content($header) .
        $get_content($body) .
        $get_content($footer)
    );

    return ob_get_clean();
}
