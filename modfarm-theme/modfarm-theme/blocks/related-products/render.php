<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_related_products_block')) {
function modfarm_render_related_products_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $limit = max(1, min(24, (int) ($attributes['productsPerPage'] ?? 3)));
    $columns = max(1, min(6, (int) ($attributes['columns'] ?? 3)));
    $ids = modfarm_store_block_related_offer_ids($offer_id, [
        'limit' => $limit,
        'taxonomy' => $attributes['taxonomy'] ?? '',
        'manualIds' => $attributes['manualIds'] ?? [],
    ]);

    if (empty($ids)) {
        return modfarm_store_block_is_editor_context() ? '<div class="mfs-related-products">No related products found.</div>' : '';
    }

    $wrapper_attributes = get_block_wrapper_attributes([
        'class' => 'mfs-related-products mfs-related-products--cols-' . $columns,
        'style' => '--mfs-related-products-cols:' . $columns . ';',
    ]);

    ob_start();
    ?>
    <section <?php echo $wrapper_attributes; ?>>
        <?php if (!isset($attributes['showHeading']) || $attributes['showHeading'] !== false) : ?>
            <h2 class="mfs-related-products__heading"><?php echo esc_html($attributes['heading'] ?? 'Related Products'); ?></h2>
        <?php endif; ?>

        <div class="mfs-related-products__grid">
            <?php foreach ($ids as $related_id) : ?>
                <div class="mfs-related-products__item">
                    <?php
                    echo modfarm_store_block_render_offer_card((int) $related_id, [
                        'layout' => $attributes['cardLayout'] ?? 'vertical',
                        'showExcerpt' => !isset($attributes['showExcerpt']) || $attributes['showExcerpt'] !== false,
                        'showDetails' => !isset($attributes['showDetails']) || $attributes['showDetails'] !== false,
                        'buttonLabel' => $attributes['buttonLabel'] ?? 'Buy Now',
                    ]);
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
}
