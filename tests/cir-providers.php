<?php
/** Lightweight provider contract tests. Run: php tests/cir-providers.php */

define( 'ABSPATH', __DIR__ );
function get_current_blog_id() { return 3; }
function get_permalink( $id ) { return 'https://example.test/books/' . $id . '/'; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function get_posts( array $args ) { return $args['post__in']; }
function add_action() {}
function modfarm_cir_entity_reference( array $raw ) { $raw['local_key'] = sprintf( 'site:%d:%s:%s:%s', $raw['site_id'], $raw['provider'], $raw['entity_type'], $raw['provider_id'] ); return $raw; }

require dirname( __DIR__ ) . '/modfarm-theme/modfarm-theme/inc/cir-providers.php';

function assert_value( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$result = modfarm_theme_resolve_handpicked_books_presentation( array( 'books' => array( 9, 4, 9, 7 ), 'books-per-page' => 2, 'show-pagination' => true ) );
assert_value( array_column( $result['collection']['members'], 'provider_id' ) === array( '9', '4', '7' ), 'editorial order and unique IDs are preserved' );
assert_value( 2 === $result['collection']['displayed_count'], 'display limit is reported' );
assert_value( false === $result['collection']['complete'], 'truncation is reported' );
assert_value( true === $result['collection']['pagination'], 'pagination is reported' );
assert_value( 3 === count( $result['dependencies'] ), 'each selected Book is a dependency' );

echo "Theme CIR provider tests passed.\n";
