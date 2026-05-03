<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_offer_buy_button_block')) {
function modfarm_render_offer_buy_button_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $label = isset($attributes['label']) && is_string($attributes['label']) && trim($attributes['label']) !== ''
        ? trim($attributes['label'])
        : 'Buy Now';

    $ready = modfarm_store_block_readiness($offer_id);
    $wrapper_attributes = get_block_wrapper_attributes(['class' => 'mfs-offer-buy-button']);

    if (!empty($ready['ready'])) {
        $buy_url = add_query_arg('mf_buy_offer', $offer_id, home_url('/'));
        return '<div ' . $wrapper_attributes . '><a class="mfs-offer-buy-button__link" href="' . esc_url($buy_url) . '">' . esc_html($label) . '</a></div>';
    }

    $reason = !empty($ready['reasons'][0]) ? (string) $ready['reasons'][0] : 'This offer is not available right now.';
    $message = modfarm_store_block_is_editor_context() ? $reason : 'Unavailable';

    return '<div ' . $wrapper_attributes . '><span class="mfs-offer-buy-button__link mfs-offer-buy-button__link--disabled" aria-disabled="true">' . esc_html($message) . '</span></div>';
}
}
