<?php
/** World presentation consumes the Core public projection, never authoring notes. */
defined( 'ABSPATH' ) || exit;

function modfarm_world_resolve_series( $attributes, $block = null ) {
    if ( 'manual' === ( $attributes['placement'] ?? 'auto' ) ) {
        $id = absint( $attributes['seriesId'] ?? 0 );
        $term = $id ? get_term( $id, 'book-series' ) : null;
        return $term instanceof WP_Term ? $term : null;
    }
    $queried = get_queried_object();
    if ( $queried instanceof WP_Term && 'book-series' === $queried->taxonomy ) return $queried;
    $post_id = absint( $block->context['postId'] ?? 0 );
    if ( ! $post_id && is_singular( 'book' ) ) $post_id = get_queried_object_id();
    if ( ! $post_id && defined( 'REST_REQUEST' ) && REST_REQUEST ) $post_id = get_the_ID();
    if ( ! $post_id || 'book' !== get_post_type( $post_id ) ) return null;
    $series = get_the_terms( $post_id, 'book-series' );
    return is_array( $series ) && count( $series ) === 1 ? reset( $series ) : null;
}

function modfarm_world_reader_books( $series_id ) {
    $books = get_posts( array( 'post_type' => 'book', 'post_status' => 'publish', 'numberposts' => -1, 'tax_query' => array( array( 'taxonomy' => 'book-series', 'field' => 'term_id', 'terms' => $series_id ) ), 'orderby' => 'title', 'order' => 'ASC' ) );
    usort( $books, static function ( $a, $b ) {
        return ( (float) get_post_meta( $a->ID, 'series_position', true ) <=> (float) get_post_meta( $b->ID, 'series_position', true ) ) ?: strnatcasecmp( $a->post_title, $b->post_title );
    } );
    return $books;
}

function modfarm_world_entry_html( $entry, $presentation ) {
    $html = '<article class="mfw-entry">';
    if ( $presentation['show_images'] && ! empty( $entry['image_id'] ) ) {
        $image = wp_get_attachment_image( absint( $entry['image_id'] ), 'medium', false, array( 'class' => 'mfw-entry-image', 'alt' => $entry['name'] ) );
        if ( $image ) $html .= $image;
    }
    $html .= '<div class="mfw-entry-copy"><h3>' . esc_html( $entry['name'] ) . '</h3>';
    if ( ! empty( $entry['summary'] ) ) $html .= '<p class="mfw-entry-summary">' . nl2br( esc_html( $entry['summary'] ) ) . '</p>';
    if ( 'full' === $presentation['description'] && ! empty( $entry['description'] ) ) $html .= '<div class="mfw-rich-text">' . wp_kses_post( wpautop( $entry['description'] ) ) . '</div>';
    return $html . '</div></article>';
}

function modfarm_world_section_content( $key, $public, $presentation, $term ) {
    if ( 'overview' === $key ) return trim( wp_strip_all_tags( $public['overview'] ) ) ? '<div class="mfw-rich-text">' . wp_kses_post( wpautop( $public['overview'] ) ) . '</div>' : '';
    if ( 'books' === $key ) {
        if ( 'included' !== $presentation['books'] || ! function_exists( 'modfarm_render_archive_book_list_block' ) || ! modfarm_world_reader_books( $term->term_id ) ) return '';
        return modfarm_render_archive_book_list_block( array( 'show-pagination' => false ), '', null, $term );
    }
    $entries = array_filter( $public['elements'], static function ( $entry ) use ( $key ) { return $key === $entry['type']; } );
    if ( ! $entries ) return '';
    $html = '<div class="mfw-entries">';
    foreach ( $entries as $entry ) $html .= modfarm_world_entry_html( $entry, $presentation );
    return $html . '</div>';
}

