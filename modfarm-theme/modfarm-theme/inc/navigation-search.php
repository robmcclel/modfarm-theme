<?php
/** Optional native site search for navigation blocks. */
add_action('customize_register', function ($manager) {
    $manager->add_section('mfs_navigation_search', [
        'title' => __('Navigation Search', 'modfarm'),
        'panel' => 'nav_menus',
        'priority' => 90,
        'description' => __('Adds search to header navigation blocks. Individual blocks can override this setting.', 'modfarm'),
    ]);
    $manager->add_setting('mfs_navigation_search', [
        'default' => false,
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'transport' => 'refresh',
        'sanitize_callback' => 'rest_sanitize_boolean',
    ]);
    $manager->add_control('mfs_navigation_search', [
        'label' => __('Show navigation search', 'modfarm'),
        'section' => 'mfs_navigation_search',
        'type' => 'checkbox',
    ]);
}, 20);

function mfs_navigation_search_form() {
    $id = wp_unique_id('mfs-search-');
    return '<form role="search" class="mfs-nav-search-form" method="get" action="' . esc_url(home_url('/')) . '">'
        . '<label class="mfs-search-label" for="' . esc_attr($id) . '">' . esc_html__('Search this site', 'modfarm') . '</label>'
        . '<div class="mfs-search-fields"><input id="' . esc_attr($id) . '" type="search" name="s" placeholder="' . esc_attr__('Search…', 'modfarm') . '" required>'
        . '<button type="submit">' . esc_html__('Search', 'modfarm') . '</button></div></form>';
}

function mfs_navigation_search_toggle() {
    // Native details remains operable without JavaScript.
    return '<details class="mfs-nav-search"><summary aria-label="' . esc_attr__('Search this site', 'modfarm') . '">'
        . '<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>'
        . '</summary><div class="mfs-nav-search-popover">' . mfs_navigation_search_form() . '</div></details>';
}
