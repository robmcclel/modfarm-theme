<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_theme_product_card_auto_description')) {
function modfarm_theme_product_card_auto_description(int $offer_id): string {
    $post = get_post($offer_id);
    if (!$post instanceof WP_Post || trim((string) $post->post_content) === '') {
        return '';
    }

    $find_slot = static function (array $blocks) use (&$find_slot): string {
        foreach ($blocks as $parsed_block) {
            if (!is_array($parsed_block)) {
                continue;
            }

            $inner_blocks = is_array($parsed_block['innerBlocks'] ?? null) ? $parsed_block['innerBlocks'] : [];
            if (($parsed_block['blockName'] ?? '') === 'modfarm/content-slot' && !empty($inner_blocks)) {
                return trim(do_blocks(serialize_blocks($inner_blocks)));
            }

            if (!empty($inner_blocks)) {
                $description = $find_slot($inner_blocks);
                if ($description !== '') {
                    return $description;
                }
            }
        }

        return '';
    };

    $description = $find_slot(parse_blocks((string) $post->post_content));
    if ($description !== '') {
        return $description;
    }

    $manual_excerpt = trim((string) $post->post_excerpt);
    return $manual_excerpt !== '' ? $manual_excerpt : trim((string) $post->post_content);
}
}

if (!function_exists('modfarm_render_theme_product_card_block')) {
function modfarm_render_theme_product_card_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $wrapper_attributes = get_block_wrapper_attributes(['class' => 'mfs-theme-product-card']);
    $description = ($attributes['descriptionMode'] ?? 'auto') === 'custom'
        ? (string) ($attributes['descriptionOverride'] ?? '')
        : modfarm_theme_product_card_auto_description($offer_id);

    $card = modfarm_store_block_render_offer_card($offer_id, [
        'layout' => 'commerce',
        'imageAspect' => 'auto',
        'showImage' => !isset($attributes['showImage']) || $attributes['showImage'] !== false,
        'showTitle' => !empty($attributes['showTitle']),
        'showExcerpt' => !empty($attributes['showExcerpt']),
        'showPrice' => !isset($attributes['showPrice']) || $attributes['showPrice'] !== false,
        'showDetails' => !isset($attributes['showDetails']) || $attributes['showDetails'] !== false,
        'showPrimaryButton' => !isset($attributes['showPrimaryButton']) || $attributes['showPrimaryButton'] !== false,
        'showSecondaryButton' => !isset($attributes['showSecondaryButton']) || $attributes['showSecondaryButton'] !== false,
        'descriptionOverride' => $description,
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
