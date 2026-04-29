<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Tab Panel (pass-through render)
 *
 * Keeps tab-panel static. The save() already outputs the wrapper and InnerBlocks.
 */
function modfarm_render_tab_panel( $attributes, $content, $block ) {
    return $content;
}