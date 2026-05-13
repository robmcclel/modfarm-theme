<?php
if ( ! function_exists( 'modfarm_render_book_details_block' ) ) {
function modfarm_render_book_details_block( $attributes, $content, $block ) {
	$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
	
	if ( ! $post_id ) {
	return '<div class="mfs-book-details"><p>⚠️ No post context found.</p></div>';
    }

	$fields = [];

	// Helper: add meta field
	$add_meta = function( $label, $meta_key ) use ( $post_id, &$fields ) {
		$value = get_post_meta( $post_id, $meta_key, true );
		if ( $value ) {
			$fields[] = [ $label, esc_html( $value ) ];
		}
	};

	// Helper: add taxonomy terms as links
	$add_taxonomy = function( $label, $taxonomy ) use ( $post_id, &$fields ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$links = array_map( function( $term ) {
				return sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_term_link( $term ) ),
					esc_html( $term->name )
				);
			}, $terms );
			$fields[] = [ $label, implode( ', ', $links ) ];
		}
	};

	// Add meta fields
	if ( ! empty( $attributes['show_publication_date'] ) ) $add_meta( 'Publication Date', 'publication_date' );
	if ( ! empty( $attributes['show_hardcover_publication_date'] ) ) $add_meta( 'Hardcover Pub Date', 'hardcover_publication_date' );
	if ( ! empty( $attributes['show_pages'] ) ) $add_meta( 'Page Count', 'page_count' );
	if ( ! empty( $attributes['show_isbn'] ) ) $add_meta( 'ISBN', 'isbn' );
	if ( ! empty( $attributes['show_asin'] ) ) $add_meta( 'ASIN', 'asin' );
	if ( ! empty( $attributes['show_publisher'] ) ) $add_meta( 'Publisher', 'publisher' );
	if ( ! empty( $attributes['show_edition'] ) ) $add_meta( 'Edition', 'edition' );

	if ( ! empty( $attributes['show_audiobook_publisher'] ) ) $add_meta( 'Audiobook Publisher', 'audiobook_publisher' );
	if ( ! empty( $attributes['show_audiobook_narrator'] ) ) $add_meta( 'Audiobook Narrator', 'audiobook_narrator' );
	if ( ! empty( $attributes['show_audiobook_duration'] ) ) $add_meta( 'Audiobook Duration', 'audiobook_duration' );
	if ( ! empty( $attributes['show_audiobook_publication_date'] ) ) $add_meta( 'Audiobook Pub Date', 'audiobook_publication_date' );

	if ( ! empty( $attributes['show_translator'] ) ) $add_meta( 'Translator', 'translator' );
	if ( ! empty( $attributes['show_editor'] ) ) $add_meta( 'Editor', 'editor' );
	if ( ! empty( $attributes['show_reading_order'] ) ) $add_meta( 'Reading Order', 'reading_order' );
	if ( ! empty( $attributes['show_series_position'] ) ) $add_meta( 'Series Position', 'series_position' );

	// Add taxonomy fields
	if ( ! empty( $attributes['show_format'] ) ) $add_taxonomy( 'Format', 'book_format' );
	if ( ! empty( $attributes['show_genre'] ) ) $add_taxonomy( 'Genre', 'genre' );
	if ( ! empty( $attributes['show_series_name'] ) ) $add_taxonomy( 'Series', 'series_name' );
	if ( ! empty( $attributes['show_universe'] ) ) $add_taxonomy( 'Universe', 'universe' );

	if ( empty( $fields ) ) {
    	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
    		return '<div class="mfs-book-details"><p>No metadata selected or available.</p></div>';
    	}
    	return '';
    }
    
    //if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
    //	error_log( 'BLOCK CONTEXT: ' . print_r( $block->context, true ) );
    //	error_log( 'POST ID: ' . $post_id );
    //}

	$wrapper_attributes = get_block_wrapper_attributes();

	ob_start();
	?>
	<div <?= $wrapper_attributes ?>>
		<dl class="mfs-book-details">
			<?php foreach ( $fields as $field ) : ?>
				<dt><?= esc_html( $field[0] ) ?></dt>
				<dd><?= $field[1] ?></dd>
			<?php endforeach; ?>
		</dl>
	</div>
	<?php
	return ob_get_clean();
}
}
