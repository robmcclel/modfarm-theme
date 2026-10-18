<?php
function modfarm_render_hero_cover_block($attributes, $content = '', $block = null) {
    $image_url = modfarm_resolve_hero_image($attributes, $block);
    $align = $attributes['contentAlign'] ?? 'center';
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
    $vertical = $attributes['contentVerticalAlign'] ?? 'center';
    $vertical = in_array($vertical, ['top', 'center', 'bottom'], true) ? $vertical : 'center';
    $mobile_align = $attributes['mobileContentAlign'] ?? 'inherit';
    $mobile_align = in_array($mobile_align, ['left', 'center', 'right'], true) ? $mobile_align : $align;
    $mobile_vertical = $attributes['mobileVerticalAlign'] ?? 'inherit';
    $mobile_vertical = in_array($mobile_vertical, ['top', 'center', 'bottom'], true) ? $mobile_vertical : $vertical;
    $positions = ['top' => 'flex-start', 'center' => 'center', 'bottom' => 'flex-end'];
    $width = $attributes['contentMaxWidth'] ?? '1200px';
    $width = preg_match('/^\d+(?:\.\d+)?(?:px|rem|em|%|vw)$/', $width) ? $width : '1200px';
    $height = max(0, min(2000, (int) ($attributes['minHeight'] ?? 420)));
    $classes = 'modfarm-hero-cover align-' . $align;
    if (!empty($attributes['structuredLayout'])) {
        $classes .= ' modfarm-hero-cover--structured';
    }
    if (!$image_url) {
        $classes .= ' modfarm-hero-cover--no-image';
    }
    $style = 'min-height:' . $height . 'px;';
    if ($image_url) {
        $style .= 'background-image:url("' . esc_url($image_url) . '");';
    }
    $style .= '--mf-hero-width:' . $width . ';--mf-hero-align:' . $align . ';';
    $style .= '--mf-hero-vertical:' . $positions[$vertical] . ';';
    $style .= '--mf-hero-mobile-align:' . $mobile_align . ';--mf-hero-mobile-vertical:' . $positions[$mobile_vertical] . ';';
    $wrapper_attributes = ['class' => $classes, 'style' => $style];
    if (!empty($attributes['__preview'])) {
        $wrapper_attributes['data-mf-hero-image'] = $image_url;
    }
    $wrapper = get_block_wrapper_attributes($wrapper_attributes);
    $overlay = $attributes['overlayGradient'] ?? '';
    $overlay = $overlay ?: ($attributes['overlayColor'] ?? '#000000');
    $opacity = max(0, min(100, (int) ($attributes['dimRatio'] ?? 20))) / 100;
    ob_start(); ?>
    <section <?php echo $wrapper; ?>>
        <div aria-hidden="true" class="modfarm-hero-cover__overlay" style="background:<?php echo esc_attr($overlay); ?>;opacity:<?php echo esc_attr($opacity); ?>;"></div>
        <?php if (empty($attributes['__preview'])) echo modfarm_hero_edge_markup($attributes); ?>
        <?php if (empty($attributes['__preview'])) : ?>
            <div class="modfarm-hero-cover__content"><?php echo $content; ?></div>
        <?php endif; ?>
    </section>
    <?php return ob_get_clean();
}
