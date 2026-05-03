<?php
/**
 * ModFarm Theme Functions
 * Loads block types, settings, utilities, and admin tools.
 */

// Theme setup and media support
function modfarm_author_setup() {
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');
    add_theme_support('layout');
    add_theme_support('custom-spacing');
    add_theme_support('custom-units');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('post-thumbnails');
    add_theme_support('core-block-patterns'); //Added for Archive Support
    add_theme_support('block-patterns'); //Added for Archive Support
    add_theme_support( 'title-tag' );

    add_filter('upload_mimes', function($mimes) {
        $mimes['epub'] = 'application/epub+zip';
        $mimes['mobi'] = 'application/x-mobipocket-ebook';
        return $mimes;
    });

    register_block_pattern_category('modfarm', ['label' => __('ModFarm Patterns', 'modfarm-author')]);
}
add_action('after_setup_theme', 'modfarm_author_setup');

require_once get_template_directory() . '/inc/content-slot-payloads.php';

add_action('after_setup_theme', function() {
    register_nav_menus([
        'primary'   => __('Primary Menu (Header)', 'modfarm-theme'),
        'secondary' => __('Secondary Menu (Header)', 'modfarm-theme'),
        'footer'    => __('Footer Menu', 'modfarm-theme'),
    ]);
});

/**
 * Load editor styles without @import.
 * - Child-theme safe
 * - Cache-busted via filemtime
 * - Works in block editor + site editor
 */
add_action('after_setup_theme', function () {
    // Enables editor styles
    add_theme_support('editor-styles');
});

add_filter( 'admin_email_check_interval', '__return_false' );

add_action('enqueue_block_editor_assets', function () {
    // List your editor CSS files here (relative to the theme root)
    $editor_styles = [
        'assets/css/editor-style.css', // your base editor stylesheet
        'assets/css/common.css',
        'assets/css/footers.css',
        'assets/css/book-cards.css',
    ];

    foreach ($editor_styles as $rel_path) {
        $path = get_theme_file_path($rel_path); // child-safe path
        $uri  = get_theme_file_uri($rel_path);  // child-safe URL

        if (file_exists($path)) {
            wp_enqueue_style(
                'mf-editor-' . md5($rel_path),
                $uri,
                [],
                filemtime($path)
            );
        }
    }
});

add_action('enqueue_block_editor_assets', function () {
    wp_register_script(
        'modfarm-editor-deps',
        get_template_directory_uri() . '/assets/js/modfarm-editor-deps.js',
        ['wp-data', 'wp-compose', 'wp-core-data'],
        filemtime(get_template_directory() . '/assets/js/modfarm-editor-deps.js'),
        true
    );
    wp_enqueue_script('modfarm-editor-deps');
});

add_action('enqueue_block_editor_assets', function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->base !== 'post') {
        return;
    }

    $supported_types = ['page', 'book', 'post', 'offer', 'mf_offer'];
    if (!in_array((string) $screen->post_type, $supported_types, true)) {
        return;
    }

    $post_id = 0;
    if (!empty($_GET['post'])) {
        $post_id = absint($_GET['post']);
    } elseif (!empty($_POST['post_ID'])) {
        $post_id = absint($_POST['post_ID']);
    }

    $panel_config = [
        'content_state' => 'Plain',
        'layout_mode' => modfarm_get_ppb_layout_mode_for_post(0, (string) $screen->post_type, []),
        'zones' => [
            'header' => ['present' => false, 'pattern' => '', 'locked' => false, 'contains_content_slot' => false],
            'body'   => ['present' => false, 'pattern' => '', 'locked' => false, 'contains_content_slot' => false],
            'footer' => ['present' => false, 'pattern' => '', 'locked' => false, 'contains_content_slot' => false],
            'data'   => ['present' => false, 'pattern' => '', 'locked' => false, 'contains_content_slot' => false],
        ],
        'actions' => [
            'mode' => 'disabled',
            'zones' => [
                'header' => ['enabled' => false, 'meta_key' => '', 'patterns' => []],
                'footer' => ['enabled' => false, 'meta_key' => '', 'patterns' => []],
            ],
        ],
    ];

    if ($post_id > 0) {
        $panel_config = modfarm_get_local_ppb_manager_config_for_post($post_id, (string) $screen->post_type);
    }

    wp_register_script(
        'modfarm-ppb-zones-panel',
        get_template_directory_uri() . '/assets/js/ppb-zones-panel.js',
        ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-blocks'],
        filemtime(get_template_directory() . '/assets/js/ppb-zones-panel.js'),
        true
    );

    wp_register_style(
        'modfarm-ppb-zones-panel',
        get_template_directory_uri() . '/assets/css/ppb-zones-panel.css',
        [],
        filemtime(get_template_directory() . '/assets/css/ppb-zones-panel.css')
    );

    wp_enqueue_script('modfarm-ppb-zones-panel');
    wp_enqueue_style('modfarm-ppb-zones-panel');
    wp_add_inline_script(
        'modfarm-ppb-zones-panel',
        'window.ModFarmPPBZonesPanel = ' . wp_json_encode([
            'enabled' => true,
            'summary' => $panel_config,
        ]) . ';',
        'before'
    );
});

add_action('init', function () {
    $meta_keys = modfarm_ppb_local_chrome_override_meta_keys();
    $shared_args = [
        'single' => true,
        'type' => 'string',
        'show_in_rest' => true,
        'auth_callback' => static function (): bool {
            return current_user_can('edit_posts');
        },
        'sanitize_callback' => static function ($value): string {
            if (!function_exists('modfarm_ppb_normalize_slug')) {
                return is_string($value) ? sanitize_text_field($value) : '';
            }

            $normalized = modfarm_ppb_normalize_slug($value);
            return $normalized !== '' ? sanitize_text_field($normalized) : '';
        },
    ];

    foreach (['page', 'post', 'book', 'offer', 'mf_offer'] as $post_type) {
        if (!post_type_exists($post_type)) {
            continue;
        }

        foreach ($meta_keys as $meta_key) {
            register_post_meta($post_type, $meta_key, $shared_args);
        }
    }
});

