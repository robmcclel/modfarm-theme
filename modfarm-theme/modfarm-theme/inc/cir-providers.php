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

function modfarm_theme_taxonomy_grid_entity_type( $taxonomy ) {
	$map = function_exists( 'modfarm_bms_entity_taxonomies' ) ? array_flip( modfarm_bms_entity_taxonomies() ) : array(
		'book-author' => 'person', 'book-series' => 'series', 'book-genre' => 'genre',
		'book-format' => 'format', 'book-language' => 'language', 'book-tags' => 'tag',
	);
	return sanitize_key( $map[ $taxonomy ] ?? 'term' );
}

/** Describe the same dynamic term and book set used by Taxonomy Grid rendering. */
function modfarm_theme_resolve_taxonomy_grid_presentation( array $attributes, array $context = array() ) {
	if ( ! function_exists( 'modfarm_taxonomy_grid_resolve_terms' ) ) { return new WP_Error( 'taxonomy-grid-resolver-unavailable' ); }
	$selection = modfarm_taxonomy_grid_resolve_terms( $attributes, 1 );
	if ( is_wp_error( $selection ) ) { return $selection; }

	$taxonomy = $selection['taxonomy'];
	$entity_type = modfarm_theme_taxonomy_grid_entity_type( $taxonomy );
	$terms = array_slice( $selection['terms'], 0, 5000 );
	$term_references = array_map( function ( $term ) use ( $entity_type ) {
		return modfarm_theme_cir_entity_reference( $entity_type, $term->term_id );
	}, $terms );
	$visible_ids = array_fill_keys( array_map( function ( $term ) { return (int) $term->term_id; }, $selection['visible'] ), true );
	$presented = array_values( array_filter( $term_references, function ( $reference ) use ( $visible_ids ) {
		return isset( $visible_ids[ absint( $reference['provider_id'] ?? 0 ) ] );
	} ) );

	$term_ids = array_map( function ( $term ) { return (int) $term->term_id; }, $terms );
	$book_ids = $term_ids ? get_posts( array(
		'post_type' => 'book', 'post_status' => 'publish', 'posts_per_page' => 5001,
		'fields' => 'ids', 'no_found_rows' => true, 'orderby' => 'ID', 'order' => 'ASC',
		'tax_query' => array( array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $term_ids ) ),
	) ) : array();
	$books_complete = count( $book_ids ) <= 5000;
	$book_ids = array_slice( array_values( array_unique( array_map( 'absint', $book_ids ) ) ), 0, 5000 );
	$book_references = array_map( function ( $id ) { return modfarm_theme_cir_entity_reference( 'book', $id ); }, $book_ids );
	if ( 'books_by_series' === $selection['group_mode'] ) {
		$presented = array_merge( $presented, $book_references );
	}

	$dependencies = array(
		array( 'key' => 'site:' . get_current_blog_id() . ':query:taxonomy:' . $taxonomy, 'type' => 'query' ),
		array( 'key' => 'site:' . get_current_blog_id() . ':query:post-type:book', 'type' => 'query' ),
	);
	foreach ( array_merge( $term_references, $book_references ) as $reference ) {
		if ( ! empty( $reference['local_key'] ) ) $dependencies[] = array( 'key' => $reference['local_key'], 'type' => 'entity' );
	}

	return array(
		'presented_entities' => $presented,
		'selection_method' => 'dynamic-taxonomy-query',
		'query_scope' => array(
			'taxonomy' => $taxonomy,
			'group_mode' => $selection['group_mode'],
			'display_mode' => sanitize_key( $attributes['displayMode'] ?? 'all' ),
			'parent_id' => absint( $attributes['parentId'] ?? 0 ),
			'hide_empty' => ! empty( $attributes['hideEmpty'] ),
			'hide_parents' => ! empty( $attributes['hideParents'] ),
			'series_genre_slug' => $selection['genre_slug'],
		),
		'collection' => array(
			'entity_type' => $entity_type,
			'selection_method' => 'dynamic-taxonomy-query',
			'members' => $term_references,
			'member_count' => count( $selection['terms'] ),
			'displayed_count' => count( $selection['visible'] ),
			'complete' => count( $selection['terms'] ) <= 5000,
			'pagination' => $selection['pagination'],
			'ordering' => sanitize_key( $attributes['orderBy'] ?? 'name_asc' ),
			'book_members' => $book_references,
			'book_count' => count( $book_ids ),
			'books_complete' => $books_complete,
		),
		'presentation' => array( 'columns' => absint( $attributes['columns'] ?? 4 ), 'show_toc' => ! empty( $attributes['showTOC'] ) ),
		'dependencies' => $dependencies,
	);
}

add_action( 'init', function () {
	if ( ! function_exists( 'modfarm_cir_register_block_provider' ) ) { return; }
	modfarm_cir_register_block_provider( 'modfarm/creator-credit', array( 'provider' => 'modfarm-theme', 'version' => '1', 'resolve' => 'modfarm_theme_resolve_creator_credit_presentation' ) );
	modfarm_cir_register_block_provider( 'modfarm/handpicked-books', array( 'provider' => 'modfarm-theme', 'version' => '1', 'resolve' => 'modfarm_theme_resolve_handpicked_books_presentation' ) );
	modfarm_cir_register_block_provider( 'modfarm/featured-book', array( 'provider' => 'modfarm-theme', 'version' => '1', 'resolve' => 'modfarm_theme_resolve_featured_book_presentation' ) );
	modfarm_cir_register_block_provider( 'modfarm/taxonomy-grid', array( 'provider' => 'modfarm-theme', 'version' => '2', 'resolve' => 'modfarm_theme_resolve_taxonomy_grid_presentation' ) );
}, 30 );
