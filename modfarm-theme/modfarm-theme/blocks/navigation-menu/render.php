<?php
function modfarm_render_navigation_menu_block($attributes) {
    $layout       = $attributes['layoutType'] ?? 'simple';
    $mode         = $attributes['mode'] ?? 'header'; // 'header' or 'footer'
    $left_id      = $attributes['leftMenu'] ?? 0;
    $right_id     = $attributes['rightMenu'] ?? 0;
    $center       = $attributes['centerContent'] ?? 'site-title'; // site-title | site-logo | site-icon | site-icon-title | none
    $override     = !empty($attributes['localStyle']); // enable inline/local styling
    $no_collapse  = !empty($attributes['noCollapse']); // NEW: keep expanded on mobile

    // Pull defaults from ModFarm Settings
    $options   = get_option('modfarm_theme_settings', []);
    $bg        = $attributes['navBg']         ?? ($options['nav_bg_color']        ?? '#000000');
    $text      = $attributes['navColor']      ?? ($options['nav_text_color']      ?? '#ffffff');
    $hover     = $attributes['navHover']      ?? ($options['nav_hover_color']     ?? '#ffffff');
    $submenuBg = $attributes['submenuBg']     ?? ($options['submenu_bg_color']    ?? '#222222');
    $submenuTx = $attributes['submenuColor']  ?? ($options['submenu_text_color']  ?? '#ffffff');
    $font      = $attributes['fontFamily']    ?? ($options['nav_font']            ?? 'inherit');
    $font_size = $attributes['fontSize']      ?? ($options['nav_font_size']       ?? '');
    $padding   = $attributes['navPadding']    ?? ($options['nav_padding']         ?? 'regular');
    $transparent = $override ? !empty($attributes['transparent']) : !empty($options['nav_transparent']);

    // === BRAND BUILDER =======================================================
    $build_brand_html = function(string $center_mode) use ($options) {

        // Settings (separate caps for logo vs icon)
        $logoW = !empty($options['nav_logo_max_width'])  ? (int)$options['nav_logo_max_width']  : 0;
        $logoH = !empty($options['nav_logo_max_height']) ? (int)$options['nav_logo_max_height'] : 80;
        $iconW = !empty($options['nav_icon_max_width'])  ? (int)$options['nav_icon_max_width']  : 80;
        $iconH = !empty($options['nav_icon_max_height']) ? (int)$options['nav_icon_max_height'] : 80;
        $gap   = !empty($options['nav_brand_gap'])       ? (int)$options['nav_brand_gap']       : 8;

        // Determine caps based on the media kind for this mode
        $capW = 0; $capH = 0;
        if ($center_mode === 'site-logo')       { $capW = $logoW; $capH = $logoH; }
        if ($center_mode === 'site-icon')       { $capW = $iconW; $capH = $iconH; }
        if ($center_mode === 'site-icon-title') { $capW = $iconW; $capH = $iconH; } // combo uses icon sizing

        // Build media (logo or icon)
        $media_html = '';
        if ($center_mode === 'site-logo') {
            if (function_exists('get_custom_logo') && has_custom_logo()) {
                $logo = get_custom_logo();
                if ($logo) {
                    // Strip link wrapper that the_custom_logo() adds
                    $media_html = preg_replace('~</?a\b[^>]*>~i', '', $logo);
                }
            }
        } elseif ($center_mode === 'site-icon' || $center_mode === 'site-icon-title') {
            if (function_exists('has_site_icon') && has_site_icon()) {
                $src = get_site_icon_url(512);
                if ($src) {
                    $img = sprintf('<img class="custom-logo" src="%s" alt="%s" />',
                        esc_url($src), esc_attr(get_bloginfo('name')));
                    $media_html = ($center_mode === 'site-icon-title')
                        ? '<span class="mfs-brand__media">'.$img.'</span>'
                        : $img;
                }
            }
        }

        // Build text (site title)
        $text_html = '';
        if ($center_mode === 'site-title' || $center_mode === 'site-icon-title') {
            $text_html = '<span class="mfs-brand__text">'. esc_html(get_bloginfo('name')) .'</span>';
        }

        // Compose inner by mode (fallback to title if media missing)
        switch ($center_mode) {
            case 'site-logo':
            case 'site-icon':
                $inner = $media_html ?: '<span class="mfs-brand__text">'. esc_html(get_bloginfo('name')) .'</span>';
                break;
            case 'site-title':
                $inner = $text_html;
                break;
            case 'site-icon-title':
                $inner = $media_html . $text_html;
                break;
            default:
                $inner = '';
        }
        if ($inner === '') return '';

        // CSS variables for this instance (0 => none)
        $brand_style = sprintf(
            '--mfs-brand-maxw:%s;--mfs-brand-maxh:%s;--mfs-brand-gap:%dpx;',
            $capW > 0 ? $capW.'px' : 'none',
            $capH > 0 ? $capH.'px' : 'none',
            max(0, $gap)
        );

        // Always link home
        return '<div class="mfs-brand" style="'. esc_attr($brand_style) .'">'.
                 '<a class="mfs-brand__link" href="'. esc_url(home_url('/')) .'" aria-label="'. esc_attr(get_bloginfo('name')) .'">'.
                   $inner .
                 '</a>'.
               '</div>';
    };
    // ========================================================================

    // Class building
    $class = 'mfs-nav nav-padding-' . $padding;
    if ($layout === 'split')   $class .= ' mfs-nav-split';
    if ($layout === 'simple')  $class .= ' mfs-nav-simple';
    if ($mode === 'footer')    $class .= ' mfs-nav-footer';
    if ($override)             $class .= ' has-local-nav-style';
    if ($transparent)          $class .= ' nav-transparent';
    if ($no_collapse)          $class .= ' mfs-nav--no-collapse'; // NEW

    // Inline styles (nav bar container)
    $styles = [];
    if ($override) {
        if (!$transparent)       $styles[] = "background-color: {$bg}";
        $styles[] = "color: {$text}";
        $styles[] = "--submenu-bg: {$submenuBg}";
        $styles[] = "--submenu-color: {$submenuTx}";
        $styles[] = "--mf-nav-hover-color: {$hover}";
        if ($font !== 'inherit') $styles[] = "font-family: {$font}";
        if ($font_size)          $styles[] = "font-size: {$font_size}px";
    }
    $inline_style = !empty($styles) ? implode('; ', $styles) . ';' : '';

    // Helper: render toggle+overlay only when collapsing is enabled
    $render_mobile_ui = function($menu_ids = []) use ($no_collapse) {
        if ($no_collapse) return ''; // NEW: suppress UI entirely
        $html  = '<button class="mfs-nav-toggle" aria-label="Toggle Menu">&#9776;</button>';
        $html .= '<div class="mfs-nav-overlay">';
        $html .=   '<button class="mfs-nav-close" aria-label="Close Menu">&times;</button>';
        $html .=   '<nav class="mfs-nav-overlay-menu">';
        foreach ($menu_ids as $mid) {
            if ($mid) {
                $html .= wp_nav_menu([
                    'menu'       => $mid,
                    'container'  => false,
                    'menu_class' => 'mfs-nav-menu-vertical',
                    'echo'       => false
                ]);
            }
        }
        $html .=   '</nav>';
        $html .= '</div>';
        return $html;
    };

    // Begin output
    $nav_markup = '';

    if ($layout === 'split') {
        if (!$left_id && !$right_id) {
            $nav_markup .= '<div class="mfs-nav mfs-nav-split"><em>Please select menus for left and/or right.</em></div>';
        } else {
            $nav_markup .= '<div class="' . esc_attr($class) . '" style="' . esc_attr($inline_style) . '">';

            // LEFT
            $nav_markup .= '<div class="mfs-nav-left">';
            if ($left_id) {
                $nav_markup .= wp_nav_menu([
                    'menu' => $left_id,
                    'container' => false,
                    'menu_class' => 'mfs-nav-menu',
                    'echo' => false
                ]);
            }
            $nav_markup .= '</div>';

            // CENTER (brand for header)
            if ($mode === 'header') {
                $nav_markup .= '<div class="mfs-nav-center">';
                if ($center !== 'none') {
                    $nav_markup .= $build_brand_html($center);
                }
                $nav_markup .= '</div>';
            }

            // RIGHT
            $nav_markup .= '<div class="mfs-nav-right">';
            if ($right_id) {
                $nav_markup .= wp_nav_menu([
                    'menu' => $right_id,
                    'container' => false,
                    'menu_class' => 'mfs-nav-menu',
                    'echo' => false
                ]);
            }
            $nav_markup .= '</div>';

            // Toggle + Overlay (only if collapsible)
            $nav_markup .= $render_mobile_ui([$left_id, $right_id]);

            $nav_markup .= '</div>'; // .mfs-nav
        }
    } else {
        // SIMPLE
        if (!$left_id) {
            $nav_markup .= '<div class="mfs-nav mfs-nav-simple"><em>Please select a menu in block settings.</em></div>';
        } else {
            $align_class = 'align-' . ($attributes['simpleAlign'] ?? 'center');
            $nav_markup .= '<div class="' . esc_attr($class) . ' ' . esc_attr($align_class) . '" style="' . esc_attr($inline_style) . '">';

            // Brand above/left of menu (your existing placement)
            if ($mode === 'header' && $center !== 'none') {
                $nav_markup .= $build_brand_html($center);
            }

            $nav_markup .= wp_nav_menu([
                'menu' => $left_id,
                'container' => false,
                'menu_class' => 'mfs-nav-menu',
                'echo' => false
            ]);

            // Toggle + Overlay (only if collapsible)
            $nav_markup .= $render_mobile_ui([$left_id]);

            $nav_markup .= '</div>'; // .mfs-nav
        }
    }

    // Wrap and return
    $wrapper_attributes = get_block_wrapper_attributes();
    return '<div ' . $wrapper_attributes . '>' . $nav_markup . '</div>';
}