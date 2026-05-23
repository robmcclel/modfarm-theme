<?php
/**
 * Theme-side PPB integration points for ModFarm Collections.
 */

defined('ABSPATH') || exit;

if (!function_exists('modfarm_get_collection_type_defs')) {
    function modfarm_get_collection_type_defs(): array {
        $defs = get_option('mfc_content_defs', []);
        if (!is_array($defs)) {
            return [];
        }

        $types = isset($defs['types']) && is_array($defs['types']) ? $defs['types'] : [];
        $clean = [];

        foreach ($types as $slug => $def) {
            $slug = sanitize_key((string) $slug);
            if ($slug !== '' && is_array($def)) {
                $clean[$slug] = $def;
            }
        }

        return $clean;
    }
}

if (!function_exists('modfarm_get_collection_post_types')) {
    function modfarm_get_collection_post_types(): array {
        return array_keys(modfarm_get_collection_type_defs());
    }
}

if (!function_exists('modfarm_get_ppb_supported_post_types')) {
    function modfarm_get_ppb_supported_post_types(): array {
        $types = ['page', 'book', 'post', 'offer', 'mf_offer'];
        $types = array_merge($types, modfarm_get_collection_post_types());

        return array_values(array_unique(array_filter(array_map('sanitize_key', $types))));
    }
}

if (!function_exists('modfarm_is_collection_type')) {
    function modfarm_is_collection_type($post_type): bool {
        $post_type = sanitize_key((string) $post_type);
        if ($post_type === '') {
            return false;
        }

        return (bool) apply_filters('modfarm_is_collection_type', false, $post_type);
    }
}

if (!function_exists('modfarm_get_collection_layout_mode')) {
    function modfarm_get_collection_layout_mode($post_type): string {
        $post_type = sanitize_key((string) $post_type);
        $types = modfarm_get_collection_type_defs();
        $mode = isset($types[$post_type]['ppb']['layout_mode']) ? sanitize_key((string) $types[$post_type]['ppb']['layout_mode']) : 'ppb';

        if (!in_array($mode, ['ppb', 'hybrid', 'hybrid-sidebar'], true)) {
            $mode = 'ppb';
        }

        return (string) apply_filters('modfarm_collection_layout_mode', $mode, $post_type);
    }
}

if (!function_exists('modfarm_get_collection_hybrid_template_slug')) {
    function modfarm_get_collection_hybrid_template_slug($post_type): string {
        $mode = modfarm_get_collection_layout_mode($post_type);

        if ($mode === 'hybrid-sidebar') {
            return locate_template('singular-hybrid-sidebar.php') ? 'singular-hybrid-sidebar.php' : '';
        }

        if ($mode === 'hybrid') {
            return locate_template('singular-hybrid.php') ? 'singular-hybrid.php' : '';
        }

        return '';
    }
}

if (!function_exists('modfarm_get_collection_patterns')) {
    function modfarm_get_collection_patterns($post_type, $context = 'single'): array {
        $post_type = sanitize_key((string) $post_type);
        $context   = sanitize_key((string) $context);

        if (!in_array($context, ['single', 'archive'], true)) {
            $context = 'single';
        }

        $fallbacks = [
            'single' => [
                'header' => 'modfarm/collection-header-default',
                'body'   => 'modfarm/collection-body-default',
                'footer' => 'modfarm/collection-footer-default',
            ],
            'archive' => [
                'header' => '',
                'body'   => 'modfarm/collection-archive-body-default',
                'footer' => '',
            ],
        ];

        $patterns = apply_filters('modfarm_collection_patterns', [], $post_type, $context);
        $patterns = is_array($patterns) ? $patterns : [];

        $normalized = [];
        foreach (['header', 'body', 'footer'] as $slot) {
            $value = isset($patterns[$slot]) ? (string) $patterns[$slot] : '';
            $value = function_exists('modfarm_ppb_normalize_slug') ? modfarm_ppb_normalize_slug($value) : trim($value);
            $normalized[$slot] = $value !== '' ? $value : $fallbacks[$context][$slot];
        }

        return $normalized;
    }
}

