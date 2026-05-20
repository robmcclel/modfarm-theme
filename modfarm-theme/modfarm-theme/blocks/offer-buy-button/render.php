<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_offer_buy_button_block')) {
function modfarm_render_offer_buy_button_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $label = isset($attributes['label']) && is_string($attributes['label']) && trim($attributes['label']) !== ''
        ? trim($attributes['label'])
        : 'Buy Now';
    $alignment = isset($attributes['alignment']) && in_array($attributes['alignment'], ['left', 'center', 'right'], true)
        ? $attributes['alignment']
        : 'left';
    $type = isset($attributes['type']) && in_array($attributes['type'], ['inherit', 'primary', 'secondary'], true)
        ? $attributes['type']
        : 'inherit';
    if ($type === 'inherit') {
        $type = 'primary';
    }
    $display_mode = isset($attributes['displayMode']) && in_array($attributes['displayMode'], ['single', 'sibling_cluster'], true)
        ? $attributes['displayMode']
        : 'single';
    $cluster_heading = isset($attributes['clusterHeading']) && is_string($attributes['clusterHeading'])
        ? $attributes['clusterHeading']
        : 'View All Available Formats & Editions';

    if ($display_mode === 'sibling_cluster') {
        if ($offer_id <= 0 || !class_exists('ModFarm_Store_Format_Render')) {
            if (modfarm_store_block_is_editor_context()) {
                return '<div class="mfs-offer-buy-button mfs-offer-buy-button--empty">No Offer selected or ModFarm Store is not active.</div>';
            }
            return '';
        }

        return ModFarm_Store_Format_Render::group([
            'current_offer_id' => $offer_id,
            'heading' => $cluster_heading,
            'action' => 'checkout',
            'alignment' => $alignment,
            'button_type' => $type,
            'radius_mode' => isset($attributes['radiusMode']) ? (string) $attributes['radiusMode'] : 'inherit',
            'border_radius' => absint($attributes['border_radius'] ?? 0),
            'show_advanced' => !empty($attributes['showAdvanced']),
            'bg_color' => isset($attributes['bg_color']) ? (string) $attributes['bg_color'] : '',
            'text_color' => isset($attributes['text_color']) ? (string) $attributes['text_color'] : '',
            'border_color' => isset($attributes['border_color']) ? (string) $attributes['border_color'] : '',
        ]);
    }

    $classes = [
        'mfs-offer-buy-button',
        'mfs-align-' . $alignment,
    ];

    $button_classes = [
        'mfs-offer-buy-button__link',
        'book-page-button',
        $type === 'secondary' ? 'is-secondary' : 'is-primary',
    ];

    $inline_vars = [];
    if (($attributes['radiusMode'] ?? 'inherit') === 'custom') {
        $inline_vars[] = '--mfb-bp-override-radius:' . max(0, intval($attributes['border_radius'] ?? 0)) . 'px';
    }
    if (!empty($attributes['showAdvanced'])) {
        foreach ([
            'bg_color' => '--mfb-bp-override-bg',
            'text_color' => '--mfb-bp-override-fg',
            'border_color' => '--mfb-bp-override-border',
        ] as $attr => $var) {
            $value = isset($attributes[$attr]) ? trim((string) $attributes[$attr]) : '';
            if ($value !== '') {
                $inline_vars[] = $var . ':' . esc_attr($value);
            }
        }
    }

    $style_attr = !empty($inline_vars) ? ' style="' . esc_attr(implode(';', $inline_vars)) . '"' : '';

    $ready = modfarm_store_block_readiness($offer_id);
    $wrapper_attributes = get_block_wrapper_attributes(['class' => implode(' ', $classes)]);

    if (!empty($ready['ready'])) {
        $buy_url = add_query_arg('mf_buy_offer', $offer_id, home_url('/'));
        if (modfarm_offer_buy_button_supports_discount($offer_id)) {
            $notice = modfarm_offer_buy_button_checkout_notice();
            $form = '<form class="mfs-offer-buy-button__checkout-form" method="get" action="' . esc_url(home_url('/')) . '"' . $style_attr . '>';
            $form .= '<input type="hidden" name="mf_buy_offer" value="' . esc_attr($offer_id) . '">';
            $form .= '<label class="screen-reader-text" for="mfs_discount_' . esc_attr($offer_id) . '">' . esc_html__('Discount code', 'modfarm') . '</label>';
            $form .= '<input id="mfs_discount_' . esc_attr($offer_id) . '" class="mfs-offer-buy-button__discount" name="mf_discount" type="text" value="" placeholder="' . esc_attr__('Discount code', 'modfarm') . '" autocomplete="off">';
            $form .= '<button class="' . esc_attr(implode(' ', $button_classes)) . '" type="submit">' . esc_html($label) . '</button>';
            $form .= '</form>';

            return '<div ' . $wrapper_attributes . '>' . $notice . $form . '</div>';
        }

        return '<div ' . $wrapper_attributes . '>' . modfarm_offer_buy_button_checkout_notice() . '<a class="' . esc_attr(implode(' ', $button_classes)) . '" href="' . esc_url($buy_url) . '"' . $style_attr . '>' . esc_html($label) . '</a></div>';
    }

    $reason = !empty($ready['reasons'][0]) ? (string) $ready['reasons'][0] : 'This offer is not available right now.';
    $message = modfarm_store_block_is_editor_context() ? $reason : 'Unavailable';
    $button_classes[] = 'mfs-offer-buy-button__link--disabled';

    return '<div ' . $wrapper_attributes . '><span class="' . esc_attr(implode(' ', $button_classes)) . '" aria-disabled="true"' . $style_attr . '>' . esc_html($message) . '</span></div>';
}
}

if (!function_exists('modfarm_offer_buy_button_supports_discount')) {
function modfarm_offer_buy_button_supports_discount($offer_id) {
    if (!function_exists('modfarm_store_get_offer')) {
        return false;
    }

    $offer = modfarm_store_get_offer($offer_id);
    return !empty($offer['exists'])
        && empty($offer['is_subscription'])
        && ($offer['checkout_provider'] ?? '') === 'stripe_checkout';
}
}

if (!function_exists('modfarm_offer_buy_button_checkout_notice')) {
function modfarm_offer_buy_button_checkout_notice() {
    if (empty($_GET['mf_store_notice'])) {
        return '';
    }

    $message = sanitize_text_field(wp_unslash($_GET['mf_store_notice']));
    if ($message === '') {
        return '';
    }

    return '<div class="mfs-offer-buy-button__notice" role="alert">' . esc_html($message) . '</div>';
}
}
