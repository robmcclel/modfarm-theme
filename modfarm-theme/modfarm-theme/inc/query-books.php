<?php
/**
 * Shared book query + audio URL helpers for ModFarm.
 * Drop this in: get_template_directory() . '/inc/query-books.php'
 */

if (!defined('ABSPATH')) exit;

/**
 * Return a WP_Query for books with unified defaults.
 *
 * Args (normalized):
 * - tax_query        : array   WP tax_query clauses
 * - meta_query       : array   Additional meta_query clauses
 * - format_term      : int     Term ID in 'book-format' (optional)
 * - include_upcoming : bool    Include future publication_date (default false)
 * - window           : int     Future-day window for "Coming Soon" (optional)
 * - orderby          : string  'meta_value'|'rand'|... (default 'meta_value')
 * - order            : string  'DESC'|'ASC' (default 'DESC')
 * - posts_per_page   : int     Default 12
 * - paged            : int     Default from query var
 * - respect_archive  : bool    If true and inside archive, extend main query vars
 */
if (!function_exists('modfarm_get_books_query')) {
    function modfarm_get_books_query(array $args = []): WP_Query {
        $today = current_time('Y-m-d');

        $defaults = [
            'tax_query'        => [],
            'meta_query'       => [],
            'format_term'      => null,
            'include_upcoming' => false,
            'window'           => null,
            'orderby'          => 'meta_value',
            'order'            => 'DESC',
            'posts_per_page'   => 12,
            'paged'            => max(1, get_query_var('paged') ?: get_query_var('page') ?: 1),
            'respect_archive'  => true,
        ];
        $a = array_merge($defaults, $args);

        // Publication date windowing
        $pub_clause = ['key' => 'publication_date', 'type' => 'DATE'];

        if ($a['window'] && is_numeric($a['window'])) {
            $upper = date('Y-m-d', strtotime("$today +{$a['window']} days"));
            $pub_clause['value']   = [$today, $upper];
            $pub_clause['compare'] = 'BETWEEN';
        } elseif (!$a['include_upcoming']) {
            $pub_clause['value']   = $today;
            $pub_clause['compare'] = '<=';
        } else {
            // Include all — no upper bound
            $pub_clause['compare'] = 'EXISTS';
        }

        $meta_query = $a['meta_query'];
        $meta_query[] = $pub_clause;

        // Attach book-format if provided
        $tax_query = $a['tax_query'];
        if (!empty($a['format_term'])) {
            $tax_query[] = [
                'taxonomy' => 'book-format',
                'field'    => 'term_id',
                'terms'    => (int) $a['format_term'],
            ];
        }

        // Base args
        $query_args = [
            'post_type'      => 'book',
            'posts_per_page' => (int) $a['posts_per_page'],
            'paged'          => (int) $a['paged'],
            'tax_query'      => $tax_query,
            'meta_query'     => $meta_query,
        ];

        // Ordering
        if ($a['orderby'] === 'rand') {
            $query_args['orderby'] = 'rand';
        } else {
            $query_args['meta_key']  = 'publication_date';
            $query_args['meta_type'] = 'DATE';
            $query_args['orderby']   = [
                'meta_value' => strtoupper($a['order']) === 'ASC' ? 'ASC' : 'DESC',
                'post_date'  => 'DESC',
                'ID'         => 'DESC',
            ];
        }

        // Respect native archive if requested
        if ($a['respect_archive'] && (is_post_type_archive('book') || is_tax(['book-series','book-genre','book-author','book-language','book-tags']))) {
            // Extend main query vars sensibly: keep paging and posts_per_page, but still enforce ordering/meta window
            $query_args['paged'] = (int) $a['paged'];
            if (isset($GLOBALS['wp_query']) && $GLOBALS['wp_query'] instanceof WP_Query) {
                $query_args = array_merge($GLOBALS['wp_query']->query_vars, $query_args);
            }
        }

        return new WP_Query($query_args);
    }
}

/**
 * Construct Amazon Web Player URL for audiobook samples.
 * Requires both ASINs. Falls back to null if not enough data.
 *
 * @param string $audible_asin e.g., 'B0D82C9MV5'
 * @param string $amazon_asin  e.g., Kindle/print ASIN 'B0D828W5TN'
 * @param string $region       'com'|'co.uk'|'de'|'fr'|'ca'...
 * @param array  $opts         Extra query params (defaults set below)
 * @return string|null
 */
if (!function_exists('modfarm_get_audio_player_url')) {
    function modfarm_get_audio_player_url(?string $audible_asin, ?string $amazon_asin, string $region = 'com', array $opts = []): ?string {
        $audible_asin = trim((string)$audible_asin);
        $amazon_asin  = trim((string)$amazon_asin);
        if ($audible_asin === '' || $amazon_asin === '') {
            return null;
        }

        $defaults = [
            'useRelativeUrl'   => 'true',
            'initialCPLaunch'  => 'true',
            'isSample'         => 'true',
        ];
        $q = array_merge($defaults, $opts, [
            'asin'        => $audible_asin,
            'amazonAsin'  => $amazon_asin,
        ]);

        $base = sprintf('https://www.amazon.%s/arya/webplayer', $region);
        return esc_url_raw($base . '?' . http_build_query($q));
    }
}