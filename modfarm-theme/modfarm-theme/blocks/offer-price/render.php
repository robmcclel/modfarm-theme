<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_offer_price_block')) {
function modfarm_render_offer_price_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    if ($offer_id <= 0) {
        return modfarm_store_block_is_editor_context() ? '<div class="mfs-offer-price">No Offer selected.</div>' : '';
    }

    $price = get_post_meta($offer_id, 'mf_offer_price', true);
    $formatted = modfarm_store_block_format_price($price);

    if ($formatted === '') {
        return modfarm_store_block_is_editor_context() ? '<div class="mfs-offer-price mfs-offer-price--empty">No price set.</div>' : '';
    }

    $wrapper_attributes = get_block_wrapper_attributes(['class' => 'mfs-offer-price']);

    return '<div ' . $wrapper_attributes . '>' . esc_html($formatted) . '</div>';
}
}
