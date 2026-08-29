<?php
/** CIR block providers owned by ModFarm Theme. */

defined( 'ABSPATH' ) || exit;

function modfarm_theme_cir_entity_reference( $type, $id ) {
	$presentation_url = '';
	if ( 'book' === $type ) {
		$presentation_url = get_permalink( $id );
	} else {
		$term = get_term( $id );
		$link = $term instanceof WP_Term ? get_term_link( $term ) : '';
		$presentation_url = is_wp_error( $link ) ? '' : $link;
	}
	$raw = array( 'site_id' => get_current_blog_id(), 'provider' => 'modfarm-bms', 'entity_type' => $type, 'provider_id' => (string) absint( $id ), 'presentation_url' => $presentation_url );
	return function_exists( 'modfarm_cir_entity_reference' ) ? modfarm_cir_entity_reference( $raw ) : $raw;
}

function modfarm_theme_resolve_creator_credit_presentation( array $attributes, array $context = array() ) {
	$taxonomy = sanitize_key( $attributes['effectiveTax'] ?? $attributes['taxonomy'] ?? 'book-author' );
	if ( '__custom__' === $taxonomy ) { $taxonomy = sanitize_key( $attributes['customTax'] ?? '' ); }
	$term_id = absint( $attributes['termId'] ?? 0 );
	if ( ! $term_id && ! empty( $context['post_id'] ) && taxonomy_exists( $taxonomy ) ) {
		$ids = wp_get_post_terms( absint( $context['post_id'] ), $taxonomy, array( 'fields' => 'ids' ) );
		$term_id = ! is_wp_error( $ids ) && $ids ? absint( $ids[0] ) : 0;
	}
	if ( ! $term_id || ! taxonomy_exists( $taxonomy ) ) { return new WP_Error( 'creator-not-resolved' ); }
	$type = in_array( $taxonomy, array( 'book-author', 'book-authors' ), true ) ? 'person' : 'term';
	$reference = modfarm_theme_cir_entity_reference( $type, $term_id );
	return array( 'presented_entities' => array( $reference ), 'selection_method' => ! empty( $attributes['termId'] ) ? 'manual' : 'content-context', 'presentation' => array_filter( array( 'heading' => sanitize_text_field( $attributes['heading'] ?? '' ), 'link_to_archive' => ! empty( $attributes['linkToArchive'] ) ) ), 'dependencies' => array( array( 'key' => $reference['local_key'], 'type' => 'entity' ) ) );
}

function modfarm_theme_resolve_handpicked_books_presentation( array $attributes, array $context = array() ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) ( $attributes['books'] ?? array() ) ) ) ) );
	$ids = array_slice( $ids, 0, 5000 );
	if ( $ids ) {
		$ids = get_posts( array( 'post_type' => 'book', 'post_status' => 'publish', 'post__in' => $ids, 'orderby' => 'post__in', 'fields' => 'ids', 'posts_per_page' => count( $ids ), 'no_found_rows' => true, 'suppress_filters' => false ) );
	}
	$references = array_map( function ( $id ) { return modfarm_theme_cir_entity_reference( 'book', $id ); }, $ids );
	$display_limit = max( 1, min( 50, absint( $attributes['books-per-page'] ?? 12 ) ) );
	return array( 'presented_entities' => array_slice( $references, 0, $display_limit ), 'selection_method' => 'manual-ordered', 'collection' => array( 'entity_type' => 'book', 'selection_method' => 'manual-ordered', 'members' => $references, 'member_count' => count( $references ), 'displayed_count' => min( count( $references ), $display_limit ), 'complete' => count( $references ) <= $display_limit, 'pagination' => ! empty( $attributes['show-pagination'] ), 'ordering' => 'editorial' ), 'dependencies' => array_map( function ( $reference ) { return array( 'key' => $reference['local_key'], 'type' => 'entity' ); }, $references ) );
}

function modfarm_theme_resolve_featured_book_presentation( array $attributes, array $context = array() ) {
	if ( ! function_exists( 'mfb_resolve_featured_book_selection' ) ) { return new WP_Error( 'featured-book-resolver-unavailable' ); }
	$selection = mfb_resolve_featured_book_selection( $attributes );
	$book_id = absint( $selection['book_id'] ?? 0 );
	if ( ! $book_id ) { return new WP_Error( 'featured-book-not-resolved' ); }
	$reference = modfarm_theme_cir_entity_reference( 'book', $book_id );
	$dependencies = array( array( 'key' => $reference['local_key'], 'type' => 'entity' ) );
	foreach ( (array) ( $selection['dependencies'] ?? array() ) as $key ) { $dependencies[] = array( 'key' => 'site:' . get_current_blog_id() . ':' . $key, 'type' => 'query' ); }
	return array( 'presented_entities' => array( $reference ), 'selection_method' => $selection['selection_method'], 'query_scope' => $selection['query_scope'], 'presentation' => array_filter( array( 'heading' => sanitize_text_field( $attributes['headline'] ?? '' ), 'kicker' => sanitize_text_field( $attributes['kicker'] ?? '' ), 'cta_count' => count( array_filter( array( $attributes['btn1Source'] ?? '', $attributes['btn2Source'] ?? '', $attributes['btn3Source'] ?? '' ) ) ) ) ), 'dependencies' => $dependencies );
}

add_action( 'init', function () {
	if ( ! function_exists( 'modfarm_cir_register_block_provider' ) ) { return; }
	modfarm_cir_register_block_provider( 'modfarm/creator-credit', array( 'provider' => 'modfarm-theme', 'version' => '1', 'resolve' => 'modfarm_theme_resolve_creator_credit_presentation' ) );
	modfarm_cir_register_block_provider( 'modfarm/handpicked-books', array( 'provider' => 'modfarm-theme', 'version' => '1', 'resolve' => 'modfarm_theme_resolve_handpicked_books_presentation' ) );
	modfarm_cir_register_block_provider( 'modfarm/featured-book', array( 'provider' => 'modfarm-theme', 'version' => '1', 'resolve' => 'modfarm_theme_resolve_featured_book_presentation' ) );
}, 30 );
