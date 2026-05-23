<?php
/**
 * Archive Template
 * Handles Collection CPT archives before falling back to the standard PPB archive renderer.
 */

get_header();

if (function_exists('modfarm_render_collection_archive_page') && modfarm_render_collection_archive_page()) {
    get_footer();
    return;
}

if (function_exists('modfarm_render_archive_page')) {
    modfarm_render_archive_page();
} else {
    echo '<main><p>No archive rendering logic found.</p></main>';
}

get_footer();