function modfarm_render_world_block( $attributes, $content = '', $block = null ) {
    if ( ! function_exists( 'modfarm_world_bible_public' ) || ! function_exists( 'modfarm_world_presentation_values' ) ) return '';
    $term = modfarm_world_resolve_series( $attributes, $block );
    if ( ! $term ) return '';
    $source = modfarm_world_bible_get( $term->term_id );
    if ( 'published' !== $source['status'] ) return '';
    $p = modfarm_world_presentation_values( $source['presentation'] );
    if ( is_wp_error( $p ) ) return '';
    foreach ( array( 'display' => array( 'sections', 'tabs' ), 'books' => array( 'included', 'separate' ) ) as $key => $allowed ) if ( isset( $attributes[$key] ) && in_array( $attributes[$key], $allowed, true ) ) $p[$key] = $attributes[$key];
    foreach ( array( 'background', 'text', 'accent' ) as $key ) if ( ! empty( $attributes[$key] ) && is_string( $attributes[$key] ) && preg_match( '/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i', $attributes[$key] ) ) $p['colors'][$key] = $attributes[$key];
    $query_key = 'mf_world_through_' . $term->term_id;
    $through = 'reader' === $p['spoilers'] && isset( $_GET[$query_key] ) && is_scalar( $_GET[$query_key] ) ? absint( $_GET[$query_key] ) : 0;
    $public = modfarm_world_bible_public( $term->term_id, $through );
    $sections = array();
    foreach ( $p['sections'] as $section ) if ( $section['enabled'] ) {
        $html = modfarm_world_section_content( $section['key'], $public, $p, $term );
        if ( '' !== trim( $html ) ) $sections[] = array( 'key' => $section['key'], 'label' => $section['label'], 'html' => $html );
    }
    // A selector must remain reachable when every element is spoiler-bound.
    $has_reader_content = false;
    if ( 'reader' === $p['spoilers'] && ! $sections ) {
        $last_book = 0; $last_position = 0;
        foreach ( modfarm_world_reader_books( $term->term_id ) as $book ) {
            $position = get_post_meta( $book->ID, 'series_position', true );
            if ( is_numeric( $position ) && (float) $position > $last_position ) { $last_position = (float) $position; $last_book = $book->ID; }
        }
        $eligible = modfarm_world_bible_public( $term->term_id, $last_book );
        foreach ( $p['sections'] as $section ) if ( $section['enabled'] && 'books' !== $section['key'] && modfarm_world_section_content( $section['key'], $eligible, $p, $term ) ) { $has_reader_content = true; break; }
    }
    if ( ! $sections && ! $has_reader_content ) return '';
    $id = wp_unique_id( 'mfw-' );
    $style = '';
    foreach ( $p['colors'] as $key => $color ) if ( $color ) $style .= '--mfw-' . str_replace( '_', '-', $key ) . ':' . $color . ';';
    $wrapper = get_block_wrapper_attributes( array( 'class' => 'mfw mfw--' . $p['display'], 'style' => $style ) );
    $html = '<div ' . $wrapper . ' data-world-tabs data-world-default="' . esc_attr( $p['default_section'] ) . '">';
    if ( 'reader' === $p['spoilers'] ) {
        $html .= '<form class="mfw-spoilers" method="get"><label for="' . esc_attr( $id . '-spoilers' ) . '">I have finished</label><select id="' . esc_attr( $id . '-spoilers' ) . '" name="' . esc_attr( $query_key ) . '"><option value="0">No books yet · spoiler-free</option>';
        foreach ( modfarm_world_reader_books( $term->term_id ) as $book ) {
            $position = get_post_meta( $book->ID, 'series_position', true );
            if ( ! is_numeric( $position ) || (float) $position <= 0 ) continue;
            $html .= '<option value="' . esc_attr( $book->ID ) . '"' . selected( $through, $book->ID, false ) . '>' . esc_html( get_the_title( $book ) ) . '</option>';
        }
        $html .= '</select><button type="submit">Update guide</button>';
        foreach ( $_GET as $key => $value ) if ( $key !== $query_key && is_scalar( $value ) ) $html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( wp_unslash( $value ) ) . '">';
        $html .= '<p>Descriptions may reveal events through the selected book.</p></form>';
    }
    if ( ! $sections ) $html .= '<p>Choose a finished book to see the available world guide.</p>';
    // All sections remain visible without JavaScript. The reader script enhances
    // this into tabs with unique IDs and keyboard navigation for each instance.
    if ( 'tabs' === $p['display'] && $sections ) {
        $html .= '<div class="mfw-tablist" aria-label="' . esc_attr( $term->name . ' guide' ) . '" hidden>';
        foreach ( $sections as $section ) $html .= '<button type="button" id="' . esc_attr( $id . '-tab-' . $section['key'] ) . '" data-world-tab="' . esc_attr( $section['key'] ) . '" aria-controls="' . esc_attr( $id . '-' . $section['key'] ) . '">' . esc_html( $section['label'] ) . '</button>';
        $html .= '</div>';
    }
    foreach ( $sections as $section ) $html .= '<section class="mfw-section" id="' . esc_attr( $id . '-' . $section['key'] ) . '" data-world-panel="' . esc_attr( $section['key'] ) . '"><h2>' . esc_html( $section['label'] ) . '</h2>' . $section['html'] . '</section>';
    return $html . '</div>';
}
