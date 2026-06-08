<?php
require_once get_template_directory() . '/blocks/shared/book-options.php';

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('modfarm_search_results_term_image_url')) {
    function modfarm_search_results_term_image_url(WP_Term $term): string {
        $keys = [
            'archive_default_image',
            'modfarm_author_avatar',
            'modfarm_term_image',
            'archive_default_image_url',
            'archive_image_id',
            'term_image_id',
            'profile_image_id',
            'image_id',
            '_thumbnail_id',
        ];

        foreach ($keys as $key) {
            $value = get_term_meta($term->term_id, $key, true);
            if (empty($value)) {
                continue;
            }

            $url = modfarm_book_option_value_to_url($value);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }
}

if (!function_exists('modfarm_search_results_find_terms')) {
    function modfarm_search_results_find_terms(string $taxonomy, string $search, int $limit): array {
        if (!taxonomy_exists($taxonomy) || $search === '') {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => max(1, $limit),
            'search'     => $search,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        return is_wp_error($terms) ? [] : array_values(array_filter($terms, static function ($term) {
            return $term instanceof WP_Term;
        }));
    }
}

if (!function_exists('modfarm_search_results_find_terms_any')) {
    function modfarm_search_results_find_terms_any(array $taxonomies, string $search, int $limit): array {
        $terms = [];
        $seen = [];

        foreach ($taxonomies as $taxonomy) {
            foreach (modfarm_search_results_find_terms((string) $taxonomy, $search, $limit) as $term) {
                $key = $term->taxonomy . ':' . $term->term_id;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $terms[] = $term;
            }
        }

        return array_slice($terms, 0, max(1, $limit));
    }
}

if (!function_exists('modfarm_search_results_merge_terms')) {
    function modfarm_search_results_merge_terms(array ...$term_groups): array {
        $terms = [];
        $seen = [];

        foreach ($term_groups as $group) {
            foreach ($group as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $key = $term->taxonomy . ':' . $term->term_id;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $terms[] = $term;
            }
        }

        return $terms;
    }
}

if (!function_exists('modfarm_search_results_terms_for_books')) {
    function modfarm_search_results_terms_for_books(array $book_ids, array $taxonomies, int $limit): array {
        $terms = [];

        foreach ($book_ids as $book_id) {
            foreach ($taxonomies as $taxonomy) {
                if (!taxonomy_exists((string) $taxonomy)) {
                    continue;
                }

                $book_terms = get_the_terms((int) $book_id, (string) $taxonomy);
                if (!empty($book_terms) && !is_wp_error($book_terms)) {
                    $terms = modfarm_search_results_merge_terms($terms, $book_terms);
                }
            }
        }

        return array_slice($terms, 0, max(1, $limit));
    }
}

if (!function_exists('modfarm_search_results_book_ids_for_terms')) {
    function modfarm_search_results_book_ids_for_terms(array $terms, int $limit): array {
        $tax_query = [];

        foreach ($terms as $term) {
            if (!($term instanceof WP_Term)) {
                continue;
            }

            $tax_query[] = [
                'taxonomy' => $term->taxonomy,
                'field'    => 'term_id',
                'terms'    => [(int) $term->term_id],
            ];
        }

        if (empty($tax_query) || !post_type_exists('book')) {
            return [];
        }

        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'OR';
        }

        return get_posts([
            'post_type'              => 'book',
            'post_status'            => 'publish',
            'posts_per_page'         => max(1, $limit),
            'fields'                 => 'ids',
            'tax_query'              => $tax_query,
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
        ]);
    }
}

if (!function_exists('modfarm_search_results_first_book_id_for_term')) {
    function modfarm_search_results_first_book_id_for_term(WP_Term $term): int {
        if (!post_type_exists('book')) {
            return 0;
        }

        $ids = get_posts([
            'post_type'      => 'book',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => $term->taxonomy,
                'field'    => 'term_id',
                'terms'    => [(int) $term->term_id],
            ]],
            'meta_key'       => 'series_position',
            'orderby'        => 'meta_value_num title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        if (!empty($ids)) {
            return (int) $ids[0];
        }

        $ids = get_posts([
            'post_type'      => 'book',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => $term->taxonomy,
                'field'    => 'term_id',
                'terms'    => [(int) $term->term_id],
            ]],
            'orderby'        => 'date',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        return !empty($ids) ? (int) $ids[0] : 0;
    }
}

