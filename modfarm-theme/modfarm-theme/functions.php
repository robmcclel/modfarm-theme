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

    // Archive zones
    register_block_pattern_category('modfarm-archive-header', [ 'label' => 'ModFarm Archive Headers' ]);
    register_block_pattern_category('modfarm-archive-body',   [ 'label' => 'ModFarm Archive Layouts' ]);
    register_block_pattern_category('modfarm-archive-footer', [ 'label' => 'ModFarm Archive Footers' ]);

    // ===== Element libraries =====
    register_block_pattern_category('modfarm-book-elements',    [ 'label' => 'ModFarm Book Elements' ]);
    register_block_pattern_category('modfarm-page-elements',    [ 'label' => 'ModFarm Page Elements' ]);
    register_block_pattern_category('modfarm-post-elements',    [ 'label' => 'ModFarm Post Elements' ]);
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

    // Defaults (from settings UI, with safe fallbacks)
    $default_header = $opts['archive_header_pattern'] ?? 'modfarm/archive-header-default';
    $default_body   = $opts['archive_body_pattern']   ?? 'modfarm/archive-body-default';
    $default_footer = $opts['archive_footer_pattern'] ?? 'modfarm/archive-footer-default';

    // Known taxonomy override keys (your current settings fields)
    $taxonomy_overrides = [
        'book_series'  => $opts['archive_body_pattern_book_series']  ?? '',
        'book_genre'   => $opts['archive_body_pattern_book_genre']   ?? '',
        'book_authors' => $opts['archive_body_pattern_book_authors'] ?? '',
    ];

    /**
     * Generic expansion: if you later add settings like:
     * archive_body_pattern__book_paperback, archive_body_pattern__book_audio, etc
     * they will automatically apply without changing PHP.
     */
    foreach ($opts as $k => $v) {
        if (!is_string($k) || !str_starts_with($k, 'archive_body_pattern__')) continue;
        $tax = substr($k, strlen('archive_body_pattern__'));
        if ($tax && is_string($v) && $v !== '') {
            $taxonomy_overrides[$tax] = $v;
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
    set_theme_mod('archive_body_pattern', 'modfarm/archive-body-default');
    set_theme_mod('archive_header_pattern', 'modfarm/archive-header-default');
    set_theme_mod('archive_footer_pattern', 'modfarm/archive-footer-default');
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
    $options  = get_option('modfarm_theme_settings');
    $registry = WP_Block_Patterns_Registry::get_instance();

    switch ($type) {
        case 'page':
            $header_slug = $options['page_header_pattern'] ?? 'modfarm/modfarm-page-header';
            $body_slug   = $options['page_body_pattern']   ?? '';
            $footer_slug = $options['page_footer_pattern'] ?? 'modfarm/modfarm-page-footer';
            break;

        case 'post':
            $header_slug = $options['post_header_pattern'] ?? 'modfarm/modfarm-post-header';
            $body_slug   = $options['post_body_pattern']   ?? '';
            $footer_slug = $options['post_footer_pattern'] ?? 'modfarm/modfarm-post-footer';
            break;

        case 'book':
        case 'modfarm_book':
            $header_slug = $options['book_header_pattern'] ?? 'modfarm/modfarm-book-header';
            $body_slug   = $options['book_body_pattern']   ?? '';
            $footer_slug = $options['book_footer_pattern'] ?? 'modfarm/modfarm-book-footer';
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

    $assembled = trim($header . "\n\n" . $body . "\n\n" . $footer);

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

    // Defaults from Settings UI (with hard fallbacks)
    $header_slug = $opts['archive_header_pattern'] ?? 'modfarm/archive-header-default';
    $body_slug   = $opts['archive_body_pattern']   ?? 'modfarm/archive-body-default';
    $footer_slug = $opts['archive_footer_pattern'] ?? 'modfarm/archive-footer-default';

    // Known taxonomy overrides (current UI fields)
    $known_overrides = [
        'book_series'  => $opts['archive_body_pattern_book_series']  ?? '',
        'book_genre'   => $opts['archive_body_pattern_book_genre']   ?? '',
        'book_authors' => $opts['archive_body_pattern_book_authors'] ?? '',
    ];

    // Generic override support: archive_body_pattern__{taxonomy}
    $generic_overrides = [];
    foreach ($opts as $k => $v) {
        if (!is_string($k) || !str_starts_with($k, 'archive_body_pattern__')) continue;
        $tax = substr($k, strlen('archive_body_pattern__'));
        if ($tax && is_string($v) && $v !== '') {
            $generic_overrides[$tax] = $v;
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

    $changed = false;

    if (empty($opts['archive_header_pattern'])) {
        $opts['archive_header_pattern'] = 'modfarm/archive-header-default';
        $changed = true;
    }
    if (empty($opts['archive_body_pattern'])) {
        $opts['archive_body_pattern'] = 'modfarm/archive-body-default';
        $changed = true;
    }
    if (empty($opts['archive_footer_pattern'])) {
        $opts['archive_footer_pattern'] = 'modfarm/archive-footer-default';
        $changed = true;
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