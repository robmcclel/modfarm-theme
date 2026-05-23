<?php
defined('ABSPATH') || exit;

function modfarm_render_archive_description_block($attributes = [], $content = '', $block = null) {
    $description = get_the_archive_description();
    if (trim((string) $description) === '') {
        return '';
    }

    $align = isset($attributes['alignText']) ? sanitize_key((string) $attributes['alignText']) : 'center';
    if (!in_array($align, ['left', 'center', 'right'], true)) {
        $align = 'center';
    }

    return sprintf(
        '<div class="modfarm-archive-description has-text-align-%1$s">%2$s</div>',
        esc_attr($align),
        wp_kses_post($description)
    );
}
