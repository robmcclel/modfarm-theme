<?php
// blocks/book-cover-art/render.php

function modfarm_render_book_cover_art_block( $attributes, $content = '', $block = null ) {
	$post_id = isset( $block->context['postId'] ) ? $block->context['postId'] : get_the_ID();
	if ( ! $post_id || $post_id === 0 ) {
		return '<div class="modfarm-cover-art missing-cover">No post context.</div>';
	}

	$meta_key   = $attributes['coverType'] ?? 'cover_ebook';
	$alignment  = esc_attr( $attributes['alignment'] ?? 'center' );
	$class_name = "modfarm-cover-art align-{$alignment}";

	$image_value = get_post_meta( $post_id, $meta_key, true );
	if ( is_numeric( $image_value ) ) {
		$image_url = wp_get_attachment_url( $image_value );
	} else {
		$image_url = esc_url( $image_value );
	}

	if ( ! $image_url && has_post_thumbnail( $post_id ) ) {
		$image_url = get_the_post_thumbnail_url( $post_id, 'full' );
	}

	if ( ! $image_url ) {
		return '<div class="modfarm-cover-art missing-cover">No image found.</div>';
	}

	$alt = $attributes['customAlt'] ?? 'Book Cover';

	ob_start();
        ?>
        <div class="<?php echo esc_attr( $class_name ); ?>">
        	<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" />
        </div>
        <?php
    return ob_get_clean();
}