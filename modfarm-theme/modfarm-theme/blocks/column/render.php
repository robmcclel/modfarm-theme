<?php
// modfarm/column/render.php

if ( ! function_exists( 'modfarm_render_column_block' ) ) {
	function modfarm_render_column_block( $attributes, $content ) {
		return '<div class="modfarm-column">' . $content . '</div>';
	}
}