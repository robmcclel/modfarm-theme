<?php
/**
 * Theme Icon block server render (buffered markup -> return string)
 */

function modfarm_render_theme_icon_block( $attrs ) {
    $defaults = [
        'size'            => 48,
        'shape'           => 'square',   // square | rounded | circle
        'linkHome'        => true,
        'overrideImageID' => 0,
        'overrideImageURL'=> '',
        'alt'             => '',
        'className'       => '',
        'displayMode'     => 'inline',   // inline | block
        'align'           => 'left',     // left | center | right (applies when displayMode=block)
    ];
    $a = wp_parse_args( $attrs, $defaults );

    $size  = max(16, min(512, (int) $a['size']));
    $shape = in_array($a['shape'], ['square','rounded','circle'], true) ? $a['shape'] : 'square';
    $mode  = in_array($a['displayMode'], ['inline','block'], true) ? $a['displayMode'] : 'inline';
    $align = in_array($a['align'], ['left','center','right'], true) ? $a['align'] : 'left';
    $alt   = trim( (string) $a['alt'] );

    // Build wrapper classes
    $classes = array_filter([
        'mf-theme-icon',
        'mf-theme-icon--' . $shape,
        $mode === 'block' ? 'mf-theme-icon--block' : '',
        $mode === 'block' ? 'mf-theme-icon--align-' . $align : '',
        sanitize_html_class( $a['className'] ?? '' ),
    ]);
    $class_name = implode(' ', $classes);

    // Resolve image URL (override -> site icon)
    $image_url = '';
    if (!empty($a['overrideImageID'])) {
        $image_url = wp_get_attachment_image_url( (int) $a['overrideImageID'], 'full' );
        if (empty($image_url) && !empty($a['overrideImageURL'])) {
            $image_url = esc_url_raw($a['overrideImageURL']);
        }
    } elseif (function_exists('get_site_icon_url') && has_site_icon()) {
        $image_url = get_site_icon_url($size);
    }

    // Prepare inner HTML (img or SVG fallback)
    if (!empty($image_url)) {
        $inner_html = sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async" />',
            esc_url($image_url),
            esc_attr($alt ?: get_bloginfo('name') . ' icon'),
            $size, $size
        );
    } else {
        // Monogram SVG fallback (keeps layout from collapsing)
        $initial = mb_strtoupper(mb_substr(get_bloginfo('name'), 0, 1, 'UTF-8'), 'UTF-8');
        $radius  = $shape === 'circle' ? floor($size/2) : ($shape === 'rounded' ? 12 : 0);
        $font_px = max(12, floor($size * 0.6));
        $inner_html = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d" role="img" aria-label="%2$s"><rect width="100%%" height="100%%" rx="%3$d" ry="%3$d" fill="#222"/><text x="50%%" y="54%%" text-anchor="middle" font-family="system-ui,-apple-system,Segoe UI,Roboto" font-size="%4$d" fill="#fff">%5$s</text></svg>',
            $size,
            esc_attr($alt ?: get_bloginfo('name') . ' icon'),
            $radius,
            $font_px,
            esc_html($initial)
        );
    }

    // Optional link wrapper
    if (!empty($a['linkHome'])) {
        $inner_html = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url(home_url('/')),
            esc_attr(get_bloginfo('name') . ' home'),
            $inner_html
        );
    }

    // Render (buffer -> echo -> return)
    $wrap_tag = $mode === 'block' ? 'div' : 'span';

    ob_start();
    ?>
    <<?php echo $wrap_tag; ?> class="<?php echo esc_attr($class_name); ?>">
        <?php echo $inner_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </<?php echo $wrap_tag; ?>>
    <?php
    return ob_get_clean();
}

/**
 * Editor-only: ensure the ServerSideRender component script is enqueued
 * so the editor preview works (no change to your registrar).
 */
add_action('enqueue_block_editor_assets', function () {
    if (wp_script_is('wp-server-side-render', 'registered')) {
        wp_enqueue_script('wp-server-side-render');
    }
});