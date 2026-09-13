<?php
function modfarm_render_navigation_menu_block($attributes) {
    $layout       = $attributes['layoutType'] ?? 'simple';
    $mode         = $attributes['mode'] ?? 'header'; // 'header' or 'footer'
    $left_id      = $attributes['leftMenu'] ?? 0;
    $right_id     = $attributes['rightMenu'] ?? 0;
    $center       = $attributes['centerContent'] ?? 'site-title'; // site-title | site-logo | site-icon | site-icon-title | none
    $override     = !empty($attributes['localStyle']); // enable inline/local styling
    $no_collapse  = !empty($attributes['noCollapse']); // NEW: keep expanded on mobile

    $presentation = in_array($attributes['mobilePresentation'] ?? 'overlay', ['below', 'drawer', 'overlay'], true) ? ($attributes['mobilePresentation'] ?? 'overlay') : 'overlay';
    $drawer_side = ($attributes['drawerSide'] ?? 'right') === 'left' ? 'left' : 'right';
    $show_descriptions = !empty($attributes['showDescriptions']);
    $menu_args = ['container' => false, 'echo' => false, 'fallback_cb' => false,
        'mfs_enhanced' => true, 'mfs_descriptions' => $show_descriptions];
    $render_menu = function ($id, $class_name) use ($menu_args) {
        return wp_nav_menu(array_merge($menu_args, ['menu' => $id, 'menu_class' => $class_name, 'menu_id' => wp_unique_id('mfs-menu-')]));
    };

    // Pull defaults from ModFarm Settings
    $options   = get_option('modfarm_theme_settings', []);
    $bg        = $attributes['navBg']         ?? ($options['nav_bg_color']        ?? '#000000');
    $text      = $attributes['navColor']      ?? ($options['nav_text_color']      ?? '#ffffff');
    $hover     = $attributes['navHover']      ?? ($options['nav_hover_color']     ?? '#ffffff');
    $submenuBg = $attributes['submenuBg']     ?? ($options['submenu_bg_color']    ?? '#222222');
    $submenuTx = $attributes['submenuColor']  ?? ($options['submenu_text_color']  ?? '#ffffff');
    $font      = $override
        ? ($attributes['fontFamily'] ?? ($options['nav_font'] ?? 'inherit'))
        : ($options['nav_font'] ?? 'inherit');
    $font_size = $override
        ? ($attributes['fontSize'] ?? ($options['nav_font_size'] ?? ''))
        : ($options['nav_font_size'] ?? '');
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

    $class .= ' mfs-nav--' . $presentation . ' mfs-nav--drawer-' . $drawer_side;

    // Inline styles (nav bar container)
    $resolved_font = $font === 'inherit'
        ? 'inherit'
        : modfarm_font_css_value(modfarm_effective_font_family((string) $font));
    $resolved_font_size = max(1, intval($font_size ?: 16));
    $styles = [
        "--mf-nav-font: {$resolved_font}",
        "--mf-nav-font-size: {$resolved_font_size}px",
    ];
    foreach (['mobileBg' => ['mobile_nav_bg_color', '--mfs-mobile-bg'], 'mobileColor' => ['mobile_nav_text_color', '--mfs-mobile-color']] as $attribute => $config) {
        $color = $override && !empty($attributes[$attribute]) ? $attributes[$attribute] : ($options[$config[0]] ?? '');
        $color = sanitize_hex_color($color);
        if ($color) $styles[] = $config[1] . ':' . $color;
    }
    foreach (['coverWidth' => [56, 32, 120, 'cover-width'], 'iconSize' => [24, 16, 64, 'icon-size'], 'imageGap' => [16, 4, 32, 'image-gap'], 'dropdownWidth' => [320, 220, 480, 'dropdown-width']] as $key => $limits) {
        $styles[] = '--mfs-menu-' . $limits[3] . ':' . max($limits[1], min($limits[2], (int)($attributes[$key] ?? $limits[0]))) . 'px';
    }
    if ($override) {
        if (!$transparent && $bg !== '') $styles[] = "background-color: {$bg}";
        if ($text !== '')      $styles[] = "color: {$text}";
        if ($submenuBg !== '') $styles[] = "--submenu-bg: {$submenuBg}";
        if ($submenuTx !== '') $styles[] = "--submenu-color: {$submenuTx}";
        if ($hover !== '')     $styles[] = "--mf-nav-hover-color: {$hover}";
    }
    $inline_style = !empty($styles) ? implode('; ', $styles) . ';' : '';

    $render_mobile_ui = function ($menu_ids = []) use ($no_collapse, $presentation, $render_menu) {
        if ($no_collapse) return '';
        $id = wp_unique_id('mfs-mobile-');
        $html = '<button type="button" class="mfs-nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="' . esc_attr($id) . '"><span aria-hidden="true">&#9776;</span><span class="mfs-nav-toggle-label">Menu</span></button>';
        $html .= '<div id="' . esc_attr($id) . '" class="mfs-nav-overlay" hidden>';
        $html .= '<div class="mfs-nav-panel"' . ($presentation !== 'below' ? ' role="dialog" aria-modal="true" aria-label="Site navigation"' : '') . ' tabindex="-1">';
        $html .= '<div class="mfs-nav-panel-header"><span>Menu</span><button type="button" class="mfs-nav-close" aria-label="Close menu">&times;</button></div>';
        $html .= '<nav class="mfs-nav-overlay-menu" aria-label="Mobile navigation">';
        foreach (array_unique(array_filter($menu_ids)) as $mid) $html .= $render_menu($mid, 'mfs-nav-menu-vertical');
        return $html . '</nav></div></div>';
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
                $nav_markup .= $render_menu($left_id, 'mfs-nav-menu');
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
                $nav_markup .= $render_menu($right_id, 'mfs-nav-menu');
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

            $nav_markup .= $render_menu($left_id, 'mfs-nav-menu');

            // Toggle + Overlay (only if collapsible)
            $nav_markup .= $render_mobile_ui([$left_id]);

            $nav_markup .= '</div>'; // .mfs-nav
        }
    }

    // Wrap and return
    $wrapper_attributes = get_block_wrapper_attributes();
    return '<div ' . $wrapper_attributes . '>' . $nav_markup . '</div>';
}
