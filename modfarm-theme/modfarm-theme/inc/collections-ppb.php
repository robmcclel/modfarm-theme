<?php
/**
 * Theme-side PPB integration points for ModFarm Collections.
 */

defined('ABSPATH') || exit;

if (!function_exists('modfarm_is_collection_type')) {
    function modfarm_is_collection_type($post_type): bool {
        $post_type = sanitize_key((string) $post_type);
        if ($post_type === '') {
            return false;
        }

        return (bool) apply_filters('modfarm_is_collection_type', false, $post_type);
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
                'header' => 'modfarm/collection-archive-header-default',
                'body'   => 'modfarm/collection-archive-body-default',
                'footer' => 'modfarm/collection-archive-footer-default',
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

        $patterns = modfarm_get_collection_patterns($post_type, 'archive');

        echo do_blocks(modfarm_get_pattern_content_by_slug($patterns['header']));
        echo do_blocks(modfarm_get_pattern_content_by_slug($patterns['body']));
        echo do_blocks(modfarm_get_pattern_content_by_slug($patterns['footer']));

        return true;
    }
}

add_filter('template_include', function ($template) {
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