/**
 * Global semantic tokens for Block Styles (works in frontend + editor iframe).
 */
add_action('enqueue_block_assets', function () {
    $opts = get_option('modfarm_theme_settings', []);

    $primary   = $opts['primary_color']     ?? '';
    $secondary = $opts['secondary_color']   ?? '';
    $btn_bg    = $opts['button_color']      ?? ($primary ?: '#222222');
    $btn_fg    = $opts['button_text_color'] ?? '#ffffff';

    $vars = [];
    if ($primary)   $vars['--mfs-primary']   = $primary;
    if ($secondary) $vars['--mfs-secondary'] = $secondary;
    if ($btn_bg)    $vars['--mfs-button-bg'] = $btn_bg;
    if ($btn_fg)    $vars['--mfs-button-fg'] = $btn_fg;

    if (empty($vars)) return;

    $css = ":root{\n";
    foreach ($vars as $k => $v) {
        $css .= "  {$k}: {$v};\n";
    }
    $css .= "}\n";

    // Attach to a guaranteed style handle in both editor + frontend.
    // wp-block-library is safe, but make sure it's enqueued.
    wp_enqueue_style('wp-block-library');
    wp_add_inline_style('wp-block-library', $css);
}, 5);


/**
 * Block Style CSS (frontend + editor iframe).
 */
add_action('enqueue_block_assets', function () {
    $css = <<<CSS
/* ModFarm Block Styles (Phase 1) */

/* Group backgrounds */
.wp-block-group.is-style-mf-primary {
  background-color: var(--mfs-primary);
}
.wp-block-group.is-style-mf-secondary {
  background-color: var(--mfs-secondary);
}

/* Buttons */
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

    wp_enqueue_style('wp-block-library');
    wp_add_inline_style('wp-block-library', $css);
}, 20);

/**
 * Inject ModFarm colors into the editor palette (ColorPalette + global color UI).
 */
add_filter('block_editor_settings_all', function ($settings) {
    $opts = get_option('modfarm_theme_settings', []);

    $primary   = $opts['primary_color']     ?? '';
    $secondary = $opts['secondary_color']   ?? '';
    $btn_bg    = $opts['button_color']      ?? '';
    $btn_fg    = $opts['button_text_color'] ?? '';

    $palette = [];

    if ($primary)   $palette[] = ['name' => 'Primary', 'slug' => 'mf-primary', 'color' => $primary];
    if ($secondary) $palette[] = ['name' => 'Secondary', 'slug' => 'mf-secondary', 'color' => $secondary];
    if ($btn_bg)    $palette[] = ['name' => 'Button', 'slug' => 'mf-button', 'color' => $btn_bg];
    if ($btn_fg)    $palette[] = ['name' => 'Button Text', 'slug' => 'mf-button-text', 'color' => $btn_fg];

    if (empty($palette)) return $settings;

    // Modern location Gutenberg uses:
    if (!isset($settings['__experimentalFeatures'])) $settings['__experimentalFeatures'] = [];
    if (!isset($settings['__experimentalFeatures']['color'])) $settings['__experimentalFeatures']['color'] = [];

    $settings['__experimentalFeatures']['color']['palette'] = $palette;

    return $settings;
}, 20);

/**
 * Make the editor palette reflect ModFarm Settings.
 * Overrides theme.json palette with actual hex colors (not CSS vars),
 * so the swatches render correctly and match the site.
 */
add_filter('wp_theme_json_data_theme', function ($theme_json) {
    $opts = get_option('modfarm_theme_settings', []);

    // Pull your ModFarm settings (hex expected)
    $base      = $opts['background_color']  ?? '#ffffff';
    $text      = $opts['body_text_color']   ?? '#111111';
    $accent    = $opts['link_color']        ?? '#0066cc';
    $highlight = $opts['primary_color']     ?? '#336699';
    $gray      = '#cdcdcd';

    // If you also want "Button" available in palette:
    $button    = $opts['button_color']      ?? $highlight;
    $buttonTxt = $opts['button_text_color'] ?? '#ffffff';

    // Build palette with REAL colors
    $palette = [
        [ 'slug' => 'base',      'color' => $base,      'name' => 'Base' ],
        [ 'slug' => 'body-text',      'color' => $text,      'name' => 'Text' ],
        [ 'slug' => 'accent',    'color' => $accent,    'name' => 'Accent Link' ],
        [ 'slug' => 'highlight', 'color' => $highlight, 'name' => 'Primary' ],
        [ 'slug' => 'gray',      'color' => $gray,      'name' => 'Light Gray' ],

        // Optional extra swatches:
        [ 'slug' => 'mf-button',      'color' => $button,    'name' => 'Button' ],
        [ 'slug' => 'mf-button-text', 'color' => $buttonTxt, 'name' => 'Button Text' ],
    ];

    $data = $theme_json->get_data();

    // Ensure structure exists
    $data['settings']['color']['custom'] = true;
    $data['settings']['color']['defaultPalette'] = false;
    $data['settings']['color']['palette'] = $palette;

    return new WP_Theme_JSON_Data($data, 'theme');
}, 100);


/**
 * Create default menus on theme switch (runs per-site in multisite).
 * - Creates Primary / Secondary / Footer menus if missing
 * - Adds a placeholder link to each
 * - Assigns them to the registered menu locations
 */
add_action('after_switch_theme', function () {

    $menus_to_create = [
        'Primary Menu' => [
            'location' => 'primary',
            'items'    => [
                ['title' => 'Placeholder Link', 'url' => home_url('/')],
            ],
        ],
        'Secondary Menu' => [
            'location' => 'secondary',
            'items'    => [
                ['title' => 'Placeholder Link', 'url' => home_url('/')],
            ],
        ],
        'Footer Menu' => [
            'location' => 'footer',
            'items'    => [
                ['title' => 'Placeholder Link', 'url' => home_url('/')],
            ],
        ],
    ];

    foreach ($menus_to_create as $menu_name => $config) {

        $menu_obj = wp_get_nav_menu_object($menu_name);

        // If menu already exists, do nothing (prevents duplicates)
        if ($menu_obj && !empty($menu_obj->term_id)) {
            continue;
        }

        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id) || !$menu_id) {
            continue;
        }

        // Add items
        foreach ($config['items'] as $item) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'  => $item['title'],
                'menu-item-url'    => $item['url'],
                'menu-item-status' => 'publish',
            ]);
        }

        // Assign to theme location
        if (!empty($config['location'])) {
            $locations = (array) get_theme_mod('nav_menu_locations', []);
            $locations[$config['location']] = (int) $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
});