if (!function_exists('modfarm_get_pattern_content_by_slug')) {
    function modfarm_get_pattern_content_by_slug($slug): string {
        $slug = is_string($slug) ? $slug : '';

        if (function_exists('modfarm_ppb_get_pattern_content_by_slug')) {
            return modfarm_ppb_get_pattern_content_by_slug($slug);
        }

        if (!class_exists('WP_Block_Patterns_Registry')) {
            return '';
        }

        $slug = trim($slug);
        if ($slug === '') {
            return '';
        }

        $registry = WP_Block_Patterns_Registry::get_instance();
        if (!$registry || !method_exists($registry, 'get_registered')) {
            return '';
        }

        $pattern = $registry->get_registered($slug);
        if (is_array($pattern) && !empty($pattern['content'])) {
            return (string) $pattern['content'];
        }

        if (is_object($pattern) && !empty($pattern->content)) {
            return (string) $pattern->content;
        }

        return '';
    }
}

if (!function_exists('modfarm_ppb_get_collection_zoned_content_markup')) {
    function modfarm_ppb_get_collection_zoned_content_markup($post_type, $context = 'single'): string {
        if ($context === 'single' && modfarm_get_collection_layout_mode($post_type) !== 'ppb') {
            return '';
        }

        $patterns = modfarm_get_collection_patterns($post_type, $context);

        $header = modfarm_get_pattern_content_by_slug($patterns['header']);
        $body   = modfarm_get_pattern_content_by_slug($patterns['body']);
        $footer = modfarm_get_pattern_content_by_slug($patterns['footer']);

        if ($body === '' || !function_exists('modfarm_ppb_build_zone_markup')) {
            return '';
        }

        return implode("\n\n", [
            modfarm_ppb_build_zone_markup('header', $header, [
                'pattern' => $patterns['header'],
            ]),
            modfarm_ppb_build_zone_markup('body', $body, [
                'pattern' => $patterns['body'],
            ]),
            modfarm_ppb_build_zone_markup('footer', $footer, [
                'pattern' => $patterns['footer'],
            ]),
        ]);
    }
}

if (!function_exists('modfarm_render_collection_archive_page')) {
    function modfarm_render_collection_archive_page(): bool {
        if (!is_post_type_archive()) {
            return false;
        }

        $post_type = get_query_var('post_type');
        if (is_array($post_type)) {
            $post_type = reset($post_type);
        }

        $post_type = sanitize_key((string) $post_type);
        if ($post_type === '' || !modfarm_is_collection_type($post_type)) {
            return false;
        }

        $opts = get_option('modfarm_theme_settings', []);
        $header_slug = function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('archive_header_pattern', $opts['archive_header_pattern'] ?? null, $opts)
            : (string) ($opts['archive_header_pattern'] ?? '');
        $footer_slug = function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('archive_footer_pattern', $opts['archive_footer_pattern'] ?? null, $opts)
            : (string) ($opts['archive_footer_pattern'] ?? '');
        $patterns = modfarm_get_collection_patterns($post_type, 'archive');

        echo do_blocks(modfarm_get_pattern_content_by_slug($header_slug));
        echo do_blocks(modfarm_get_pattern_content_by_slug($patterns['body']));
        echo do_blocks(modfarm_get_pattern_content_by_slug($footer_slug));

        return true;
    }
}

add_filter('template_include', function ($template) {
    if (is_singular()) {
        $post_type = get_post_type();
        $post_type = sanitize_key((string) $post_type);

        if ($post_type !== '' && modfarm_is_collection_type($post_type)) {
            $hybrid_template = modfarm_get_collection_hybrid_template_slug($post_type);
            if ($hybrid_template !== '') {
                $resolved = locate_template($hybrid_template);
                return $resolved ?: $template;
            }
        }
    }

    if (!is_post_type_archive()) {
        return $template;
    }

    $post_type = get_query_var('post_type');
    if (is_array($post_type)) {
        $post_type = reset($post_type);
    }

    $post_type = sanitize_key((string) $post_type);
    if ($post_type === '' || !modfarm_is_collection_type($post_type)) {
        return $template;
    }

    $archive_template = locate_template('archive.php');
    return $archive_template ?: $template;
}, 20);