if (!function_exists('modfarm_search_results_direct_book_ids')) {
    function modfarm_search_results_direct_book_ids(string $search, int $limit): array {
        if (!post_type_exists('book') || $search === '') {
            return [];
        }

        return get_posts([
            'post_type'              => 'book',
            'post_status'            => 'publish',
            'posts_per_page'         => max(1, $limit),
            'fields'                 => 'ids',
            's'                      => $search,
            'orderby'                => 'relevance',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
        ]);
    }
}

if (!function_exists('modfarm_search_results_book_card')) {
    function modfarm_search_results_book_card(int $book_id, string $cover_source): array {
        $permalink = get_permalink($book_id) ?: '';
        $cover_data = modfarm_book_cover_data($book_id, $cover_source);
        $series_terms = get_the_terms($book_id, 'book-series');
        $series_name = (!empty($series_terms) && !is_wp_error($series_terms)) ? $series_terms[0]->name : '';
        $series_pos = get_post_meta($book_id, 'series_position', true);

        return [
            'id'              => $book_id,
            'title'           => get_the_title($book_id),
            'permalink'       => $permalink,
            'image_url'       => (string) ($cover_data['url'] ?? ''),
            'aspect'          => modfarm_book_cover_aspect((string) ($cover_data['source'] ?? $cover_source), 'auto'),
            'image_fit'       => modfarm_book_cover_image_fit((string) ($cover_data['source'] ?? $cover_source), 'auto'),
            'format'          => null,
            'show_title'      => true,
            'series_name'     => $series_name,
            'series_position' => $series_pos,
            'volume_text'     => __('Book', 'modfarm'),
            'audio_mode'      => 'off',
            'button'          => [
                'text'   => __('See The Book', 'modfarm'),
                'url'    => $permalink,
                'target' => '_self',
                'bg'     => '',
                'fg'     => '',
                'origin' => 'search-results',
            ],
        ];
    }
}