/**
 * Theme supports: logo + site icon.
 */
add_action('after_setup_theme', function () {
    add_theme_support('custom-logo', [
        'height'      => 256,
        'width'       => 256,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    add_theme_support('site-icon'); // WordPress Site Icon (favicon)
});



add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'modfarm-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_style(
        'modfarm-common-style',
        get_template_directory_uri() . '/assets/css/common.css',
        ['modfarm-style'],
        '1.0'
    );
    
    wp_enqueue_style(
        'modfarm-footers',
        get_template_directory_uri() . '/assets/css/footers.css',
        ['modfarm-common-style'],
        '1.0'
    );
});

function modfarm_output_nav_custom_properties() {
    $options = get_option('modfarm_theme_settings');

    $styles = ':root {';

    if (!empty($options['nav_font'])) {
        $styles .= '--mf-nav-font: ' . esc_attr($options['nav_font']) . ';';
    }
    if (!empty($options['nav_font_size'])) {
        $styles .= '--mf-nav-font-size: ' . intval($options['nav_font_size']) . 'px;';
    }
    if (!empty($options['nav_text_color'])) {
        $styles .= '--mf-nav-color: ' . esc_attr($options['nav_text_color']) . ';';
    }
    if (!empty($options['nav_bg_color'])) {
        $styles .= '--mf-nav-bg: ' . esc_attr($options['nav_bg_color']) . ';';
    }
    if (!empty($options['nav_hover_color'])) {
        $styles .= '--mf-nav-hover-color: ' . esc_attr($options['nav_hover_color']) . ';';
    }
    if (!empty($options['submenu_bg_color'])) {
        $styles .= '--submenu-bg: ' . esc_attr($options['submenu_bg_color']) . ';';
    }
    if (!empty($options['submenu_text_color'])) {
        $styles .= '--submenu-color: ' . esc_attr($options['submenu_text_color']) . ';';
    }

    $styles .= '}';

    echo '<style>' . $styles . '</style>';
}

add_action('wp_head', 'modfarm_output_nav_custom_properties');
add_action('admin_head', 'modfarm_output_nav_custom_properties');

require_once get_template_directory() . '/inc/theme-settings.php';
require_once get_template_directory() . '/inc/author-meta.php';
require_once get_template_directory() . '/blocks/register-blocks.php'; // ✅ All blocks now loaded here
require_once get_template_directory() . '/inc/pattern-category-registration.php';
require_once get_template_directory() . '/inc/modfarm-admin-panel.php';
require_once get_template_directory() . '/inc/modfarm-settings.php';
require_once get_template_directory() . '/inc/archive-settings.php';
require_once get_template_directory() . '/inc/query-books.php';
require_once get_template_directory() . '/inc/render-helpers.php';
require_once get_template_directory() . '/inc/ppb-zone-detector.php';


add_action('wp_enqueue_scripts', function () {
    $theme = get_template_directory_uri();
    $dir   = get_template_directory();
    wp_enqueue_style('modfarm-book-cards', $theme . '/assets/css/book-cards.css', [], filemtime($dir . '/assets/css/book-cards.css'));
    wp_enqueue_script('modfarm-book-analytics', $theme . '/assets/js/analytics-book-cards.js', [], filemtime($dir . '/assets/js/analytics-book-cards.js'), true);
});



if ( is_admin() ) {
    add_filter( 'block_categories_all', 'modfarm_register_block_categories', 1 );
}

function modfarm_register_block_categories( $categories ) {
    $slugs = wp_list_pluck( $categories, 'slug' );

    if ( ! in_array( 'modfarm-book-page', $slugs, true ) ) {
        $categories[] = [
            'slug'  => 'modfarm-book-page',
            'title' => __( 'ModFarm Book Page', 'modfarm' ),
        ];
    }

    if ( ! in_array( 'modfarm-theme', $slugs, true ) ) {
        $categories[] = [
            'slug'  => 'modfarm-theme',
            'title' => __( 'ModFarm Theme', 'modfarm' ),
        ];
    }

    if ( ! in_array( 'modfarm-store', $slugs, true ) ) {
        $categories[] = [
            'slug'  => 'modfarm-store',
            'title' => __( 'ModFarm Store', 'modfarm' ),
        ];
    }

    return $categories;
}

