<?php
/** Shared navigation profiles. Layout patterns consume these at render time. */
function modfarm_navigation_profile_fields($mode = 'header') {
    return [
        'layoutType' => ['Layout', 'simple', ['simple'=>'Simple menu', 'split'=>'Split menus']],
        'leftMenu' => ['Main / left menu', 0, 'menu'],
        'rightMenu' => ['Right menu (split layout)', 0, 'menu'],
        'simpleAlign' => ['Menu alignment', $mode === 'footer' ? 'center' : 'left', ['left'=>'Left','center'=>'Center','right'=>'Right']],
        'centerContent' => ['Site Title', $mode === 'footer' ? 'none' : 'site-title', ['none'=>'None','site-title'=>'Site title','site-logo'=>'Site logo','site-icon'=>'Site icon','site-icon-title'=>'Icon + title']],
        'searchMode' => ['Search', $mode === 'footer' ? 'hide' : 'inherit', ['inherit'=>'Use existing Customizer setting','show'=>'Show search','hide'=>'Hide search']],
        'noCollapse' => ['Keep menu expanded on mobile', $mode === 'footer', 'bool'],
        'mobilePresentation' => ['Mobile menu presentation', 'overlay', ['below'=>'Below header','drawer'=>'Side drawer','overlay'=>'Full-screen overlay']],
        'drawerSide' => ['Drawer side', 'right', ['left'=>'Left','right'=>'Right']],
        'showDescriptions' => ['Show menu descriptions', false, 'bool'],
        'coverWidth' => ['Cover width (px)', 56, [32,120]],
        'iconSize' => ['Icon size (px)', 24, [16,64]],
        'imageGap' => ['Image / text gap (px)', 16, [4,32]],
        'dropdownWidth' => ['Dropdown width (px)', 320, [220,480]],
    ];
}
function modfarm_sanitize_navigation_profiles($input) {
    $input = is_array($input) ? $input : [];
    $clean = [];
    foreach (['header','footer'] as $mode) {
        $profile = is_array($input[$mode] ?? null) ? $input[$mode] : [];
        $clean[$mode] = ['enabled' => !empty($profile['enabled'])];
        foreach (modfarm_navigation_profile_fields($mode) as $key => [$label,$default,$type]) {
            $value = $profile[$key] ?? $default;
            if (!is_scalar($value)) $value = $default;
            if ($type === 'bool') $value = !empty($value);
            elseif ($type === 'menu') $value = absint($value);
            elseif (isset($type[0]) && is_int($type[0])) $value = max($type[0], min($type[1], (int)$value));
            elseif (!array_key_exists((string)$value, $type)) $value = $default;
            $clean[$mode][$key] = $value;
        }
    }
    return $clean;
}
function modfarm_resolve_navigation_attributes($attributes) {
    $options = get_option('modfarm_theme_settings', []);
    $mode = ($attributes['mode'] ?? 'header') === 'footer' ? 'footer' : 'header';
    $profiles = modfarm_sanitize_navigation_profiles($options['navigation_profiles'] ?? []);
    if (($attributes['settingsSource'] ?? 'inherit') !== 'local' && $profiles[$mode]['enabled']) {
        $profile = $profiles[$mode];
        unset($profile['enabled']);
        $attributes = array_merge($attributes, $profile);
    }
    return $attributes;
}
function modfarm_navigation_profiles_field() {
    $options = get_option('modfarm_theme_settings', []);
    $profiles = modfarm_sanitize_navigation_profiles($options['navigation_profiles'] ?? []);
    $menus = [0 => 'Select a menu'];
    foreach (wp_get_nav_menus() as $menu) $menus[$menu->term_id] = $menu->name;
    echo '<p class="description">Enable a shared profile to apply navigation across all content types and archives. Individual Nav blocks can choose “Use this block’s settings” for exceptions. Menu links are edited in Appearance → Menus. Footer blocks do not display branding.</p>';
    $sections = [
        ['shared', 'Shared Navigation', 'Choose which navigation profiles apply across the site.', [['header','enabled'], ['footer','enabled']]],
        ['main', 'Main Site Navigation', 'Select the menus, arrangement, site title or image, and search behavior for the main header.', array_map(static function ($key) { return ['header', $key]; }, ['layoutType','leftMenu','rightMenu','simpleAlign','centerContent','searchMode','showDescriptions','coverWidth','iconSize','imageGap','dropdownWidth'])],
        ['mobile', 'Mobile Navigation', 'Choose how the main menu opens on smaller screens. Switch preview devices and open the menu to compare.', array_map(static function ($key) { return ['header', $key]; }, ['noCollapse','mobilePresentation','drawerSide'])],
        ['footer', 'Footer Navigation', 'Choose footer menus and their alignment. Footer navigation does not display site branding.', array_map(static function ($key) { return ['footer', $key]; }, ['layoutType','leftMenu','rightMenu','simpleAlign','noCollapse','mobilePresentation','drawerSide','showDescriptions','coverWidth','iconSize','imageGap','dropdownWidth'])],
    ];
    foreach ($sections as [$section, $title, $description, $keys]) {
        echo '<section class="mf-nav-section" data-nav-section="' . esc_attr($section) . '"><h3 class="mf-group-title mf-major-title">' . esc_html($title) . '</h3><p class="description">' . esc_html($description) . '</p><table class="form-table mf-form-table"><tbody>';
        foreach ($keys as [$mode, $key]) {
            $fields = ['enabled'=>['Use shared ' . $mode . ' navigation',false,'bool']] + modfarm_navigation_profile_fields($mode);
            [$label,$default,$type] = $fields[$key];
            $name = 'modfarm_theme_settings[navigation_profiles][' . $mode . '][' . $key . ']';
            $id = 'mf-nav-' . $mode . '-' . $key;
            $value = $profiles[$mode][$key];
            echo '<tr><th scope="row"><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label></th><td>';
            if ($type === 'bool') {
                echo '<input type="hidden" name="' . esc_attr($name) . '" value="0"><input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" value="1" ' . checked($value,true,false) . '>';
            } elseif (is_array($type) && isset($type[0]) && is_int($type[0])) {
                echo '<input type="number" id="' . esc_attr($id) . '" name="' . esc_attr($name) . '" min="' . $type[0] . '" max="' . $type[1] . '" value="' . esc_attr($value) . '">';
            } else {
                $choices = $type === 'menu' ? $menus : $type;
                echo '<select id="' . esc_attr($id) . '" name="' . esc_attr($name) . '">';
                foreach ($choices as $choice => $text) echo '<option value="' . esc_attr($choice) . '" ' . selected($value,$choice,false) . '>' . esc_html($text) . '</option>';
                echo '</select>';
            }
            if ($key === 'simpleAlign') echo '<p class="description">Aligns the menu links in Simple menu mode. Split menus place links on either side of the site title or image.</p>';
            if ($key === 'layoutType') echo '<p class="description">Select the general arrangement of this navigation.</p>';
            echo '</td></tr>';
        }
        echo '</tbody></table></section>';
    }
    // Preserve existing footer values without exposing unused branding controls.
    foreach (['centerContent', 'searchMode'] as $key) echo '<input type="hidden" name="modfarm_theme_settings[navigation_profiles][footer][' . esc_attr($key) . ']" value="' . esc_attr($profiles['footer'][$key]) . '">';
}
