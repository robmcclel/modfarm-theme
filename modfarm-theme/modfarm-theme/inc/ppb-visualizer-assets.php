<?php
/**
 * Capture the styles requested by rendered blocks for standalone preview documents.
 */
defined('ABSPATH') || exit;

function modfarm_ppb_visualizer_capture_styles(callable $render): array {
    $styles = wp_styles();
    $original_queue = $styles->queue;
    // The admin page's styles do not belong in the frontend preview.
    $styles->queue = [];

    try {
        $content = $render();
        // Let WordPress resolve dependencies, versions, RTL and inline styles.
        // A clone prevents printing a preview from marking admin styles as done.
        $preview_styles = clone $styles;
        $preview_styles->done = [];
        $preview_styles->to_do = [];
        $preview_styles->do_concat = false;
        $preview_styles->concat = '';
        $preview_styles->concat_version = '';
        $preview_styles->print_html = '';
        $preview_styles->print_code = '';

        ob_start();
        try {
            $preview_styles->do_items($styles->queue);
            $markup = (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
        return ['content' => $content, 'styles' => $markup];
    } finally {
        $styles->queue = $original_queue;
    }
}
