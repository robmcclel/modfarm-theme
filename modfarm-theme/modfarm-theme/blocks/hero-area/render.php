<?php
function modfarm_render_hero_area_block($attributes, $content = '') {
    $area = ($attributes['area'] ?? '') === 'navigation' ? 'navigation' : 'content';
    return '<div ' . get_block_wrapper_attributes(['class' => 'mf-hero-area mf-hero-area--' . $area]) . '><div class="mf-hero-area__inner">' . $content . '</div></div>';
}
