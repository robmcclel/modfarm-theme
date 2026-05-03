<?php
/**
 * Render callback for Book Author Credit block
 */

if ( ! function_exists( 'modfarm_render_book_author_credit_block' ) ) {
	function modfarm_render_book_author_credit_block( $attributes, $content = '', $block = null ) {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		// Attributes
		$alignment   = isset( $attributes['alignment'] ) ? sanitize_html_class( 'align-' . $attributes['alignment'] ) : 'align-left';
		$font_size   = isset( $attributes['fontSize'] ) && is_numeric( $attributes['fontSize'] ) ? intval( $attributes['fontSize'] ) : null;
		$text_color  = isset( $attributes['textColor'] ) ? sanitize_hex_color( $attributes['textColor'] ) : null;
		$show_avatar = ! empty( $attributes['showAvatar'] );

		// Inline styles
		$style = '';
		if ( $font_size ) {
			$style .= "font-size: {$font_size}px;";
		}
		if ( $text_color ) {
			$style .= "color: {$text_color};";
		}

		// Get authors
		$authors = get_the_terms( $post_id, 'book-author' );
		$author_links = [];

		if ( ! empty( $authors ) && ! is_wp_error( $authors ) ) {
			foreach ( $authors as $term ) {
				$name = $term->name ?? '';
				$link = get_term_link( $term );

				if ( is_wp_error( $link ) || empty( $name ) ) {
					continue;
				}

				$author_links[] = sprintf(
					'<a class="mfs-author-name" href="%s">%s</a>',
					esc_url( $link ),
					esc_html( $name )
				);
			}
		}

		// Build author output
		if ( empty( $author_links ) ) {
			$author_output = '<p class="mfs-author-credit-line">By <span class="mfs-author-fallback">Select Author</span></p>';
		} else {
			$count = count( $author_links );

            if ( $count === 1 ) {
            	$author_output = '<p class="mfs-author-credit-line">By ' . $author_links[0] . '</p>';
            } elseif ( $count === 2 ) {
            	$author_output = '<p class="mfs-author-credit-line">By ' . $author_links[0] . ' and ' . $author_links[1] . '</p>';
            } else {
            	$last = array_pop( $author_links );
            	$author_output = '<p class="mfs-author-credit-line">By ' . implode( ', ', $author_links ) . ', and ' . $last . '</p>';
            }
		}

		// Output
		ob_start();
		?>
		<div class="mfs-author-credit-entry <?php echo esc_attr( $alignment ); ?>" <?php if ( $style ) echo 'style="' . esc_attr( $style ) . '"'; ?>>
			<?php if ( $show_avatar && ! empty( $authors ) ) : ?>
				<?php foreach ( $authors as $author ) :
					$profile_picture_id = absint( get_term_meta( $author->term_id, 'archive_default_image', true ) );
					$avatar_url = $profile_picture_id ? wp_get_attachment_image_url( $profile_picture_id, 'thumbnail' ) : '';
					if ( ! empty( $avatar_url ) ): ?>
						<img class="mfs-author-avatar" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $author->name ); ?>" />
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php echo $author_output; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
