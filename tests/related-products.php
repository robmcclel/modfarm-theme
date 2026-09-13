<?php
define('ABSPATH', __DIR__ . '/');
function get_template_directory() { return dirname(__DIR__) . '/modfarm-theme/modfarm-theme'; }
function wp_parse_args($args, $defaults) { return array_merge($defaults, $args); }
function absint($value) { return abs((int) $value); }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_-]/', '', strtolower($value)); }
function get_post_type($id) { return $id === 100 ? 'book' : ($id === 101 ? 'modfarm_book' : 'mf_offer'); }
function get_post_status($id) { return $id === 9 ? 'draft' : 'publish'; }
function get_post_meta($id, $key, $single) { return $GLOBALS['linked_book'][$id] ?? 0; }
function get_the_ID() { return $GLOBALS['current_id'] ?? 0; }
function taxonomy_exists($taxonomy) { return $taxonomy === 'offer_collection'; }
function wp_get_post_terms($id, $taxonomy, $args) { return $id === 1 ? [20] : []; }
function is_wp_error($value) { return false; }
function wp_list_pluck($items, $key) { return array_map(fn($item) => $item->$key, $items); }
function modfarm_get_promoted_display_ids($type, $id, $display, $relationship, $args) {
    $GLOBALS['calls'][] = [$type, $id];
    return array_slice($GLOBALS['promotions']["$type:$id"] ?? [], 0, $args['limit']);
}
class WP_Query {
    public $posts;
    public function __construct($args) {
        $GLOBALS['query'] = $args;
        $this->posts = [(object) ['ID' => 7]];
    }
}
require get_template_directory() . '/blocks/related-products/render.php';
function same($expected, $actual, $label) {
    if ($expected !== $actual) throw new RuntimeException($label . ': ' . var_export($actual, true));
}
$linked_book = [1 => 100];
$promotions = ['book:100' => [1, 2, 3, 4]];
$offer = ['contextType' => 'mf_offer', 'contextId' => 1];
same([2, 3, 4], modfarm_related_products_offer_ids(1, $offer), 'Offer follows linked Book and excludes itself without losing a slot');
same([1, 2, 3], modfarm_related_products_offer_ids(0, ['contextType' => 'book', 'contextId' => 100]), 'Book recommends its promoted Offers');
$promotions['mf_offer:1'] = [5];
same([5], modfarm_related_products_offer_ids(1, $offer), 'Direct cross-promotion wins over linked Book');
same([6, 2], modfarm_related_products_offer_ids(1, $offer + ['manualIds' => [6, 1, 9, 100, 2]]), 'Existing manual list keeps order and excludes current, draft and non-Offers');
same([5], modfarm_related_products_offer_ids(1, $offer + ['manualIds' => [6], 'sourceMode' => 'automatic']), 'Automatic ignores retained manual picks');
same([], modfarm_related_products_offer_ids(1, $offer + ['sourceMode' => 'manual', 'taxonomy' => '__all__']), 'Empty manual mode does not silently recommend something else');
$promotions = [];
same([7], modfarm_related_products_offer_ids(1, $offer + ['taxonomy' => 'offer_collection']), 'Offer taxonomy fallback');
same([20], $query['tax_query'][0]['terms'], 'Uses source Offer terms');
same([1], $query['post__not_in'], 'Taxonomy excludes current Offer');
same([], modfarm_related_products_offer_ids(0, ['contextType' => 'book', 'contextId' => 100, 'taxonomy' => 'offer_collection']), 'Book does not guess Offer taxonomy terms');
same([7], modfarm_related_products_offer_ids(0, ['contextType' => 'book', 'contextId' => 100, 'taxonomy' => '__all__']), 'Book supports explicit all-Offers fallback');
same([], modfarm_related_products_offer_ids(1, $offer), 'No matches and no fallback stays empty');
$block = (object) ['context' => ['postId' => 100, 'postType' => 'book']];
same(100, modfarm_store_block_get_relationship_context([], $block)['id'], 'Book block context detection');
$block->context = ['postId' => 1, 'postType' => 'mf_offer'];
same(1, modfarm_store_block_get_offer_id([], $block), 'Offer block context detection');
same(2, modfarm_store_block_get_offer_id(['offerId' => 2], $block), 'Advanced source override');
echo "Related Products tests passed.\n";
