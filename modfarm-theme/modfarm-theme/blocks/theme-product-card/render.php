<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_theme_product_card_block')) {
function modfarm_render_theme_product_card_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $wrapper_attributes = get_block_wrapper_attributes(['class' => 'mfs-theme-product-card']);

    $card = modfarm_store_block_render_offer_card($offer_id, [
        'layout' => $attributes['layout'] ?? 'commerce',
        'imageAspect' => $attributes['imageAspect'] ?? '1 / 1',
        'showImage' => !isset($attributes['showImage']) || $attributes['showImage'] !== false,
        'showTitle' => !empty($attributes['showTitle']),
        'showExcerpt' => !empty($attributes['showExcerpt']),
        'showPrice' => !isset($attributes['showPrice']) || $attributes['showPrice'] !== false,
        'showDetails' => !isset($attributes['showDetails']) || $attributes['showDetails'] !== false,
        'showPrimaryButton' => !isset($attributes['showPrimaryButton']) || $attributes['showPrimaryButton'] !== false,
        'showSecondaryButton' => !isset($attributes['showSecondaryButton']) || $attributes['showSecondaryButton'] !== false,
        'excerptWords' => $attributes['excerptWords'] ?? 24,
        'descriptionOverride' => ($attributes['descriptionMode'] ?? 'auto') === 'custom' ? ($attributes['descriptionOverride'] ?? '') : '',
        'detailOverride' => ($attributes['detailMode'] ?? 'auto') === 'custom' ? ($attributes['detailOverride'] ?? '') : '',
        'primaryButtonLabel' => $attributes['primaryButtonLabel'] ?? 'Buy Now',
        'secondaryButtonLabel' => $attributes['secondaryButtonLabel'] ?? 'Learn More',
        'secondaryButtonLink' => $attributes['secondaryButtonLink'] ?? 'permalink',
        'buttonStyleMode' => $attributes['buttonStyleMode'] ?? 'inherit',
        'buttonLayout' => $attributes['buttonLayout'] ?? 'joined',
        'buttonCorners' => $attributes['buttonCorners'] ?? 'square',
        'primaryButtonBg' => $attributes['primaryButtonBg'] ?? '',
        'primaryButtonFg' => $attributes['primaryButtonFg'] ?? '',
        'primaryButtonBorder' => $attributes['primaryButtonBorder'] ?? '',
        'secondaryButtonBg' => $attributes['secondaryButtonBg'] ?? '',
        'secondaryButtonFg' => $attributes['secondaryButtonFg'] ?? '',
        'secondaryButtonBorder' => $attributes['secondaryButtonBorder'] ?? '',
    ]);

    if ($card === '') {
        return '';
    }

    return '<div ' . $wrapper_attributes . '>' . $card . '</div>';
}
}
