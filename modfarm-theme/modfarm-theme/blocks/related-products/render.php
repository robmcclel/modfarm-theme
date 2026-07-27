<?php
require_once get_template_directory() . '/blocks/shared/offer-blocks.php';

if (!function_exists('modfarm_related_products_offer_ids')) {
function modfarm_related_products_offer_ids(int $current_offer_id, array $args = []): array {
    $args = wp_parse_args($args, [
        'limit' => 3,
        'taxonomy' => '',
        'manualIds' => [],
        'contextType' => '',
        'contextId' => 0,
    ]);
    $limit = max(1, min(24, (int) $args['limit']));

    // 1. An explicit manual list always wins.
    $manual_ids = array_values(array_filter(array_map('absint', (array) $args['manualIds']), static function ($id) use ($current_offer_id) {
        return $id !== $current_offer_id && get_post_type($id) === 'mf_offer' && get_post_status($id) === 'publish';
    }));
    if (!empty($manual_ids)) {
        return array_slice($manual_ids, 0, $limit);
    }

    // 2. Resolve Offers promoted for this exact Core context (plus only those
    // family relationships that Core explicitly marks with family scope).
    $context_type = sanitize_key((string) $args['contextType']);
    $context_id = absint($args['contextId']);
    if ($context_type !== '' && $context_id > 0 && function_exists('modfarm_get_promoted_display_ids')) {
        $promoted_ids = modfarm_get_promoted_display_ids($context_type, $context_id, 'mf_offer', 'promotes', [
            'limit' => $limit,
        ]);
        $promoted_ids = array_values(array_filter(array_map('absint', $promoted_ids), static function ($id) use ($current_offer_id) {
            return $id !== $current_offer_id && get_post_type($id) === 'mf_offer' && get_post_status($id) === 'publish';
        }));
        if (!empty($promoted_ids)) {
            return array_slice($promoted_ids, 0, $limit);
        }
    }

    // 3. No fallback really means no products.
    $taxonomy = sanitize_key((string) $args['taxonomy']);
    if ($taxonomy === '') {
        return [];
    }

    $query_args = [
        'post_type' => 'mf_offer',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => $current_offer_id > 0 ? [$current_offer_id] : [],
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ];

    // "Any published Offers" is the only fallback that intentionally queries all.
    if ($taxonomy !== '__all__') {
        if ($current_offer_id <= 0 || !taxonomy_exists($taxonomy)) {
            return [];
        }
        $terms = wp_get_post_terms($current_offer_id, $taxonomy, ['fields' => 'ids']);
        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }
        $query_args['tax_query'] = [[
            'taxonomy' => $taxonomy,
            'field' => 'term_id',
            'terms' => array_map('absint', $terms),
        ]];
    }

    $query = new WP_Query($query_args);
    return array_map('intval', wp_list_pluck($query->posts, 'ID'));
}
}

if (!function_exists('modfarm_render_related_products_block')) {
function modfarm_render_related_products_block($attributes = [], $content = '', $block = null) {
    $offer_id = modfarm_store_block_get_offer_id($attributes, $block);
    $context = modfarm_store_block_get_relationship_context($attributes, $block);
    $limit = max(1, min(24, (int) ($attributes['productsPerPage'] ?? 3)));
    $columns = max(1, min(6, (int) ($attributes['columns'] ?? 3)));
    $display_layout = in_array(($attributes['displayLayout'] ?? 'grid'), ['grid', 'horizontal'], true)
        ? $attributes['displayLayout']
        : 'grid';
    $ids = modfarm_related_products_offer_ids($offer_id, [
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
        'class' => 'mfs-related-products mfs-related-products--cols-' . $columns . ' mfs-related-products--' . $display_layout,
        'style' => '--mfs-related-products-cols:' . $columns . ';',
    ]);

    ob_start();
    static $scroll_count = 0;
    $scroll_count++;
    $scroll_id = 'mfs-related-products-scroll-' . $scroll_count;
    ?>
    <section <?php echo $wrapper_attributes; ?><?php echo $display_layout === 'horizontal' ? ' data-mf-card-scroll-wrap' : ''; ?>>
        <?php if (!isset($attributes['showHeading']) || $attributes['showHeading'] !== false) : ?>
            <h2 class="mfs-related-products__heading"><?php echo esc_html($attributes['heading'] ?? 'Related Products'); ?></h2>
        <?php endif; ?>

        <?php if ($display_layout === 'horizontal') : ?>
            <div class="mfb-scroll-head mfs-related-products__scroll-head">
                <div class="mfb-scroll-controls" aria-label="<?php esc_attr_e('Product carousel controls', 'modfarm'); ?>">
                    <button type="button" class="mfb-scroll-control mfb-scroll-control--prev" data-mf-card-scroll-target="<?php echo esc_attr($scroll_id); ?>" data-mf-card-scroll-direction="-1" aria-label="<?php esc_attr_e('Previous products', 'modfarm'); ?>"><span aria-hidden="true">&larr;</span></button>
                    <button type="button" class="mfb-scroll-control mfb-scroll-control--next" data-mf-card-scroll-target="<?php echo esc_attr($scroll_id); ?>" data-mf-card-scroll-direction="1" aria-label="<?php esc_attr_e('Next products', 'modfarm'); ?>"><span aria-hidden="true">&rarr;</span></button>
                </div>
            </div>
        <?php endif; ?>

        <div id="<?php echo esc_attr($scroll_id); ?>" class="mfs-related-products__grid"<?php echo $display_layout === 'horizontal' ? ' data-mf-card-scroll-rail' : ''; ?>>
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
