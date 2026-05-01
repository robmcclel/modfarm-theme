<?php
/**
 * ModFarm Theme Settings Panel
 *
 * DROP-IN VERSION (with PPB pattern dropdowns filtered by Pattern Category Ã¢â‚¬Å“lanesÃ¢â‚¬Â)
 */

/**
 * Register the settings and (legacy) sections/fields.
 * We still register them for sanitation/compatibility, but the UI is custom.
 */
function modfarm_register_settings() {
    register_setting(
        'modfarm_theme_settings_group',
        'modfarm_theme_settings',
        'modfarm_sanitize_settings'
    );

    // Legacy sections Ã¢â‚¬â€œ kept for compatibility but not used by do_settings_sections().
    add_settings_section('modfarm_section_colors',      'Colors',                     null, 'modfarm_theme_settings');
    add_settings_section('modfarm_section_fonts',       'Fonts',                      null, 'modfarm_theme_settings');
    add_settings_section('modfarm_section_navigation',  'Navigation',                 null, 'modfarm_theme_settings');
    add_settings_section('modfarm_section_layout',      'Layout',                     null, 'modfarm_theme_settings');
    add_settings_section('modfarm_section_templates',   'Default Layout Templates',   null, 'modfarm_theme_settings');
    add_settings_section('modfarm_section_book_cards',  'Book Cards & Buttons',       null, 'modfarm_theme_settings');

    // === Color fields (existing) ===
    modfarm_add_color_field('primary_color',       'Primary Color',             'modfarm_section_colors');
    modfarm_add_color_field('header_text_color',   'Header Text Color',         'modfarm_section_colors');
    modfarm_add_color_field('body_text_color',     'Body Text Color',           'modfarm_section_colors');
    modfarm_add_color_field('link_color',          'Link Color',                'modfarm_section_colors');
    modfarm_add_color_field('button_color',        'Button Background Color',   'modfarm_section_colors');
    modfarm_add_color_field('button_text_color',   'Button Text Color',         'modfarm_section_colors');
    modfarm_add_color_field('background_color',    'Site Background Color',     'modfarm_section_colors');
    modfarm_add_color_field('secondary_color',     'Secondary Color',           'modfarm_section_colors');

    // === Book Cards & Buttons Ã¢â‚¬â€œ COLORS (already working) ===
    add_settings_field(
        'book_card_button_bg_color',
        'Primary Button Background',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_button_bg_color']
    );

    add_settings_field(
        'book_card_button_text_color',
        'Primary Button Text Color',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_button_text_color']
    );

    add_settings_field(
        'book_card_button_border_color',
        'Primary Button Border Color',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_button_border_color']
    );

    // Audio/sample CTA
    add_settings_field(
        'book_card_sample_bg_color',
        'Sample Button Background',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_sample_bg_color']
    );

    add_settings_field(
        'book_card_sample_text_color',
        'Sample Button Text Color',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_sample_text_color']
    );

    add_settings_field(
        'book_card_sample_border_color',
        'Sample Button Border Color',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_sample_border_color']
    );

    // Pagination theme
    add_settings_field(
        'book_card_pagination_accent_color',
        'Pagination Color',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_pagination_accent_color']
    );

    // === Book Cards & Buttons Ã¢â‚¬â€œ SHAPE / LAYOUT / EFFECT ===
    add_settings_field(
        'book_card_cover_shape',
        'Cover Shape (Default)',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        [
            'id' => 'book_card_cover_shape',
            'options' => [
                ''         => 'Square (inherit)',
                'rounded'  => 'Rounded corners',
            ],
        ]
    );

    add_settings_field(
        'book_card_button_shape',
        'Primary Button Shape (Default)',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        [
            'id' => 'book_card_button_shape',
            'options' => [
                ''         => 'Square',
                'rounded'  => 'Rounded',
                'pill'     => 'Pill',
            ],
        ]
    );

    add_settings_field(
        'book_card_sample_shape',
        'Sample Button Shape (Default)',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        [
            'id' => 'book_card_sample_shape',
            'options' => [
                ''         => 'Square',
                'rounded'  => 'Rounded',
                'pill'     => 'Pill',
            ],
        ]
    );

    add_settings_field(
        'book_card_cta_mode',
        'CTA Spacing (Default)',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        [
            'id' => 'book_card_cta_mode',
            'options' => [
                ''        => 'Auto / Theme default',
                'joined'  => 'Joined (button touches cover)',
                'gap'     => 'Gap (space between cover & button)',
            ],
        ]
    );

    add_settings_field(
        'book_card_shadow_style',
        'Shadow Style (Default)',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        [
            'id' => 'book_card_shadow_style',
            'options' => [
                ''           => 'Flat (no extra shadows)',
                'shadow-sm'  => 'Small shadow',
                'shadow-md'  => 'Medium shadow',
                'shadow-lg'  => 'Large shadow',
                'emboss'     => 'Embossed (inset effect)',
            ],
        ]
    );

    // === Book Cards & Buttons Ã¢â‚¬â€œ VISIBILITY TOGGLES ===
    add_settings_field(
        'book_card_hide_title',
        'Hide Title on Book Cards',
        'modfarm_checkbox_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_hide_title']
    );

    add_settings_field(
        'book_card_hide_series',
        'Hide Series on Book Cards',
        'modfarm_checkbox_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_hide_series']
    );

    add_settings_field(
        'book_card_hide_primary_button',
        'Hide Primary Button on Book Cards',
        'modfarm_checkbox_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_hide_primary_button']
    );

    add_settings_field(
        'book_card_hide_sample_button',
        'Hide Sample Button on Book Cards',
        'modfarm_checkbox_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_card_hide_sample_button']
    );

    // === Book Page Buttons (NEW) ===
    // Primary (Filled)
    add_settings_field(
        'book_page_primary_bg_color',
        'Book Page Primary Button Background',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_primary_bg_color']
    );

    add_settings_field(
        'book_page_primary_text_color',
        'Book Page Primary Button Text',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_primary_text_color']
    );

    add_settings_field(
        'book_page_primary_border_color',
        'Book Page Primary Button Border',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_primary_border_color']
    );

    // Secondary (Outline)
    add_settings_field(
        'book_page_secondary_bg_color',
        'Book Page Secondary Button Background',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_secondary_bg_color']
    );

    add_settings_field(
        'book_page_secondary_text_color',
        'Book Page Secondary Button Text',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_secondary_text_color']
    );

    add_settings_field(
        'book_page_secondary_border_color',
        'Book Page Secondary Button Border',
        'modfarm_color_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_secondary_border_color']
    );

    // Shared
    add_settings_field(
        'book_page_button_border_width',
        'Book Page Button Border Width (px)',
        'modfarm_text_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_button_border_width']
    );

    add_settings_field(
        'book_page_button_radius',
        'Book Page Button Border Radius (px)',
        'modfarm_text_field',
        'modfarm_theme_settings',
        'modfarm_section_book_cards',
        ['id' => 'book_page_button_radius']
    );

    // === Fonts (existing) ===
    add_settings_field('heading_font',     'Heading Font',     'modfarm_font_dropdown',  'modfarm_theme_settings', 'modfarm_section_fonts',      ['id' => 'heading_font']);
    add_settings_field('body_font',        'Body Font',        'modfarm_font_dropdown',  'modfarm_theme_settings', 'modfarm_section_fonts',      ['id' => 'body_font']);
    add_settings_field('site_title_font',  'Site Title Font',  'modfarm_font_dropdown',  'modfarm_theme_settings', 'modfarm_section_fonts',      ['id' => 'site_title_font']);
    add_settings_field('nav_font',         'Navigation Font',  'modfarm_font_dropdown',  'modfarm_theme_settings', 'modfarm_section_fonts',      ['id' => 'nav_font']);

    // === Navigation (existing) ===
    add_settings_field('nav_bg_color',     'Nav Background Color',    'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'nav_bg_color']);
    add_settings_field('nav_text_color',   'Nav Text Color',          'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'nav_text_color']);
    add_settings_field('nav_hover_color',  'Nav Hover Color',         'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'nav_hover_color']);
    add_settings_field('submenu_bg_color', 'Submenu Background Color','modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'submenu_bg_color']);
    add_settings_field('submenu_text_color','Submenu Text Color',     'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'submenu_text_color']);

    // === Footer Navigation (NEW) ===
    add_settings_field(
        'footer_nav_mode',
        'Footer Nav Style Mode',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_navigation',
        [
            'id'      => 'footer_nav_mode',
            'options' => [
                'inherit' => 'Inherit Header Nav',
                'manual'  => 'Manual Footer Colors',
                'auto'    => 'Auto (Contrast from Site Background)',
            ],
        ]
    );

    add_settings_field('footer_nav_bg_color',      'Footer Nav Background Color',     'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'footer_nav_bg_color']);
    add_settings_field('footer_nav_text_color',    'Footer Nav Text Color',           'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'footer_nav_text_color']);
    add_settings_field('footer_nav_hover_color',   'Footer Nav Hover Color',          'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'footer_nav_hover_color']);
    add_settings_field('footer_submenu_bg_color',  'Footer Submenu Background Color', 'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'footer_submenu_bg_color']);
    add_settings_field('footer_submenu_text_color','Footer Submenu Text Color',       'modfarm_color_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'footer_submenu_text_color']);

    add_settings_field('footer_nav_transparent',   'Footer Transparent Background',   'modfarm_checkbox_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'footer_nav_transparent']);

    add_settings_field(
        'nav_font_size',
        'Nav Font Size',
        'modfarm_text_field',
        'modfarm_theme_settings',
        'modfarm_section_navigation',
        ['id' => 'nav_font_size']
    );

    add_settings_field(
        'nav_padding',
        'Nav Padding',
        'modfarm_select_field',
        'modfarm_theme_settings',
        'modfarm_section_navigation',
        [
            'id'      => 'nav_padding',
            'options' => [
                'compact'  => 'Compact',
                'regular'  => 'Regular',
                'spacious' => 'Spacious',
            ],
        ]
    );

    // Logo sizing
    add_settings_field('nav_logo_max_width',  'Logo Max Width (px)',  'modfarm_text_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id'=>'nav_logo_max_width']);
    add_settings_field('nav_logo_max_height', 'Logo Max Height (px)', 'modfarm_text_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id'=>'nav_logo_max_height']);

    // Icon sizing
    add_settings_field('nav_icon_max_width',  'Icon Max Width (px)',  'modfarm_text_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id'=>'nav_icon_max_width']);
    add_settings_field('nav_icon_max_height', 'Icon Max Height (px)', 'modfarm_text_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id'=>'nav_icon_max_height']);

    // Brand spacing
    add_settings_field('nav_brand_gap', 'Brand IconÃ¢â‚¬â€œTitle Gap (px)', 'modfarm_text_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id'=>'nav_brand_gap']);

    add_settings_field('nav_transparent', 'Transparent Background', 'modfarm_checkbox_field', 'modfarm_theme_settings', 'modfarm_section_navigation', ['id' => 'nav_transparent']);

    // === Layout (existing) ===
    add_settings_field('content_width', 'Content Width (e.g., 1200px or 90%)', 'modfarm_text_field', 'modfarm_theme_settings', 'modfarm_section_layout', ['id' => 'content_width']);

    // === Template pattern selectors (existing) ===
    // BOOK Layout
    add_settings_field('book_header_pattern', 'Book Header Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'book_header_pattern']);
    add_settings_field('book_body_pattern',   'Book Body Pattern',    'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'book_body_pattern']);
    add_settings_field('book_footer_pattern', 'Book Footer Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'book_footer_pattern']);

    // PAGE Layout
    add_settings_field('page_header_pattern', 'Page Header Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'page_header_pattern']);
    add_settings_field('page_body_pattern',   'Page Body Pattern',    'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'page_body_pattern']);
    add_settings_field('page_footer_pattern', 'Page Footer Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'page_footer_pattern']);

    // POST Layout
    add_settings_field('post_header_pattern', 'Post Header Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'post_header_pattern']);
    add_settings_field('post_body_pattern',   'Post Body Pattern',    'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'post_body_pattern']);
    add_settings_field('post_footer_pattern', 'Post Footer Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'post_footer_pattern']);

    // ARCHIVE Layout
    add_settings_field('archive_header_pattern',              'Archive Header Pattern',      'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'archive_header_pattern']);
    add_settings_field('archive_body_pattern',                'Archive Body Pattern',        'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'archive_body_pattern']);
    add_settings_field('archive_body_pattern_book_series',    'Book Series Archive Pattern', 'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'archive_body_pattern_book_series']);
    add_settings_field('archive_body_pattern_book_genre',     'Genre Archive Pattern',       'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'archive_body_pattern_book_genre']);
    add_settings_field('archive_body_pattern_book_authors',   'Author Archive Pattern',      'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'archive_body_pattern_book_authors']);
    add_settings_field('archive_footer_pattern',              'Archive Footer Pattern',      'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'archive_footer_pattern']);
}
add_action('admin_init', 'modfarm_register_settings');


/** Helper field callbacks (unchanged) **/

function modfarm_add_color_field($id, $label, $section) {
    add_settings_field($id, $label, function () use ($id) {
        $options = get_option('modfarm_theme_settings');
        $value = $options[$id] ?? '';
        echo '<input type="text" class="modfarm-color-field" name="modfarm_theme_settings[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" />';
    }, 'modfarm_theme_settings', $section);
}

function modfarm_font_dropdown($args) {
    $id = $args['id'];
    $options = get_option('modfarm_theme_settings');
    $value = $options[$id] ?? '';

    $fonts = [
        'Inter, sans-serif'        => 'Inter',
        'Merriweather, serif'      => 'Merriweather',
        'Lato, sans-serif'         => 'Lato',
        'Playfair Display, serif'  => 'Playfair Display',
        'Arvo, serif'              => 'Arvo',
        'Roboto, sans-serif'       => 'Roboto',
    ];

    echo '<select name="modfarm_theme_settings[' . esc_attr($id) . ']">';
    foreach ($fonts as $css_val => $label) {
        echo '<option value="' . esc_attr($css_val) . '" ' . selected($value, $css_val, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function modfarm_text_field($args) {
    $id = $args['id'];
    $options = get_option('modfarm_theme_settings');
    $value = $options[$id] ?? '';
    echo '<input type="text" name="modfarm_theme_settings[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" />';
}

function modfarm_color_field($args) {
    $id = $args['id'];
    $options = get_option('modfarm_theme_settings');
    $value = $options[$id] ?? '';
    echo '<input type="text" class="modfarm-color-field" name="modfarm_theme_settings[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" />';
}

function modfarm_select_field($args) {
    $id = $args['id'];
    $options = $args['options'] ?? [];
    $settings = get_option('modfarm_theme_settings');
    $value = $settings[$id] ?? '';

    echo '<select name="modfarm_theme_settings[' . esc_attr($id) . ']">';
    foreach ($options as $key => $label) {
        echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function modfarm_checkbox_field($args) {
    $id = $args['id'];
    $options = get_option('modfarm_theme_settings');
    $value = $options[$id] ?? '';
    echo '<input type="checkbox" name="modfarm_theme_settings[' . esc_attr($id) . ']" value="1" ' . checked($value, '1', false) . ' />';
}

/**
 * ============================================================
 * PPB Pattern Lanes (NEW)
 * ============================================================
 *
 * We filter dropdown options by block pattern category so PPB
 * selectors don't get cluttered.
 *
 * IMPORTANT: Categories MUST match those you register in functions.php.
 */
function modfarm_ppb_pattern_category_map(): array {
    return [
        // Book Layout
        'book_header_pattern' => 'modfarm-book-header',
        'book_body_pattern'   => 'modfarm-book-body',
        'book_footer_pattern' => 'modfarm-book-footer',

        // Page Layout
        'page_header_pattern' => 'modfarm-page-header',
        'page_body_pattern'   => 'modfarm-page-body',
        'page_footer_pattern' => 'modfarm-page-footer',

        // Post Layout
        'post_header_pattern' => 'modfarm-post-header',
        'post_body_pattern'   => 'modfarm-post-body',
        'post_footer_pattern' => 'modfarm-post-footer',

        // Archive Layout
        'archive_header_pattern' => 'modfarm-archive-header',
        'archive_body_pattern'   => 'modfarm-archive-body',
        'archive_footer_pattern' => 'modfarm-archive-footer',

        // Archive overrides
        'archive_body_pattern_book_series'  => 'modfarm-archive-body',
        'archive_body_pattern_book_genre'   => 'modfarm-archive-body',
        'archive_body_pattern_book_authors' => 'modfarm-archive-body',
    ];
}

/**
 * Return registered patterns limited to a block-pattern category slug.
 *
 * @param string $category_slug e.g. "modfarm-book-header"
 * @param bool   $include_user_patterns include user/* patterns if they are registered with that category
 * @return array [slug => title]
 */
function modfarm_get_registered_patterns_by_category(string $category_slug, bool $include_user_patterns = true): array {
    $patterns = [];

    if (!class_exists('WP_Block_Patterns_Registry') || did_action('init') === 0) {
        return $patterns;
    }

    $registry = WP_Block_Patterns_Registry::get_instance();

    foreach ($registry->get_all_registered() as $pattern) {
        $slug  = $pattern['name']  ?? ($pattern['slug'] ?? '');
        $title = $pattern['title'] ?? '';

        if (!$slug || !$title) continue;

        if (!$include_user_patterns && str_starts_with($slug, 'user/')) {
            continue;
        }

        $cats = $pattern['categories'] ?? [];
        if (!is_array($cats)) $cats = [];

        if (in_array($category_slug, $cats, true)) {
            $patterns[$slug] = $title;
        }
    }

    asort($patterns, SORT_NATURAL | SORT_FLAG_CASE);

    return $patterns;
}

/**
 * Return patterns for a given PPB field ID using the lane map.
 *
 * If a field isn't mapped, fall back to previous behavior:
 * show all modfarm/* + user/* patterns.
 */
function modfarm_get_registered_patterns_for_field(string $field_id): array {
    $map = modfarm_ppb_pattern_category_map();

    if (!empty($map[$field_id])) {
        return modfarm_get_registered_patterns_by_category($map[$field_id], true);
    }

    // Fallback: old behavior (all modfarm/* + user/*)
    $patterns = [];

    if (!class_exists('WP_Block_Patterns_Registry') || did_action('init') === 0) {
        return $patterns;
    }

    $registry = WP_Block_Patterns_Registry::get_instance();

    foreach ($registry->get_all_registered() as $pattern) {
        $slug  = $pattern['name']  ?? ($pattern['slug'] ?? '');
        $title = $pattern['title'] ?? '';

        if (!$slug || !$title) continue;

        if (str_starts_with($slug, 'modfarm/') || str_starts_with($slug, 'user/')) {
            $patterns[$slug] = $title;
        }
    }

    asort($patterns, SORT_NATURAL | SORT_FLAG_CASE);

    return $patterns;
}

/**
 * Pattern dropdown (PPB selector) Ã¢â‚¬â€œ now category-scoped by lane map.
 */
function modfarm_pattern_dropdown($args) {
    $id      = $args['id'];
    $options = get_option('modfarm_theme_settings');
    $value   = $options[$id] ?? '';
    $selected_value = modfarm_ppb_normalize_admin_pattern_value($value);

    $patterns = modfarm_get_registered_patterns_for_field($id);

    echo '<select name="modfarm_theme_settings[' . esc_attr($id) . ']">';
    echo '<option value="default"' . selected($selected_value, 'default', false) . '>' . esc_html__('Default', 'modfarm') . '</option>';

    foreach ($patterns as $slug => $title) {
        echo '<option value="' . esc_attr($slug) . '" ' . selected($selected_value, $slug, false) . '>' . esc_html($title) . '</option>';
    }

    echo '</select>';

    // Optional lane hint (comment out if you don't want it)
    $map = modfarm_ppb_pattern_category_map();
    if (!empty($map[$id])) {
        echo '<p class="description">Showing patterns in: <code>' . esc_html($map[$id]) . '</code></p>';
    }
}

/**
 * Normalize PPB admin dropdown values so legacy blank/none states show as the
 * explicit "Default" choice in the UI.
 */
function modfarm_ppb_normalize_admin_pattern_value($value): string {
    if (!is_string($value)) {
        return 'default';
    }

    $normalized = strtolower(trim($value, " \t\n\r\0\x0B-—"));
    if ($normalized === '' || $normalized === 'none' || $normalized === 'default') {
        return 'default';
    }

    if (function_exists('modfarm_ppb_normalize_slug')) {
        $mapped = modfarm_ppb_normalize_slug($value);
        if ($mapped !== '') {
            return $mapped;
        }
    }

    return $value;
}

/**
 * Build supported content-type options for Apply All preview.
 */
function modfarm_get_ppb_apply_all_content_types(): array {
    $types = [
        'book' => 'Books',
        'page' => 'Pages',
        'post' => 'Posts',
    ];

    if (post_type_exists('offer')) {
        $types['offer'] = 'Offers';
    }

    return $types;
}

/**
 * Build a lightweight pattern matrix for the Apply All preview UI.
 */
function modfarm_get_ppb_apply_all_pattern_matrix(): array {
    $matrix = [];

    foreach (array_keys(modfarm_get_ppb_apply_all_content_types()) as $post_type) {
        foreach (['header', 'body', 'footer'] as $zone) {
            $field_id = function_exists('modfarm_ppb_get_field_id_for_post_zone')
                ? modfarm_ppb_get_field_id_for_post_zone($post_type, $zone)
                : '';
            $matrix[$post_type][$zone] = [];

            if ($field_id === '') {
                continue;
            }

            foreach (modfarm_get_registered_patterns_for_field($field_id) as $slug => $title) {
                $matrix[$post_type][$zone][] = [
                    'value' => $slug,
                    'label' => $title,
                ];
            }
        }
    }

    return $matrix;
}

/**
 * Render the Apply All preview report as admin HTML.
 */
function modfarm_render_ppb_apply_all_preview_markup(array $report): string {
    $totals = $report['totals'] ?? [];
    $items = $report['items'] ?? [];
    $content_type_label = modfarm_get_ppb_apply_all_content_types()[$report['content_type'] ?? ''] ?? ucfirst((string) ($report['content_type'] ?? 'Items'));
    $zone_label = ucfirst((string) ($report['zone'] ?? 'zone'));
    $pattern_slug = (string) ($report['pattern'] ?? '');

    ob_start();
    ?>
    <div class="mf-ppb-preview-report">
        <div class="mf-ppb-preview-header">
            <div>
                <h4>Apply All Preview</h4>
                <p>
                    <?php echo esc_html($content_type_label); ?> ·
                    <?php echo esc_html($zone_label); ?> Zone ·
                    <?php echo $pattern_slug !== '' ? esc_html($pattern_slug) : esc_html__('No pattern selected', 'modfarm'); ?>
                </p>
            </div>
        </div>

        <div class="mf-ppb-preview-stats">
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Total items</span>
                <strong><?php echo esc_html((string) ($totals['items'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Will update</span>
                <strong><?php echo esc_html((string) ($totals['will_update'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Skipped locked</span>
                <strong><?php echo esc_html((string) ($totals['skipped_locked'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Skipped legacy/unzoned</span>
                <strong><?php echo esc_html((string) ($totals['skipped_legacy'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Slot content detected</span>
                <strong><?php echo esc_html((string) ($totals['slot_content_detected'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Potential conflicts</span>
                <strong><?php echo esc_html((string) ($totals['potential_conflicts'] ?? 0)); ?></strong>
            </div>
        </div>

        <div class="mf-ppb-preview-list">
            <h5>Affected items</h5>
            <?php if (empty($items)) : ?>
                <p class="description">No matching items were found for this preview.</p>
            <?php else : ?>
                <ul class="mf-ppb-preview-items">
                    <?php foreach ($items as $item) : ?>
                        <?php
                        $status_class = 'is-skip';
                        if (($item['action'] ?? '') === 'will_update') {
                            $status_class = 'is-update';
                        } elseif (($item['action'] ?? '') === 'skip_locked') {
                            $status_class = 'is-locked';
                        }
                        $status_label = [
                            'will_update' => 'Will update',
                            'skip_locked' => 'Skipped locked',
                            'skip_legacy' => 'Skipped legacy/unzoned',
                        ][$item['action'] ?? 'skip_legacy'];
                        ?>
                        <li class="mf-ppb-preview-item">
                            <div class="mf-ppb-preview-item__top">
                                <strong>
                                    <?php if (!empty($item['edit_link'])) : ?>
                                        <a href="<?php echo esc_url($item['edit_link']); ?>"><?php echo esc_html($item['title'] ?? 'Untitled'); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html($item['title'] ?? 'Untitled'); ?>
                                    <?php endif; ?>
                                </strong>
                                <span class="mf-ppb-preview-pill <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                            </div>
                            <div class="mf-ppb-preview-item__meta">
                                <span><?php echo esc_html($item['content_state'] ?? 'Unknown'); ?></span>
                                <span><?php echo esc_html($item['layout_mode'] ?? 'Unknown layout'); ?></span>
                                <span>Status: <?php echo esc_html($item['status'] ?? 'unknown'); ?></span>
                                <?php if (!empty($item['zone']['locked'])) : ?>
                                    <span>Locked</span>
                                <?php endif; ?>
                                <?php if (!empty($item['zone']['contains_content_slot'])) : ?>
                                    <span>Content-slot preserved</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['notes'])) : ?>
                                <div class="mf-ppb-preview-item__notes">
                                    <?php echo esc_html(implode(' ', $item['notes'])); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render a compact execution summary for Apply All zoned runs.
 */
function modfarm_render_ppb_apply_all_result_markup(array $result): string {
    $totals = $result['totals'] ?? [];
    $content_type_label = modfarm_get_ppb_apply_all_content_types()[$result['content_type'] ?? ''] ?? ucfirst((string) ($result['content_type'] ?? 'Items'));
    $zone_label = ucfirst((string) ($result['zone'] ?? 'zone'));
    $pattern_slug = (string) ($result['pattern'] ?? '');
    $updated_items = $result['updated_items'] ?? [];
    $failed_items = $result['failed_items'] ?? [];

    ob_start();
    ?>
    <div class="mf-ppb-preview-report mf-ppb-preview-report--result">
        <div class="mf-ppb-preview-header">
            <div>
                <h4>Apply Result</h4>
                <p>
                    <?php echo esc_html($content_type_label); ?> ·
                    <?php echo esc_html($zone_label); ?> Zone ·
                    <?php echo esc_html($pattern_slug); ?>
                </p>
            </div>
        </div>

        <div class="mf-ppb-preview-stats">
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Updated</span>
                <strong><?php echo esc_html((string) ($totals['updated'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Skipped locked</span>
                <strong><?php echo esc_html((string) ($totals['skipped_locked'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Skipped legacy/unzoned</span>
                <strong><?php echo esc_html((string) ($totals['skipped_legacy'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Slot content preserved</span>
                <strong><?php echo esc_html((string) ($totals['slot_content_preserved'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Potential conflicts</span>
                <strong><?php echo esc_html((string) ($totals['potential_conflicts'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Failed</span>
                <strong><?php echo esc_html((string) ($totals['failed'] ?? 0)); ?></strong>
            </div>
        </div>

        <?php if (!empty($updated_items)) : ?>
            <div class="mf-ppb-preview-list">
                <h5>Updated items</h5>
                <ul class="mf-ppb-preview-items">
                    <?php foreach ($updated_items as $item) : ?>
                        <li class="mf-ppb-preview-item">
                            <div class="mf-ppb-preview-item__top">
                                <strong><?php echo esc_html($item['title'] ?? 'Untitled'); ?></strong>
                                <span class="mf-ppb-preview-pill is-update">Updated</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($failed_items)) : ?>
            <div class="mf-ppb-preview-list">
                <h5>Failed items</h5>
                <ul class="mf-ppb-preview-items">
                    <?php foreach ($failed_items as $item) : ?>
                        <li class="mf-ppb-preview-item">
                            <div class="mf-ppb-preview-item__top">
                                <strong><?php echo esc_html($item['title'] ?? 'Untitled'); ?></strong>
                                <span class="mf-ppb-preview-pill is-skip">Failed</span>
                            </div>
                            <?php if (!empty($item['message'])) : ?>
                                <div class="mf-ppb-preview-item__notes"><?php echo esc_html($item['message']); ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * AJAX preview endpoint for read-only Apply All analysis.
 */
function modfarm_ajax_ppb_apply_all_preview() {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('You do not have permission to run this preview.', 'modfarm')], 403);
    }

    check_ajax_referer('modfarm_ppb_apply_all_preview', 'nonce');

    $content_type = sanitize_key((string) ($_POST['contentType'] ?? ''));
    $zone = sanitize_key((string) ($_POST['zone'] ?? ''));
    $pattern = sanitize_text_field(wp_unslash((string) ($_POST['pattern'] ?? '')));

    if (!isset(modfarm_get_ppb_apply_all_content_types()[$content_type])) {
        wp_send_json_error(['message' => __('Unsupported content type for preview.', 'modfarm')], 400);
    }

    if (!in_array($zone, ['header', 'body', 'footer'], true)) {
        wp_send_json_error(['message' => __('Unsupported zone for preview.', 'modfarm')], 400);
    }

    $pattern_matrix = modfarm_get_ppb_apply_all_pattern_matrix();
    $available_patterns = $pattern_matrix[$content_type][$zone] ?? [];
    $valid_pattern_values = wp_list_pluck($available_patterns, 'value');

    if ($pattern === '' || !in_array($pattern, $valid_pattern_values, true)) {
        wp_send_json_error(['message' => __('Select a valid pattern before running the preview.', 'modfarm')], 400);
    }

    $report = modfarm_get_ppb_apply_all_preview_report($content_type, $zone, $pattern);

    wp_send_json_success([
        'html' => modfarm_render_ppb_apply_all_preview_markup($report),
        'report' => $report,
    ]);
}
add_action('wp_ajax_modfarm_ppb_apply_all_preview', 'modfarm_ajax_ppb_apply_all_preview');

/**
 * AJAX execution endpoint for safe Apply All runs on zoned header/body/footer only.
 */
function modfarm_ajax_ppb_apply_all_execute() {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('You do not have permission to run this action.', 'modfarm')], 403);
    }

    check_ajax_referer('modfarm_ppb_apply_all_execute', 'nonce');

    $content_type = sanitize_key((string) ($_POST['contentType'] ?? ''));
    $zone = sanitize_key((string) ($_POST['zone'] ?? ''));
    $pattern = sanitize_text_field(wp_unslash((string) ($_POST['pattern'] ?? '')));

    if (!isset(modfarm_get_ppb_apply_all_content_types()[$content_type])) {
        wp_send_json_error(['message' => __('Unsupported content type for Apply All.', 'modfarm')], 400);
    }

    if (!in_array($zone, ['header', 'body', 'footer'], true)) {
        wp_send_json_error(['message' => __('Apply All execution is currently limited to Header, Body, and Footer zones.', 'modfarm')], 400);
    }

    $pattern_matrix = modfarm_get_ppb_apply_all_pattern_matrix();
    $available_patterns = $pattern_matrix[$content_type][$zone] ?? [];
    $valid_pattern_values = wp_list_pluck($available_patterns, 'value');
    if ($pattern === '' || !in_array($pattern, $valid_pattern_values, true)) {
        wp_send_json_error(['message' => __('Select a valid pattern before applying changes.', 'modfarm')], 400);
    }

    $preview = modfarm_get_ppb_apply_all_preview_report($content_type, $zone, $pattern);
    $result = [
        'content_type' => $content_type,
        'zone' => $zone,
        'pattern' => $pattern,
        'totals' => [
            'updated' => 0,
            'skipped_locked' => (int) ($preview['totals']['skipped_locked'] ?? 0),
            'skipped_legacy' => (int) ($preview['totals']['skipped_legacy'] ?? 0),
            'slot_content_preserved' => 0,
            'potential_conflicts' => (int) ($preview['totals']['potential_conflicts'] ?? 0),
            'failed' => 0,
        ],
        'updated_items' => [],
        'failed_items' => [],
    ];

    foreach (($preview['items'] ?? []) as $item) {
        if (($item['action'] ?? '') !== 'will_update') {
            continue;
        }

        $post_id = (int) ($item['post_id'] ?? 0);
        $preserves_slots = !empty($item['zone']['contains_content_slot']);
        $updated = function_exists('modfarm_ppb_replace_post_zone_with_pattern')
            ? modfarm_ppb_replace_post_zone_with_pattern($post_id, $zone, $pattern)
            : false;

        if ($updated) {
            $result['totals']['updated']++;
            if ($preserves_slots) {
                $result['totals']['slot_content_preserved']++;
            }
            $result['updated_items'][] = [
                'post_id' => $post_id,
                'title' => $item['title'] ?? sprintf('#%d', $post_id),
            ];
            continue;
        }

        $result['totals']['failed']++;
        $result['failed_items'][] = [
            'post_id' => $post_id,
            'title' => $item['title'] ?? sprintf('#%d', $post_id),
            'message' => __('Replacement did not produce a content change.', 'modfarm'),
        ];
    }

    wp_send_json_success([
        'html' => modfarm_render_ppb_apply_all_result_markup($result),
        'result' => $result,
    ]);
}
add_action('wp_ajax_modfarm_ppb_apply_all_execute', 'modfarm_ajax_ppb_apply_all_execute');


/**
 * Sanitization: keep as-is, just aware of all keys.
 */
function modfarm_sanitize_settings($settings) {
    $allowed_keys = [
        'primary_color',
        'header_text_color',
        'body_text_color',
        'link_color',
        'button_color',
        'button_text_color',
        'background_color',
        'secondary_color',
        'heading_font',
        'body_font',
        'site_title_font',
        'nav_font',
        'nav_font_size',
        'nav_bg_color',
        'nav_text_color',
        'nav_hover_color',
        'submenu_bg_color',
        'submenu_text_color',
        'nav_padding',
        'nav_logo_max_width',
        'nav_logo_max_height',
        'nav_icon_max_width',
        'nav_icon_max_height',
        'nav_brand_gap',
        'nav_transparent',
        'footer_nav_mode',
        'footer_nav_bg_color',
        'footer_nav_text_color',
        'footer_nav_hover_color',
        'footer_submenu_bg_color',
        'footer_submenu_text_color',
        'footer_nav_transparent',
        'content_width',
        'book_header_pattern',
        'book_body_pattern',
        'book_footer_pattern',
        'page_header_pattern',
        'page_body_pattern',
        'page_footer_pattern',
        'post_header_pattern',
        'post_body_pattern',
        'post_footer_pattern',
        'archive_header_pattern',
        'archive_body_pattern',
        'archive_body_pattern_book_series',
        'archive_body_pattern_book_genre',
        'archive_body_pattern_book_authors',
        'archive_footer_pattern',

        // Book Cards & Buttons Ã¢â‚¬â€œ colors
        'book_card_button_bg_color',
        'book_card_button_text_color',
        'book_card_button_border_color',
        'book_card_sample_bg_color',
        'book_card_sample_text_color',
        'book_card_sample_border_color',
        'book_card_pagination_accent_color',
        'book_card_pagination_surface_color',
        'book_card_pagination_text_color',

        // Book Cards & Buttons Ã¢â‚¬â€œ shape/layout/effect
        'book_card_cover_shape',
        'book_card_button_shape',
        'book_card_sample_shape',
        'book_card_cta_mode',
        'book_card_shadow_style',

        // Book Cards & Buttons Ã¢â‚¬â€œ visibility toggles
        'book_card_hide_title',
        'book_card_hide_series',
        'book_card_hide_primary_button',
        'book_card_hide_sample_button',

        // Book Page Buttons (NEW)
        'book_page_primary_bg_color',
        'book_page_primary_text_color',
        'book_page_primary_border_color',
        'book_page_secondary_bg_color',
        'book_page_secondary_text_color',
        'book_page_secondary_border_color',
        'book_page_button_border_width',
        'book_page_button_radius',
    ];

    $clean = [];

    foreach ($allowed_keys as $key) {
        if (!isset($settings[$key])) {
            continue;
        }
        $val = $settings[$key];

        switch ($key) {
            case 'nav_logo_max_width':
            case 'nav_logo_max_height':
            case 'nav_icon_max_width':
            case 'nav_icon_max_height':
            case 'nav_brand_gap':
            case 'nav_font_size':
            case 'book_page_button_border_width':
            case 'book_page_button_radius':
                $num = intval($val);
                $clean[$key] = $num > 0 ? (string) $num : '';
                break;

            default:
                $clean[$key] = sanitize_text_field($val);
        }

        // Normalize placeholder/default values so runtime fallback logic can apply.
        if (str_contains($key, '_pattern')) {
            $normalized = strtolower(trim((string) $clean[$key], " \t\n\r\0\x0B-Ã¢â‚¬â€"));
            if ($normalized === '' || $normalized === 'none' || $normalized === 'default') {
                $clean[$key] = '';
            } elseif (function_exists('modfarm_ppb_normalize_slug')) {
                $mapped = modfarm_ppb_normalize_slug($clean[$key]);
                if ($mapped !== '') {
                    $clean[$key] = $mapped;
                }
            }
        }
    }

    return $clean;
}


/**
 * Add the ModFarm Settings page under Appearance.
 */
function modfarm_add_settings_page() {
    add_theme_page(
        'ModFarm Settings',
        'ModFarm Settings',
        'edit_theme_options',
        'modfarm_theme_settings',
        'modfarm_render_settings_page'
    );
}
add_action('admin_menu', 'modfarm_add_settings_page');


/**
 * New tabbed ModFarm Settings UI.
 */
function modfarm_render_settings_page() {
    $option_group = 'modfarm_theme_settings_group';
    $option_name  = 'modfarm_theme_settings';
    $opts         = get_option($option_name, []);
    ?>
    <div class="wrap modfarm-settings-wrap">
        <h1 class="wp-heading-inline">ModFarm Settings</h1>

        <form method="post" action="options.php" class="modfarm-settings-form">
            <?php settings_fields($option_group); ?>

            <div class="modfarm-settings-shell">

                <!-- Top-level tabs -->
                <nav class="mf-tabs" role="tablist" aria-label="ModFarm settings sections">
                    <button type="button"
                            class="mf-tab is-active"
                            data-tab="site-basics"
                            role="tab"
                            aria-selected="true">
                        Site Basics
                    </button>
                    <button type="button"
                            class="mf-tab"
                            data-tab="theme-fonts"
                            role="tab"
                            aria-selected="false">
                        Theme &amp; Fonts
                    </button>
                    <button type="button"
                            class="mf-tab"
                            data-tab="book-presentation"
                            role="tab"
                            aria-selected="false">
                        Book Presentation
                    </button>
                    <button type="button"
                            class="mf-tab"
                            data-tab="page-layouts"
                            role="tab"
                            aria-selected="false">
                        Page Layouts
                    </button>
                </nav>

                <div class="mf-tab-panels">

                    <!-- SITE BASICS -->
                    <section id="mf-tab-site-basics"
                             class="mf-tab-panel is-active"
                             role="tabpanel">
                        <div class="mf-panel-inner">
                            <h2 class="mf-panel-title">Site Basics</h2>
                            <p class="mf-panel-intro">
                                High-level defaults for how your site is laid out.
                            </p>

                            <div class="mf-settings-grid">
                                <div class="mf-settings-main">
                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Layout</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row">
                                                    <label for="mf-content-width">Content Width</label>
                                                </th>
                                                <td>
                                                    <?php
                                                    modfarm_text_field([
                                                        'id' => 'content_width',
                                                    ]);
                                                    ?>
                                                    <p class="description">
                                                        Example: <code>1200px</code> or <code>90%</code>.
                                                        This controls the maximum width for most content areas.
                                                    </p>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <aside class="mf-settings-preview">
                                    <div class="mf-preview-card">
                                        <div class="mf-preview-cover"></div>
                                        <div class="mf-preview-meta">
                                            <div class="mf-preview-title">Site Layout</div>
                                            <div class="mf-preview-author">Central content column</div>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </section>

                    <!-- THEME & FONTS -->
                    <section id="mf-tab-theme-fonts"
                             class="mf-tab-panel"
                             role="tabpanel">
                        <div class="mf-panel-inner">
                            <h2 class="mf-panel-title">Theme &amp; Fonts</h2>
                            <p class="mf-panel-intro">
                                Global colors, typography, and navigation styles.
                            </p>

                            <div class="mf-settings-grid">
                                <div class="mf-settings-main">

                                    <!-- Colors -->
                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Global Colors</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <?php
                                            $color_fields = [
                                                'primary_color'     => 'Primary Color',
                                                'secondary_color'   => 'Secondary Color',
                                                'background_color'  => 'Site Background Color',
                                                'header_text_color' => 'Header Text Color',
                                                'body_text_color'   => 'Body Text Color',
                                                'link_color'        => 'Link Color',
                                                'button_color'      => 'Button Background Color',
                                                'button_text_color' => 'Button Text Color',
                                            ];
                                            foreach ($color_fields as $field_id => $label) : ?>
                                                <tr>
                                                    <th scope="row"><label><?php echo esc_html($label); ?></label></th>
                                                    <td><?php modfarm_color_field(['id' => $field_id]); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Fonts -->
                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Fonts</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Heading Font</label></th>
                                                <td><?php modfarm_font_dropdown(['id' => 'heading_font']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Body Font</label></th>
                                                <td><?php modfarm_font_dropdown(['id' => 'body_font']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Site Title Font</label></th>
                                                <td><?php modfarm_font_dropdown(['id' => 'site_title_font']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Navigation Font</label></th>
                                                <td><?php modfarm_font_dropdown(['id' => 'nav_font']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Navigation -->
                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Navigation</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Nav Background Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'nav_bg_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Nav Text Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'nav_text_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Nav Hover Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'nav_hover_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Submenu Background Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'submenu_bg_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Submenu Text Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'submenu_text_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Nav Font Size</label></th>
                                                <td>
                                                    <?php modfarm_text_field(['id' => 'nav_font_size']); ?>
                                                    <p class="description">Optional; numeric pixels (e.g. <code>15</code>).</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Nav Padding</label></th>
                                                <td>
                                                    <?php
                                                    modfarm_select_field([
                                                        'id'      => 'nav_padding',
                                                        'options' => [
                                                            'compact'  => 'Compact',
                                                            'regular'  => 'Regular',
                                                            'spacious' => 'Spacious',
                                                        ],
                                                    ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Logo Max Width (px)</label></th>
                                                <td><?php modfarm_text_field(['id' => 'nav_logo_max_width']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Logo Max Height (px)</label></th>
                                                <td><?php modfarm_text_field(['id' => 'nav_logo_max_height']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Icon Max Width (px)</label></th>
                                                <td><?php modfarm_text_field(['id' => 'nav_icon_max_width']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Icon Max Height (px)</label></th>
                                                <td><?php modfarm_text_field(['id' => 'nav_icon_max_height']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Brand IconÃ¢â‚¬â€œTitle Gap (px)</label></th>
                                                <td><?php modfarm_text_field(['id' => 'nav_brand_gap']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Transparent Background</label></th>
                                                <td><?php modfarm_checkbox_field(['id' => 'nav_transparent']); ?></td>
                                            </tr>

                                            <tr>
                                              <th scope="row"><label style="font-weight:800;">Footer Navigation</label></th>
                                              <td><em>Separate styling for footer menus so header Ã¢â‚¬Å“heroÃ¢â‚¬Â settings donÃ¢â‚¬â„¢t break footer visibility.</em></td>
                                            </tr>

                                            <tr>
                                              <th scope="row"><label>Footer Nav Style Mode</label></th>
                                              <td>
                                                <?php modfarm_select_field([
                                                  'id' => 'footer_nav_mode',
                                                  'options' => [
                                                    'inherit' => 'Inherit Header Nav',
                                                    'manual'  => 'Manual Footer Colors',
                                                    'auto'    => 'Auto (Contrast from Site Background)',
                                                  ],
                                                ]); ?>
                                                <p class="description">
                                                  <strong>Auto</strong> chooses readable text colors based on the Site Background Color.
                                                </p>
                                              </td>
                                            </tr>

                                            <tr>
                                              <th scope="row"><label>Footer Nav Background Color</label></th>
                                              <td><?php modfarm_color_field(['id' => 'footer_nav_bg_color']); ?></td>
                                            </tr>
                                            <tr>
                                              <th scope="row"><label>Footer Nav Text Color</label></th>
                                              <td><?php modfarm_color_field(['id' => 'footer_nav_text_color']); ?></td>
                                            </tr>
                                            <tr>
                                              <th scope="row"><label>Footer Nav Hover Color</label></th>
                                              <td><?php modfarm_color_field(['id' => 'footer_nav_hover_color']); ?></td>
                                            </tr>
                                            <tr>
                                              <th scope="row"><label>Footer Submenu Background Color</label></th>
                                              <td><?php modfarm_color_field(['id' => 'footer_submenu_bg_color']); ?></td>
                                            </tr>
                                            <tr>
                                              <th scope="row"><label>Footer Submenu Text Color</label></th>
                                              <td><?php modfarm_color_field(['id' => 'footer_submenu_text_color']); ?></td>
                                            </tr>
                                            <tr>
                                              <th scope="row"><label>Footer Transparent Background</label></th>
                                              <td><?php modfarm_checkbox_field(['id' => 'footer_nav_transparent']); ?></td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Theme preview -->
                                <aside class="mf-settings-preview">
                                    <div class="mf-preview-card">
                                        <div class="mf-preview-cover"></div>
                                        <div class="mf-preview-meta">
                                            <div class="mf-preview-title">The Adventure</div>
                                            <div class="mf-preview-author">by John Doe</div>
                                            <a class="mf-preview-button" href="#">Learn More</a>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </section>

                    <!-- BOOK PRESENTATION -->
                    <section id="mf-tab-book-presentation"
                             class="mf-tab-panel"
                             role="tabpanel">
                        <div class="mf-panel-inner">
                            <h2 class="mf-panel-title">Book Presentation</h2>
                            <p class="mf-panel-intro">
                                Default card style, cover type, and button layout for book blocks.
                            </p>

                            <div class="mf-settings-grid">
                                <div class="mf-settings-main">
                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Button &amp; Sample Colors</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Primary Button Background</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_button_bg_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Primary Button Text Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_button_text_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Primary Button Border Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_button_border_color']); ?></td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><label>Sample Button Background</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_sample_bg_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Sample Button Text Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_sample_text_color']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Sample Button Border Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_sample_border_color']); ?></td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><label>Pagination Accent Color</label></th>
                                                <td><?php modfarm_color_field(['id' => 'book_card_pagination_accent_color']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Shapes &amp; Effects</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Cover Shape (Default)</label></th>
                                                <td>
                                                    <?php
                                                    modfarm_select_field([
                                                        'id' => 'book_card_cover_shape',
                                                        'options' => [
                                                            ''         => 'Square (inherit)',
                                                            'rounded'  => 'Rounded corners',
                                                        ],
                                                    ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Primary Button Shape (Default)</label></th>
                                                <td>
                                                    <?php
                                                    modfarm_select_field([
                                                        'id' => 'book_card_button_shape',
                                                        'options' => [
                                                            ''        => 'Square',
                                                            'rounded' => 'Rounded',
                                                            'pill'    => 'Pill',
                                                        ],
                                                    ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Sample Button Shape (Default)</label></th>
                                                <td>
                                                    <?php
                                                    modfarm_select_field([
                                                        'id' => 'book_card_sample_shape',
                                                        'options' => [
                                                            ''        => 'Square',
                                                            'rounded' => 'Rounded',
                                                            'pill'    => 'Pill',
                                                        ],
                                                    ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>CTA Spacing (Default)</label></th>
                                                <td>
                                                    <?php
                                                    modfarm_select_field([
                                                        'id' => 'book_card_cta_mode',
                                                        'options' => [
                                                            ''        => 'Auto / Theme default',
                                                            'joined'  => 'Joined (button touches cover)',
                                                            'gap'     => 'Gap (space between cover & button)',
                                                        ],
                                                    ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Shadow Style (Default)</label></th>
                                                <td>
                                                    <?php
                                                    modfarm_select_field([
                                                        'id' => 'book_card_shadow_style',
                                                        'options' => [
                                                            ''           => 'Flat (no extra shadows)',
                                                            'shadow-sm'  => 'Small shadow',
                                                            'shadow-md'  => 'Medium shadow',
                                                            'shadow-lg'  => 'Large shadow',
                                                            'emboss'     => 'Embossed (inset effect)',
                                                        ],
                                                    ]);
                                                    ?>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Visibility</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Hide Title on Book Cards</label></th>
                                                <td><?php modfarm_checkbox_field(['id' => 'book_card_hide_title']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Hide Series on Book Cards</label></th>
                                                <td><?php modfarm_checkbox_field(['id' => 'book_card_hide_series']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Hide Primary Button on Book Cards</label></th>
                                                <td><?php modfarm_checkbox_field(['id' => 'book_card_hide_primary_button']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Hide Sample Button on Book Cards</label></th>
                                                <td><?php modfarm_checkbox_field(['id' => 'book_card_hide_sample_button']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                    <h3 class="mf-group-title">Book Page Buttons</h3>
                                    <table class="form-table mf-form-table">
                                        <tbody>
                                        <tr>
                                            <th scope="row"><label>Primary (Filled) Background</label></th>
                                            <td><?php modfarm_color_field(['id' => 'book_page_primary_bg_color']); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label>Primary (Filled) Text</label></th>
                                            <td><?php modfarm_color_field(['id' => 'book_page_primary_text_color']); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label>Primary (Filled) Border</label></th>
                                            <td><?php modfarm_color_field(['id' => 'book_page_primary_border_color']); ?></td>
                                        </tr>

                                        <tr>
                                            <th scope="row"><label>Secondary (Outline) Background</label></th>
                                            <td><?php modfarm_color_field(['id' => 'book_page_secondary_bg_color']); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label>Secondary (Outline) Text</label></th>
                                            <td><?php modfarm_color_field(['id' => 'book_page_secondary_text_color']); ?></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label>Secondary (Outline) Border</label></th>
                                            <td><?php modfarm_color_field(['id' => 'book_page_secondary_border_color']); ?></td>
                                        </tr>

                                        <tr>
                                            <th scope="row"><label>Border Width (px)</label></th>
                                            <td>
                                                <?php modfarm_text_field(['id' => 'book_page_button_border_width']); ?>
                                                <p class="description">Numeric pixels (e.g. <code>1</code>).</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label>Border Radius (px)</label></th>
                                            <td>
                                                <?php modfarm_text_field(['id' => 'book_page_button_radius']); ?>
                                                <p class="description">Numeric pixels (e.g. <code>0</code>, <code>6</code>, <code>20</code>).</p>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                </div>

                                <!-- Book card preview -->
                                <aside class="mf-settings-preview">
                                    <div class="mf-preview-card mf-preview-card--book">
                                        <div class="mf-preview-cover mf-preview-cover--book"></div>
                                        <div class="mf-preview-meta">
                                            <div class="mf-preview-title">Tunnel Rat 3</div>
                                            <div class="mf-preview-author">WalrusKing</div>
                                            <a class="mf-preview-button" href="#">See The Book</a>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </section>

                    <!-- PAGE LAYOUTS / PATTERNS -->
                    <section id="mf-tab-page-layouts"
                             class="mf-tab-panel"
                             role="tabpanel">
                        <div class="mf-panel-inner">
                            <h2 class="mf-panel-title">Page Layouts</h2>
                            <p class="mf-panel-intro">
                                Choose default header/body/footer patterns for key content types.
                                These are used by the PPB / Apply Layout tools.
                            </p>

                            <div class="mf-settings-grid">
                                <div class="mf-settings-main">
                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Book Layout</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Book Header Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'book_header_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Book Body Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'book_body_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Book Footer Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'book_footer_pattern']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Page Layout</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Page Header Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'page_header_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Page Body Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'page_body_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Page Footer Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'page_footer_pattern']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Post Layout</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Post Header Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'post_header_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Post Body Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'post_body_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Post Footer Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'post_footer_pattern']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">Archive Layout</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Archive Header Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'archive_header_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Archive Body Pattern (Default)</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'archive_body_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Book Series Archive Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'archive_body_pattern_book_series']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Genre Archive Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'archive_body_pattern_book_genre']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Author Archive Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'archive_body_pattern_book_authors']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Archive Footer Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'archive_footer_pattern']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group">
                                        <h3 class="mf-group-title">PPB Control</h3>
                                        <p class="description">
                                            Preview the impact of a PPB zone replacement across a content type before any Apply All execution exists.
                                            This preview is read-only and respects locks, hybrid rules, and portable content-slot preservation.
                                        </p>

                                        <div class="mf-ppb-preview-controls" id="mf-ppb-apply-all-preview">
                                            <div class="mf-ppb-preview-field">
                                                <label for="mf-ppb-preview-content-type">Content Type</label>
                                                <select id="mf-ppb-preview-content-type">
                                                    <?php foreach (modfarm_get_ppb_apply_all_content_types() as $value => $label) : ?>
                                                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mf-ppb-preview-field">
                                                <label for="mf-ppb-preview-zone">Zone</label>
                                                <select id="mf-ppb-preview-zone">
                                                    <option value="header">Header</option>
                                                    <option value="body">Body</option>
                                                    <option value="footer">Footer</option>
                                                </select>
                                            </div>

                                            <div class="mf-ppb-preview-field mf-ppb-preview-field--pattern">
                                                <label for="mf-ppb-preview-pattern">Pattern</label>
                                                <select id="mf-ppb-preview-pattern"></select>
                                                <p class="description mf-ppb-preview-pattern-note" id="mf-ppb-preview-pattern-note"></p>
                                            </div>

                                            <div class="mf-ppb-preview-actions">
                                                <button type="button" class="button button-secondary" id="mf-ppb-preview-run">
                                                    Preview Impact
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mf-ppb-preview-feedback" id="mf-ppb-preview-feedback" aria-live="polite"></div>
                                        <div class="mf-ppb-preview-results" id="mf-ppb-preview-results"></div>
                                        <div class="mf-ppb-preview-execute" id="mf-ppb-preview-execute" hidden>
                                            <label class="mf-ppb-preview-confirm">
                                                <input type="checkbox" id="mf-ppb-preview-confirm">
                                                <span>I understand this will update the previewed Header or Footer zones sitewide for matching zoned items only.</span>
                                            </label>
                                            <button type="button" class="button button-primary" id="mf-ppb-preview-apply" disabled>
                                                Apply Previewed Change
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <aside class="mf-settings-preview">
                                    <div class="mf-preview-card">
                                        <div class="mf-preview-cover"></div>
                                        <div class="mf-preview-meta">
                                            <div class="mf-preview-title">Apply Layout</div>
                                            <div class="mf-preview-author">Header Ã‚Â· Body Ã‚Â· Footer</div>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </section>

                </div><!-- .mf-tab-panels -->
            </div><!-- .modfarm-settings-shell -->

            <?php submit_button(__('Save Changes', 'modfarm')); ?>
        </form>
    </div>
    <?php
}


/**
 * Enqueue admin assets for the settings UI.
 */
function modfarm_admin_enqueue_scripts($hook) {
    if ($hook !== 'appearance_page_modfarm_theme_settings') {
        return;
    }

    // Color picker for .modfarm-color-field
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script(
        'modfarm-color-picker',
        get_template_directory_uri() . '/assets/js/color-picker-init.js',
        ['wp-color-picker'],
        false,
        true
    );

    // Tabbed UI styles + script
    wp_enqueue_style(
        'modfarm-settings-ui',
        get_template_directory_uri() . '/assets/css/modfarm-settings-ui.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'modfarm-settings-ui',
        get_template_directory_uri() . '/assets/js/modfarm-settings-ui.js',
        [],
        '1.0.0',
        true
    );

    wp_localize_script('modfarm-settings-ui', 'modfarmSettingsUi', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'previewNonce' => wp_create_nonce('modfarm_ppb_apply_all_preview'),
        'executeNonce' => wp_create_nonce('modfarm_ppb_apply_all_execute'),
        'applyAllPatterns' => modfarm_get_ppb_apply_all_pattern_matrix(),
        'messages' => [
            'loading' => __('Scanning matching items...', 'modfarm'),
            'missingPattern' => __('Select a valid pattern before running the preview.', 'modfarm'),
            'noPatterns' => __('No central PPB patterns are registered for this content type and zone yet.', 'modfarm'),
            'error' => __('Preview could not be generated.', 'modfarm'),
            'executing' => __('Applying the previewed change...', 'modfarm'),
            'confirmRequired' => __('Confirm the change before applying it.', 'modfarm'),
            'executionUnavailable' => __('Apply All execution is currently available for Header, Body, and Footer zones only.', 'modfarm'),
        ],
    ]);
}
add_action('admin_enqueue_scripts', 'modfarm_admin_enqueue_scripts');


/**
 * Bridge ModFarm Theme Settings Ã¢â€ â€™ Book Card CSS design tokens.
 * (unchanged)
 */
function modfarm_output_book_card_design_tokens() {
    $opts = get_option('modfarm_theme_settings', []);

    // Global fallbacks
    $primary    = $opts['primary_color']        ?? '';
    $secondary  = $opts['secondary_color']      ?? '';
    $body_text  = $opts['body_text_color']      ?? '#222222';
    $background = $opts['background_color']     ?? '#ffffff';
    $btn_bg     = $opts['button_color']         ?? ($primary ?: '#f2b100');
    $btn_text   = $opts['button_text_color']    ?? '#111111';

    // Book-card-specific overrides, falling back to global colors
    $button_bg         = $opts['book_card_button_bg_color']       ?? $btn_bg;
    $button_text       = $opts['book_card_button_text_color']     ?? $btn_text;
    $button_border     = $opts['book_card_button_border_color']   ?? $button_bg;

    $sample_bg         = $opts['book_card_sample_bg_color']       ?? 'transparent';
    $sample_text       = $opts['book_card_sample_text_color']     ?? $body_text;
    $sample_border     = $opts['book_card_sample_border_color']   ?? $body_text;

    // Pagination Ã¢â‚¬â€œ "single color" first, overrides optional
    $pag_accent_setting  = $opts['book_card_pagination_accent_color']  ?? '';
    $pag_surface_setting = $opts['book_card_pagination_surface_color'] ?? '';
    $pag_text_setting    = $opts['book_card_pagination_text_color']    ?? '';

    $pag_base    = $pag_accent_setting ?: ($primary ?: '#111111');
    $pag_surface = $pag_surface_setting !== '' ? $pag_surface_setting : $background;
    $pag_text    = $pag_text_setting !== ''    ? $pag_text_setting    : $pag_base;

    $accent_is_dark = modfarm_hex_is_dark($pag_base);
    $on_accent      = $accent_is_dark ? '#ffffff' : '#111111';

    $vars = [];

    if ($button_bg)     $vars['--mfb-btn-bg']        = $button_bg;
    if ($button_text)   $vars['--mfb-btn-fg']        = $button_text;
    if ($button_border) $vars['--mfb-btn-border']    = $button_border;

    if ($sample_bg)     $vars['--mfb-sample-bg']     = $sample_bg;
    if ($sample_text)   $vars['--mfb-sample-fg']     = $sample_text;
    if ($sample_border) $vars['--mfb-sample-border'] = $sample_border;

    if ($pag_base)    $vars['--mfb-accent']      = $pag_base;
    if ($pag_surface) $vars['--mfb-surface']     = $pag_surface;
    if ($pag_text)    $vars['--mfb-text']        = $pag_text;

    if ($pag_base) {
        $vars['--mfb-border']     = $pag_base;
        $vars['--mfb-text-muted'] = '#666666';
        $vars['--mfb-on-accent']  = $on_accent;
    }

    if (empty($vars)) {
        return;
    }

    echo "<style id='modfarm-book-card-design-tokens'>\n:root{\n";
    foreach ($vars as $name => $value) {
        echo '  ' . esc_attr($name) . ': ' . esc_html($value) . ";\n";
    }
    echo "}\n</style>\n";
}

/**
 * Bridge ModFarm Theme Settings Ã¢â€ â€™ Global semantic color tokens (Primary/Secondary/Button).
 * Used by Block Styles (Group + Button) and general theme helpers.
 */
function modfarm_output_global_color_tokens() {
    $opts = get_option('modfarm_theme_settings', []);

    $primary   = $opts['primary_color']      ?? '';
    $secondary = $opts['secondary_color']    ?? '';
    $btn_bg    = $opts['button_color']       ?? ($primary ?: '#222222');
    $btn_fg    = $opts['button_text_color']  ?? '#ffffff';

    $vars = [];

    if ($primary)   $vars['--mfs-primary']   = $primary;
    if ($secondary) $vars['--mfs-secondary'] = $secondary;

    // These are the "site button" defaults you want the style to use.
    if ($btn_bg)    $vars['--mfs-button-bg'] = $btn_bg;
    if ($btn_fg)    $vars['--mfs-button-fg'] = $btn_fg;

    if (empty($vars)) {
        return;
    }

    echo "<style id='modfarm-global-color-tokens'>\n:root{\n";
    foreach ($vars as $name => $value) {
        echo '  ' . esc_attr($name) . ': ' . esc_html($value) . ";\n";
    }
    echo "}\n</style>\n";
}
add_action('wp_head', 'modfarm_output_global_color_tokens', 19);
add_action('admin_head', 'modfarm_output_global_color_tokens', 19);

function modfarm_output_navigation_tokens() {
    $opts = get_option('modfarm_theme_settings', []);

    // Header (existing nav settings)
    $nav_bg    = $opts['nav_bg_color']       ?? '';
    $nav_text  = $opts['nav_text_color']     ?? '';
    $nav_hover = $opts['nav_hover_color']    ?? '';
    $sub_bg    = $opts['submenu_bg_color']   ?? '';
    $sub_text  = $opts['submenu_text_color'] ?? '';
    $nav_trans = !empty($opts['nav_transparent']);

    // Site context for Auto mode
    $site_bg   = $opts['background_color'] ?? '#ffffff';
    $body_text = $opts['body_text_color'] ?? '#222222';
    $link      = $opts['link_color'] ?? $body_text;

    $site_is_dark = modfarm_hex_is_dark($site_bg);

    // Footer settings
    $footer_mode = $opts['footer_nav_mode'] ?? 'inherit';

    $foot_bg    = $opts['footer_nav_bg_color']       ?? '';
    $foot_text  = $opts['footer_nav_text_color']     ?? '';
    $foot_hover = $opts['footer_nav_hover_color']    ?? '';
    $foot_subbg = $opts['footer_submenu_bg_color']   ?? '';
    $foot_subtx = $opts['footer_submenu_text_color'] ?? '';
    $foot_trans = !empty($opts['footer_nav_transparent']);

    // Auto defaults (contrast)
    $auto_text  = $site_is_dark ? '#ffffff' : '#111111';
    $auto_hover = $site_is_dark ? '#ffffff' : ($link ?: '#111111');
    $auto_subbg = $site_is_dark ? '#111111' : '#ffffff';
    $auto_subtx = $site_is_dark ? '#ffffff' : '#111111';

    // Resolve footer values by mode
    if ($footer_mode === 'inherit') {
        $resolved_bg    = $nav_trans ? 'transparent' : ($nav_bg ?: 'transparent');
        $resolved_text  = $nav_text  ?: $body_text;
        $resolved_hover = $nav_hover ?: $resolved_text;
        $resolved_subbg = $sub_bg    ?: $auto_subbg;
        $resolved_subtx = $sub_text  ?: $auto_subtx;
    } elseif ($footer_mode === 'auto') {
        $resolved_bg    = $foot_trans ? 'transparent' : ($foot_bg ?: 'transparent');
        $resolved_text  = $foot_text  ?: $auto_text;
        $resolved_hover = $foot_hover ?: $auto_hover;
        $resolved_subbg = $foot_subbg ?: $auto_subbg;
        $resolved_subtx = $foot_subtx ?: $auto_subtx;
    } else { // manual
        $resolved_bg    = $foot_trans ? 'transparent' : ($foot_bg ?: 'transparent');
        $resolved_text  = $foot_text  ?: $body_text;
        $resolved_hover = $foot_hover ?: ($link ?: $resolved_text);
        $resolved_subbg = $foot_subbg ?: 'transparent';
        $resolved_subtx = $foot_subtx ?: $resolved_text;
    }

    // Build vars
    $vars = [];

    // Header tokens (used by .mfs-nav mapping)
    if ($nav_bg !== '')   $vars['--mfs-nav-bg']    = $nav_trans ? 'transparent' : $nav_bg;
    if ($nav_text !== '') $vars['--mfs-nav-fg']    = $nav_text;
    if ($nav_hover !== '')$vars['--mfs-nav-hover'] = $nav_hover;
    if ($sub_bg !== '')   $vars['--mfs-sub-bg']    = $sub_bg;
    if ($sub_text !== '') $vars['--mfs-sub-fg']    = $sub_text;

    // Footer tokens (used by .mfs-nav-footer mapping)
    $vars['--mfs-footnav-bg']    = $resolved_bg;
    $vars['--mfs-footnav-fg']    = $resolved_text;
    $vars['--mfs-footnav-hover'] = $resolved_hover;
    $vars['--mfs-footsub-bg']    = $resolved_subbg;
    $vars['--mfs-footsub-fg']    = $resolved_subtx;

    echo "<style id='modfarm-navigation-tokens'>\n:root{\n";
    foreach ($vars as $name => $value) {
        if ($value === '' || $value === null) continue;
        echo '  ' . esc_attr($name) . ': ' . esc_html($value) . ";\n";
    }
    echo "}\n</style>\n";
}
add_action('wp_head', 'modfarm_output_navigation_tokens', 18);
add_action('admin_head', 'modfarm_output_navigation_tokens', 18);

function modfarm_hex_is_dark(string $hex): bool {
    $hex = trim($hex);
    if ($hex === '') {
        return false;
    }

    if ($hex[0] === '#') {
        $hex = substr($hex, 1);
    }
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    if (strlen($hex) !== 6) {
        return false;
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;

    return $luminance < 0.5;
}

add_action('wp_head', 'modfarm_output_book_card_design_tokens', 20);
add_action('admin_head', 'modfarm_output_book_card_design_tokens');

/**
 * Bridge ModFarm Theme Settings Ã¢â€ â€™ Book Page Button design tokens.
 */
function modfarm_output_book_page_button_design_tokens() {
    $opts = get_option('modfarm_theme_settings', []);

    // Global fallbacks
    $primary    = $opts['primary_color']     ?? '';
    $body_text  = $opts['body_text_color']   ?? '#222222';
    $btn_bg     = $opts['button_color']      ?? ($primary ?: '#f2b100');
    $btn_text   = $opts['button_text_color'] ?? '#111111';

    // Primary (filled) defaults
    $p_bg     = $opts['book_page_primary_bg_color']     ?? $btn_bg;
    $p_fg     = $opts['book_page_primary_text_color']   ?? $btn_text;
    $p_border = $opts['book_page_primary_border_color'] ?? $p_bg;

    // Secondary (outline) defaults
    $s_bg     = array_key_exists('book_page_secondary_bg_color', $opts)
        ? ($opts['book_page_secondary_bg_color'] ?? '')
        : 'transparent';
    if ($s_bg === '') $s_bg = 'transparent';

    $s_fg     = $opts['book_page_secondary_text_color']   ?? $body_text;
    $s_border = $opts['book_page_secondary_border_color'] ?? $body_text;

    // Shared
    $border_w = isset($opts['book_page_button_border_width']) && $opts['book_page_button_border_width'] !== ''
        ? max(0, intval($opts['book_page_button_border_width']))
        : 1;

    $radius   = isset($opts['book_page_button_radius']) && $opts['book_page_button_radius'] !== ''
        ? max(0, intval($opts['book_page_button_radius']))
        : 0;

    $vars = [
        '--mfb-bp-primary-bg'        => $p_bg,
        '--mfb-bp-primary-fg'        => $p_fg,
        '--mfb-bp-primary-border'    => $p_border,

        '--mfb-bp-secondary-bg'      => $s_bg,
        '--mfb-bp-secondary-fg'      => $s_fg,
        '--mfb-bp-secondary-border'  => $s_border,

        '--mfb-bp-btn-border-width'  => $border_w . 'px',
        '--mfb-bp-btn-radius'        => $radius . 'px',
    ];

    echo "<style id='modfarm-book-page-button-design-tokens'>\n:root{\n";
    foreach ($vars as $name => $value) {
        if ($value === '' || $value === null) continue;
        echo '  ' . esc_attr($name) . ': ' . esc_html($value) . ";\n";
    }
    echo "}\n</style>\n";
}

add_action('wp_head', 'modfarm_output_book_page_button_design_tokens', 21);
add_action('admin_head', 'modfarm_output_book_page_button_design_tokens');

function modfarm_compute_book_card_body_classes() {
    $opts    = get_option('modfarm_theme_settings', []);
    $classes = [];

    $cover_shape = $opts['book_card_cover_shape'] ?? '';
    if ($cover_shape === 'rounded') {
        $classes[] = 'mfb-default-cover-rounded';
    }

    $button_shape = $opts['book_card_button_shape'] ?? '';
    if ($button_shape === 'rounded') {
        $classes[] = 'mfb-default-button-rounded';
    } elseif ($button_shape === 'pill') {
        $classes[] = 'mfb-default-button-pill';
    }

    $sample_shape = $opts['book_card_sample_shape'] ?? '';
    if ($sample_shape === 'rounded') {
        $classes[] = 'mfb-default-sample-rounded';
    } elseif ($sample_shape === 'pill') {
        $classes[] = 'mfb-default-sample-pill';
    }

    $cta_mode = $opts['book_card_cta_mode'] ?? '';
    if ($cta_mode === 'gap') {
        $classes[] = 'mfb-default-cta-gap';
    } elseif ($cta_mode === 'joined') {
        $classes[] = 'mfb-default-cta-joined';
    }

    $shadow = $opts['book_card_shadow_style'] ?? '';
    if ($shadow === 'shadow-sm') {
        $classes[] = 'mfb-default-effect-shadow-sm';
    } elseif ($shadow === 'shadow-md') {
        $classes[] = 'mfb-default-effect-shadow-md';
    } elseif ($shadow === 'shadow-lg') {
        $classes[] = 'mfb-default-effect-shadow-lg';
    } elseif ($shadow === 'emboss') {
        $classes[] = 'mfb-default-effect-emboss';
    } else {
        $classes[] = 'mfb-default-effect-flat';
    }

    if (!empty($opts['book_card_hide_title'])) {
        $classes[] = 'mfb-hide-title';
    }
    if (!empty($opts['book_card_hide_series'])) {
        $classes[] = 'mfb-hide-series';
    }
    if (!empty($opts['book_card_hide_primary_button'])) {
        $classes[] = 'mfb-hide-primary-button';
    }
    if (!empty($opts['book_card_hide_sample_button'])) {
        $classes[] = 'mfb-hide-sample-button';
    }

    return $classes;
}

function modfarm_book_card_body_classes($classes) {
    $extra = modfarm_compute_book_card_body_classes();
    if (!empty($extra)) {
        $classes = array_merge($classes, $extra);
    }
    return $classes;
}
add_filter('body_class', 'modfarm_book_card_body_classes');

function modfarm_admin_book_card_body_classes($classes) {
    $extra = modfarm_compute_book_card_body_classes();
    if (empty($extra)) {
        return $classes;
    }

    $extra_str = implode(
        ' ',
        array_map('sanitize_html_class', $extra)
    );

    if (!empty($classes)) {
        $classes .= ' ' . $extra_str;
    } else {
        $classes  = $extra_str;
    }

    return $classes;
}
add_filter('admin_body_class', 'modfarm_admin_book_card_body_classes');

/**
 * Block Style CSS (applies on frontend + editor).
 */
function modfarm_enqueue_block_style_css() {
    $css = <<<CSS
/* ==========================================================
   ModFarm Block Styles (Phase 1)
   Group: Primary / Secondary (background only)
   Button: Button / Ghost Button (from site button settings)
   ========================================================== */

/* Group backgrounds */
.wp-block-group.is-style-mf-primary {
  background-color: var(--mfs-primary);
}
.wp-block-group.is-style-mf-secondary {
  background-color: var(--mfs-secondary);
}

/* Buttons (core/button uses .wp-block-button__link) */
.wp-block-button.is-style-mf-button .wp-block-button__link {
  background-color: var(--mfs-button-bg);
  color: var(--mfs-button-fg);
  border: 1px solid var(--mfs-button-bg);
}

.wp-block-button.is-style-mf-ghost-button .wp-block-button__link {
  background-color: transparent;
  color: var(--mfs-button-bg);
  border: 1px solid var(--mfs-button-bg);
}
CSS;

    // Attach to a handle that exists everywhere. 'wp-block-library' is safe.
    wp_add_inline_style('wp-block-library', $css);
}
add_action('enqueue_block_assets', 'modfarm_enqueue_block_style_css', 20);

function modfarm_enqueue_nav_token_css() {
    $css = <<<CSS
/* ==========================================================
   ModFarm Nav Tokens: header vs footer mapping
   ========================================================== */

/* Default (Header / general nav instance): bind to header tokens */
.mfs-nav{
  --mf-nav-bg: var(--mfs-nav-bg, transparent);
  --mf-nav-color: var(--mfs-nav-fg, #111);
  --mf-nav-hover-color: var(--mfs-nav-hover, currentColor);
  --submenu-bg: var(--mfs-sub-bg, #ffffff);
  --submenu-color: var(--mfs-sub-fg, #111111);
}

/* Footer instance: bind to footer tokens */
.mfs-nav.mfs-nav-footer{
  --mf-nav-bg: var(--mfs-footnav-bg, transparent);
  --mf-nav-color: var(--mfs-footnav-fg, #111);
  --mf-nav-hover-color: var(--mfs-footnav-hover, currentColor);
  --submenu-bg: var(--mfs-footsub-bg, #ffffff);
  --submenu-color: var(--mfs-footsub-fg, #111111);
}

/* Ensure hover uses the var name referenced by the block CSS */
.mfs-nav-menu li a:hover,
.mfs-nav-menu li a:focus,
.mfs-nav-menu li a:active{
  color: var(--mf-nav-hover-color);
}
CSS;

    wp_add_inline_style('wp-block-library', $css);
}

add_action('enqueue_block_assets', 'modfarm_enqueue_nav_token_css', 21);

/**
 * Register ModFarm Block Styles (Primary/Secondary for Group; Button/Ghost for Button).
 */
function modfarm_register_block_styles_editor_assets() {
    // Only in editor, but safe either way.
    wp_register_script('modfarm-block-styles', '', ['wp-blocks'], '1.0.0', true);

    $js = <<<JS
(function(wp){
  if (!wp || !wp.blocks || !wp.blocks.registerBlockStyle) return;

  var r = wp.blocks.registerBlockStyle;

  // Group styles: Primary / Secondary
  r('core/group',  { name: 'mf-primary',   label: 'Primary'   });
  r('core/group',  { name: 'mf-secondary', label: 'Secondary' });

  // Button styles: Button / Ghost Button
  r('core/button', { name: 'mf-button',       label: 'Button' });
  r('core/button', { name: 'mf-ghost-button', label: 'Ghost Button' });

})(window.wp);
JS;

    wp_add_inline_script('modfarm-block-styles', $js);
    wp_enqueue_script('modfarm-block-styles');
}
add_action('enqueue_block_editor_assets', 'modfarm_register_block_styles_editor_assets');

/**
 * Inject ModFarm Settings colors into the editor palette (optional; currently disabled in your file).
 */
function modfarm_inject_editor_palette($settings) {
    $opts = get_option('modfarm_theme_settings', []);

    $primary   = $opts['primary_color']     ?? '';
    $secondary = $opts['secondary_color']   ?? '';
    $btn_bg    = $opts['button_color']      ?? '';
    $btn_fg    = $opts['button_text_color'] ?? '';

    $palette = [];
    if ($primary)   $palette[] = ['name' => 'Primary',     'slug' => 'mf-primary',     'color' => $primary];
    if ($secondary) $palette[] = ['name' => 'Secondary',   'slug' => 'mf-secondary',   'color' => $secondary];
    if ($btn_bg)    $palette[] = ['name' => 'Button',      'slug' => 'mf-button',      'color' => $btn_bg];
    if ($btn_fg)    $palette[] = ['name' => 'Button Text', 'slug' => 'mf-button-text', 'color' => $btn_fg];

    if (empty($palette)) return $settings;

    if (!isset($settings['__experimentalFeatures'])) {
        $settings['__experimentalFeatures'] = [];
    }
    if (!isset($settings['__experimentalFeatures']['color'])) {
        $settings['__experimentalFeatures']['color'] = [];
    }

    // Override the palette shown in ColorPalette / styles UI.
    $settings['__experimentalFeatures']['color']['palette'] = $palette;

    // Optional: also enable custom colors (leave true unless you want to lock it down)
    if (!isset($settings['__experimentalFeatures']['color']['custom'])) {
        $settings['__experimentalFeatures']['color']['custom'] = true;
    }

    return $settings;
}
// add_filter('block_editor_settings_all', 'modfarm_inject_editor_palette', 100);
