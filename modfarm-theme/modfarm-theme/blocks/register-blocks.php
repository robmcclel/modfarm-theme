<?php
/**
 * Registers all dynamic ModFarm blocks using render_callback.
 * This file is FINALIZED and should not be changed unless adding new blocks.
 */

add_action('init', function () {
    $blocks = [
        // Book Page blocks
        'book-cover-art'             => 'modfarm_render_book_cover_art_block',
        'book-page-audio'            => 'modfarm_render_book_page_audio_block',
        'book-page-buttons'          => 'modfarm_render_book_page_buttons_block',
        'book-page-title'            => 'modfarm_render_book_page_title_block',
        'book-page-description'      => 'modfarm_render_book_page_description_block',
        'book-page-author-books'     => 'modfarm_render_book_page_author_books_block',
        'book-page-series'           => 'modfarm_render_book_page_series_block',
        'book-page-series-list'      => 'modfarm_render_book_page_series_list_block',
        'book-page-sales-links'      => 'modfarm_render_book_page_sales_links_block',
        'book-author-credit'         => 'modfarm_render_book_author_credit_block',
        'book-details'               => 'modfarm_render_book_details_block',
        'book-page-tax'              => 'modfarm_render_book_page_tax_block',
        'advanced-book-details'      => 'modfarm_render_advanced_book_details_block',
        'series-nav'                 => 'modfarm_render_series_nav_block',
        'book-page-short-description' => 'modfarm_render_book_page_short_description_block',
        'hero-cover'                 => 'modfarm_render_hero_cover_block',

        // Footer blocks
        'footer-three-column'        => 'modfarm_render_footer_three_column_block',
        'footer-two-row'             => 'modfarm_render_footer_two_row_block',

        // Author/utility blocks
        'multi-tax-format'           => 'modfarm_render_multi_tax_format_block',
        'handpicked-books'           => 'modfarm_render_handpicked_books_block',
        'content-slot'               => 'modfarm_render_content_slot_block',
        'theme-icon'                 => 'modfarm_render_theme_icon_block',
        'taxonomy-grid'              => 'modfarm_render_taxonomy_grid_block',
        'tax-description'            => 'modfarm_render_tax_description_block',
        'coming-soon-list'           => 'modfarm_render_coming_soon_list_block',
        'generic-cards'              => 'modfarm_render_generic_cards_block',

        // Page utility blocks
        'columns'                    => 'modfarm_render_columns_block',
        'column'                     => 'modfarm_render_column_block',
        'navigation-menu'            => 'modfarm_render_navigation_menu_block',
        'archive-book-list'          => 'modfarm_render_archive_book_list_block',
        'archive-layout-loader'      => 'modfarm_render_archive_layout_loader_block',
        'simple-tabs'                => 'modfarm_render_simple_tabs_block',
        'tab-panel'                  => 'modfarm_render_tab_panel',
        'featured-book'              => 'modfarm_render_featured_book_block',
        'creator-credit'             => 'modfarm_render_creator_credit_block',
        'site-background'            => 'modfarm_render_site_background_block',
        'table-of-contents'          => 'modfarm_render_table_of_contents_block',
        'simple-gallery'             => 'modfarm_render_simple_gallery_block',
        'format-icons'               => 'modfarm_render_format_icons_block',
        'featured-banner'            => 'modfarm_render_featured_banner_block'
    ];

    foreach ($blocks as $slug => $callback) {
        $block_dir     = get_template_directory() . '/blocks/' . $slug;
        $block_json    = $block_dir . '/block.json';
        $render_php    = $block_dir . '/render.php';
        $editor_script = $block_dir . '/index.js';
        $script_handle = "modfarm-{$slug}-editor";

        // 🔥 FIXED: Read block.json early so it's available before logs
        $block_data = file_exists($block_json)
            ? json_decode(file_get_contents($block_json), true)
            : [];

        // Register editor script if index.js exists
        if (file_exists($editor_script)) {
            wp_register_script(
                $script_handle,
                get_template_directory_uri() . "/blocks/{$slug}/index.js",
                [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-block-editor' ],
                filemtime($editor_script),
                true
            );
        }

        // Check if render is declared in block.json
        $uses_render_in_json = isset($block_data['render']) && str_starts_with($block_data['render'], 'file:');

        // Include PHP render file if NOT using "render": "file:..."
        if (!$uses_render_in_json && file_exists($render_php)) {
            require_once $render_php;
        }

        // Register the block
        if (file_exists($block_json)) {
            $register_args = [];

            if (!$uses_render_in_json) {
                $register_args['render_callback'] = $callback;
            }

            register_block_type($block_dir, $register_args);
        }
    }
});