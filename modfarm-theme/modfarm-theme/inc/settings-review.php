<?php
/** Settings-only identity editing and read-only appearance rendering. */
defined('ABSPATH') || exit;

function modfarm_save_site_identity($input) {
    if (!current_user_can('manage_options') || !is_array($input)) return [];
    foreach (['blogname', 'blogdescription'] as $key) {
        if (isset($input[$key]) && is_scalar($input[$key])) update_option($key, sanitize_text_field($input[$key]));
    }
    foreach (['site_icon', 'custom_logo'] as $key) {
        if (!isset($input[$key]) || !is_scalar($input[$key])) continue;
        $id = absint($input[$key]);
        if ($id && !wp_attachment_is_image($id)) continue;
        if ($key === 'custom_logo') set_theme_mod($key, $id);
        else update_option($key, $id);
    }
    // The canonical WordPress options/theme mod remain the source of truth.
    return [];
}

function modfarm_site_identity_fields() {
    echo '<table class="form-table mf-form-table"><tbody>';
    foreach (['blogname' => 'Site Title', 'blogdescription' => 'Tagline'] as $key => $label) {
        echo '<tr><th><label for="mf-identity-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td><input id="mf-identity-' . esc_attr($key) . '" type="text" name="modfarm_site_identity[' . esc_attr($key) . ']" value="' . esc_attr(get_option($key)) . '"></td></tr>';
    }
    foreach (['custom_logo' => 'Site Logo', 'site_icon' => 'Site Icon'] as $key => $label) {
        $id = $key === 'custom_logo' ? get_theme_mod($key, 0) : get_option($key, 0);
        echo '<tr><th>' . esc_html($label) . '</th><td><input type="hidden" id="mf-identity-' . esc_attr($key) . '" name="modfarm_site_identity[' . esc_attr($key) . ']" value="' . absint($id) . '"><img class="mf-identity-image" alt="" src="' . esc_url($id ? wp_get_attachment_image_url($id, 'medium') : '') . '"' . ($id ? '' : ' hidden') . '><button type="button" class="button" data-identity-select="' . esc_attr($key) . '">Choose image</button> <button type="button" class="button" data-identity-clear="' . esc_attr($key) . '">Remove</button><p class="description">' . ($key === 'site_icon' ? 'Use a square image, at least 512 × 512 pixels.' : 'Used when navigation displays the site logo.') . '</p></td></tr>';
    }
    echo '</tbody></table>';
}

function modfarm_ajax_settings_appearance() {
    if (!current_user_can('edit_theme_options')) wp_send_json_error(['message' => 'You cannot preview settings.'], 403);
    check_ajax_referer('modfarm_settings_appearance', 'nonce');
    $request = wp_unslash($_POST['settings'] ?? '');
    $raw = is_string($request) ? json_decode($request, true) : null;
    if (!is_array($raw)) wp_send_json_error(['message' => 'Invalid settings.'], 400);
    $saved = get_option('modfarm_theme_settings', []);
    // No writes: do not call the settings sanitizer, which saves collection data.
    $preview = is_array($saved) ? $saved : [];
    foreach ($raw as $key => $value) {
        if ($key === 'navigation_profiles') $preview[$key] = modfarm_sanitize_navigation_profiles($value);
        elseif (is_scalar($value) && (array_key_exists($key, $preview) || preg_match('/^(nav_|mobile_nav_|footer_|book_|primary_color$|secondary_color$|background_color$|header_text_color$|body_text_color$|link_color$|button_|heading_font$|body_font$|site_title_)/', $key))) {
            $preview[$key] = sanitize_text_field($value);
        }
    }
    $identity_request = wp_unslash($_POST['identity'] ?? '');
    $identity = is_string($identity_request) ? json_decode($identity_request, true) : [];
    $identity = is_array($identity) ? $identity : [];
    $title_filter = static function ($value) use ($identity) { return isset($identity['blogname']) && is_scalar($identity['blogname']) ? sanitize_text_field($identity['blogname']) : $value; };
    $icon_filter = static function ($value) use ($identity) { if (!isset($identity['site_icon']) || !is_scalar($identity['site_icon'])) return $value; $id = absint($identity['site_icon']); return !$id || wp_attachment_is_image($id) ? $id : $value; };
    $logo_filter = static function ($value) use ($identity) { $id = absint($identity['custom_logo'] ?? $value); return !$id || wp_attachment_is_image($id) ? $id : $value; };
    add_filter('option_blogname', $title_filter);
    add_filter('pre_option_site_icon', $icon_filter);
    add_filter('theme_mod_custom_logo', $logo_filter);
    // Empty colors mean the renderer's defaults, matching cleared saved fields.
    foreach ($preview as $key => $value) if (str_ends_with($key, '_color') && $value === '') unset($preview[$key]);
    $filter = static function () use ($preview) { return $preview; };
    add_filter('pre_option_modfarm_theme_settings', $filter);
    try {
        $header = modfarm_render_navigation_menu_block(['mode' => 'header']);
        $footer = modfarm_render_navigation_menu_block(['mode' => 'footer', 'centerContent' => 'none', 'noCollapse' => true]);
        $tokens = modfarm_ppb_visualizer_inline_token_styles() . '<style>' . modfarm_nav_token_css() . '</style>';
        if (function_exists('modfarm_font_library')) {
            $catalog = modfarm_font_library();
            $families = [];
            foreach (['heading_font', 'body_font', 'site_title_font', 'nav_font'] as $key) {
                $family = modfarm_effective_font_family($preview[$key] ?? '');
                if (isset($catalog[$family])) $families[$family] = str_replace('%20', '+', rawurlencode(str_replace('+', ' ', modfarm_google_font_family_query($catalog[$family]))));
            }
            if ($families) $tokens .= '<link rel="stylesheet" href="' . esc_url('https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap') . '">';
        }
    } finally {
        remove_filter('pre_option_modfarm_theme_settings', $filter);
        remove_filter('option_blogname', $title_filter);
        remove_filter('pre_option_site_icon', $icon_filter);
        remove_filter('theme_mod_custom_logo', $logo_filter);
    }
    wp_send_json_success(['header' => $header, 'footer' => $footer, 'tokens' => $tokens]);
}
add_action('wp_ajax_modfarm_settings_appearance', 'modfarm_ajax_settings_appearance');
