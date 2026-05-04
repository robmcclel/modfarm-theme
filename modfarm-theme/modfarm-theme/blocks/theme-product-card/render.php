<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_theme_product_card_block')) {
function modfarm_render_theme_product_card_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $wrapper_attributes = get_block_wrapper_attributes(['class' => 'mfs-theme-product-card']);

    $card = modfarm_store_block_render_offer_card($offer_id, [
        'layout' => $attributes['layout'] ?? 'vertical',
        'imageAspect' => $attributes['imageAspect'] ?? '3 / 4',
        'showImage' => !isset($attributes['showImage']) || $attributes['showImage'] !== false,
        'showTitle' => !isset($attributes['showTitle']) || $attributes['showTitle'] !== false,
        'showExcerpt' => !isset($attributes['showExcerpt']) || $attributes['showExcerpt'] !== false,
        'showPrice' => !isset($attributes['showPrice']) || $attributes['showPrice'] !== false,
        'showDetails' => !isset($attributes['showDetails']) || $attributes['showDetails'] !== false,
        'showButton' => !isset($attributes['showButton']) || $attributes['showButton'] !== false,
        'excerptWords' => $attributes['excerptWords'] ?? 24,
        'buttonLabel' => $attributes['buttonLabel'] ?? 'Buy Now',
        'buttonType' => $attributes['buttonType'] ?? 'primary',
    ]);

    if ($card === '') {
        return '';
    }

    return '<div ' . $wrapper_attributes . '>' . $card . '</div>';
}
}
