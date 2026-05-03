<?php
defined('ABSPATH') || exit;

if (!function_exists('modfarm_store_block_get_offer_id')) {
function modfarm_store_block_get_offer_id($attributes = [], $block = null): int {
    $offer_id = isset($attributes['offerId']) ? absint($attributes['offerId']) : 0;
    if ($offer_id > 0 && get_post_type($offer_id) === 'mf_offer') {
        return $offer_id;
    }

    if ($block && !empty($block->context['postId'])) {
        $context_id = absint($block->context['postId']);
        if ($context_id > 0 && get_post_type($context_id) === 'mf_offer') {
            return $context_id;
        }
    }

    $current_id = get_the_ID();
    return ($current_id && get_post_type($current_id) === 'mf_offer') ? (int) $current_id : 0;
}
}

if (!function_exists('modfarm_store_block_format_price')) {
function modfarm_store_block_format_price($price): string {
    if ($price === '') {
        return '';
    }

    if (function_exists('wc_price')) {
        return wp_strip_all_tags(wc_price($price));
    }

    return '$' . number_format((float) $price, 2);
}
}

if (!function_exists('modfarm_store_block_readiness')) {
function modfarm_store_block_readiness(int $offer_id): array {
    if ($offer_id <= 0) {
        return [
            'ready' => false,
            'product_id' => 0,
            'reasons' => ['No Offer selected.'],
        ];
    }

    if (class_exists('ModFarm_Store_Offer_Sync')) {
        return ModFarm_Store_Offer_Sync::get_offer_purchase_readiness($offer_id);
    }

    return [
        'ready' => false,
        'product_id' => 0,
        'reasons' => ['ModFarm Store is not active.'],
    ];
}
}

if (!function_exists('modfarm_store_block_is_editor_context')) {
function modfarm_store_block_is_editor_context(): bool {
    return is_admin() || (defined('REST_REQUEST') && REST_REQUEST);
}
}

if (!function_exists('modfarm_store_block_label')) {
function modfarm_store_block_label($value): string {
    $value = is_string($value) ? $value : '';
    if ($value === '') {
        return 'None';
    }

    $labels = [
        'digital' => 'Digital',
        'print' => 'Print',
        'signed' => 'Signed',
        'merch' => 'Merch',
        'subscription' => 'Subscription',
        'none' => 'None',
        'media' => 'Media files',
        'bookfunnel' => 'BookFunnel',
        'woo_downloadable' => 'Woo downloadable',
    ];

    return $labels[$value] ?? ucwords(str_replace('_', ' ', $value));
}
}
