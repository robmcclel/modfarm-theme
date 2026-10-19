<?php
/** Run: php tests/taxonomy-grid-book-controls.php */
define('ABSPATH', __DIR__);
function get_template_directory() { return dirname(__DIR__) . '/modfarm-theme/modfarm-theme'; }
function absint($v) { return abs((int)$v); }
function sanitize_key($v) { return preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$v)); }
function sanitize_title($v) { return sanitize_key($v); }
function sanitize_text_field($v) { return strip_tags($v); }
function __($v, $domain = '') { return $v; }
function esc_attr($v) { return htmlspecialchars((string)$v); }
function esc_html($v) { return esc_attr($v); }
function esc_url($v) { return esc_attr($v); }
function esc_attr_e($v, $domain = '') { echo esc_attr($v); }
function esc_html_e($v, $domain = '') { echo esc_html($v); }
function wp_parse_args($a, $d) { return array_merge($d, $a); }
function wp_json_encode($v) { return json_encode($v); }
function taxonomy_exists($v) { return true; }
function is_wp_error($v) { return false; }
function get_queried_object_id() { return 100; }
function get_option($key, $fallback = []) { return $fallback; }
function get_permalink($id) { return 'https://example.test/books/' . $id; }
function get_term_link($term) { return 'https://example.test/series/' . $term->slug; }
function get_the_title($id) { return 'Book ' . $id; }
function get_the_post_thumbnail_url($id, $size) { return ''; }
function get_post_meta($id, $key, $single) {
    return ['series_position' => [11 => '2', 12 => '1', 13 => '3', 21 => '1'][$id] ?? '',
        'cover_image_audio' => 'https://example.test/audio.jpg',
        'audio_sample_url' => 'https://example.test/sample.mp3'][$key] ?? '';
}
class WP_Term {
    public function __construct(public int $term_id, public string $name, public string $slug, public int $count) {}
}
function get_terms($args) { return [new WP_Term(1, 'Alpha', 'alpha', 3), new WP_Term(2, 'Beta', 'beta', 1)]; }
function modfarm_filter_books_by_language($args, $attrs) {
    if (!empty($attrs['bookLanguage'])) $args['language'] = $attrs['bookLanguage'];
    return $args;
}
function matches_tax($query, $book) {
    if (isset($query['taxonomy'])) return in_array($book[$query['taxonomy']], (array)$query['terms']);
    $matches = [];
    foreach ($query as $key => $clause) if (is_array($clause)) $matches[] = matches_tax($clause, $book);
    return ($query['relation'] ?? 'AND') === 'OR' ? in_array(true, $matches, true) : !in_array(false, $matches, true);
}
function get_posts($args) {
    $books = [11 => ['book-series'=>1,'book-format'=>7,'language'=>'en'],
        12 => ['book-series'=>1,'book-format'=>7,'language'=>'en'],
        13 => ['book-series'=>1,'book-format'=>7,'language'=>'fr'],
        21 => ['book-series'=>2,'book-format'=>8,'language'=>'en']];
    return array_keys(array_filter($books, fn($book) => matches_tax($args['tax_query'] ?? [], $book)
        && (empty($args['language']) || $args['language'] === $book['language'])));
}
class WP_Query { public int $found_posts; public function __construct($args) { $this->found_posts = count(get_posts($args)); } }
function modfarm_render_book_card($card) { $GLOBALS['cards'][] = $card; echo '<article>Book</article>'; }
if (!function_exists('mb_substr')) { function mb_substr($v, $start, $length) { return substr($v, $start, $length); } }
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
set_error_handler(function($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
require get_template_directory() . '/blocks/taxonomy-grid/render.php';
$base = ['groupMode'=>'books_by_series', 'book-format'=>['id'=>7], 'bookLanguage'=>'en', 'hideEmpty'=>false, 'bookCoverSource'=>'cover_image_audio'];
$selection = modfarm_taxonomy_grid_resolve_terms($base);
check(array_column($selection['terms'], 'term_id') === [1], 'Series with no matching books must be omitted even with Hide Empty off.');
check($selection['terms'][0]->count === 2, 'Counts must intersect format and language.');
$html = modfarm_render_taxonomy_grid_block($base);
check(str_contains($html, 'taxgrid-series-1') && !str_contains($html, 'taxgrid-series-2'), 'TOC and sections must both omit unmatched series.');
check(array_column($GLOBALS['cards'], 'id') === [12, 11], 'Books must be format/language filtered and in series order.');
check($GLOBALS['cards'][0]['aspect'] === '1 / 1', 'Audiobook covers must use square aspect.');
$default = $GLOBALS['cards'][0];
check($default['show_title'] && $default['series_name'] === 'Alpha' && $default['audio_mode'] === 'auto' && $default['button']['url'] !== '', 'Legacy visibility defaults must remain enabled.');
foreach (['cardShowTitle'=>'title', 'cardShowSeries'=>'series', 'cardShowPrimaryButton'=>'primary', 'cardShowSampleButton'=>'sample'] as $attribute => $key) {
    $GLOBALS['cards'] = [];
    modfarm_render_taxonomy_grid_block($base + ['cardUseGlobal'=>false, $attribute=>false]);
    $card = $GLOBALS['cards'][0];
    check(match($key) { 'title' => !$card['show_title'], 'series' => $card['series_name'] === '' && $card['series_position'] === '',
        'primary' => $card['button']['url'] === '', 'sample' => $card['audio_mode'] === 'off' }, 'Visibility control failed: ' . $key);
}
check(modfarm_book_card_visibility(['cardShowTitle'=>false])['title'] === true, 'Global defaults must take precedence.');
$no_format = modfarm_filter_books_by_format(['tax_query'=>['relation'=>'OR', ['taxonomy'=>'book-series','terms'=>[1]]]], []);
check($no_format['tax_query']['relation'] === 'OR', 'No-format queries must retain their original structure.');
$filtered = modfarm_filter_books_by_format($no_format, ['book-format'=>['id'=>7]]);
check($filtered['tax_query']['relation'] === 'AND' && $filtered['tax_query'][0]['relation'] === 'OR', 'Format must intersect an existing OR query.');
$GLOBALS['cards'] = [];
modfarm_render_taxonomy_grid_block(['groupMode'=>'books_by_series']);
check(count($GLOBALS['cards']) === 4, 'Unfiltered legacy grids must retain all books.');
// Exercise the real shared audio renderer: labels and disabled samples.
require get_template_directory() . '/template-parts/book/ui.php';
function wp_audio_shortcode($args) { return '<audio></audio>'; }
$audio = mfb_ui_audio(['audio_mode'=>'sample', 'audio_sample'=>'https://example.test/sample.mp3', 'sample_button_text'=>'Listen Now']);
check(str_contains($audio, 'Listen Now') && !str_contains($audio, 'Play Sample'), 'Custom sample labels must reach the real audio renderer.');
check(mfb_ui_audio(['audio_mode'=>'off', 'audio_sample'=>'https://example.test/sample.mp3']) === '', 'Disabled samples must render nothing.');
restore_error_handler();
echo "Taxonomy Grid: filtering, TOC, ordering, artwork, defaults, and four visibility controls passed.\n";