add_action('init', function () {
    
    // Book zones
    register_block_pattern_category('modfarm-book-header', [ 'label' => 'ModFarm Book Headers' ]);
    register_block_pattern_category('modfarm-book-body',   [ 'label' => 'ModFarm Book Layouts' ]);
    register_block_pattern_category('modfarm-book-footer', [ 'label' => 'ModFarm Book Footers' ]);
    
    // Page zones
    register_block_pattern_category('modfarm-page-header', [ 'label' => 'ModFarm Page Headers' ]);
    register_block_pattern_category('modfarm-page-body',   [ 'label' => 'ModFarm Page Layouts' ]);
    register_block_pattern_category('modfarm-page-footer', [ 'label' => 'ModFarm Page Footers' ]);

    // Post zones
    register_block_pattern_category('modfarm-post-header', [ 'label' => 'ModFarm Post Headers' ]);
    register_block_pattern_category('modfarm-post-body',   [ 'label' => 'ModFarm Post Layouts' ]);
    register_block_pattern_category('modfarm-post-footer', [ 'label' => 'ModFarm Post Footers' ]);

    // Offer zones
    register_block_pattern_category('modfarm-offer-header', [ 'label' => 'ModFarm Offer Headers' ]);
    register_block_pattern_category('modfarm-offer-body',   [ 'label' => 'ModFarm Offer Layouts' ]);
    register_block_pattern_category('modfarm-offer-footer', [ 'label' => 'ModFarm Offer Footers' ]);

    // Archive zones
    register_block_pattern_category('modfarm-archive-header', [ 'label' => 'ModFarm Archive Headers' ]);
    register_block_pattern_category('modfarm-archive-body',   [ 'label' => 'ModFarm Archive Layouts' ]);
    register_block_pattern_category('modfarm-archive-footer', [ 'label' => 'ModFarm Archive Footers' ]);

    // ===== Element libraries =====
    register_block_pattern_category('modfarm-book-elements',    [ 'label' => 'ModFarm Book Elements' ]);
    register_block_pattern_category('modfarm-page-elements',    [ 'label' => 'ModFarm Page Elements' ]);
    register_block_pattern_category('modfarm-post-elements',    [ 'label' => 'ModFarm Post Elements' ]);
    register_block_pattern_category('modfarm-offer-elements',   [ 'label' => 'ModFarm Offer Elements' ]);
    register_block_pattern_category('modfarm-archive-elements', [ 'label' => 'ModFarm Archive Elements' ]);

    $base_dir = get_template_directory() . '/inc/patterns';

    // Recursive scan for *.php pattern files (supports subfolders)
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $pattern = require $file->getPathname();

        if (
            is_array($pattern) &&
            isset($pattern['title'], $pattern['content']) &&
            is_string($pattern['title']) &&
            is_string($pattern['content'])
        ) {
            $slug = $pattern['slug'] ?? 'modfarm/' . sanitize_title($pattern['title']);
            register_block_pattern($slug, $pattern);
        }
    }
}, 1);


