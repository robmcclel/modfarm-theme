<?php
// File: blocks/two-column/render.php

if ( ! function_exists( 'modfarm_render_two_column_block' ) ) {
	function modfarm_render_two_column_block( $attributes, $content ) {
		ob_start();

		$layout         = isset( $attributes['layout'] ) ? $attributes['layout'] : '50-50';
		$gap            = isset( $attributes['gap'] ) ? $attributes['gap'] : '20';
		$centered       = ! empty( $attributes['centered'] ) ? 'align-centered' : '';
		$reverse_mobile = ! empty( $attributes['reverseMobile'] ) ? 'reverse-mobile' : '';

		$classes = implode( ' ', array_filter( [
			'modfarm-two-column',
			"layout-$layout",
			"gap-$gap",
			'valign-top',
			'stack-mobile',
			$reverse_mobile,
			$centered,
		] ) );

		echo '<div class="' . esc_attr( $classes ) . '">';
		echo do_blocks( $content );
		echo '</div>';

		return ob_get_clean();
	}
}