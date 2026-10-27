<?php
/** Footer identity shortcodes work in both content and directly rendered patterns. */
defined('ABSPATH') || exit;

function modfarm_footer_login_shortcode($atts = []): string {
    $atts = shortcode_atts(['align' => ''], $atts, 'modfarm_footer_login');
    $alignment = is_string($atts['align']) ? strtolower(trim($atts['align'])) : '';
    $link = is_user_logged_in()
        ? '<a href="' . esc_url(wp_logout_url()) . '">Logout</a>'
        : '<a href="' . esc_url(wp_login_url()) . '">Login</a>';
    $content = '&copy; ' . esc_html(wp_date('Y')) . ' ' . esc_html(get_bloginfo('name')) . ' &middot; ' . $link;
    // No alignment attribute preserves inherited paragraph/group alignment.
    if (!in_array($alignment, ['left', 'center', 'right'], true)) return $content;
    return '<span class="modfarm-footer-login" style="display:block;text-align:' . esc_attr($alignment) . '">' . $content . '</span>';
}
add_shortcode('modfarm_footer_login', 'modfarm_footer_login_shortcode');

/** Expand only our footer shortcodes; leave unrelated shortcodes untouched. */
function modfarm_render_footer_shortcodes_in_block(string $content): string {
    // the_content expands shortcodes after do_blocks; do not expand twice there.
    if (doing_filter('the_content') || !str_contains($content, '[modfarm_footer_')) return $content;
    $pattern = get_shortcode_regex(['modfarm_footer_login', 'modfarm_footer_copyright']);
    return (string) preg_replace_callback('/' . $pattern . '/s', static function ($match) {
        return do_shortcode($match[0]);
    }, $content);
}
foreach (['core/paragraph', 'core/html', 'core/freeform'] as $footer_block) {
    add_filter('render_block_' . $footer_block, 'modfarm_render_footer_shortcodes_in_block');
}
// Unwrapped HTML in older patterns is represented as a nameless block.
add_filter('render_block', static function ($content, $block) {
    return empty($block['blockName']) ? modfarm_render_footer_shortcodes_in_block($content) : $content;
}, 10, 2);
