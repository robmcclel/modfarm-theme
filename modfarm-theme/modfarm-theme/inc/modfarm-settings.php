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

    // OFFER Layout
    add_settings_field('offer_header_pattern', 'Offer Header Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'offer_header_pattern']);
    add_settings_field('offer_body_pattern',   'Offer Body Pattern',    'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'offer_body_pattern']);
    add_settings_field('offer_footer_pattern', 'Offer Footer Pattern',  'modfarm_pattern_dropdown', 'modfarm_theme_settings', 'modfarm_section_templates', ['id' => 'offer_footer_pattern']);

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

        // Offer Layout
        'offer_header_pattern' => 'modfarm-offer-header',
        'offer_body_pattern'   => 'modfarm-offer-body',
        'offer_footer_pattern' => 'modfarm-offer-footer',

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

    if (post_type_exists('mf_offer')) {
        $types['mf_offer'] = 'Offers';
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
 * Build visualizer content type definitions for the PPB layout browser.
 */
function modfarm_get_ppb_visualizer_content_types(): array {
    $types = [
        'book' => [
            'label' => __('Book Page', 'modfarm'),
            'sample_label' => __('Sample Book', 'modfarm'),
            'fields' => [
                'header' => 'book_header_pattern',
                'body' => 'book_body_pattern',
                'footer' => 'book_footer_pattern',
            ],
        ],
        'page' => [
            'label' => __('Standard Page', 'modfarm'),
            'sample_label' => __('Sample Page', 'modfarm'),
            'fields' => [
                'header' => 'page_header_pattern',
                'body' => 'page_body_pattern',
                'footer' => 'page_footer_pattern',
            ],
        ],
        'post' => [
            'label' => __('Blog Post', 'modfarm'),
            'sample_label' => __('Sample Post', 'modfarm'),
            'fields' => [
                'header' => 'post_header_pattern',
                'body' => 'post_body_pattern',
                'footer' => 'post_footer_pattern',
            ],
        ],
    ];

    if (post_type_exists('offer')) {
        $types['offer'] = [
            'label' => __('Offer Page', 'modfarm'),
            'sample_label' => __('Sample Offer', 'modfarm'),
            'fields' => [
                'header' => 'offer_header_pattern',
                'body' => 'offer_body_pattern',
                'footer' => 'offer_footer_pattern',
            ],
        ];
    } elseif (post_type_exists('mf_offer')) {
        $types['mf_offer'] = [
            'label' => __('Offer Page', 'modfarm'),
            'sample_label' => __('Sample Offer', 'modfarm'),
            'fields' => [
                'header' => 'offer_header_pattern',
                'body' => 'offer_body_pattern',
                'footer' => 'offer_footer_pattern',
            ],
        ];
    }

    $types['archive'] = [
        'label' => __('Book Archive', 'modfarm'),
        'sample_label' => __('Archive Context', 'modfarm'),
        'fields' => [
            'header' => 'archive_header_pattern',
            'body' => 'archive_body_pattern',
            'footer' => 'archive_footer_pattern',
        ],
    ];

    return $types;
}

/**
 * Return sample posts for the visualizer. These are only used as render context.
 */
function modfarm_get_ppb_visualizer_samples(): array {
    $samples = [];

    foreach (modfarm_get_ppb_visualizer_content_types() as $post_type => $config) {
        if ($post_type === 'archive' || !post_type_exists($post_type)) {
            $samples[$post_type] = [];
            continue;
        }

        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => ['publish', 'draft', 'private'],
            'posts_per_page' => 12,
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => false,
        ]);

        $samples[$post_type] = array_map(static function ($post) {
            return [
                'value' => (string) $post->ID,
                'label' => get_the_title($post) ?: __('Untitled', 'modfarm'),
            ];
        }, $posts);
    }

    return $samples;
}

/**
 * Return one representative book for the Book Presentation preview.
 */
function modfarm_get_book_presentation_preview_sample(): array {
    $fallback = [
        'title' => __('Book Title', 'modfarm'),
        'series' => __('Series Name', 'modfarm'),
        'coverUrl' => '',
        'hasRealBook' => false,
    ];

    if (!post_type_exists('book')) {
        return $fallback;
    }

    $books = get_posts([
        'post_type' => 'book',
        'post_status' => ['publish', 'draft', 'private'],
        'posts_per_page' => 8,
        'orderby' => 'date',
        'order' => 'DESC',
        'suppress_filters' => false,
    ]);

    foreach ($books as $book) {
        $book_id = (int) $book->ID;
        $cover_url = get_the_post_thumbnail_url($book_id, 'large') ?: '';

        foreach (['cover_ebook', 'cover_image_kindle', 'cover_image_flat', 'cover_image_3d', 'cover_image_audio', 'cover_image_composite'] as $meta_key) {
            if ($cover_url !== '') {
                break;
            }

            $meta_value = get_post_meta($book_id, $meta_key, true);
            if (!$meta_value) {
                continue;
            }

            $cover_url = is_numeric($meta_value)
                ? (wp_get_attachment_image_url((int) $meta_value, 'large') ?: '')
                : esc_url_raw((string) $meta_value);
        }

        $series = '';
        $series_terms = get_the_terms($book_id, 'book-series');
        if (!empty($series_terms) && !is_wp_error($series_terms)) {
            $series = $series_terms[0]->name;
            $series_position = get_post_meta($book_id, 'series_position', true);
            if ($series_position !== '') {
                $series .= ' ' . __('Book', 'modfarm') . ' ' . $series_position;
            }
        }

        if ($cover_url !== '') {
            return [
                'title' => get_the_title($book_id) ?: $fallback['title'],
                'series' => $series ?: $fallback['series'],
                'coverUrl' => $cover_url,
                'hasRealBook' => true,
            ];
        }
    }

    if (!empty($books)) {
        $book_id = (int) $books[0]->ID;
        return [
            'title' => get_the_title($book_id) ?: $fallback['title'],
            'series' => $fallback['series'],
            'coverUrl' => '',
            'hasRealBook' => true,
        ];
    }

    return $fallback;
}

/**
 * Resolve a submitted visualizer slug against the field's central default.
 */
function modfarm_ppb_visualizer_resolve_slug(string $field_id, string $submitted, array $options): string {
    $submitted = trim($submitted);
    if ($submitted !== '' && $submitted !== 'default') {
        return function_exists('modfarm_ppb_normalize_slug') ? modfarm_ppb_normalize_slug($submitted) : $submitted;
    }

    return function_exists('modfarm_ppb_resolve_pattern_slug')
        ? modfarm_ppb_resolve_pattern_slug($field_id, $options[$field_id] ?? null, $options)
        : '';
}

/**
 * Return frontend stylesheet URLs needed by ModFarm blocks inside the preview iframe.
 */
function modfarm_ppb_visualizer_stylesheet_urls(): array {
    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    $urls = [
        includes_url('css/dist/block-library/style.min.css'),
        get_stylesheet_uri(),
        $theme_uri . '/assets/css/common.css',
        $theme_uri . '/assets/css/book-cards.css',
        $theme_uri . '/assets/css/footers.css',
    ];

    $block_json_files = glob($theme_dir . '/blocks/*/block.json') ?: [];
    foreach ($block_json_files as $block_json) {
        $block_data = json_decode((string) file_get_contents($block_json), true);
        if (!is_array($block_data) || empty($block_data['style'])) {
            continue;
        }

        $styles = is_array($block_data['style']) ? $block_data['style'] : [$block_data['style']];
        foreach ($styles as $style) {
            if (!is_string($style) || !str_starts_with($style, 'file:')) {
                continue;
            }

            $relative = ltrim(substr($style, 5), './\\');
            $path = dirname($block_json) . '/' . str_replace('\\', '/', $relative);
            if (!file_exists($path)) {
                continue;
            }

            $urls[] = $theme_uri . '/blocks/' . basename(dirname($block_json)) . '/' . str_replace('\\', '/', $relative);
        }
    }

    return array_values(array_unique(array_filter($urls)));
}

/**
 * Capture inline design-token styles normally printed into frontend/admin heads.
 */
function modfarm_ppb_visualizer_inline_token_styles(): string {
    $callbacks = [
        'modfarm_output_global_color_tokens',
        'modfarm_output_navigation_tokens',
        'modfarm_output_book_card_design_tokens',
        'modfarm_output_book_page_button_design_tokens',
    ];

    ob_start();
    foreach ($callbacks as $callback) {
        if (function_exists($callback)) {
            $callback();
        }
    }

    return (string) ob_get_clean();
}

/**
 * Render a small iframe document for the PPB visualizer.
 */
function modfarm_build_ppb_visualizer_document(array $args): string {
    $types = modfarm_get_ppb_visualizer_content_types();
    $content_type = sanitize_key($args['content_type'] ?? 'book');
    if (!isset($types[$content_type])) {
        $content_type = 'book';
    }

    $preview_scope = sanitize_key($args['preview_scope'] ?? 'ppb_layout');
    if ($preview_scope === '') {
        $preview_scope = 'ppb_layout';
    }

    $active_zone = sanitize_key($args['active_zone'] ?? 'header');
    if (!in_array($active_zone, ['header', 'body', 'footer'], true)) {
        $active_zone = 'header';
    }

    $options = get_option('modfarm_theme_settings', []);
    if (!is_array($options)) {
        $options = [];
    }

    $sample_id = absint($args['sample_id'] ?? 0);
    $sample_is_active = false;
    if ($sample_id > 0 && $content_type !== 'archive') {
        $sample_post = get_post($sample_id);
        if ($sample_post && $sample_post->post_type === $content_type) {
            $GLOBALS['post'] = $sample_post;
            setup_postdata($sample_post);
            $sample_is_active = true;
        }
    }

    $fields = $types[$content_type]['fields'];
    $submitted_patterns = is_array($args['patterns'] ?? null) ? $args['patterns'] : [];
    $zone_markup = [];
    $resolved = [];

    foreach (['header', 'body', 'footer'] as $zone) {
        $field_id = $fields[$zone] ?? '';
        $slug = $field_id ? modfarm_ppb_visualizer_resolve_slug($field_id, (string) ($submitted_patterns[$zone] ?? ''), $options) : '';
        $resolved[$zone] = $slug;
        $content = ($slug && function_exists('modfarm_ppb_get_pattern_content_by_slug'))
            ? modfarm_ppb_get_pattern_content_by_slug($slug)
            : '';

        $zone_markup[$zone] = sprintf(
            '<div class="mf-ppb-viz-zone mf-ppb-viz-zone--%1$s" data-zone="%1$s">%2$s</div>',
            esc_attr($zone),
            do_blocks($content)
        );
    }

    $body_classes = get_body_class([
        'mf-ppb-visualizer-doc',
        'mf-ppb-visualizer-doc--scope-' . sanitize_html_class($preview_scope),
        'mf-ppb-visualizer-doc--' . sanitize_html_class($content_type),
        'wp-site-blocks',
    ]);
    $global_styles = function_exists('wp_get_global_stylesheet') ? wp_get_global_stylesheet() : '';

    $document = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    foreach (modfarm_ppb_visualizer_stylesheet_urls() as $stylesheet_url) {
        $document .= '<link rel="stylesheet" href="' . esc_url($stylesheet_url) . '">';
    }
    $document .= modfarm_ppb_visualizer_inline_token_styles();
    if ($global_styles !== '') {
        $document .= '<style id="global-styles-inline-css">' . str_replace('</style', '<\/style', $global_styles) . '</style>';
    }
    $document .= '<style>
      html{margin:0;padding:0;background:var(--mfs-background,#fff);overflow-x:hidden;overflow-y:auto;}
      body{margin:0;padding:0;background:var(--mfs-background,#fff);color:var(--mfs-body,#1d2327);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:13px;line-height:1.45;overflow-x:hidden;overflow-y:auto;}
      a{color:var(--mfs-link,#2271b1);}
      h1,h2,h3,h4,h5,h6{color:var(--mfs-heading,var(--mfs-body,#1d2327));}
      .mf-ppb-viz-canvas{width:100%;min-height:100vh;background:var(--mfs-background,#fff);overflow:visible;}
      .mf-ppb-viz-zone{position:relative;min-height:24px;overflow:visible;}
      .mf-ppb-viz-zone:empty::after{content:"Pattern preview unavailable";display:block;padding:24px;color:#646970;background:#f6f7f7;}
      .mf-ppb-viz-zone > *:first-child{margin-top:0;}
      .mf-ppb-viz-zone > *:last-child{margin-bottom:0;}
      .wp-site-blocks{padding:0;}
      img{max-width:100%;height:auto;}
    </style>';
    $document .= '</head><body class="' . esc_attr(implode(' ', $body_classes)) . '"><div class="wp-site-blocks"><main class="mf-ppb-viz-canvas">';
    $document .= $zone_markup['header'] . $zone_markup['body'] . $zone_markup['footer'];
    $document .= '</main></div></body></html>';

    if ($sample_is_active) {
        wp_reset_postdata();
    }

    return $document;
}

/**
 * AJAX endpoint for the PPB layout browser visual preview.
 */
function modfarm_ajax_ppb_visualizer_preview() {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('You do not have permission to preview PPB layouts.', 'modfarm')], 403);
    }

    check_ajax_referer('modfarm_ppb_visualizer_preview', 'nonce');

    $patterns = [];
    $raw_patterns = isset($_POST['patterns']) ? wp_unslash($_POST['patterns']) : '';
    if (is_string($raw_patterns) && $raw_patterns !== '') {
        $decoded = json_decode($raw_patterns, true);
        if (is_array($decoded)) {
            $patterns = $decoded;
        }
    }

    $html = modfarm_build_ppb_visualizer_document([
        'content_type' => isset($_POST['contentType']) ? sanitize_key(wp_unslash($_POST['contentType'])) : 'book',
        'sample_id' => isset($_POST['sampleId']) ? absint($_POST['sampleId']) : 0,
        'active_zone' => isset($_POST['activeZone']) ? sanitize_key(wp_unslash($_POST['activeZone'])) : 'header',
        'preview_scope' => isset($_POST['previewScope']) ? sanitize_key(wp_unslash($_POST['previewScope'])) : 'ppb_layout',
        'patterns' => $patterns,
    ]);

    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_modfarm_ppb_visualizer_preview', 'modfarm_ajax_ppb_visualizer_preview');

/**
 * Persistent option names for chunked PPB Apply All runs.
 */
function modfarm_ppb_apply_all_active_runs_option_name(): string {
    return 'modfarm_ppb_apply_all_active_runs';
}

function modfarm_ppb_apply_all_run_log_option_name(): string {
    return 'modfarm_ppb_apply_all_run_log';
}

/**
 * Resolve a persistent run type label for admin UI.
 */
function modfarm_ppb_get_run_type_label(array $run): string {
    $run_type = (string) ($run['run_type'] ?? 'apply_all');
    return $run_type === 'safe_convert' ? 'Safe Convert' : 'Apply All';
}

/**
 * Resolve the primary action label for progress and results.
 */
function modfarm_ppb_get_run_primary_action_label(array $run): string {
    $run_type = (string) ($run['run_type'] ?? 'apply_all');
    return $run_type === 'safe_convert' ? 'Converted' : 'Updated';
}

/**
 * Read and write active Apply All run records.
 */
function modfarm_ppb_get_apply_all_active_runs(): array {
    $runs = get_option(modfarm_ppb_apply_all_active_runs_option_name(), []);
    return is_array($runs) ? $runs : [];
}

function modfarm_ppb_save_apply_all_active_runs(array $runs): void {
    update_option(modfarm_ppb_apply_all_active_runs_option_name(), $runs, false);
}

function modfarm_ppb_get_apply_all_run(string $run_id): array {
    $runs = modfarm_ppb_get_apply_all_active_runs();
    $run = $runs[$run_id] ?? [];
    return is_array($run) ? $run : [];
}

function modfarm_ppb_store_apply_all_run(array $run): void {
    if (empty($run['run_id']) || !is_string($run['run_id'])) {
        return;
    }

    $runs = modfarm_ppb_get_apply_all_active_runs();
    $runs[$run['run_id']] = $run;
    modfarm_ppb_save_apply_all_active_runs($runs);
}

function modfarm_ppb_delete_apply_all_run(string $run_id): void {
    $runs = modfarm_ppb_get_apply_all_active_runs();
    unset($runs[$run_id]);
    modfarm_ppb_save_apply_all_active_runs($runs);
}

/**
 * Read and append recent completed Apply All runs.
 */
function modfarm_ppb_get_apply_all_run_log(): array {
    $log = get_option(modfarm_ppb_apply_all_run_log_option_name(), []);
    return is_array($log) ? $log : [];
}

function modfarm_ppb_append_apply_all_run_log(array $run): void {
    $log = modfarm_ppb_get_apply_all_run_log();
    array_unshift($log, $run);
    $log = array_slice($log, 0, 15);
    update_option(modfarm_ppb_apply_all_run_log_option_name(), $log, false);
}

/**
 * Shape an active run into lightweight progress data for the admin UI.
 */
function modfarm_ppb_get_apply_all_run_progress(array $run): array {
    $eligible_total = (int) ($run['eligible_total'] ?? 0);
    $remaining = is_array($run['queue'] ?? null) ? count($run['queue']) : 0;
    $processed = max(0, $eligible_total - $remaining);
    $percent = $eligible_total > 0 ? (int) floor(($processed / $eligible_total) * 100) : 100;

    return [
        'run_id' => (string) ($run['run_id'] ?? ''),
        'run_type' => (string) ($run['run_type'] ?? 'apply_all'),
        'run_type_label' => modfarm_ppb_get_run_type_label($run),
        'primary_action_label' => modfarm_ppb_get_run_primary_action_label($run),
        'status' => (string) ($run['status'] ?? 'queued'),
        'content_type' => (string) ($run['content_type'] ?? ''),
        'zone' => (string) ($run['zone'] ?? ''),
        'pattern' => (string) ($run['pattern'] ?? ''),
        'eligible_total' => $eligible_total,
        'processed' => $processed,
        'remaining' => $remaining,
        'percent' => $percent,
        'totals' => is_array($run['totals'] ?? null) ? $run['totals'] : [],
        'started_at' => (string) ($run['started_at'] ?? ''),
        'updated_at' => (string) ($run['updated_at'] ?? ''),
        'finished_at' => (string) ($run['finished_at'] ?? ''),
    ];
}

/**
 * Process one Apply All batch and return the updated run record.
 */
function modfarm_ppb_process_apply_all_run_batch(array $run): array {
    $queue = array_values(array_map('intval', is_array($run['queue'] ?? null) ? $run['queue'] : []));
    $batch_size = max(1, (int) ($run['batch_size'] ?? 25));
    $batch = array_splice($queue, 0, $batch_size);

    $run['status'] = 'running';
    $run['queue'] = $queue;

    foreach ($batch as $post_id) {
        $item = modfarm_get_ppb_apply_all_item_preview($post_id, (string) $run['content_type'], (string) $run['zone']);
        $action = (string) ($item['action'] ?? '');

        if ($action !== 'will_update') {
            if ($action === 'skip_locked') {
                $run['totals']['skipped_locked'] = (int) ($run['totals']['skipped_locked'] ?? 0) + 1;
            } else {
                $run['totals']['skipped_legacy'] = (int) ($run['totals']['skipped_legacy'] ?? 0) + 1;
            }
            continue;
        }

        $preserves_slots = !empty($item['zone']['contains_content_slot']);
        $updated = function_exists('modfarm_ppb_replace_post_zone_with_pattern')
            ? modfarm_ppb_replace_post_zone_with_pattern($post_id, (string) $run['zone'], (string) $run['pattern'])
            : false;

        if ($updated) {
            $run['totals']['updated'] = (int) ($run['totals']['updated'] ?? 0) + 1;
            if ($preserves_slots) {
                $run['totals']['slot_content_preserved'] = (int) ($run['totals']['slot_content_preserved'] ?? 0) + 1;
            }
            if (count($run['updated_items'] ?? []) < 100) {
                $run['updated_items'][] = [
                    'post_id' => $post_id,
                    'title' => $item['title'] ?? sprintf('#%d', $post_id),
                ];
            }
            continue;
        }

        $run['totals']['failed'] = (int) ($run['totals']['failed'] ?? 0) + 1;
        if (count($run['failed_items'] ?? []) < 100) {
            $run['failed_items'][] = [
                'post_id' => $post_id,
                'title' => $item['title'] ?? sprintf('#%d', $post_id),
                'message' => __('Replacement did not produce a content change.', 'modfarm'),
            ];
        }
    }

    $run['updated_at'] = gmdate('c');

    if (empty($run['queue'])) {
        $run['status'] = 'completed';
        $run['finished_at'] = gmdate('c');
    }

    return $run;
}

/**
 * Process one Safe Convert batch and return the updated run record.
 */
function modfarm_ppb_process_safe_convert_run_batch(array $run): array {
    $queue = array_values(array_map('intval', is_array($run['queue'] ?? null) ? $run['queue'] : []));
    $batch_size = max(1, (int) ($run['batch_size'] ?? 25));
    $batch = array_splice($queue, 0, $batch_size);

    $run['status'] = 'running';
    $run['queue'] = $queue;

    foreach ($batch as $post_id) {
        $item = function_exists('modfarm_get_ppb_safe_convert_item_preview')
            ? modfarm_get_ppb_safe_convert_item_preview($post_id, (string) $run['content_type'])
            : [];
        $action = (string) ($item['action'] ?? '');

        if ($action !== 'will_convert') {
            $run['totals']['skipped_zoned'] = (int) ($run['totals']['skipped_zoned'] ?? 0) + 1;
            continue;
        }

        $converted = function_exists('modfarm_ppb_safe_convert_post_to_zoned')
            ? modfarm_ppb_safe_convert_post_to_zoned($post_id)
            : false;

        if ($converted) {
            $run['totals']['updated'] = (int) ($run['totals']['updated'] ?? 0) + 1;
            if (!empty($item['has_slot_content'])) {
                $run['totals']['slot_content_preserved'] = (int) ($run['totals']['slot_content_preserved'] ?? 0) + 1;
            }
            if (count($run['updated_items'] ?? []) < 100) {
                $run['updated_items'][] = [
                    'post_id' => $post_id,
                    'title' => $item['title'] ?? sprintf('#%d', $post_id),
                ];
            }
            continue;
        }

        $run['totals']['failed'] = (int) ($run['totals']['failed'] ?? 0) + 1;
        if (count($run['failed_items'] ?? []) < 100) {
            $run['failed_items'][] = [
                'post_id' => $post_id,
                'title' => $item['title'] ?? sprintf('#%d', $post_id),
                'message' => __('Safe Convert did not produce a content change.', 'modfarm'),
            ];
        }
    }

    $run['updated_at'] = gmdate('c');

    if (empty($run['queue'])) {
        $run['status'] = 'completed';
        $run['finished_at'] = gmdate('c');
    }

    return $run;
}

/**
 * Process one batch for any persistent PPB run type.
 */
function modfarm_ppb_process_run_batch(array $run): array {
    $run_type = (string) ($run['run_type'] ?? 'apply_all');
    if ($run_type === 'safe_convert') {
        return modfarm_ppb_process_safe_convert_run_batch($run);
    }

    return modfarm_ppb_process_apply_all_run_batch($run);
}

/**
 * Render a lightweight persistent run log for recent PPB batch activity.
 */
function modfarm_render_ppb_apply_all_run_log_markup(): string {
    $runs = modfarm_ppb_get_apply_all_run_log();

    ob_start();
    ?>
    <div class="mf-ppb-run-log">
        <h4>Recent PPB Runs</h4>
        <?php if (empty($runs)) : ?>
            <p class="description">No completed PPB runs have been logged yet.</p>
        <?php else : ?>
            <ul class="mf-ppb-run-log__items">
                <?php foreach ($runs as $run) :
                    $content_type_label = modfarm_get_ppb_apply_all_content_types()[$run['content_type'] ?? ''] ?? ucfirst((string) ($run['content_type'] ?? 'Items'));
                    $zone_label = ucfirst((string) ($run['zone'] ?? 'zone'));
                    $totals = is_array($run['totals'] ?? null) ? $run['totals'] : [];
                    ?>
                    <li class="mf-ppb-run-log__item">
                        <div class="mf-ppb-run-log__title">
                            <strong><?php echo esc_html($content_type_label . ' · ' . $zone_label . ' Zone'); ?></strong>
                            <span><?php echo esc_html((string) ($run['finished_at'] ?? $run['started_at'] ?? '')); ?></span>
                        </div>
                        <div class="mf-ppb-run-log__meta">
                            <span><?php echo esc_html((string) ($run['pattern'] ?? '')); ?></span>
                            <span>Eligible: <?php echo esc_html((string) ($run['eligible_total'] ?? 0)); ?></span>
                            <span>Updated: <?php echo esc_html((string) ($totals['updated'] ?? 0)); ?></span>
                            <span>Skipped locked: <?php echo esc_html((string) ($totals['skipped_locked'] ?? 0)); ?></span>
                            <span>Skipped non-zoned: <?php echo esc_html((string) ($totals['skipped_legacy'] ?? 0)); ?></span>
                            <span>Failed: <?php echo esc_html((string) ($totals['failed'] ?? 0)); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Render a lightweight persistent run log for recent PPB batch activity.
 */
function modfarm_render_ppb_run_log_markup(): string {
    $runs = modfarm_ppb_get_apply_all_run_log();

    ob_start();
    ?>
    <div class="mf-ppb-run-log">
        <h4>Recent PPB Runs</h4>
        <?php if (empty($runs)) : ?>
            <p class="description">No completed PPB runs have been logged yet.</p>
        <?php else : ?>
            <ul class="mf-ppb-run-log__items">
                <?php foreach ($runs as $run) :
                    $content_type_label = modfarm_get_ppb_apply_all_content_types()[$run['content_type'] ?? ''] ?? ucfirst((string) ($run['content_type'] ?? 'Items'));
                    $run_type = (string) ($run['run_type'] ?? 'apply_all');
                    $run_type_label = modfarm_ppb_get_run_type_label($run);
                    $zone = (string) ($run['zone'] ?? '');
                    $totals = is_array($run['totals'] ?? null) ? $run['totals'] : [];
                    $primary_action_label = modfarm_ppb_get_run_primary_action_label($run);
                    ?>
                    <li class="mf-ppb-run-log__item">
                        <div class="mf-ppb-run-log__title">
                            <strong><?php echo esc_html($content_type_label . ' - ' . $run_type_label . ($zone !== '' ? ' - ' . ucfirst($zone) . ' Zone' : '')); ?></strong>
                            <span><?php echo esc_html((string) ($run['finished_at'] ?? $run['started_at'] ?? '')); ?></span>
                        </div>
                        <div class="mf-ppb-run-log__meta">
                            <span><?php echo esc_html((string) ($run['pattern'] ?? '')); ?></span>
                            <span>Eligible: <?php echo esc_html((string) ($run['eligible_total'] ?? 0)); ?></span>
                            <span><?php echo esc_html($primary_action_label); ?>: <?php echo esc_html((string) ($totals['updated'] ?? 0)); ?></span>
                            <?php if ($run_type === 'safe_convert') : ?>
                                <span>Skipped zoned: <?php echo esc_html((string) ($totals['skipped_zoned'] ?? 0)); ?></span>
                            <?php else : ?>
                                <span>Skipped locked: <?php echo esc_html((string) ($totals['skipped_locked'] ?? 0)); ?></span>
                                <span>Skipped non-zoned: <?php echo esc_html((string) ($totals['skipped_legacy'] ?? 0)); ?></span>
                            <?php endif; ?>
                            <span>Failed: <?php echo esc_html((string) ($totals['failed'] ?? 0)); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php

    return (string) ob_get_clean();
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
            <p class="description">Showing summary counts first. Use the filters below to inspect only the subset you care about.</p>
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
 * Render a compact preview summary for Bulk Safe Convert.
 */
function modfarm_render_ppb_safe_convert_preview_markup(array $report): string {
    $totals = $report['totals'] ?? [];
    $items = array_slice($report['items'] ?? [], 0, 50);
    $content_type_label = modfarm_get_ppb_apply_all_content_types()[$report['content_type'] ?? ''] ?? ucfirst((string) ($report['content_type'] ?? 'Items'));

    ob_start();
    ?>
    <div class="mf-ppb-preview-report">
        <div class="mf-ppb-preview-header">
            <div>
                <h4>Safe Convert Preview</h4>
                <p><?php echo esc_html($content_type_label); ?> - Convert Legacy or Plain content into explicit Header, Body, and Footer zones.</p>
            </div>
        </div>

        <div class="mf-ppb-preview-stats">
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Total items</span>
                <strong><?php echo esc_html((string) ($totals['items'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Will convert</span>
                <strong><?php echo esc_html((string) ($totals['will_convert'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Skipped zoned</span>
                <strong><?php echo esc_html((string) ($totals['skipped_zoned'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Slot content detected</span>
                <strong><?php echo esc_html((string) ($totals['slot_content_detected'] ?? 0)); ?></strong>
            </div>
        </div>

        <div class="mf-ppb-preview-list">
            <h5>Preview items</h5>
            <p class="description">Showing the first 50 matching items. Existing content will be preserved inside Body Zone, with empty Header and Footer zones added.</p>
            <?php if (empty($items)) : ?>
                <p class="description">No matching items were found for this preview.</p>
            <?php else : ?>
                <ul class="mf-ppb-preview-items">
                    <?php foreach ($items as $item) : ?>
                        <li class="mf-ppb-preview-item">
                            <div class="mf-ppb-preview-item__top">
                                <strong>
                                    <?php if (!empty($item['edit_link'])) : ?>
                                        <a href="<?php echo esc_url($item['edit_link']); ?>"><?php echo esc_html($item['title'] ?? 'Untitled'); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html($item['title'] ?? 'Untitled'); ?>
                                    <?php endif; ?>
                                </strong>
                                <span class="mf-ppb-preview-pill <?php echo ($item['action'] ?? '') === 'will_convert' ? 'is-update' : 'is-skip'; ?>">
                                    <?php echo ($item['action'] ?? '') === 'will_convert' ? esc_html__('Will convert', 'modfarm') : esc_html__('Skipped zoned', 'modfarm'); ?>
                                </span>
                            </div>
                            <div class="mf-ppb-preview-item__meta">
                                <span><?php echo esc_html((string) ($item['content_state'] ?? 'Unknown')); ?></span>
                                <span><?php echo esc_html((string) ($item['layout_mode'] ?? 'Unknown layout')); ?></span>
                                <span><?php echo esc_html('Status: ' . ((string) ($item['status'] ?? 'unknown'))); ?></span>
                                <?php if (!empty($item['has_slot_content'])) : ?>
                                    <span>Content-slot preserved</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['notes'])) : ?>
                                <div class="mf-ppb-preview-item__notes"><?php echo esc_html(implode(' ', (array) $item['notes'])); ?></div>
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
 * Render a compact execution summary for Bulk Safe Convert.
 */
function modfarm_render_ppb_safe_convert_result_markup(array $result): string {
    $totals = $result['totals'] ?? [];
    $content_type_label = modfarm_get_ppb_apply_all_content_types()[$result['content_type'] ?? ''] ?? ucfirst((string) ($result['content_type'] ?? 'Items'));
    $updated_items = $result['updated_items'] ?? [];
    $failed_items = $result['failed_items'] ?? [];

    ob_start();
    ?>
    <div class="mf-ppb-preview-report mf-ppb-preview-report--result">
        <div class="mf-ppb-preview-header">
            <div>
                <h4>Safe Convert Result</h4>
                <p><?php echo esc_html($content_type_label); ?> - Bulk Safe Convert to Zoned PPB</p>
            </div>
        </div>

        <div class="mf-ppb-preview-stats">
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Converted</span>
                <strong><?php echo esc_html((string) ($totals['updated'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Skipped zoned</span>
                <strong><?php echo esc_html((string) ($totals['skipped_zoned'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Slot content preserved</span>
                <strong><?php echo esc_html((string) ($totals['slot_content_preserved'] ?? 0)); ?></strong>
            </div>
            <div class="mf-ppb-preview-stat">
                <span class="mf-ppb-preview-stat__label">Failed</span>
                <strong><?php echo esc_html((string) ($totals['failed'] ?? 0)); ?></strong>
            </div>
        </div>

        <?php if (!empty($updated_items)) : ?>
            <div class="mf-ppb-preview-list">
                <h5>Converted items</h5>
                <ul class="mf-ppb-preview-items">
                    <?php foreach ($updated_items as $item) : ?>
                        <li class="mf-ppb-preview-item">
                            <div class="mf-ppb-preview-item__top">
                                <strong><?php echo esc_html($item['title'] ?? 'Untitled'); ?></strong>
                                <span class="mf-ppb-preview-pill is-update">Converted</span>
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
 * AJAX preview endpoint for Bulk Safe Convert analysis.
 */
function modfarm_ajax_ppb_safe_convert_preview() {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('You do not have permission to run this preview.', 'modfarm')], 403);
    }

    check_ajax_referer('modfarm_ppb_safe_convert_preview', 'nonce');

    $content_type = sanitize_key((string) ($_POST['contentType'] ?? ''));
    if (!isset(modfarm_get_ppb_apply_all_content_types()[$content_type])) {
        wp_send_json_error(['message' => __('Unsupported content type for Safe Convert.', 'modfarm')], 400);
    }

    $report = function_exists('modfarm_get_ppb_safe_convert_preview_report')
        ? modfarm_get_ppb_safe_convert_preview_report($content_type)
        : [];

    wp_send_json_success([
        'html' => modfarm_render_ppb_safe_convert_preview_markup($report),
        'report' => $report,
    ]);
}
add_action('wp_ajax_modfarm_ppb_safe_convert_preview', 'modfarm_ajax_ppb_safe_convert_preview');

/**
 * AJAX execution start endpoint for chunked zoned Apply All runs.
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
    $eligible_items = array_values(array_filter($preview['items'] ?? [], static function ($item) {
        return (($item['action'] ?? '') === 'will_update') && !empty($item['post_id']);
    }));
    $eligible_ids = array_map(static function ($item) {
        return (int) $item['post_id'];
    }, $eligible_items);

    $run = [
        'run_id' => wp_generate_uuid4(),
        'run_type' => 'apply_all',
        'content_type' => $content_type,
        'zone' => $zone,
        'pattern' => $pattern,
        'status' => 'queued',
        'batch_size' => 25,
        'eligible_total' => count($eligible_ids),
        'queue' => $eligible_ids,
        'started_at' => gmdate('c'),
        'updated_at' => gmdate('c'),
        'finished_at' => '',
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
    $run = modfarm_ppb_process_run_batch($run);

    $completed = (($run['status'] ?? '') === 'completed');
    if ($completed) {
        modfarm_ppb_append_apply_all_run_log($run);
    } else {
        modfarm_ppb_store_apply_all_run($run);
    }

    $response = [
        'runId' => (string) $run['run_id'],
        'completed' => $completed,
        'run' => modfarm_ppb_get_apply_all_run_progress($run),
    ];

    if ($completed) {
        $response['html'] = modfarm_render_ppb_apply_all_result_markup($run);
        $response['runLogHtml'] = modfarm_render_ppb_run_log_markup();
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_modfarm_ppb_apply_all_execute', 'modfarm_ajax_ppb_apply_all_execute');

/**
 * AJAX execution start endpoint for chunked Bulk Safe Convert runs.
 */
function modfarm_ajax_ppb_safe_convert_execute() {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('You do not have permission to run this action.', 'modfarm')], 403);
    }

    check_ajax_referer('modfarm_ppb_safe_convert_execute', 'nonce');

    $content_type = sanitize_key((string) ($_POST['contentType'] ?? ''));
    if (!isset(modfarm_get_ppb_apply_all_content_types()[$content_type])) {
        wp_send_json_error(['message' => __('Unsupported content type for Safe Convert.', 'modfarm')], 400);
    }

    $preview = function_exists('modfarm_get_ppb_safe_convert_preview_report')
        ? modfarm_get_ppb_safe_convert_preview_report($content_type)
        : [];
    $eligible_items = array_values(array_filter($preview['items'] ?? [], static function ($item) {
        return (($item['action'] ?? '') === 'will_convert') && !empty($item['post_id']);
    }));
    $eligible_ids = array_map(static function ($item) {
        return (int) $item['post_id'];
    }, $eligible_items);

    $run = [
        'run_id' => wp_generate_uuid4(),
        'run_type' => 'safe_convert',
        'content_type' => $content_type,
        'zone' => '',
        'pattern' => '',
        'status' => 'queued',
        'batch_size' => 25,
        'eligible_total' => count($eligible_ids),
        'queue' => $eligible_ids,
        'started_at' => gmdate('c'),
        'updated_at' => gmdate('c'),
        'finished_at' => '',
        'totals' => [
            'updated' => 0,
            'skipped_zoned' => (int) ($preview['totals']['skipped_zoned'] ?? 0),
            'slot_content_preserved' => 0,
            'failed' => 0,
        ],
        'updated_items' => [],
        'failed_items' => [],
    ];
    $run = modfarm_ppb_process_run_batch($run);

    $completed = (($run['status'] ?? '') === 'completed');
    if ($completed) {
        modfarm_ppb_append_apply_all_run_log($run);
    } else {
        modfarm_ppb_store_apply_all_run($run);
    }

    $response = [
        'runId' => (string) $run['run_id'],
        'completed' => $completed,
        'run' => modfarm_ppb_get_apply_all_run_progress($run),
    ];

    if ($completed) {
        $response['html'] = modfarm_render_ppb_safe_convert_result_markup($run);
        $response['runLogHtml'] = modfarm_render_ppb_run_log_markup();
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_modfarm_ppb_safe_convert_execute', 'modfarm_ajax_ppb_safe_convert_execute');

/**
 * AJAX batch-processing endpoint for active Apply All runs.
 */
function modfarm_ajax_ppb_apply_all_process_run() {
    if (!current_user_can('edit_theme_options')) {
        wp_send_json_error(['message' => __('You do not have permission to run this action.', 'modfarm')], 403);
    }

    check_ajax_referer('modfarm_ppb_apply_all_process_run', 'nonce');

    $run_id = sanitize_text_field(wp_unslash((string) ($_POST['runId'] ?? '')));
    if ($run_id === '') {
        wp_send_json_error(['message' => __('A valid Apply All run was not provided.', 'modfarm')], 400);
    }

    $run = modfarm_ppb_get_apply_all_run($run_id);
    if (empty($run)) {
        wp_send_json_error(['message' => __('That Apply All run could not be found.', 'modfarm')], 404);
    }

    $run = modfarm_ppb_process_run_batch($run);
    $completed = (($run['status'] ?? '') === 'completed');

    if ($completed) {
        modfarm_ppb_delete_apply_all_run($run_id);
        modfarm_ppb_append_apply_all_run_log($run);
    } else {
        modfarm_ppb_store_apply_all_run($run);
    }

    $response = [
        'runId' => (string) $run['run_id'],
        'completed' => $completed,
        'run' => modfarm_ppb_get_apply_all_run_progress($run),
    ];

    if ($completed) {
        $response['html'] = (($run['run_type'] ?? 'apply_all') === 'safe_convert')
            ? modfarm_render_ppb_safe_convert_result_markup($run)
            : modfarm_render_ppb_apply_all_result_markup($run);
        $response['runLogHtml'] = modfarm_render_ppb_run_log_markup();
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_modfarm_ppb_apply_all_process_run', 'modfarm_ajax_ppb_apply_all_process_run');


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
        'offer_header_pattern',
        'offer_body_pattern',
        'offer_footer_pattern',
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
                                    <div class="mf-theme-visualizer" id="mf-theme-live-preview">
                                        <header class="mf-theme-visualizer__nav">
                                            <div class="mf-theme-visualizer__brand">
                                                <span class="mf-theme-visualizer__mark"></span>
                                                <span class="mf-theme-visualizer__site-title">Test Site 1</span>
                                            </div>
                                            <nav class="mf-theme-visualizer__links" aria-label="Preview navigation">
                                                <a href="#">Books</a>
                                                <a href="#">About</a>
                                                <a href="#">Updates</a>
                                            </nav>
                                        </header>
                                        <main class="mf-theme-visualizer__page">
                                            <section class="mf-theme-visualizer__hero">
                                                <p class="mf-theme-visualizer__eyebrow">Featured Series</p>
                                                <h3>Void Drifter</h3>
                                                <p>Action-forward science fiction with cinematic covers, fast entry points, and clean reader paths.</p>
                                                <div class="mf-theme-visualizer__actions">
                                                    <a class="mf-theme-visualizer__button" href="#">Start Reading</a>
                                                    <a class="mf-theme-visualizer__text-link" href="#">Latest News</a>
                                                </div>
                                            </section>
                                            <section class="mf-theme-visualizer__book-row" aria-label="Preview books">
                                                <div class="mf-theme-visualizer__book"></div>
                                                <div class="mf-theme-visualizer__book"></div>
                                                <div class="mf-theme-visualizer__book"></div>
                                            </section>
                                        </main>
                                        <footer class="mf-theme-visualizer__footer">
                                            <a href="#">Home</a>
                                            <a href="#">Catalog</a>
                                            <a href="#">Contact</a>
                                        </footer>
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
                                    <div class="mf-book-visualizer" id="mf-book-live-preview">
                                        <article class="mf-book-visualizer__card">
                                            <div class="mf-book-visualizer__cover">
                                                <span>BOOK<br>TITLE</span>
                                            </div>
                                            <a class="mf-book-visualizer__primary" href="#">See The Book</a>
                                            <a class="mf-book-visualizer__sample" href="#">Read Sample</a>
                                            <div class="mf-book-visualizer__meta">
                                                <div class="mf-book-visualizer__title">Book Title</div>
                                                <div class="mf-book-visualizer__series">Series Name</div>
                                            </div>
                                        </article>
                                        <div class="mf-book-visualizer__page-buttons">
                                            <a class="mf-book-visualizer__page-primary" href="#">Buy Now</a>
                                            <a class="mf-book-visualizer__page-secondary" href="#">Read Sample</a>
                                        </div>
                                        <nav class="mf-book-visualizer__pagination" aria-label="Preview pagination">
                                            <a href="#">1</a>
                                            <span>2</span>
                                            <a href="#">3</a>
                                        </nav>
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

                            <div class="mf-ppb-layout-tabs" role="tablist" aria-label="Page layout content type">
                                <?php $mf_viz_types = modfarm_get_ppb_visualizer_content_types(); ?>
                                <?php foreach ($mf_viz_types as $value => $type_config) : ?>
                                    <button
                                        type="button"
                                        class="mf-ppb-layout-tab<?php echo $value === 'book' ? ' is-active' : ''; ?>"
                                        data-ppb-layout-type="<?php echo esc_attr($value); ?>"
                                        aria-selected="<?php echo $value === 'book' ? 'true' : 'false'; ?>">
                                        <?php echo esc_html($type_config['label']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="mf-settings-grid">
                                <div class="mf-settings-main">
                                    <div class="mf-settings-group mf-ppb-layout-sample">
                                        <h3 class="mf-group-title">Preview Content</h3>
                                        <label for="mf-ppb-visualizer-sample" class="screen-reader-text">Preview Content</label>
                                        <select id="mf-ppb-visualizer-sample"></select>
                                    </div>

                                    <div class="mf-settings-group mf-ppb-layout-panel is-active" data-ppb-layout-type="book">
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

                                    <div class="mf-settings-group mf-ppb-layout-panel" data-ppb-layout-type="page">
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

                                    <div class="mf-settings-group mf-ppb-layout-panel" data-ppb-layout-type="post">
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

                                    <div class="mf-settings-group mf-ppb-layout-panel" data-ppb-layout-type="<?php echo post_type_exists('offer') ? 'offer' : 'mf_offer'; ?>">
                                        <h3 class="mf-group-title">Offer Layout</h3>
                                        <table class="form-table mf-form-table">
                                            <tbody>
                                            <tr>
                                                <th scope="row"><label>Offer Header Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'offer_header_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Offer Body Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'offer_body_pattern']); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row"><label>Offer Footer Pattern</label></th>
                                                <td><?php modfarm_pattern_dropdown(['id' => 'offer_footer_pattern']); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mf-settings-group mf-ppb-layout-panel" data-ppb-layout-type="archive">
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
                                            Central PPB selectors set defaults for future content and dynamic Hybrid chrome. Apply Previewed Change updates existing zoned content only.
                                        </p>
                                        <p class="description">
                                            Locks prevent zone replacement. Matching <code>content-slot</code> IDs preserve portable manual content during zone changes. Hybrid posts follow central defaults unless a local override is active.
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
                                                <span>I understand this will update the previewed Header, Body, or Footer zones sitewide for matching zoned items only.</span>
                                            </label>
                                            <button type="button" class="button button-primary" id="mf-ppb-preview-apply" disabled>
                                                Apply Previewed Change
                                            </button>
                                        </div>

                                        <div class="mf-settings-group">
                                            <h4 class="mf-group-title">Bulk Safe Convert</h4>
                                            <p class="description">
                                                Safe Convert adds empty Header and Footer zones and preserves all existing content inside Body Zone. This is intended for Legacy PPB and Plain content that needs the full PPB toolset.
                                            </p>

                                            <div class="mf-ppb-preview-controls" id="mf-ppb-safe-convert-preview">
                                                <div class="mf-ppb-preview-field">
                                                    <label for="mf-ppb-safe-convert-content-type">Content Type</label>
                                                    <select id="mf-ppb-safe-convert-content-type">
                                                        <?php foreach (modfarm_get_ppb_apply_all_content_types() as $value => $label) : ?>
                                                            <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mf-ppb-preview-actions">
                                                    <button type="button" class="button button-secondary" id="mf-ppb-safe-convert-run">
                                                        Preview Safe Convert
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mf-ppb-preview-feedback" id="mf-ppb-safe-convert-feedback" aria-live="polite"></div>
                                            <div class="mf-ppb-preview-results" id="mf-ppb-safe-convert-results"></div>
                                            <div class="mf-ppb-preview-execute" id="mf-ppb-safe-convert-execute" hidden>
                                                <label class="mf-ppb-preview-confirm">
                                                    <input type="checkbox" id="mf-ppb-safe-convert-confirm">
                                                    <span>I understand this will convert the previewed Legacy or Plain items to Zoned PPB by preserving current content inside Body Zone and adding empty Header and Footer zones.</span>
                                                </label>
                                                <button type="button" class="button button-primary" id="mf-ppb-safe-convert-apply" disabled>
                                                    Run Safe Convert
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mf-ppb-run-log-wrap" id="mf-ppb-run-log">
                                            <?php echo modfarm_render_ppb_run_log_markup(); ?>
                                        </div>
                                    </div>
                                </div>

                                <aside class="mf-settings-preview mf-ppb-visualizer" id="mf-ppb-visualizer">
                                    <div class="mf-ppb-visualizer__head">
                                        <div>
                                            <h3 class="mf-ppb-visualizer__title">Preview</h3>
                                            <p class="description">Live view of the selected Header, Body, and Footer patterns.</p>
                                        </div>
                                    </div>

                                    <div class="mf-ppb-visualizer__viewports" role="group" aria-label="Preview viewport">
                                        <button type="button" class="mf-ppb-visualizer__viewport is-active" data-viewport="desktop" data-width="1200" aria-pressed="true">Desktop</button>
                                        <button type="button" class="mf-ppb-visualizer__viewport" data-viewport="tablet" data-width="768" aria-pressed="false">Tablet</button>
                                        <button type="button" class="mf-ppb-visualizer__viewport" data-viewport="mobile" data-width="390" aria-pressed="false">Phone</button>
                                    </div>

                                    <div class="mf-ppb-visualizer__frame-wrap" data-viewport="desktop">
                                        <iframe
                                            id="mf-ppb-visualizer-frame"
                                            class="mf-ppb-visualizer__frame"
                                            title="PPB layout preview"
                                            scrolling="yes"
                                            sandbox="allow-same-origin">
                                        </iframe>
                                    </div>

                                    <div class="mf-ppb-visualizer__feedback" id="mf-ppb-visualizer-feedback" aria-live="polite"></div>
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
    $color_picker_js_path = get_template_directory() . '/assets/js/color-picker-init.js';
    wp_enqueue_script(
        'modfarm-color-picker',
        get_template_directory_uri() . '/assets/js/color-picker-init.js',
        ['wp-color-picker'],
        file_exists($color_picker_js_path) ? filemtime($color_picker_js_path) : '1.0.0',
        true
    );

    // Tabbed UI styles + script
    $settings_css_path = get_template_directory() . '/assets/css/modfarm-settings-ui.css';
    $settings_js_path = get_template_directory() . '/assets/js/modfarm-settings-ui.js';

    wp_enqueue_style(
        'modfarm-settings-ui',
        get_template_directory_uri() . '/assets/css/modfarm-settings-ui.css',
        [],
        file_exists($settings_css_path) ? filemtime($settings_css_path) : '1.0.0'
    );
    wp_enqueue_script(
        'modfarm-settings-ui',
        get_template_directory_uri() . '/assets/js/modfarm-settings-ui.js',
        [],
        file_exists($settings_js_path) ? filemtime($settings_js_path) : '1.0.0',
        true
    );

    wp_localize_script('modfarm-settings-ui', 'modfarmSettingsUi', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'previewNonce' => wp_create_nonce('modfarm_ppb_apply_all_preview'),
        'executeNonce' => wp_create_nonce('modfarm_ppb_apply_all_execute'),
        'safeConvertPreviewNonce' => wp_create_nonce('modfarm_ppb_safe_convert_preview'),
        'safeConvertExecuteNonce' => wp_create_nonce('modfarm_ppb_safe_convert_execute'),
        'processNonce' => wp_create_nonce('modfarm_ppb_apply_all_process_run'),
        'visualizerNonce' => wp_create_nonce('modfarm_ppb_visualizer_preview'),
        'applyAllPatterns' => modfarm_get_ppb_apply_all_pattern_matrix(),
        'contentTypeLabels' => modfarm_get_ppb_apply_all_content_types(),
        'visualizerTypes' => modfarm_get_ppb_visualizer_content_types(),
        'visualizerSamples' => modfarm_get_ppb_visualizer_samples(),
        'bookPreviewSample' => modfarm_get_book_presentation_preview_sample(),
        'messages' => [
            'loading' => __('Scanning matching items...', 'modfarm'),
            'missingPattern' => __('Select a valid pattern before running the preview.', 'modfarm'),
            'noPatterns' => __('No central PPB patterns are registered for this content type and zone yet.', 'modfarm'),
            'error' => __('Preview could not be generated.', 'modfarm'),
            'executing' => __('Applying the previewed change...', 'modfarm'),
            'executingConvert' => __('Converting the previewed items to Zoned PPB...', 'modfarm'),
            'processing' => __('Processing the next Apply All batch...', 'modfarm'),
            'processingConvert' => __('Processing the next Safe Convert batch...', 'modfarm'),
            'confirmRequired' => __('Confirm the change before applying it.', 'modfarm'),
            'executionUnavailable' => __('Apply All execution is currently available for Header, Body, and Footer zones only.', 'modfarm'),
            'convertConfirmRequired' => __('Confirm the conversion before running Safe Convert.', 'modfarm'),
        ],
        'previewPageSize' => 50,
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

    $primary    = $opts['primary_color']      ?? '';
    $secondary  = $opts['secondary_color']    ?? '';
    $background = $opts['background_color']   ?? '#ffffff';
    $header     = $opts['header_text_color']  ?? '';
    $body       = $opts['body_text_color']    ?? '#1d2327';
    $link       = $opts['link_color']         ?? ($primary ?: $body);
    $btn_bg     = $opts['button_color']       ?? ($primary ?: '#222222');
    $btn_fg     = $opts['button_text_color']  ?? '#ffffff';

    $vars = [];

    if ($primary)    $vars['--mfs-primary']    = $primary;
    if ($secondary)  $vars['--mfs-secondary']  = $secondary;
    if ($background) $vars['--mfs-background'] = $background;
    if ($header)     $vars['--mfs-heading']    = $header;
    if ($body)       $vars['--mfs-body']       = $body;
    if ($link)       $vars['--mfs-link']       = $link;

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
