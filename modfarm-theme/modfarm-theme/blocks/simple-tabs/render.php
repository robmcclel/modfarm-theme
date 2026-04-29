<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Simple Tabs (pass-through render)
 *
 * This block is registered with render_callback via register-blocks.php.
 * We intentionally return saved markup ($content) to avoid double-wrapping
 * (JS save already outputs the full tabs shell).
 */
function modfarm_render_simple_tabs_block( $attributes, $content, $block ) {
    return $content;
}