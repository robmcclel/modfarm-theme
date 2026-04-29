<?php
/**
 * ModFarm Theme Settings Panel
 * Accessible under Settings > ModFarm Settings
 */

add_action('admin_menu', function () {
    add_options_page(
        __('ModFarm Theme Settings', 'modfarm-author'),
        __('ModFarm Settings', 'modfarm-author'),
        'manage_options',
        'modfarm_theme_settings',
        'modfarm_render_settings_page'
    );
});

add_action('admin_init', function () {
    register_setting('modfarm_theme_settings_group', 'modfarm_theme_settings');

    add_settings_section('modfarm_main_settings', '', null, 'modfarm_theme_settings');

    $fields = [
        'button_color'        => 'Button Background Color',
        'button_text_color'   => 'Button Text Color',
        'link_color'          => 'Link Color',
        'primary_color'       => 'Primary Theme Color',
        'secondary_color'     => 'Secondary Theme Color',
        'body_font'           => 'Body Font',
        'heading_font'        => 'Heading Font',
        'site_bg_color'       => 'Site Background Color',
        'body_text_color'     => 'Body Text Color',
        'header_text_color'   => 'Header Text Color',
        'primary_author_name' => 'Primary Author Name'
    ];

    foreach ($fields as $id => $label) {
        add_settings_field($id, $label, function () use ($id) {
            $options = get_option('modfarm_theme_settings');
            printf(
                '<input type="text" id="%1$s" name="modfarm_theme_settings[%1$s]" value="%2$s" class="regular-text code" />',
                esc_attr($id),
                esc_attr($options[$id] ?? '')
            );
        }, 'modfarm_theme_settings', 'modfarm_main_settings');
    }
});

/* function modfarm_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('ModFarm Theme Settings', 'modfarm-author'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('modfarm_theme_settings_group');
            do_settings_sections('modfarm_theme_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
} */

// Automatically create book-author term if provided
add_action('update_option_modfarm_theme_settings', function ($old, $new) {
    if (!empty($new['primary_author_name'])) {
        $name = sanitize_text_field($new['primary_author_name']);
        if (!term_exists($name, 'book-authors')) {
            wp_insert_term($name, 'book-authors');
        }
    }
}, 10, 2);