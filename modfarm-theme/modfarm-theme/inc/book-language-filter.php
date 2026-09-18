<?php
/** Independent language selection for book collections. */
defined('ABSPATH') || exit;

function modfarm_book_language_filter_blocks() {
    return ['taxonomy-grid', 'featured-book', 'coming-soon-list', 'multi-tax-format', 'handpicked-books', 'archive-book-list', 'book-page-tax'];
}

function modfarm_book_language_is_english($term) {
    return strtolower(trim($term->name)) === 'english'
        || preg_match('/^(english|en|eng)(-|$)/i', $term->slug);
}

/** Keep existing OR groups intact: language must intersect the whole selection. */
function modfarm_filter_books_by_language(array $args, array $attributes) {
    $language = (string)($attributes['bookLanguage'] ?? '');
    if ($language === '') return $args;

    $english = $language === 'english';
    $term = ctype_digit($language) ? get_term((int)$language, 'book-language') : null;
    if ($term && !is_wp_error($term)) $english = (bool)modfarm_book_language_is_english($term);

    if ($english) {
        $terms = get_terms(['taxonomy' => 'book-language', 'hide_empty' => false]);
        $ids = [];
        if (!is_wp_error($terms)) {
            foreach ($terms as $candidate) {
                if (modfarm_book_language_is_english($candidate)) $ids[] = (int)$candidate->term_id;
            }
        }
        $clause = ['relation' => 'OR', ['taxonomy' => 'book-language', 'operator' => 'NOT EXISTS']];
        if ($ids) $clause[] = ['taxonomy' => 'book-language', 'field' => 'term_id', 'terms' => $ids, 'include_children' => false];
    } else {
        // An invalid/deleted selection matches nothing rather than broadening the list.
        $clause = ['taxonomy' => 'book-language', 'field' => 'term_id', 'terms' => [$term && !is_wp_error($term) ? (int)$term->term_id : 0], 'include_children' => false];
    }
    $existing = $args['tax_query'] ?? [];
    $args['tax_query'] = $existing ? ['relation' => 'AND', $existing, $clause] : [$clause];
    return $args;
}

add_filter('block_type_metadata', function($metadata) {
    if (in_array(str_replace('modfarm/', '', $metadata['name'] ?? ''), modfarm_book_language_filter_blocks(), true)) {
        $metadata['attributes']['bookLanguage'] = ['type' => 'string', 'default' => ''];
    }
    return $metadata;
});

// Load the shared registration/editor hooks before any of the supported editors.
add_action('init', function() {
    $path = '/assets/js/book-language-filter.js';
    wp_register_script('modfarm-book-language-filter', get_template_directory_uri() . $path,
        ['wp-hooks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'],
        filemtime(get_template_directory() . $path), true);
    foreach (modfarm_book_language_filter_blocks() as $slug) {
        $script = wp_scripts()->registered['modfarm-' . $slug . '-editor'] ?? null;
        if ($script) $script->deps[] = 'modfarm-book-language-filter';
    }
}, 20);

add_action('enqueue_block_editor_assets', function() {
    $options = [
        ['label' => __('All languages', 'modfarm'), 'value' => ''],
        ['label' => __('English (including unassigned)', 'modfarm'), 'value' => 'english'],
    ];
    $terms = get_terms(['taxonomy' => 'book-language', 'hide_empty' => false]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            if (!modfarm_book_language_is_english($term)) $options[] = ['label' => $term->name, 'value' => (string)$term->term_id];
        }
    }
    wp_localize_script('modfarm-book-language-filter', 'modfarmBookLanguageFilter', [
        'blocks' => array_map(static function($slug) { return 'modfarm/' . $slug; }, modfarm_book_language_filter_blocks()),
        'options' => $options,
    ]);
    wp_enqueue_script('modfarm-book-language-filter');
});
