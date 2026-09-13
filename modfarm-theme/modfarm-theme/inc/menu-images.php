<?php
/** Native menu-item media, shared by Appearance > Menus and the Customizer. */

function mfs_menu_image_id($value) {
    if (!is_scalar($value)) return 0;
    $id = absint($value);
    return $id && wp_attachment_is_image($id) ? $id : 0;
}

add_filter('wp_setup_nav_menu_item', function ($item) {
    // Customizer preview objects already carry the pending value, including zero.
    if (!isset($item->mfs_image_id)) {
        $item->mfs_image_id = (int) get_post_meta($item->ID, '_mfs_menu_image_id', true);
    }
    return $item;
});

function mfs_menu_image_fields($id = 0, $item = null) {
    $image_id = $item ? (int) $item->mfs_image_id : 0;
    ?>
    <div class="field-mfs-image description description-wide mfs-menu-image-field">
        <p><strong><?php esc_html_e('Image', 'modfarm'); ?></strong></p>
        <div class="mfs-menu-image-preview"><?php
            if ($image_id) echo wp_get_attachment_image($image_id, 'thumbnail', false, ['alt' => '']);
        ?></div>
        <input class="mfs-menu-image-id" type="hidden" name="mfs-menu-image[<?php echo esc_attr($id); ?>]" value="<?php echo esc_attr($image_id); ?>">
        <?php if ($id) wp_nonce_field('mfs-menu-image-' . $id, 'mfs-menu-image-nonce[' . $id . ']', false); ?>
        <p><button type="button" class="button mfs-menu-image-select"><?php esc_html_e('Select / Replace Image', 'modfarm'); ?></button>
        <button type="button" class="button-link mfs-menu-image-remove"><?php esc_html_e('Remove', 'modfarm'); ?></button></p>
        <label><?php esc_html_e('Image style', 'modfarm'); ?>
            <select class="mfs-menu-image-style">
                <option value="menu-cover"><?php esc_html_e('Cover (hidden on mobile)', 'modfarm'); ?></option>
                <option value="menu-icon"><?php esc_html_e('Icon (visible on mobile)', 'modfarm'); ?></option>
                <option value="menu-icon-only"><?php esc_html_e('Icon (no text)', 'modfarm'); ?></option>
            </select>
        </label>
        <p class="description"><?php esc_html_e('Icon (no text) keeps the navigation label available to screen readers.', 'modfarm'); ?></p>
    </div>
    <?php
}
add_action('wp_nav_menu_item_custom_fields', 'mfs_menu_image_fields', 10, 2);
add_action('wp_nav_menu_item_custom_fields_customize_template', 'mfs_menu_image_fields');

add_action('wp_update_nav_menu_item', function ($menu_id, $item_id) {
    // Customizer saves through its changeset below, never through admin form data.
    if (is_customize_preview() || !current_user_can('edit_theme_options')) return;
    $nonce = $_POST['mfs-menu-image-nonce'][$item_id] ?? '';
    if (!is_string($nonce) || !wp_verify_nonce(wp_unslash($nonce), 'mfs-menu-image-' . $item_id)) return;
    if (!isset($_POST['mfs-menu-image'][$item_id])) return;
    update_post_meta($item_id, '_mfs_menu_image_id', mfs_menu_image_id($_POST['mfs-menu-image'][$item_id]));
}, 10, 2);

/** Core sanitizes menu fields to a fixed list; restore our validated extra field. */
function mfs_menu_image_customize_hook($id) {
    static $bound = [];
    if (isset($bound[$id]) || !preg_match('/^nav_menu_item\[-?\d+\]$/', $id)) return;
    $bound[$id] = true;
    add_filter('customize_sanitize_' . $id, function ($value, $setting) {
        if (!is_array($value)) return $value;
        $pending = $setting->manager->unsanitized_post_values();
        $raw = $pending[$setting->id] ?? [];
        $value['mfs_image_id'] = is_array($raw) && array_key_exists('mfs_image_id', $raw)
            ? mfs_menu_image_id($raw['mfs_image_id'])
            : (int) get_post_meta($setting->post_id, '_mfs_menu_image_id', true);
        return $value;
    }, 10, 2);
}
add_action('customize_register', function ($manager) {
    foreach ($manager->settings() as $id => $setting) mfs_menu_image_customize_hook($id);
}, 100);
add_filter('customize_dynamic_setting_args', function ($args, $id) {
    mfs_menu_image_customize_hook($id);
    return $args;
}, 100, 2);

