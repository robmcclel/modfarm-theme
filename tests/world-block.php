<?php
/** World block public rendering and explicit archive context contract. */
define( 'ABSPATH', __DIR__ );
class WP_Term { function __construct( public $term_id = 3, public $taxonomy = 'book-series', public $name = 'The Last Brigade' ) {} }
function check( $ok, $message ) { if ( ! $ok ) throw new Exception( $message ); }
function absint( $v ) { return abs( (int) $v ); }
function get_term( $id, $tax ) { return 3 === $id && 'book-series' === $tax ? new WP_Term() : null; }
function get_queried_object() { return $GLOBALS['queried'] ?? null; }
function is_singular( $type ) { return $GLOBALS['singular'] ?? false; }
function get_queried_object_id() { return 10; }
function get_post_type( $id ) { return 10 === $id ? 'book' : 'page'; }
function get_the_ID() { return 10; }
function get_the_terms( $id, $tax ) { return $GLOBALS['series'] ?? array( new WP_Term() ); }
function is_wp_error( $v ) { return false; }
function wp_strip_all_tags( $v ) { return strip_tags( $v ); }
function wp_kses_post( $v ) { return strip_tags( $v, '<p><strong><em><h3><ul><li><a>' ); }
function wpautop( $v ) { return '<p>' . $v . '</p>'; }
function esc_html( $v ) { return htmlspecialchars( (string) $v, ENT_QUOTES ); }
function esc_attr( $v ) { return esc_html( $v ); }
function wp_unslash( $v ) { return $v; }
function selected( $a, $b, $echo = true ) { return (string) $a === (string) $b ? ' selected' : ''; }
function wp_unique_id( $prefix ) { static $id = 0; return $prefix . ++$id; }
function get_block_wrapper_attributes( $v ) { return 'class="' . esc_attr( $v['class'] ) . '" style="' . esc_attr( $v['style'] ) . '"'; }
function wp_get_attachment_image( $id, $size, $icon, $attrs ) { return '<img class="' . esc_attr( $attrs['class'] ) . '" src="https://dev.test/portrait.jpg" alt="' . esc_attr( $attrs['alt'] ) . '">'; }
function get_posts( $args ) { check( 'publish' === $args['post_status'] && 3 === $args['tax_query'][0]['terms'], 'Book query escaped selected Series/published status' ); return array( (object) array( 'ID' => 10, 'post_title' => 'The Last Stand' ) ); }
function get_post_meta( $id, $key, $single ) { return '1'; }
function get_the_title( $post ) { return $post->post_title; }
function modfarm_world_bible_get( $id ) { return $GLOBALS['world']; }
function modfarm_world_presentation_values( $p ) { return $p; }
function modfarm_world_bible_public( $id, $through = 0 ) {
    $world = $GLOBALS['world'];
    $elements = array_filter( $world['elements'], static function ( $item ) use ( $through ) { return 'public' === $item['visibility'] && 'published' === $item['status'] && ( ! $item['spoiler_book_id'] || 10 === $through ); } );
    foreach ( $elements as &$item ) unset( $item['internal_notes'] );
    return array( 'overview' => $world['overview'], 'elements' => $elements );
}
function modfarm_render_archive_book_list_block( $a, $content, $block, $term ) { check( $term instanceof WP_Term && 3 === $term->term_id, 'Explicit Series context was lost' ); $GLOBALS['book_calls']++; return '<div class="book-cards">Existing archive book cards</div>'; }
require __DIR__ . '/../modfarm-theme/modfarm-theme/blocks/world/render.php';
$p = array( 'display' => 'tabs', 'books' => 'separate', 'default_section' => 'character', 'description' => 'full', 'show_images' => true, 'spoilers' => 'free', 'colors' => array( 'background' => '', 'text' => '', 'accent' => '' ), 'sections' => array( array( 'key' => 'books', 'label' => 'Books', 'enabled' => true ), array( 'key' => 'overview', 'label' => 'World', 'enabled' => true ), array( 'key' => 'character', 'label' => 'Characters', 'enabled' => true ), array( 'key' => 'location', 'label' => 'Places', 'enabled' => true ) ) );
$GLOBALS['book_calls'] = 0;
$entry = array( 'id' => 'wb-a00000000', 'name' => 'Mara Vance', 'type' => 'character', 'summary' => 'Commander', 'description' => '<strong>Rich character context</strong>', 'image_id' => 7, 'spoiler_book_id' => 0, 'visibility' => 'public', 'status' => 'published', 'internal_notes' => 'SECRET' );
$GLOBALS['world'] = array( 'status' => 'published', 'overview' => '<strong>Fractured America</strong>', 'internal_notes' => 'PRIVATE', 'elements' => array( $entry ), 'presentation' => $p );
$manual = array( 'placement' => 'manual', 'seriesId' => 3 );
check( null === modfarm_world_resolve_series( array() ), 'Landing page invented a Series' );
$GLOBALS['queried'] = new WP_Term(); check( 3 === modfarm_world_resolve_series( array() )->term_id, 'Series archive resolution failed' );
$GLOBALS['queried'] = new WP_Term( 4, 'book-author' ); $GLOBALS['singular'] = true;
check( 3 === modfarm_world_resolve_series( array() )->term_id, 'Book Series resolution failed' );
$GLOBALS['series'] = array( new WP_Term(), new WP_Term( 4 ) ); check( null === modfarm_world_resolve_series( array() ), 'Ambiguous Book silently chose a Series' );
check( null === modfarm_world_resolve_series( array( 'placement' => 'manual', 'seriesId' => 999 ) ), 'Invalid manual selection fell back to archive' );
$rendered = modfarm_render_world_block( $manual );
check( str_contains( $rendered, '<strong>Rich character context</strong>' ) && str_contains( $rendered, '<strong>Fractured America</strong>' ), 'Rich formatting was lost' );
check( ! str_contains( $rendered, 'SECRET' ) && ! str_contains( $rendered, 'PRIVATE' ), 'Private authoring notes leaked' );
check( ! str_contains( $rendered, 'data-world-tab="location"' ) && ! str_contains( $rendered, 'book-cards' ), 'Empty sections or separate Books rendered' );
$GLOBALS['world']['status'] = 'draft'; check( '' === modfarm_render_world_block( $manual ), 'Draft world rendered' ); $GLOBALS['world']['status'] = 'published';
$GLOBALS['world']['elements'][0]['status'] = 'draft'; check( ! str_contains( modfarm_render_world_block( $manual ), 'Mara Vance' ), 'Draft entry rendered' ); $GLOBALS['world']['elements'][0]['status'] = 'published';
$GLOBALS['world']['presentation']['books'] = 'included';
$before = get_queried_object(); check( str_contains( modfarm_render_world_block( $manual ), 'book-cards' ) && get_queried_object() === $before && 1 === $GLOBALS['book_calls'], 'Included Books lost context or mutated global query' );
$GLOBALS['world']['presentation']['books'] = 'separate';
$summary = modfarm_render_world_block( $manual + array( 'display' => 'sections' ) ); check( ! str_contains( $summary, 'role="tab"' ) && str_contains( $summary, 'mfw--sections' ), 'Sections override failed' );
$GLOBALS['world']['presentation']['description'] = 'summary'; check( ! str_contains( modfarm_render_world_block( $manual ), 'Rich character context' ), 'Summary mode rendered full description' ); $GLOBALS['world']['presentation']['description'] = 'full';
$GLOBALS['world']['elements'][0]['spoiler_book_id'] = 10; $GLOBALS['world']['overview'] = ''; $GLOBALS['world']['presentation']['spoilers'] = 'reader';
$_GET = array(); $locked = modfarm_render_world_block( $manual ); check( str_contains( $locked, 'I have finished' ) && ! str_contains( $locked, 'Rich character context' ), 'Locked content leaked or selector was unreachable' );
$_GET = array( 'mf_world_through_3' => '10' ); check( str_contains( modfarm_render_world_block( $manual ), 'Rich character context' ), 'Explicit spoiler choice did not reveal eligible content' );
$_GET = array(); $GLOBALS['world']['elements'][0]['spoiler_book_id'] = 0; $GLOBALS['world']['overview'] = '<strong>Fractured America</strong>'; $GLOBALS['world']['presentation']['spoilers'] = 'free';
$legacy_fixture = '<!doctype html><html><body>' . modfarm_render_world_block( $manual ) . modfarm_render_world_block( $manual ) . modfarm_render_world_block( $manual + array( 'display' => 'sections' ) ) . '</body></html>';
// Region contract stubs exercise the real Theme renderer independently of Core.
if ( true ) {
    function modfarm_world_regions( $p ) { return $p['regions'] ?? array(); }
    function modfarm_world_region_default( $id ) { return array( 'id' => $id, 'title' => 'Region', 'source' => 'elements', 'type' => 'character', 'element_ids' => array(), 'layout' => 'illustrated', 'columns' => 3, 'image_ratio' => 'original', 'image_fit' => 'contain', 'image_side' => 'left', 'description' => 'inherit', 'show_heading' => true, 'enabled' => true, 'colors' => array( 'background' => '', 'text' => '', 'accent' => '' ) ); }
}
$earth = $entry; $earth['id'] = 'wb-earth0000'; $earth['type'] = 'location'; $earth['name'] = 'A Postapocalyptic Earth';
$ships = $entry; $ships['id'] = 'wb-ships0000'; $ships['type'] = 'technology'; $ships['name'] = 'The Airships';
$divers = $entry; $divers['id'] = 'wb-divers000'; $divers['name'] = 'The Hell Divers';
$private = $entry; $private['id'] = 'wb-private00'; $private['name'] = 'Hidden secret'; $private['visibility'] = 'internal';
$draft = $entry; $draft['id'] = 'wb-draft0000'; $draft['name'] = 'Unfinished secret'; $draft['status'] = 'draft';
$spoiler = $entry; $spoiler['id'] = 'wb-spoiler00'; $spoiler['name'] = 'Later revelation'; $spoiler['spoiler_book_id'] = 10;
$GLOBALS['world']['elements'] = array( $earth, $ships, $divers, $private, $draft, $spoiler );
$intro = modfarm_world_region_default( 'region-intro' ); $intro['title'] = 'Explore the world'; $intro['element_ids'] = array( $ships['id'], $earth['id'], $divers['id'], $private['id'], $draft['id'], $spoiler['id'] ); $intro['colors']['background'] = '#142128'; $intro['colors']['text'] = '#ffffff';
$features = $intro; $features['id'] = 'region-features'; $features['title'] = 'Life above the storms'; $features['layout'] = 'alternating'; $features['element_ids'] = array( $earth['id'], $ships['id'] );
$empty = $intro; $empty['id'] = 'region-empty'; $empty['element_ids'] = array( $private['id'], $draft['id'] );
$books = modfarm_world_region_default( 'region-books' ); $books['title'] = 'Books'; $books['source'] = 'books';
$GLOBALS['world']['presentation']['regions'] = array( $intro, $features, $empty, $books );
$GLOBALS['world']['presentation']['default_region'] = 'region-features';
$region_html = modfarm_render_world_block( $manual + array( 'display' => 'sections' ) );
check( str_contains( $region_html, 'mfw-region--illustrated' ) && str_contains( $region_html, 'mfw-region--alternating' ), 'Region layouts did not render' );
check( strpos( $region_html, 'The Airships' ) < strpos( $region_html, 'A Postapocalyptic Earth' ), 'Mixed-type selected entry order changed' );
check( substr_count( $region_html, 'A Postapocalyptic Earth' ) >= 2, 'Reusing content across Regions failed' );
check( ! str_contains( $region_html, 'Hidden secret' ) && ! str_contains( $region_html, 'Unfinished secret' ) && ! str_contains( $region_html, 'Later revelation' ) && ! str_contains( $region_html, 'region-empty' ), 'Unavailable Region entries or empty Region leaked' );
check( str_contains( $region_html, 'book-cards' ), 'Explicit Books Region did not render with legacy separate default' );
check( ! str_contains( modfarm_render_world_block( $manual + array( 'books' => 'separate' ) ), 'book-cards' ), 'Placement Books override was ignored' );
foreach ( array( 'illustrated', 'portraits', 'alternating', 'immersive', 'editorial', 'banner', 'compact' ) as $layout ) {
    $GLOBALS['world']['presentation']['regions'][0]['layout'] = $layout;
    check( str_contains( modfarm_render_world_block( $manual ), 'mfw-region--' . $layout ), 'Missing layout: ' . $layout );
}
$GLOBALS['world']['presentation']['regions'][0]['layout'] = 'illustrated';
$tab_html = modfarm_render_world_block( $manual );
check( str_contains( $tab_html, 'data-world-default="region-features"' ) && str_contains( $tab_html, 'mfw-region--illustrated' ) && str_contains( $tab_html, 'mfw-region--alternating' ), 'Navigation switch lost layout or initial Region' );
$overview = modfarm_world_region_default( 'region-overview-feature' );
$overview = array_replace( $overview, array( 'source' => 'overview', 'title' => 'An immersive world', 'layout' => 'immersive', 'image_id' => 7, 'image_shape' => 'circle', 'text_align' => 'center', 'image_align' => 'right' ) );
$GLOBALS['world']['presentation']['regions'] = array( $overview );
$overview_html = modfarm_render_world_block( $manual + array( 'display' => 'sections' ) );
check( str_contains( $overview_html, 'mfw-region--immersive' ) && str_contains( $overview_html, 'mfw-entry-image' ) && str_contains( $overview_html, '<strong>Fractured America</strong>' ), 'Overview bypassed the feature renderer' );
check( 1 === substr_count( $overview_html, '>An immersive world<' ) && str_contains( $overview_html, '--mfw-image-radius:50%' ) && str_contains( $overview_html, '--mfw-text-align:center' ), 'Overview heading or circular/aligned artwork was lost' );
$GLOBALS['world']['presentation']['regions'][0]['show_heading'] = false;
check( ! str_contains( modfarm_render_world_block( $manual ), '<h3>' ), 'Hidden Overview heading rendered' );
$GLOBALS['world']['overview'] = '';
check( '' === modfarm_render_world_block( $manual ), 'Artwork-only empty Overview rendered' );
$metadata = json_decode( file_get_contents( __DIR__ . '/../modfarm-theme/modfarm-theme/blocks/world/block.json' ), true );
check( $metadata['style'] === $metadata['editorStyle'], 'Editor and reader styles differ' );
if ( in_array( '--regions-fixture', $argv, true ) ) {
    $css = file_get_contents( __DIR__ . '/../modfarm-theme/modfarm-theme/blocks/world/style.css' );
    echo '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font:18px/1.65 Arial;background:#10181c}main{max-width:1280px;margin:auto}h1{color:white;padding:24px}' . $css . '</style></head><body><main><h1>World Regions · layout review</h1>' . $region_html . $tab_html . $overview_html . '</main></body></html>';
} elseif ( in_array( '--fixture', $argv, true ) ) echo $legacy_fixture;
else echo "World block renderer and Regions: passed\n";
