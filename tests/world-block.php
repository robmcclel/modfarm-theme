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
function wp_get_attachment_image( $id, $size, $icon, $attrs ) { return '<img src="https://dev.test/portrait.jpg" alt="' . esc_attr( $attrs['alt'] ) . '">'; }
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
if ( in_array( '--fixture', $argv, true ) ) {
    echo '<!doctype html><html><body>' . modfarm_render_world_block( $manual ) . modfarm_render_world_block( $manual ) . modfarm_render_world_block( $manual + array( 'display' => 'sections' ) ) . '</body></html>';
} else echo "World block renderer: passed\n";
