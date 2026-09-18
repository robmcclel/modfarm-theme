<?php
/** Standalone behavior fixtures: php tests/book-language-filter.php */
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function add_filter(...$args) {}
function is_wp_error($value) { return false; }
function get_template_directory() { return dirname(__DIR__); }
function absint($value) { return abs((int)$value); }
function sanitize_key($value) { return $value; }
function sanitize_title($value) { return $value; }
function taxonomy_exists($value) { return true; }
function is_taxonomy_hierarchical($value) { return false; }
function current_time($format) { return '2026-09-18'; }
function wp_reset_postdata() {}
function get_post_type($id) { return 'book'; }
$languages = [(object)['term_id'=>10, 'name'=>'English', 'slug'=>'english'], (object)['term_id'=>20, 'name'=>'French', 'slug'=>'french']];
$series = [(object)['term_id'=>1, 'name'=>'Series A', 'count'=>3], (object)['term_id'=>2, 'name'=>'Series B', 'count'=>1]];
$books = [
    ['ID'=>1, 'book-language'=>[], 'book-series'=>[1]],
    ['ID'=>2, 'book-language'=>[10], 'book-series'=>[1]],
    ['ID'=>3, 'book-language'=>[20], 'book-series'=>[1]],
    ['ID'=>4, 'book-language'=>[20], 'book-series'=>[2]],
];
function get_terms($args) { return $args['taxonomy'] === 'book-language' ? $GLOBALS['languages'] : $GLOBALS['series']; }
function get_term($id, $taxonomy) { foreach ($GLOBALS['languages'] as $term) if ($term->term_id === $id) return $term; return null; }
function matches($book, $query) {
    if (isset($query['taxonomy'])) {
        $terms = $book[$query['taxonomy']] ?? [];
        return ($query['operator'] ?? 'IN') === 'NOT EXISTS' ? !$terms : (bool)array_intersect($terms, (array)$query['terms']);
    }
    $results = [];
    foreach ($query as $key=>$clause) if ($key !== 'relation') $results[] = matches($book, $clause);
    return ($query['relation'] ?? 'AND') === 'OR' ? in_array(true, $results, true) : !in_array(false, $results, true);
}
class WP_Query {
    public $posts, $found_posts;
    public function __construct($args) {
        $rows = array_values(array_filter($GLOBALS['books'], static function($book) use ($args) { return matches($book, $args['tax_query'] ?? []); }));
        if (!empty($GLOBALS['missing_dates']) && isset($args['meta_key'])) $rows = [];
        if (($args['order'] ?? '') === 'DESC') $rows = array_reverse($rows);
        $this->found_posts = count($rows);
        $rows = array_slice($rows, 0, $args['posts_per_page'] ?? count($rows));
        $this->posts = array_map(static function($book) { return (object)$book; }, $rows);
    }
    public function have_posts() { return (bool)$this->posts; }
}
function check($condition, $message) { if (!$condition) throw new Exception($message); }
require dirname(__DIR__) . '/inc/book-language-filter.php';
require dirname(__DIR__) . '/blocks/featured-book/render.php';
require dirname(__DIR__) . '/blocks/taxonomy-grid/render.php';
function selected_ids($language, $args = []) {
    $args = modfarm_filter_books_by_language($args, ['bookLanguage'=>$language]);
    return array_column((new WP_Query($args))->posts, 'ID');
}
check(selected_ids('') === [1,2,3,4], 'All languages preserves existing lists');
check(selected_ids('english') === [1,2], 'English includes assigned and unassigned');
check(selected_ids('10') === [1,2], 'Explicit English ID has the same default semantics');
check(selected_ids('20') === [3,4], 'French excludes unassigned books');
check(selected_ids('999') === [], 'Deleted language does not broaden results');
$args = ['tax_query'=>['relation'=>'OR', ['taxonomy'=>'book-series','terms'=>[1]], ['taxonomy'=>'book-series','terms'=>[2]]]];
check(selected_ids('english', $args) === [1,2], 'Language intersects existing OR filters');
check(selected_ids('20', ['posts_per_page'=>1]) === [3], 'Filter precedes page limit');
$languages = [];
check(selected_ids('english') === [1], 'English works without an English term');
$languages = [(object)['term_id'=>10, 'name'=>'English', 'slug'=>'en'], (object)['term_id'=>20, 'name'=>'French', 'slug'=>'fr']];
check(mfb_pick_latest_by_date('publication_date', 0, 'english') === 2, 'Latest English excludes newer French');
$missing_dates = true;
check(mfb_pick_latest_by_date('publication_date', 0, 'english') === 2, 'Fallback also excludes French');
check(mfb_pick_latest_by_date('publication_date', 4, 'english') === 4, 'Explicit pin takes precedence');
$resolved = modfarm_taxonomy_grid_resolve_terms(['groupMode'=>'books_by_series','bookLanguage'=>'english']);
check(array_column($resolved['terms'], 'term_id') === [1], 'Empty translated sections removed before TOC');
check($resolved['terms'][0]->count === 2 && $series[0]->count === 3, 'Filtered counts do not mutate cached terms');
$resolved = modfarm_taxonomy_grid_resolve_terms(['groupMode'=>'terms','bookLanguage'=>'english','hideEmpty'=>true,'enablePagination'=>true,'perPage'=>1], 2);
check($resolved['pages'] === 1 && $resolved['page'] === 1, 'Term pagination uses filtered set');
echo "Book language behavior checks passed.\n";
