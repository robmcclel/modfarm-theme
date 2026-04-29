<?php
/**
 * Archive Template
 * Handles taxonomy archives, post type archives, and the blog index
 */

get_header();

if (function_exists('modfarm_render_archive_page')) {
    modfarm_render_archive_page();
} else {
    echo '<main><p>No archive rendering logic found.</p></main>';
}

get_footer();