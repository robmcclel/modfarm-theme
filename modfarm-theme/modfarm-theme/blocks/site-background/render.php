<?php
/**
 * Render the background from block settings so it survives filtered post saves.
 */
function modfarm_render_site_background_block($attributes) {
    $color = static function ($value) {
        // ColorPalette emits hex colors. Theme palettes can also supply CSS color values.
        $value = trim($value);
        return preg_match('/\A(?:#[0-9a-f]{3,8}|[a-z]+|(?:rgba?|hsla?|hwb|lab|lch|oklab|oklch|color|var)\([a-z0-9.,%\/ +\-]+\))\z/i', $value)
            ? $value : '';
    };
    $rgba = static function ($value, $opacity) {
        $hex = sanitize_hex_color($value);
        if (!$hex) {
            return '';
        }
        $hex = substr($hex, 1);
        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }
        [$red, $green, $blue] = array_map('hexdec', str_split($hex, 2));
        $alpha = min(1, max(0, $opacity));
        return "rgba($red,$green,$blue,$alpha)";
    };
    $choice = static function ($value, $options) {
        return in_array($value, $options, true) ? $value : $options[0];
    };

    $mode = $attributes['mode'] ?? 'color';
    $base = $color($attributes['color'] ?? '');
    $opacity = $attributes['bgOpacity'] ?? 1;
    if ($base && $opacity < 1) {
        $base = $rgba($base, $opacity);
    }

    $layers = [];
    if ($mode === 'gradient') {
        $start = $color($attributes['gradientStartColor'] ?? '');
        $end = $color($attributes['gradientEndColor'] ?? '');
        if ($start && $end) {
            $angle = (float) ($attributes['gradientAngle'] ?? 180);
            $start_position = min(100, max(0, $attributes['gradientStartPos'] ?? 0));
            $end_position = min(100, max(0, $attributes['gradientEndPos'] ?? 100));
            $layers[] = "linear-gradient({$angle}deg, $start $start_position%, $end $end_position%)";
        }
    } elseif ($mode === 'image' && is_string($attributes['image']['url'] ?? null)) {
        $url = esc_url_raw($attributes['image']['url'], ['http', 'https']);
        if ($url !== '') {
            $overlay_opacity = $attributes['overlayOpacity'] ?? 0;
            $overlay = $overlay_opacity > 0 ? $rgba($attributes['overlayColor'] ?? '', $overlay_opacity) : '';
            if ($overlay) {
                $layers[] = "linear-gradient($overlay, $overlay)";
            }
            // Keep the URL inside the CSS string and prevent a closing style tag.
            $url = strtr($url, ['"' => '%22', '\\' => '%5C', '<' => '%3C', '>' => '%3E']);
            $layers[] = 'url("' . $url . '")';
        }
    }

    $css = 'body{';
    if ($base && ($mode === 'color' || ($mode === 'image' && $layers))) {
        $css .= "background-color:$base;";
    }
    if ($layers) {
        $size = $mode === 'gradient' ? 'cover' : $choice($attributes['size'] ?? '', ['cover', 'contain', 'auto']);
        $repeat = $mode === 'gradient' ? 'no-repeat' : $choice($attributes['repeat'] ?? '', ['no-repeat', 'repeat', 'repeat-x', 'repeat-y']);
        $attachment = $choice($attributes['attachment'] ?? '', ['scroll', 'fixed', 'local']);
        $position = $choice($attributes['position'] ?? '', [
            'center center', 'top center', 'bottom center', 'center left', 'center right',
            'top left', 'top right', 'bottom left', 'bottom right',
        ]);
        $css .= 'background-image:' . implode(',', $layers) . ';';
        $css .= "background-size:$size;background-repeat:$repeat;background-attachment:$attachment;background-position:$position;";
    }
    $css .= 'background-origin:padding-box;background-clip:border-box;background-blend-mode:normal;}';

    $headings = ':is(h1,h2,h3,h4,h5,h6,.wp-block-heading)';
    $text = $color($attributes['textColor'] ?? '');
    if ($text) {
        $css .= " body{color:$text;} body $headings{color:$text;}";
    }
    $link = $color($attributes['linkColor'] ?? '');
    if ($link) {
        $css .= " body a{color:$link;} body $headings a{color:$link;}";
    }

    return '<style id="mf-site-page-background">' . $css . '</style>';
}
