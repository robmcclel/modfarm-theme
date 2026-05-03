<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_offer_price_block')) {
function modfarm_render_offer_price_block($attributes = [], $content = '', $block = null) {
    $alignment = isset($attributes['alignment']) ? sanitize_key($attributes['alignment']) : 'left';
    if (!in_array($alignment, ['left', 'center', 'right'], true)) {
        $alignment = 'left';
    }

    $size = isset($attributes['size']) ? sanitize_key($attributes['size']) : 'inherit';
    if (!in_array($size, ['inherit', 'small', 'medium', 'large', 'xlarge', 'custom'], true)) {
        $size = 'inherit';
    }

    $weight = isset($attributes['weight']) ? sanitize_key($attributes['weight']) : '700';
    if (!in_array($weight, ['inherit', '400', '600', '700', '800'], true)) {
        $weight = '700';
    }

    $style_vars = [];
    if ($size === 'custom') {
        $custom_size = isset($attributes['customSize']) ? intval($attributes['customSize']) : 0;
        if ($custom_size > 0) {
            $style_vars[] = '--mfs-offer-price-size:' . $custom_size . 'px';
        }
    }
    if ($weight !== 'inherit') {
        $style_vars[] = '--mfs-offer-price-weight:' . $weight;
    }

    $class = trim('mfs-offer-price mfs-align-' . $alignment . ' mfs-offer-price--' . $size);
    $attr_args = ['class' => $class];
    if (!empty($style_vars)) {
        $attr_args['style'] = implode(';', $style_vars) . ';';
    }

    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    if ($offer_id <= 0) {
        $wrapper_attributes = get_block_wrapper_attributes($attr_args);
        return modfarm_store_block_is_editor_context() ? '<div ' . $wrapper_attributes . '>No Offer selected.</div>' : '';
    }

    $price = get_post_meta($offer_id, 'mf_offer_price', true);
    $formatted = modfarm_store_block_format_price($price);

    if ($formatted === '') {
        $attr_args['class'] .= ' mfs-offer-price--empty';
        $wrapper_attributes = get_block_wrapper_attributes($attr_args);
        return modfarm_store_block_is_editor_context() ? '<div ' . $wrapper_attributes . '>No price set.</div>' : '';
    }

    $wrapper_attributes = get_block_wrapper_attributes($attr_args);

    return '<div ' . $wrapper_attributes . '>' . esc_html($formatted) . '</div>';
}
}
