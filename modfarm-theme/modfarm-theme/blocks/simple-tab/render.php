<?php
if (!defined('ABSPATH')) exit;

/**
 * Callback render for child block (Option A protocol).
 * $content contains the InnerBlocks HTML.
 */
function modfarm_render_simple_tab_block( $attributes, $content, $block ) {
  return $content;
}