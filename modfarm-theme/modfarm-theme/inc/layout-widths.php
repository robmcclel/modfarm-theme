<?php
/** Shared site widths and Group overrides, in classic, PPB and editor layouts. */
defined('ABSPATH') || exit;

function modfarm_sanitize_layout_width($value): string {
    if (!is_scalar($value)) return '';
    $value = strtolower(trim((string) $value));
    // Keep the existing length/percentage setting; reject arbitrary CSS tokens.
    if (!preg_match('/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vw|vh|vmin|vmax)$/', $value)) return '';
    return (float) $value > 0 ? $value : '';
}

function modfarm_layout_widths(): array {
    $settings = get_option('modfarm_theme_settings', []);
    return [
        'contentSize' => modfarm_sanitize_layout_width($settings['content_width'] ?? '') ?: '1100px',
        'wideSize' => modfarm_sanitize_layout_width($settings['wide_width'] ?? '') ?: '1300px',
    ];
}

// Concrete values make native Group controls and the editor agree with the site.
add_filter('wp_theme_json_data_theme', function ($theme_json) {
    return $theme_json->update_with(['version' => 2, 'settings' => ['layout' => modfarm_layout_widths()]]);
}, 30);

add_action('enqueue_block_assets', function () {
    $path = get_template_directory() . '/assets/css/layout-widths.css';
    wp_enqueue_style('modfarm-layout-widths', get_template_directory_uri() . '/assets/css/layout-widths.css', [], filemtime($path));
    $widths = modfarm_layout_widths();
    wp_add_inline_style('modfarm-layout-widths', ':root{--mf-content-width:' . $widths['contentSize'] . ';--mf-wide-width:' . $widths['wideSize'] . ';}');
});

add_action('enqueue_block_editor_assets', function () {
    $path = get_template_directory() . '/assets/js/layout-widths.js';
    wp_enqueue_script('modfarm-layout-widths-editor', get_template_directory_uri() . '/assets/js/layout-widths.js', ['wp-hooks', 'wp-element'], filemtime($path), true);
});

function modfarm_group_layout_widths(array $layout): array {
    if (!empty($layout['type']) && $layout['type'] !== 'constrained') return [];
    $vars = [];
    foreach (['contentSize' => '--mf-layout-content-width', 'wideSize' => '--mf-layout-wide-width'] as $key => $var) {
        $value = modfarm_sanitize_layout_width($layout[$key] ?? '');
        if ($value !== '') $vars[$var] = $value;
    }
    return $vars;
}

// Core's generated selectors only reach immediate children. Inheritable tokens
// let slot children use the same local widths without changing authored blocks.
add_filter('render_block_core/group', function ($html, $block) {
    $vars = modfarm_group_layout_widths($block['attrs']['layout'] ?? []);
    if (!$vars || !class_exists('WP_HTML_Tag_Processor')) return $html;
    $tags = new WP_HTML_Tag_Processor($html);
    if (!$tags->next_tag()) return $html;
    $style = rtrim((string) $tags->get_attribute('style'), "; \t\n\r\0\x0B");
    foreach ($vars as $name => $value) $style .= ';' . $name . ':' . $value;
    $tags->set_attribute('style', ltrim($style, ';'));
    return $tags->get_updated_html();
}, 20, 2);
