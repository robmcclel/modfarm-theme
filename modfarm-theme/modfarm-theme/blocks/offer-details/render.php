<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_offer_details_block')) {
function modfarm_render_offer_details_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    if ($offer_id <= 0) {
        return modfarm_store_block_is_editor_context() ? '<div class="mfs-offer-details">No Offer selected.</div>' : '';
    }

    $show_type = !isset($attributes['showType']) || $attributes['showType'] !== false;
    $show_delivery = !isset($attributes['showDelivery']) || $attributes['showDelivery'] !== false;
    $show_weight = !isset($attributes['showWeight']) || $attributes['showWeight'] !== false;

    $type = get_post_meta($offer_id, 'mf_offer_type', true) ?: 'digital';
    $mode = get_post_meta($offer_id, 'mf_offer_delivery_mode', true) ?: 'none';
    $weight_g = intval(get_post_meta($offer_id, 'mf_offer_weight_g', true));
    $is_physical = in_array($type, ['print', 'signed', 'merch'], true);

    $rows = [];

    if ($show_type) {
        $rows[] = ['Offer Type', modfarm_store_block_label($type)];
    }

    if ($show_delivery) {
        $rows[] = ['Delivery', modfarm_store_block_label($mode)];
    }

    if ($show_weight && $is_physical && $weight_g > 0) {
        $rows[] = ['Weight', number_format_i18n($weight_g) . ' g'];
    }

    if (modfarm_store_block_is_editor_context() && $mode === 'bookfunnel') {
        $provider_ref = trim((string) get_post_meta($offer_id, 'mf_offer_provider_ref', true));
        $rows[] = ['Provider', $provider_ref !== '' ? 'Configured' : 'Missing provider reference'];
    }

    $wrapper_attributes = get_block_wrapper_attributes(['class' => 'mfs-offer-details']);

    if (empty($rows)) {
        return modfarm_store_block_is_editor_context() ? '<div ' . $wrapper_attributes . '>No details selected or available.</div>' : '';
    }

    ob_start();
    ?>
    <div <?php echo $wrapper_attributes; ?>>
        <dl class="mfs-offer-details__list">
            <?php foreach ($rows as $row) : ?>
                <div class="mfs-offer-details__row">
                    <dt><?php echo esc_html($row[0]); ?></dt>
                    <dd><?php echo esc_html($row[1]); ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </div>
    <?php
    return ob_get_clean();
}
}
