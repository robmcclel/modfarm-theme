<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_render_related_products_block')) {
function modfarm_render_related_products_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $context = modfarm_store_block_get_relationship_context($attributes, $block);
    $limit = max(1, min(24, (int) ($attributes['productsPerPage'] ?? 3)));
    $columns = max(1, min(6, (int) ($attributes['columns'] ?? 3)));
    $ids = modfarm_store_block_related_offer_ids($offer_id, [
        'limit' => $limit,
        'taxonomy' => $attributes['taxonomy'] ?? '',
        'manualIds' => $attributes['manualIds'] ?? [],
        'contextType' => $context['type'] ?? '',
        'contextId' => $context['id'] ?? 0,
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
                        'layout' => $attributes['cardLayout'] ?? 'commerce',
                        'imageAspect' => $attributes['imageAspect'] ?? '1 / 1',
                        'showTitle' => !empty($attributes['showTitle']),
                        'showExcerpt' => !empty($attributes['showExcerpt']),
                        'showDetails' => !isset($attributes['showDetails']) || $attributes['showDetails'] !== false,
                        'showPrimaryButton' => !isset($attributes['showPrimaryButton']) || $attributes['showPrimaryButton'] !== false,
                        'showSecondaryButton' => !isset($attributes['showSecondaryButton']) || $attributes['showSecondaryButton'] !== false,
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
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
}
