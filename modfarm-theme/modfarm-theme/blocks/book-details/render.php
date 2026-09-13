<?php
if ( ! function_exists( 'modfarm_render_book_details_block' ) ) {
function modfarm_render_book_details_block( $attributes, $content, $block ) {
	$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
	
	if ( ! $post_id ) {
	return '<div class="mfs-book-details"><p>⚠️ No post context found.</p></div>';
    }

	$fields = [];
	$language = function_exists( 'modfarm_language_context' ) ? modfarm_language_context( $post_id, (int) ( $attributes['mfLanguage'] ?? 0 ) ) : 0;
	$phrase = static function( $key, $fallback ) use ( $language ) {
		return function_exists( 'modfarm_phrase' ) ? modfarm_phrase( $key, $language ) : $fallback;
	};
	$date_meta_keys = [
		'publication_date',
		'hardcover_publication_date',
		'paperback_publication_date',
		'audiobook_publication_date',
	];

	$format_meta_value = function( $value, $meta_key ) use ( $date_meta_keys ) {
		$value = trim( (string) $value );
		if ( in_array( $meta_key, $date_meta_keys, true ) ) {
			$timestamp = strtotime( $value );
			if ( $timestamp ) {
				return date_i18n( 'F j, Y', $timestamp );
			}
		}
		return $value;
	};

	// Helper: add meta field
	$add_meta = function( $label, $meta_key ) use ( $post_id, &$fields, $format_meta_value ) {
		$value = get_post_meta( $post_id, $meta_key, true );
		if ( $value ) {
			$value = $format_meta_value( $value, $meta_key );
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
	if ( ! empty( $attributes['show_publication_date'] ) ) $add_meta( $phrase( 'book_detail.publication_date', 'Publication Date' ), 'publication_date' );
	if ( ! empty( $attributes['show_hardcover_publication_date'] ) ) $add_meta( $phrase( 'book_detail.hardcover_publication_date', 'Hardcover Pub Date' ), 'hardcover_publication_date' );
	if ( ! empty( $attributes['show_paperback_publication_date'] ) ) $add_meta( $phrase( 'book_detail.paperback_publication_date', 'Paperback Pub Date' ), 'paperback_publication_date' );
	if ( ! empty( $attributes['show_pages'] ) ) $add_meta( $phrase( 'book_detail.page_count', 'Page Count' ), 'page_count' );
	if ( ! empty( $attributes['show_isbn'] ) ) $add_meta( $phrase( 'book_detail.isbn', 'ISBN' ), 'isbn' );
	if ( ! empty( $attributes['show_asin'] ) ) $add_meta( $phrase( 'book_detail.asin', 'ASIN' ), 'asin' );
	if ( ! empty( $attributes['show_publisher'] ) ) $add_meta( $phrase( 'book_detail.publisher', 'Publisher' ), 'publisher' );
	if ( ! empty( $attributes['show_edition'] ) ) $add_meta( $phrase( 'book_detail.edition', 'Edition' ), 'edition' );

	if ( ! empty( $attributes['show_audiobook_publisher'] ) ) $add_meta( $phrase( 'book_detail.audiobook_publisher', 'Audiobook Publisher' ), 'audiobook_publisher' );
	if ( ! empty( $attributes['show_audiobook_narrator'] ) ) $add_meta( $phrase( 'book_detail.audiobook_narrator', 'Audiobook Narrator' ), 'audiobook_narrator' );
	if ( ! empty( $attributes['show_audiobook_duration'] ) ) $add_meta( $phrase( 'book_detail.audiobook_duration', 'Audiobook Duration' ), 'audiobook_duration' );
	if ( ! empty( $attributes['show_audiobook_publication_date'] ) ) $add_meta( $phrase( 'book_detail.audiobook_publication_date', 'Audiobook Pub Date' ), 'audiobook_publication_date' );

	if ( ! empty( $attributes['show_translator'] ) ) $add_meta( $phrase( 'book_detail.translator', 'Translator' ), 'translator' );
	if ( ! empty( $attributes['show_editor'] ) ) $add_meta( $phrase( 'book_detail.editor', 'Editor' ), 'editor' );
	if ( ! empty( $attributes['show_reading_order'] ) ) $add_meta( $phrase( 'book_detail.reading_order', 'Reading Order' ), 'reading_order' );
	if ( ! empty( $attributes['show_series_position'] ) ) $add_meta( $phrase( 'book_detail.series_position', 'Series Position' ), 'series_position' );

	// Add taxonomy fields
	if ( ! empty( $attributes['show_format'] ) ) $add_taxonomy( $phrase( 'book_detail.format', 'Format' ), 'book_format' );
	if ( ! empty( $attributes['show_genre'] ) ) $add_taxonomy( $phrase( 'book_detail.genre', 'Genre' ), 'genre' );
	if ( ! empty( $attributes['show_series_name'] ) ) $add_taxonomy( $phrase( 'series.series', 'Series' ), 'series_name' );
	if ( ! empty( $attributes['show_universe'] ) ) $add_taxonomy( $phrase( 'book_detail.universe', 'Universe' ), 'universe' );

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