add_action('init', function () {
    $user_patterns = get_posts([
        'post_type'   => 'wp_block',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    foreach ($user_patterns as $post) {
        $content = isset($post->post_content) ? (string) $post->post_content : '';
        $title   = isset($post->post_title) ? (string) $post->post_title : '';

        if ($title === '' || $content === '') continue;

        // ✅ Do NOT call has_blocks() here (it can trigger parser warnings if any wp_block is malformed)
        if (strpos($content, '<!-- wp:') === false) continue;

        $slug  = 'user/' . sanitize_title($post->post_name);

        $terms = wp_get_post_terms($post->ID, 'wp_pattern_category', ['fields' => 'slugs']);
        $categories = !empty($terms) ? $terms : ['modfarm-body'];

        register_block_pattern($slug, [
            'title'       => $title,
            'content'     => $content,
            'description' => 'User-created pattern',
            'categories'  => $categories,
        ]);
    }
}, 9);


/**
 * Canonical PPB fallback defaults.
 *
 * Fresh sites and sites with unused PPB settings must still resolve to
 * usable patterns, even when the settings UI stores empty or placeholder values.
 */
function modfarm_ppb_canonical_defaults(): array {
    return [
        'archive_header_pattern'            => 'modfarm/archive-header-basic',
        'archive_body_pattern'              => 'modfarm/basic-archive-layout',
        'archive_footer_pattern'            => 'modfarm/footer-simple',
        'archive_body_pattern_book_series'  => 'modfarm/basic-archive-layout',
        'archive_body_pattern_book_genre'   => 'modfarm/basic-archive-layout',
        'archive_body_pattern_book_authors' => 'modfarm/basic-archive-layout',
        'book_header_pattern'               => 'modfarm/book-header-basic-left',
        'book_body_pattern'                 => 'modfarm/book-plain-left-series-left',
        'book_footer_pattern'               => 'modfarm/footer-simple',
        'page_header_pattern'               => 'modfarm/page-header-basic-left',
        'page_body_pattern'                 => 'modfarm/page-clear',
        'page_footer_pattern'               => 'modfarm/footer-simple',
        'post_header_pattern'               => 'modfarm/post-header-basic-left',
        'post_body_pattern'                 => 'modfarm/post-body-basic',
        'post_footer_pattern'               => 'modfarm/post-footer-simple-comments',
        'offer_header_pattern'              => 'modfarm/offer-header-basic-left',
        'offer_body_pattern'                => 'modfarm/offer-body-basic',
        'offer_footer_pattern'              => 'modfarm/offer-footer-simple',
    ];
}

/**
 * Map legacy PPB slugs to the canonical slugs currently registered in the theme.
 */
function modfarm_ppb_legacy_slug_map(): array {
    return [
        'book-marquee-desc-aud-series-also-3col-centered' => 'modfarm/book-marquee-desc-aud-series-also-3col-centered',
        'book-marquee-desc-aud-series-also-3col-left-1'   => 'modfarm/book-marquee-desc-aud-series-also-3col-left-1',
        'book-marquee-desc-aud-series-also-3col-left'     => 'modfarm/book-marquee-desc-aud-series-also-3col-left',
        'book-plain-left-series-also-centered'            => 'modfarm/book-plain-left-series-also-centered',
        'book-plain-left-series-also-left'                => 'modfarm/book-plain-left-series-also-left',
        'book-plain-left-series-centered'                 => 'modfarm/book-plain-left-series-centered',
        'book-plain-left-series-left'                     => 'modfarm/book-plain-left-series-left',
        'book-plain-right-series-also-centered'           => 'modfarm/book-plain-right-series-also-centered',
        'book-plain-right-series-centered'                => 'modfarm/book-plain-right-series-centered',
        'book-standard-left-series-also-centered'         => 'modfarm/book-standard-left-series-also-centered',
        'book-standard-left-series-also-left'             => 'modfarm/book-standard-left-series-also-left',
        'book-standard-no-image-left-series-left'         => 'modfarm/book-standard-no-image-left-series-left',
        'book-standard-no-image-right-series-center'      => 'modfarm/book-standard-no-image-right-series-center',
        'book-standard-right-series-also-centered'        => 'modfarm/book-standard-right-series-also-centered',
    ];
}

/**
 * Treat empty strings, null, "none", "default", and UI placeholders as missing.
 */
function modfarm_ppb_normalize_slug($slug): string {
    if (!is_string($slug)) {
        return '';
    }

    $slug = trim($slug);
    if ($slug === '') {
        return '';
    }

    $normalized = strtolower(trim($slug, " \t\n\r\0\x0B-—"));
    if ($normalized === '' || $normalized === 'none' || $normalized === 'default') {
        return '';
    }

    $legacy_map = modfarm_ppb_legacy_slug_map();
    if (isset($legacy_map[$slug])) {
        return $legacy_map[$slug];
    }

    return $slug;
}

/**
 * Check whether a PPB slug resolves to a registered pattern or user pattern.
 */
function modfarm_ppb_pattern_exists(string $slug): bool {
    $slug = modfarm_ppb_normalize_slug($slug);
    if ($slug === '') {
        return false;
    }

    if (str_starts_with($slug, 'user/')) {
        $post_name = substr($slug, 5);
        $post = get_page_by_path($post_name, OBJECT, 'wp_block');
        return $post && is_string($post->post_content) && strpos($post->post_content, '<!-- wp:') !== false;
    }

    if (function_exists('get_block_pattern')) {
        $p = get_block_pattern($slug);
        if (is_array($p) && !empty($p['content'])) {
            return true;
        }
    }

    if (class_exists('WP_Block_Patterns_Registry')) {
        $reg = WP_Block_Patterns_Registry::get_instance();
        if ($reg && method_exists($reg, 'get_registered')) {
            $p = $reg->get_registered($slug);
            if (is_array($p) && !empty($p['content'])) return true;
            if (is_object($p) && !empty($p->content))   return true;
        }
    }

    return false;
}

/**
 * Resolve the stored block pattern content for a given slug.
 */
function modfarm_ppb_get_pattern_content_by_slug(string $slug): string {
    $slug = modfarm_ppb_normalize_slug($slug);
    if ($slug === '') {
        return '';
    }

    if (str_starts_with($slug, 'user/')) {
        $post_name = substr($slug, 5);
        $post = get_page_by_path($post_name, OBJECT, 'wp_block');
        if ($post && is_string($post->post_content) && strpos($post->post_content, '<!-- wp:') !== false) {
            return (string) $post->post_content;
        }
        return '';
    }

    if (function_exists('get_block_pattern')) {
        $pattern = get_block_pattern($slug);
        if (is_array($pattern) && !empty($pattern['content'])) {
            return (string) $pattern['content'];
        }
    }

    if (class_exists('WP_Block_Patterns_Registry')) {
        $registry = WP_Block_Patterns_Registry::get_instance();
        if ($registry && method_exists($registry, 'get_registered')) {
            $pattern = $registry->get_registered($slug);
            if (is_array($pattern) && !empty($pattern['content'])) {
                return (string) $pattern['content'];
            }
            if (is_object($pattern) && !empty($pattern->content)) {
                return (string) $pattern->content;
            }
        }
    }

    return '';
}

/**
 * Local per-post PPB header/footer override meta keys used by Hybrid.
 */
function modfarm_ppb_local_chrome_override_meta_keys(): array {
    return [
        'header' => '_modfarm_ppb_local_header_pattern',
        'footer' => '_modfarm_ppb_local_footer_pattern',
    ];
}

/**
 * Check whether a post uses one of the Hybrid singular templates.
 */
function modfarm_ppb_is_hybrid_template_for_post(int $post_id, string $post_type = ''): bool {
    unset($post_type);

    if ($post_id <= 0) {
        return false;
    }

    $template_slug = (string) get_page_template_slug($post_id);
    return in_array($template_slug, ['singular-hybrid.php', 'singular-hybrid-sidebar.php'], true);
}

/**
 * Resolve a saved local Hybrid override to a valid pattern slug.
 */
function modfarm_ppb_get_local_chrome_override_slug(int $post_id, string $slot): string {
    $meta_keys = modfarm_ppb_local_chrome_override_meta_keys();
    if (!isset($meta_keys[$slot])) {
        return '';
    }

    $raw_value = get_post_meta($post_id, $meta_keys[$slot], true);
    $candidate = modfarm_ppb_normalize_slug($raw_value);

    return ($candidate !== '' && modfarm_ppb_pattern_exists($candidate)) ? $candidate : '';
}

/**
 * Map a post type + slot to the PPB settings field used for its pattern lane.
 */
function modfarm_ppb_get_field_id_for_post_zone(string $post_type, string $slot): string {
    $map = [
        'page' => [
            'header' => 'page_header_pattern',
            'body' => 'page_body_pattern',
            'footer' => 'page_footer_pattern',
        ],
        'post' => [
            'header' => 'post_header_pattern',
            'body' => 'post_body_pattern',
            'footer' => 'post_footer_pattern',
        ],
        'book' => [
            'header' => 'book_header_pattern',
            'body' => 'book_body_pattern',
            'footer' => 'book_footer_pattern',
        ],
        'modfarm_book' => [
            'header' => 'book_header_pattern',
            'body' => 'book_body_pattern',
            'footer' => 'book_footer_pattern',
        ],
        'offer' => [
            'header' => 'offer_header_pattern',
            'body' => 'offer_body_pattern',
            'footer' => 'offer_footer_pattern',
        ],
        'mf_offer' => [
            'header' => 'offer_header_pattern',
            'body' => 'offer_body_pattern',
            'footer' => 'offer_footer_pattern',
        ],
    ];

    return $map[$post_type][$slot] ?? '';
}

/**
 * Resolve the effective Hybrid header/footer slugs for a single post.
 */
function modfarm_ppb_get_effective_hybrid_chrome_slugs_for_post(int $post_id, string $post_type, ?array $options = null): array {
    $options = is_array($options) ? $options : get_option('modfarm_theme_settings', []);

    switch ($post_type) {
        case 'page':
            $resolved = [
                'header' => modfarm_ppb_resolve_pattern_slug('page_header_pattern', $options['page_header_pattern'] ?? null, $options),
                'footer' => modfarm_ppb_resolve_pattern_slug('page_footer_pattern', $options['page_footer_pattern'] ?? null, $options),
            ];
            break;

        case 'post':
        default:
            $resolved = [
                'header' => modfarm_ppb_resolve_pattern_slug('post_header_pattern', $options['post_header_pattern'] ?? null, $options),
                'footer' => modfarm_ppb_resolve_pattern_slug('post_footer_pattern', $options['post_footer_pattern'] ?? null, $options),
            ];
            break;
    }

    foreach (['header', 'footer'] as $slot) {
        $override = modfarm_ppb_get_local_chrome_override_slug($post_id, $slot);
        if ($override !== '') {
            $resolved[$slot] = $override;
        }
    }

    return $resolved;
}

/**
 * Resolve a PPB setting key to a usable pattern slug.
 *
 * This ensures fresh installs and unused settings fall back to canonical
 * patterns instead of rendering blank output.
 */
function modfarm_ppb_resolve_pattern_slug(string $key, $raw_value = null, ?array $options = null): string {
    $defaults = modfarm_ppb_canonical_defaults();
    $options  = is_array($options) ? $options : get_option('modfarm_theme_settings', []);

    if ($raw_value === null) {
        $raw_value = $options[$key] ?? null;
    }

    $candidate = modfarm_ppb_normalize_slug($raw_value);
    if ($candidate !== '' && modfarm_ppb_pattern_exists($candidate)) {
        return $candidate;
    }

    $fallback = modfarm_ppb_normalize_slug($defaults[$key] ?? '');
    if ($fallback !== '' && modfarm_ppb_pattern_exists($fallback)) {
        return $fallback;
    }

    return '';
}


function modfarm_detect_archive_image_type($taxonomy) {
    $map = [
        'book_audio'     => 'audiobook',
        'book_paperback' => '3d',
        'book_hardcover' => '3d',
        'book_genre'     => 'composite',
        'book_series'    => 'composite',
        'book_authors'   => 'kindle',
    ];
    return $map[$taxonomy] ?? 'kindle';
}

/**
 * Archive pattern resolution for PPB.
 * Uses ModFarm Settings (option) rather than theme_mods.
 *
 * Expected option keys (current UI):
 * - archive_header_pattern
 * - archive_body_pattern
 * - archive_footer_pattern
 * - archive_body_pattern_book_series
 * - archive_body_pattern_book_genre
 * - archive_body_pattern_book_authors
 *
 * Also supports a future generic convention:
 * - archive_body_pattern__{taxonomy}
 */
function modfarm_get_archive_patterns() {
    $opts = get_option('modfarm_theme_settings', []);

    // Fresh or unused sites must resolve to usable archive patterns.
    $default_header = modfarm_ppb_resolve_pattern_slug('archive_header_pattern', $opts['archive_header_pattern'] ?? null, $opts);
    $default_body   = modfarm_ppb_resolve_pattern_slug('archive_body_pattern', $opts['archive_body_pattern'] ?? null, $opts);
    $default_footer = modfarm_ppb_resolve_pattern_slug('archive_footer_pattern', $opts['archive_footer_pattern'] ?? null, $opts);

    $taxonomy_overrides = [
        'book_series'  => modfarm_ppb_resolve_pattern_slug('archive_body_pattern_book_series', $opts['archive_body_pattern_book_series'] ?? null, $opts),
        'book_genre'   => modfarm_ppb_resolve_pattern_slug('archive_body_pattern_book_genre', $opts['archive_body_pattern_book_genre'] ?? null, $opts),
        'book_authors' => modfarm_ppb_resolve_pattern_slug('archive_body_pattern_book_authors', $opts['archive_body_pattern_book_authors'] ?? null, $opts),
    ];

    /**
     * Generic expansion: if you later add settings like:
     * archive_body_pattern__book_paperback, archive_body_pattern__book_audio, etc
     * they will automatically apply without changing PHP.
     */
    foreach ($opts as $k => $v) {
        if (!is_string($k) || !str_starts_with($k, 'archive_body_pattern__')) continue;
        $tax = substr($k, strlen('archive_body_pattern__'));
        if ($tax) {
            $resolved = modfarm_ppb_resolve_pattern_slug('archive_body_pattern', $v, $opts);
            if ($resolved !== '') {
                $taxonomy_overrides[$tax] = $resolved;
            }
        }
    }

    return [
        'default' => [
            'header' => $default_header,
            'body'   => $default_body,
            'footer' => $default_footer,
        ],
        'taxonomy_overrides' => $taxonomy_overrides,
    ];
}



function modfarm_setup_theme_defaults() {
    set_theme_mod('archive_body_pattern', 'modfarm/basic-archive-layout');
    set_theme_mod('archive_header_pattern', 'modfarm/archive-header-basic');
    set_theme_mod('archive_footer_pattern', 'modfarm/footer-simple');
}

/**
 * Serialize a PPB-managed content region as an explicit ModFarm zone block.
 *
 * New PPB-created singular content should store replaceable header/body/footer
 * regions as visible editor zones while remaining frontend-invisible.
 */
function modfarm_ppb_build_zone_markup(string $slot, string $content, array $meta = []): string {
    $attrs = array_filter([
        'slot'    => $slot,
        'origin'  => $meta['origin']  ?? 'ppb',
        'pattern' => $meta['pattern'] ?? '',
        'locked'  => !empty($meta['locked']),
        'version' => isset($meta['version']) ? (int) $meta['version'] : 1,
    ], static function ($value, $key) {
        if ($key === 'locked') {
            return true;
        }
        return $value !== '';
    }, ARRAY_FILTER_USE_BOTH);

    $json = wp_json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $inner = trim($content);

    return "<!-- wp:modfarm/zone {$json} -->\n{$inner}\n<!-- /wp:modfarm/zone -->";
}


add_action('wp_insert_post', 'modfarm_assemble_post_layout_on_insert', 10, 3);

function modfarm_assemble_post_layout_on_insert($post_id, $post, $update) {
    // Never touch updates, revisions, or autosaves
    if ($update || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

    // ✅ Never assemble during imports
    if (defined('WP_IMPORTING') && WP_IMPORTING) return;
    if (!empty($_REQUEST['mf_import']) && $_REQUEST['mf_import'] === '1') return;

    // If Hybrid is in effect (or PPB body is disallowed), DO NOT assemble
    if (function_exists('modfarm_is_hybrid_post') && modfarm_is_hybrid_post($post_id)) return;
    if (false === apply_filters('modfarm_ppb_allow_body_for_post', true, $post_id)) return;

    // ✅ If content already exists in DB, do NOT overwrite it
    $existing = trim((string) get_post_field('post_content', $post_id));
    if ($existing !== '') return;

    $type     = $post->post_type;
    $options  = get_option('modfarm_theme_settings', []);
    $registry = WP_Block_Patterns_Registry::get_instance();

    switch ($type) {
        case 'page':
            $header_slug = modfarm_ppb_resolve_pattern_slug('page_header_pattern', $options['page_header_pattern'] ?? null, $options);
            $body_slug   = modfarm_ppb_resolve_pattern_slug('page_body_pattern', $options['page_body_pattern'] ?? null, $options);
            $footer_slug = modfarm_ppb_resolve_pattern_slug('page_footer_pattern', $options['page_footer_pattern'] ?? null, $options);
            break;

        case 'post':
            $header_slug = modfarm_ppb_resolve_pattern_slug('post_header_pattern', $options['post_header_pattern'] ?? null, $options);
            $body_slug   = modfarm_ppb_resolve_pattern_slug('post_body_pattern', $options['post_body_pattern'] ?? null, $options);
            $footer_slug = modfarm_ppb_resolve_pattern_slug('post_footer_pattern', $options['post_footer_pattern'] ?? null, $options);
            break;

        case 'book':
        case 'modfarm_book':
            $header_slug = modfarm_ppb_resolve_pattern_slug('book_header_pattern', $options['book_header_pattern'] ?? null, $options);
            $body_slug   = modfarm_ppb_resolve_pattern_slug('book_body_pattern', $options['book_body_pattern'] ?? null, $options);
            $footer_slug = modfarm_ppb_resolve_pattern_slug('book_footer_pattern', $options['book_footer_pattern'] ?? null, $options);
            break;

        case 'offer':
        case 'mf_offer':
            $header_slug = modfarm_ppb_resolve_pattern_slug('offer_header_pattern', $options['offer_header_pattern'] ?? null, $options);
            $body_slug   = modfarm_ppb_resolve_pattern_slug('offer_body_pattern', $options['offer_body_pattern'] ?? null, $options);
            $footer_slug = modfarm_ppb_resolve_pattern_slug('offer_footer_pattern', $options['offer_footer_pattern'] ?? null, $options);
            break;

        default:
            return;
    }

    $get = static function($slug) use ($registry) {
        if (!$slug) return '';
        $p = $registry->get_registered($slug);
        return (is_array($p) && !empty($p['content'])) ? (string) $p['content'] : '';
    };

    $header = $get($header_slug);
    $body   = $get($body_slug);
    $footer = $get($footer_slug);

    if ($body === '') return;

    $assembled = implode("\n\n", [
        modfarm_ppb_build_zone_markup('header', $header, [
            'pattern' => $header_slug,
        ]),
        modfarm_ppb_build_zone_markup('body', $body, [
            'pattern' => $body_slug,
        ]),
        modfarm_ppb_build_zone_markup('footer', $footer, [
            'pattern' => $footer_slug,
        ]),
    ]);

    remove_action('wp_insert_post', 'modfarm_assemble_post_layout_on_insert', 10);
    wp_update_post([
        'ID'           => $post_id,
        'post_content' => $assembled,
    ]);
    add_action('wp_insert_post', 'modfarm_assemble_post_layout_on_insert', 10, 3);

    if (function_exists('modfarm_set_template_origin') && defined('MF_ORIGIN_PPB')) {
        modfarm_set_template_origin($post_id, MF_ORIGIN_PPB);
    }
}


/**
 * Render archive using ModFarm Settings patterns, with taxonomy-specific overrides.
 *
 * Uses option: modfarm_theme_settings
 * Keys:
 *  - archive_header_pattern
 *  - archive_body_pattern
 *  - archive_footer_pattern
 *  - archive_body_pattern_book_series
 *  - archive_body_pattern_book_genre
 *  - archive_body_pattern_book_authors
 *
 * Also supports future generic overrides:
 *  - archive_body_pattern__{taxonomy}
 */
function modfarm_render_archive_page() {
    if (!class_exists('WP_Block_Patterns_Registry')) {
        echo '<p>Pattern registry not available. Theme may not support block patterns.</p>';
        return;
    }

    $opts = get_option('modfarm_theme_settings', []);

    // Fresh or unused archive settings must still render usable defaults.
    $header_slug = modfarm_ppb_resolve_pattern_slug('archive_header_pattern', $opts['archive_header_pattern'] ?? null, $opts);
    $body_slug   = modfarm_ppb_resolve_pattern_slug('archive_body_pattern', $opts['archive_body_pattern'] ?? null, $opts);
    $footer_slug = modfarm_ppb_resolve_pattern_slug('archive_footer_pattern', $opts['archive_footer_pattern'] ?? null, $opts);

    // Known taxonomy overrides (current UI fields)
    $known_overrides = [
        'book_series'  => modfarm_ppb_resolve_pattern_slug('archive_body_pattern_book_series', $opts['archive_body_pattern_book_series'] ?? null, $opts),
        'book_genre'   => modfarm_ppb_resolve_pattern_slug('archive_body_pattern_book_genre', $opts['archive_body_pattern_book_genre'] ?? null, $opts),
        'book_authors' => modfarm_ppb_resolve_pattern_slug('archive_body_pattern_book_authors', $opts['archive_body_pattern_book_authors'] ?? null, $opts),
    ];

    // Generic override support: archive_body_pattern__{taxonomy}
    $generic_overrides = [];
    foreach ($opts as $k => $v) {
        if (!is_string($k) || !str_starts_with($k, 'archive_body_pattern__')) continue;
        $tax = substr($k, strlen('archive_body_pattern__'));
        if ($tax) {
            $resolved = modfarm_ppb_resolve_pattern_slug('archive_body_pattern', $v, $opts);
            if ($resolved !== '') {
                $generic_overrides[$tax] = $resolved;
            }
        }
    }

    // Apply override for the current taxonomy archive
    if (is_tax()) {
        $qo = get_queried_object();
        $taxonomy = (is_object($qo) && !empty($qo->taxonomy)) ? $qo->taxonomy : '';

        if ($taxonomy) {
            if (!empty($generic_overrides[$taxonomy])) {
                $body_slug = $generic_overrides[$taxonomy];
            } elseif (!empty($known_overrides[$taxonomy])) {
                $body_slug = $known_overrides[$taxonomy];
            }
        }
    }

    $registry = WP_Block_Patterns_Registry::get_instance();

    $get_content = static function($slug) use ($registry) {
        if (!$slug) return '';
        $p = $registry->get_registered($slug);
        return (is_array($p) && !empty($p['content'])) ? (string) $p['content'] : '';
    };

    $header = $get_content($header_slug);
    $body   = $get_content($body_slug);
    $footer = $get_content($footer_slug);

    // Render; if a pattern slug is bad, it will render as empty (safe fail)
    echo do_blocks($header);
    echo do_blocks($body);
    echo do_blocks($footer);
}

/**
 * Theme activation defaults (OPTION-based, not theme_mod-based).
 * Ensures ModFarm Settings has reasonable archive defaults on fresh installs.
 */
add_action('after_switch_theme', function () {
    $opts = get_option('modfarm_theme_settings', []);
    $defaults = modfarm_ppb_canonical_defaults();

    $changed = false;

    foreach ($defaults as $key => $fallback) {
        $current = modfarm_ppb_normalize_slug($opts[$key] ?? null);
        if ($current === '' || !modfarm_ppb_pattern_exists($current)) {
            $opts[$key] = $fallback;
            $changed = true;
        }
    }

    if ($changed) {
        update_option('modfarm_theme_settings', $opts);
    }
});



//PPB and Book Page Debug confirmation
add_action('wp_insert_post', function($post_id,$post,$update){
  if ($post->post_type !== 'book' || $update) return;
  $options = get_option('modfarm_theme_settings', []);
  $body = $options['book_body_pattern'] ?? get_theme_mod('book_body_pattern', '');
  error_log("[PPB] book_body_pattern resolved to '{$body}'");
}, 9, 3);


add_action('admin_enqueue_scripts', function () {
	if (!is_admin()) return;

	$screen = get_current_screen();
	if (!$screen || strpos($screen->base, 'edit-tags') === false) return;

	wp_enqueue_media();
	wp_enqueue_script(
		'modfarm-archive-media',
		get_template_directory_uri() . '/assets/js/admin-archive-media.js',
		['jquery'],
		null,
		true
	);
});



function modfarm_theme_enqueue_scripts() {
    wp_enqueue_script(
        'modfarm-navigation-toggle',
        get_template_directory_uri() . '/assets/js/navigation-toggle.js',
        [],
        null,
        true // load in footer
    );
}
add_action('wp_enqueue_scripts', 'modfarm_theme_enqueue_scripts');

function modfarm_get_menu_ref($menu_name) {
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu) return 0;

    return (int) $menu->term_id;
}

add_shortcode('modfarm_site_title', function () {
    return esc_html(get_bloginfo('name'));
});

add_shortcode('modfarm_site_tagline', function () {
    return esc_html(get_bloginfo('description'));
});

add_shortcode('modfarm_menu', function ($atts) {
    $atts = shortcode_atts([
        'location' => 'primary',
        'class'    => 'modfarm-menu',
    ], $atts);

    if (!has_nav_menu($atts['location'])) return '';

    return wp_nav_menu([
        'theme_location' => $atts['location'],
        'container'      => 'nav',
        'container_class'=> esc_attr($atts['class']),
        'echo'           => false,
    ]);
});

function modfarm_footer_login_shortcode() {
    $year = date('Y');
    $site = get_bloginfo('name');

    $link = is_user_logged_in()
        ? '<a href="' . esc_url(wp_logout_url()) . '">Logout</a>'
        : '<a href="' . esc_url(wp_login_url()) . '">Login</a>';

    return "&copy; {$year} {$site} &middot; {$link}";
}
add_shortcode('modfarm_footer_login', 'modfarm_footer_login_shortcode');

add_action('wp_head', function () {
    $settings = get_option('modfarm_theme_settings', []);
    $vars = [
        '--mf-primary'         => $settings['primary_color'] ?? '#336699',
        '--mf-header-text'     => $settings['header_text_color'] ?? '#ffffff',
        '--mf-body-text'       => $settings['body_text_color'] ?? '#111111',
        '--mf-link-color'      => $settings['link_color'] ?? '#0066cc',
        '--mf-button-bg'       => $settings['button_color'] ?? '#336699',
        '--mf-button-text'     => $settings['button_text_color'] ?? '#ffffff',
        '--mf-background'      => $settings['background_color'] ?? '#ffffff',
        '--mf-secondary'       => $settings['secondary_color'] ?? '#eeeeee',
        '--mf-heading-font'    => $settings['heading_font'] ?? 'Merriweather, serif',
        '--mf-body-font'       => $settings['body_font'] ?? 'Inter, sans-serif',
        '--mf-content-width'   => $settings['content_width'] ?? '1200px',
        '--mf-nav-center-max-width'   => $settings['nav_center_max_width'] ?? '250px',
    ];

    echo '<style>:root {';
    foreach ($vars as $var => $value) {
        echo "$var: " . esc_attr($value) . ";\n";
    }
    echo '}</style>';
});

add_action('wp_enqueue_scripts', 'modfarm_enqueue_google_fonts');
add_action('admin_enqueue_scripts', 'modfarm_enqueue_google_fonts');
function modfarm_enqueue_google_fonts() {
    $options = get_option('modfarm_theme_settings');
    $fonts = [];

    foreach (['body_font', 'heading_font'] as $key) {
        $font_string = $options[$key] ?? '';
        if ($font_string && !preg_match('/^(Georgia|Arial)/i', $font_string)) {
            $fonts[] = str_replace(' ', '+', $font_string);
        }
    }

    if (!empty($fonts)) {
        $query_args = [
            'family' => implode('&family=', array_unique($fonts)),
            'display' => 'swap',
        ];
        $fonts_url = 'https://fonts.googleapis.com/css2?' . http_build_query($query_args);
        wp_enqueue_style('modfarm-google-fonts', $fonts_url, false);
    }
}
