<?php
if (!function_exists('modfarm_render_zone_block')) {
    function modfarm_render_zone_block($attributes = [], $content = '', $block = null) {
        unset($attributes, $block);

        // Zone wrappers are editor/admin affordances only. Frontend output should
        // render only the inner blocks with no visible wrapper or label.
        return (string) $content;
    }
}
