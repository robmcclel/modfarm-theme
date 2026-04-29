<?php
function modfarm_render_book_page_description_block( $attributes ) {
	if ( is_admin() && ! is_singular( 'book' ) ) {
    	$post_type = get_post_type();
    	
    	if ( $post_type !== 'book' ) {
    		return '<p style="opacity:0.6;"><em>Book Description block only renders on Book posts.</em></p>';
    	}
    }

	$post_id = get_the_ID();
	$description = get_post_meta( $post_id, 'book_description', true );

	if ( empty( $description ) ) {
		return '';
	}

	$classes = 'book-page-description';
	if ( ! empty( $attributes['align'] ) ) {
		$classes .= ' align' . sanitize_html_class( $attributes['align'] );
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<?php echo wp_kses_post( wpautop( $description ) ); ?>
	</div>
	<?php
	return ob_get_clean();
}