if (!function_exists('modfarm_search_results_posts')) {
    function modfarm_search_results_posts(string $search, int $limit): array {
        if ($search === '') {
            return [];
        }

        $query = new WP_Query([
            'post_type'              => apply_filters('modfarm_search_results_post_section_types', ['post']),
            'post_status'            => 'publish',
            'posts_per_page'         => max(1, $limit),
            's'                      => $search,
            'orderby'                => 'relevance',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        return $query->posts;
    }
}

if (!function_exists('modfarm_search_results_book_meta')) {
    function modfarm_search_results_book_meta(int $book_id): string {
        $pieces = [];

        $authors = taxonomy_exists('book-author') ? get_the_terms($book_id, 'book-author') : [];
        if ((empty($authors) || is_wp_error($authors)) && taxonomy_exists('book-authors')) {
            $authors = get_the_terms($book_id, 'book-authors');
        }
        if (!empty($authors) && !is_wp_error($authors)) {
            $pieces[] = implode(', ', wp_list_pluck($authors, 'name'));
        }

        $series = get_the_terms($book_id, 'book-series');
        if (!empty($series) && !is_wp_error($series)) {
            $pieces[] = $series[0]->name;
        }

        return implode(' | ', array_filter($pieces));
    }
}

if (!function_exists('modfarm_search_results_excerpt')) {
    function modfarm_search_results_excerpt(int $post_id, int $words = 24): string {
        $excerpt = get_the_excerpt($post_id);
        if ($excerpt === '') {
            $excerpt = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
        }

        return wp_trim_words($excerpt, $words);
    }
}

if (!function_exists('modfarm_render_search_results_block')) {
    function modfarm_render_search_results_block($attributes, $content = '', $block = null): string {
        $a = wp_parse_args($attributes, [
            'booksLimit'      => 8,
            'postsLimit'      => 8,
            'termsLimit'      => 6,
            'showBooks'       => true,
            'showPosts'       => true,
            'showAuthors'     => true,
            'showSeries'      => true,
            'bookCoverSource' => 'cover_ebook',
            'anchor'          => '',
        ]);

        $search = get_search_query(false);
        $search = is_string($search) ? trim($search) : '';

        if ($search === '') {
            return '<div class="mf-search-results"><p class="mf-search-results__empty">' . esc_html__('Enter a search term to find books, posts, authors, and series.', 'modfarm') . '</p></div>';
        }

        $books_limit = max(1, min(24, (int) $a['booksLimit']));
        $posts_limit = max(1, min(24, (int) $a['postsLimit']));
        $terms_limit = max(1, min(24, (int) $a['termsLimit']));

        $direct_author_terms = modfarm_search_results_find_terms_any(['book-author', 'book-authors'], $search, $terms_limit);
        $direct_series_terms = modfarm_search_results_find_terms('book-series', $search, $terms_limit);

        $book_ids = array_values(array_unique(array_merge(
            modfarm_search_results_direct_book_ids($search, $books_limit),
            modfarm_search_results_book_ids_for_terms(array_merge($direct_author_terms, $direct_series_terms), $books_limit)
        )));
        $book_ids = array_slice($book_ids, 0, $books_limit);

        $author_terms = array_slice(modfarm_search_results_merge_terms(
            $direct_author_terms,
            modfarm_search_results_terms_for_books($book_ids, ['book-author', 'book-authors'], $terms_limit)
        ), 0, $terms_limit);
        $series_terms = array_slice(modfarm_search_results_merge_terms(
            $direct_series_terms,
            modfarm_search_results_terms_for_books($book_ids, ['book-series'], $terms_limit)
        ), 0, $terms_limit);

        $posts = modfarm_search_results_posts($search, $posts_limit);

        $sections = [
            'books' => $book_ids,
            'posts' => $posts,
            'authors' => $author_terms,
            'series' => $series_terms,
        ];

        /**
         * Filter grouped search result data before rendering.
         *
         * This is the intended hook point for data-layer or knowledge-index
         * ranking/enrichment once those signals are available.
         */
        $sections = apply_filters('modfarm_search_results_sections', $sections, $search, $a);

        $anchor = sanitize_title($a['anchor'] ?? '');
        $wrapper_attrs = 'class="mf-search-results"';
        if ($anchor !== '') {
            $wrapper_attrs .= ' id="' . esc_attr($anchor) . '"';
        }

        ob_start();
        echo '<div ' . $wrapper_attrs . '>';

        $render_header = static function (string $title, int $count): void {
            echo '<div class="mf-search-section__header">';
            echo '<h2 class="mf-search-section__title">' . esc_html($title) . '</h2>';
            echo '<span class="mf-search-section__count">' . esc_html(sprintf(_n('%d result', '%d results', $count, 'modfarm'), $count)) . '</span>';
            echo '</div>';
        };

        if (!empty($a['showBooks']) && !empty($sections['books'])) {
            echo '<section class="mf-search-section mf-search-section--books">';
            $render_header(__('Books', 'modfarm'), count($sections['books']));
            echo '<div class="mf-search-books mfb-wrapper is-archive mfb-effect--flat mfb-cover--square mfb-button--square mfb-sample--square mfb-cta--gap mfb-wrapper--grid">';
            echo '<div class="mfb-grid" style="--mfb-cols:4;">';
            foreach ($sections['books'] as $book_id) {
                $book_id = (int) $book_id;
                $permalink = get_permalink($book_id);
                if (!$permalink) {
                    continue;
                }

                echo '<div class="mfb-item mf-search-book">';
                if (function_exists('modfarm_render_book_card')) {
                    modfarm_render_book_card(modfarm_search_results_book_card($book_id, (string) $a['bookCoverSource']));
                } else {
                    $cover = modfarm_book_cover_data($book_id, (string) $a['bookCoverSource']);
                    echo '<article class="mfb-card"><div class="mfb-media">';
                    echo '<a class="mfb-image" href="' . esc_url($permalink) . '">';
                    if (!empty($cover['url'])) {
                        echo '<img src="' . esc_url($cover['url']) . '" alt="' . esc_attr(get_the_title($book_id)) . '" loading="lazy" decoding="async">';
                    }
                    echo '</a></div>';
                    echo '<span class="mfb-title"><a href="' . esc_url($permalink) . '">' . esc_html(get_the_title($book_id)) . '</a></span>';
                    echo '</article>';
                }
                echo '</div>';
            }
            echo '</div></div>';
            echo '</section>';
        }

        if (!empty($a['showPosts']) && !empty($sections['posts'])) {
            echo '<section class="mf-search-section mf-search-section--posts">';
            $render_header(__('Articles', 'modfarm'), count($sections['posts']));
            echo '<div class="mf-search-posts">';
            foreach ($sections['posts'] as $post) {
                if (!($post instanceof WP_Post)) {
                    continue;
                }

                $permalink = get_permalink($post);
                if (!$permalink) {
                    continue;
                }

                echo '<article class="mf-search-result">';
                echo '<h3 class="mf-search-result__title"><a href="' . esc_url($permalink) . '">' . esc_html(get_the_title($post)) . '</a></h3>';
                echo '<div class="mf-search-result__meta">' . esc_html(get_the_date('', $post)) . '</div>';
                $excerpt = modfarm_search_results_excerpt((int) $post->ID, 30);
                if ($excerpt !== '') {
                    echo '<p class="mf-search-result__excerpt">' . esc_html($excerpt) . '</p>';
                }
                echo '</article>';
            }
            echo '</div>';
            echo '</section>';
        }

        $render_term_section = static function (string $title, array $terms, string $class_name): void {
            if (empty($terms)) {
                return;
            }

            echo '<section class="mf-search-section ' . esc_attr($class_name) . '">';
            echo '<div class="mf-search-section__header">';
            echo '<h2 class="mf-search-section__title">' . esc_html($title) . '</h2>';
            echo '<span class="mf-search-section__count">' . esc_html(sprintf(_n('%d result', '%d results', count($terms), 'modfarm'), count($terms))) . '</span>';
            echo '</div>';
            echo '<div class="mf-search-terms">';
            foreach ($terms as $term) {
                if (!($term instanceof WP_Term)) {
                    continue;
                }

                $url = get_term_link($term);
                if (is_wp_error($url)) {
                    continue;
                }

                $image = modfarm_search_results_term_image_url($term);
                if ($image === '' && $term->taxonomy === 'book-series') {
                    $first_book_id = modfarm_search_results_first_book_id_for_term($term);
                    if ($first_book_id > 0) {
                        $image = modfarm_book_cover_url($first_book_id, 'cover_ebook', true);
                    }
                }
                echo '<article class="mf-search-term mf-search-term--' . esc_attr(sanitize_html_class($term->taxonomy)) . '">';
                if ($image !== '') {
                    echo '<a class="mf-search-term__image" href="' . esc_url($url) . '"><img src="' . esc_url($image) . '" alt="' . esc_attr($term->name) . '" loading="lazy" decoding="async"></a>';
                }
                echo '<h3 class="mf-search-term__title"><a href="' . esc_url($url) . '">' . esc_html($term->name) . '</a></h3>';
                echo '<div class="mf-search-term__meta">' . esc_html(sprintf(_n('%d book', '%d books', (int) $term->count, 'modfarm'), (int) $term->count)) . '</div>';
                if (!empty($term->description)) {
                    echo '<p class="mf-search-term__description">' . esc_html(wp_trim_words(wp_strip_all_tags($term->description), 22)) . '</p>';
                }
                echo '</article>';
            }
            echo '</div>';
            echo '</section>';
        };

        if (!empty($a['showAuthors']) && !empty($sections['authors'])) {
            $render_term_section(__('Authors', 'modfarm'), $sections['authors'], 'mf-search-section--authors');
        }

        if (!empty($a['showSeries']) && !empty($sections['series'])) {
            $render_term_section(__('Series', 'modfarm'), $sections['series'], 'mf-search-section--series');
        }

        if (
            (empty($a['showBooks']) || empty($sections['books'])) &&
            (empty($a['showPosts']) || empty($sections['posts'])) &&
            (empty($a['showAuthors']) || empty($sections['authors'])) &&
            (empty($a['showSeries']) || empty($sections['series']))
        ) {
            echo '<p class="mf-search-results__empty">' . esc_html__('No matching books, articles, authors, or series found.', 'modfarm') . '</p>';
        }

        echo '</div>';

        return ob_get_clean();
    }
}