add_action('customize_save_after', function ($manager) {
    foreach ($manager->settings() as $setting) {
        if (!($setting instanceof WP_Customize_Nav_Menu_Item_Setting)) continue;
        if (!in_array($setting->update_status, ['inserted', 'updated'], true)) continue;
        $value = $setting->post_value();
        if (is_array($value) && isset($value['mfs_image_id']) && $setting->post_id > 0) {
            // Core has now remapped negative IDs for newly created menu items.
            update_post_meta($setting->post_id, '_mfs_menu_image_id', mfs_menu_image_id($value['mfs_image_id']));
        }
    }
});

function mfs_enqueue_menu_image_editor($customizer = false) {
    wp_enqueue_media();
    $deps = $customizer ? ['jquery', 'customize-nav-menus', 'media-editor'] : ['jquery', 'nav-menu', 'media-editor'];
    wp_enqueue_script('mfs-menu-images', get_template_directory_uri() . '/assets/js/menu-images.js', $deps,
        filemtime(get_template_directory() . '/assets/js/menu-images.js'), true);
    wp_enqueue_style('mfs-menu-images', get_template_directory_uri() . '/assets/css/menu-images.css', [],
        filemtime(get_template_directory() . '/assets/css/menu-images.css'));
}
add_action('admin_enqueue_scripts', function ($screen) {
    if ($screen === 'nav-menus.php') mfs_enqueue_menu_image_editor();
});
add_action('customize_controls_enqueue_scripts', function () { mfs_enqueue_menu_image_editor(true); });

// Expose Image beside the native advanced menu properties in both editors.
add_filter('manage_nav-menus_columns', function ($columns) {
    $columns['mfs-image'] = __('Image', 'modfarm');
    return $columns;
}, 20);
add_filter('default_hidden_columns', function ($hidden, $screen) {
    if ($screen->id === 'nav-menus') $hidden = array_unique(array_merge($hidden, ['mfs-image', 'description']));
    return $hidden;
}, 10, 2);

// Existing saved Screen Options omit newly introduced columns, which otherwise
// makes them checked automatically. Require an explicit choice for these fields.
add_filter('hidden_columns', function ($hidden, $screen) {
    if ($screen->id !== 'nav-menus') return $hidden;
    $choices = get_user_meta(get_current_user_id(), '_mfs_menu_optional_fields', true);
    foreach (['mfs-image', 'description'] as $field) {
        if (empty($choices[$field])) $hidden[] = $field;
    }
    return array_unique($hidden);
}, 20, 2);
function mfs_record_menu_field_choices($meta_id, $user_id, $key, $value) {
    if ($key !== 'managenav-menuscolumnshidden' || !is_array($value)) return;
    update_user_meta($user_id, '_mfs_menu_optional_fields', [
        'mfs-image' => !in_array('mfs-image', $value, true),
        'description' => !in_array('description', $value, true),
    ]);
}
add_filter('update_user_metadata', function ($check, $user_id, $key, $value) {
    // Runs even when the chosen hidden-column list equals a previously saved list.
    if ($check === null) mfs_record_menu_field_choices(0, $user_id, $key, $value);
    return $check;
}, 10, 4);

/** Only enhance menus explicitly rendered by our navigation block. */
add_filter('nav_menu_item_title', function ($title, $item, $args, $depth) {
    if (empty($args->mfs_enhanced)) return $title;
    $image_id = (int) ($item->mfs_image_id ?? 0);
    $image = $image_id ? wp_get_attachment_image($image_id, 'medium', false, [
        'class' => 'mfs-menu-image', 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '120px',
    ]) : '';
    $description = !empty($args->mfs_descriptions) && trim($item->description ?? '') !== ''
        ? '<span class="mfs-menu-description">' . esc_html(wp_strip_all_tags($item->description)) . '</span>' : '';
    $icon_only = $image !== '' && in_array('menu-icon-only', (array)($item->classes ?? []), true);
    return $image . '<span class="mfs-menu-copy' . ($icon_only ? ' mfs-menu-copy--hidden' : '') . '"><span class="mfs-menu-label">' . $title . '</span>' . ($icon_only ? '' : $description) . '</span>';
}, 10, 4);
add_filter('nav_menu_css_class', function ($classes, $item, $args) {
    if (!empty($args->mfs_enhanced) && !empty($item->mfs_image_id)) {
        $classes[] = 'mfs-has-menu-image';
        if (in_array('menu-icon-only', $classes, true)) $classes[] = 'menu-icon';
        if (!in_array('menu-icon', $classes, true) && !in_array('menu-cover', $classes, true)) $classes[] = 'menu-cover';
    }
    return $classes;
}, 10, 3);
