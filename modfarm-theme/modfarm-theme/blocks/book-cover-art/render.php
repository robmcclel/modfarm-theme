<?php
// blocks/book-cover-art/render.php
require_once get_template_directory() . '/blocks/shared/book-options.php';

function modfarm_render_book_cover_art_block( $attributes, $content = '', $block = null ) {
	$post_id = isset( $block->context['postId'] ) ? $block->context['postId'] : get_the_ID();
	if ( ! $post_id || $post_id === 0 ) {
		return '<div class="modfarm-cover-art missing-cover">No post context.</div>';
	}

	$cover_type = modfarm_book_option_normalize_cover_source( (string) ( $attributes['coverType'] ?? 'cover_ebook' ) );
	$alignment  = esc_attr( $attributes['alignment'] ?? 'center' );
	$class_name = "modfarm-cover-art align-{$alignment}";

	$image_url = modfarm_book_cover_url( (int) $post_id, $cover_type );

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
