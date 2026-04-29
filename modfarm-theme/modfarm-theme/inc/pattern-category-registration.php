<?php
/**
 * Registers the custom block pattern category for ModFarm patterns.
 */

add_action('init', function () {
    register_block_pattern_category(
        'modfarm-patterns',
        [
            'label'       => __('ModFarm Patterns', 'modfarm-author'),
            'description' => __('Custom layout patterns built for the ModFarm Author Theme.')
        ]
    );
});