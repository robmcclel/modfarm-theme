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
        return '<div ' . $wrapper_attributes . '><a class="' . esc_attr(implode(' ', $button_classes)) . '" href="' . esc_url($buy_url) . '"' . $style_attr . '>' . esc_html($label) . '</a></div>';
    }

    $reason = !empty($ready['reasons'][0]) ? (string) $ready['reasons'][0] : 'This offer is not available right now.';
    $message = modfarm_store_block_is_editor_context() ? $reason : 'Unavailable';
    $button_classes[] = 'mfs-offer-buy-button__link--disabled';

    return '<div ' . $wrapper_attributes . '><span class="' . esc_attr(implode(' ', $button_classes)) . '" aria-disabled="true"' . $style_attr . '>' . esc_html($message) . '</span></div>';
}
}
