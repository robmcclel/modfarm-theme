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

if (!function_exists('modfarm_store_block_get_relationship_context')) {
function modfarm_store_block_get_relationship_context($attributes = [], $block = null): array {
    $post_id = 0;
    $post_type = '';

    if ($block && !empty($block->context['postId'])) {
        $post_id = absint($block->context['postId']);
        $post_type = !empty($block->context['postType']) ? sanitize_key((string) $block->context['postType']) : '';
    }

    if (!$post_id) {
        $post_id = absint(get_the_ID());
    }

    if ($post_id && $post_type === '') {
        $post_type = sanitize_key((string) get_post_type($post_id));
    }

    $type_map = [
        'post' => 'post',
        'page' => 'page',
        'book' => 'book',
        'modfarm_book' => 'book',
        'mf_offer' => 'mf_offer',
    ];

    $relationship_type = $type_map[$post_type] ?? $post_type;
    if ($relationship_type && function_exists('modfarm_is_supported_relationship_object_type') && !modfarm_is_supported_relationship_object_type($relationship_type)) {
        $relationship_type = '';
    }

    return [
        'type' => $relationship_type,
        'id' => $post_id,
        'post_type' => $post_type,
    ];
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

if (!function_exists('modfarm_store_block_get_offer_image')) {
function modfarm_store_block_get_offer_image(int $offer_id, string $size = 'large'): string {
    if ($offer_id <= 0) {
        return '';
    }

    $image = get_the_post_thumbnail_url($offer_id, $size);
    return is_string($image) ? $image : '';
}
}

if (!function_exists('modfarm_store_block_get_offer_excerpt')) {
function modfarm_store_block_get_offer_excerpt(int $offer_id, int $words = 24): string {
    if ($offer_id <= 0) {
        return '';
    }

    $excerpt = get_the_excerpt($offer_id);
    if ($excerpt === '') {
        $post = get_post($offer_id);
        $excerpt = $post ? wp_strip_all_tags(strip_shortcodes((string) $post->post_content)) : '';
    }

    return $excerpt !== '' ? wp_trim_words($excerpt, max(1, $words), '...') : '';
}
}

if (!function_exists('modfarm_store_block_get_offer_details')) {
function modfarm_store_block_get_offer_details(int $offer_id): array {
    if ($offer_id <= 0) {
        return [];
    }

    $type = get_post_meta($offer_id, 'mf_offer_type', true) ?: 'digital';
    $mode = get_post_meta($offer_id, 'mf_offer_delivery_mode', true) ?: 'none';

    $details = [];
    if ($type !== '') {
        $details[] = modfarm_store_block_label($type);
    }
    if ($mode !== '' && $mode !== 'none') {
        $details[] = modfarm_store_block_label($mode);
    }

    return array_values(array_filter(array_unique($details)));
}
}

if (!function_exists('modfarm_store_block_render_offer_card')) {
function modfarm_store_block_render_offer_card(int $offer_id, array $args = []): string {
    if ($offer_id <= 0 || get_post_type($offer_id) !== 'mf_offer') {
        return modfarm_store_block_is_editor_context() ? '<div class="mfs-product-card mfs-product-card--empty">No Offer selected.</div>' : '';
    }

    $args = wp_parse_args($args, [
        'layout' => 'commerce',
        'imageAspect' => '1 / 1',
        'showImage' => true,
        'showTitle' => false,
        'showExcerpt' => false,
        'showPrice' => true,
        'showDetails' => true,
        'showPrimaryButton' => true,
        'showSecondaryButton' => true,
        'excerptWords' => 24,
        'descriptionOverride' => '',
        'detailOverride' => '',
        'primaryButtonLabel' => 'Buy Now',
        'secondaryButtonLabel' => 'Learn More',
        'secondaryButtonLink' => 'permalink',
        'buttonStyleMode' => 'inherit',
        'buttonLayout' => 'joined',
        'buttonCorners' => 'square',
        'primaryButtonBg' => '',
        'primaryButtonFg' => '',
        'primaryButtonBorder' => '',
        'secondaryButtonBg' => '',
        'secondaryButtonFg' => '',
        'secondaryButtonBorder' => '',
        'linkTitle' => true,
    ]);

    $layout = in_array((string) $args['layout'], ['commerce', 'vertical', 'horizontal'], true) ? (string) $args['layout'] : 'commerce';
    $button_layout = in_array((string) $args['buttonLayout'], ['joined', 'gap'], true) ? (string) $args['buttonLayout'] : 'joined';
    $button_corners = in_array((string) $args['buttonCorners'], ['inherit', 'square', 'rounded', 'pill'], true) ? (string) $args['buttonCorners'] : 'square';
    $permalink = get_permalink($offer_id);
    $title = get_the_title($offer_id);
    $image = modfarm_store_block_get_offer_image($offer_id);
    $price = modfarm_store_block_format_price(get_post_meta($offer_id, 'mf_offer_price', true));
    $details = modfarm_store_block_get_offer_details($offer_id);
    if (trim((string) $args['detailOverride']) !== '') {
        $details = [trim((string) $args['detailOverride'])];
    }
    $ready = modfarm_store_block_readiness($offer_id);
    $buy_url = !empty($ready['ready']) ? add_query_arg('mf_buy_offer', $offer_id, home_url('/')) : '';
    $secondary_url = (string) $args['secondaryButtonLink'] === 'checkout' && $buy_url !== '' ? $buy_url : $permalink;
    $excerpt = trim((string) $args['descriptionOverride']);
    if ($excerpt === '') {
        $excerpt = modfarm_store_block_get_offer_excerpt($offer_id, (int) $args['excerptWords']);
    }

    $style_vars = [];
    if ((string) $args['buttonStyleMode'] === 'custom') {
        $map = [
            'primaryButtonBg' => '--mfs-product-primary-bg',
            'primaryButtonFg' => '--mfs-product-primary-fg',
            'primaryButtonBorder' => '--mfs-product-primary-border',
            'secondaryButtonBg' => '--mfs-product-secondary-bg',
            'secondaryButtonFg' => '--mfs-product-secondary-fg',
            'secondaryButtonBorder' => '--mfs-product-secondary-border',
        ];
        foreach ($map as $key => $var) {
            $value = trim((string) $args[$key]);
            if ($value !== '') {
                $style_vars[] = $var . ':' . esc_attr($value);
            }
        }
    }

    if ($button_corners === 'square') {
        $style_vars[] = '--mfs-product-button-radius:0px';
    } elseif ($button_corners === 'rounded') {
        $style_vars[] = '--mfs-product-button-radius:8px';
    } elseif ($button_corners === 'pill') {
        $style_vars[] = '--mfs-product-button-radius:999px';
    }

    $classes = [
        'mfs-product-card',
        'mfs-product-card--' . $layout,
        'mfs-product-card--buttons-' . $button_layout,
        'mfs-product-card--button-count-' . ((int) !empty($args['showPrimaryButton']) + (int) !empty($args['showSecondaryButton'])),
        empty($ready['ready']) ? 'mfs-product-card--unavailable' : '',
    ];

    ob_start();
    ?>
    <article class="<?php echo esc_attr(implode(' ', array_filter($classes))); ?>" data-offer-id="<?php echo esc_attr($offer_id); ?>"<?php echo $style_vars ? ' style="' . esc_attr(implode(';', $style_vars)) . ';"' : ''; ?>>
        <?php if (!empty($args['showImage']) && $image !== '') : ?>
            <a class="mfs-product-card__media" href="<?php echo esc_url($permalink); ?>" style="aspect-ratio: <?php echo esc_attr((string) $args['imageAspect']); ?>;">
                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async" />
            </a>
        <?php endif; ?>

        <div class="mfs-product-card__body">
            <?php if (!empty($args['showPrimaryButton']) || !empty($args['showSecondaryButton'])) : ?>
                <div class="mfs-product-card__actions">
                    <?php if (!empty($args['showPrimaryButton'])) : ?>
                        <?php if ($buy_url !== '') : ?>
                            <a class="mfs-product-card__button mfs-product-card__button--primary" href="<?php echo esc_url($buy_url); ?>">
                                <?php echo esc_html((string) $args['primaryButtonLabel']); ?>
                            </a>
                        <?php elseif (modfarm_store_block_is_editor_context()) : ?>
                            <span class="mfs-product-card__button mfs-product-card__button--primary mfs-product-card__button--disabled" aria-disabled="true">
                                <?php echo esc_html(!empty($ready['reasons'][0]) ? (string) $ready['reasons'][0] : 'Unavailable'); ?>
                            </span>
                        <?php else : ?>
                            <span class="mfs-product-card__button mfs-product-card__button--primary mfs-product-card__button--disabled" aria-disabled="true"><?php esc_html_e('Unavailable', 'modfarm'); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($args['showSecondaryButton'])) : ?>
                        <a class="mfs-product-card__button mfs-product-card__button--secondary" href="<?php echo esc_url($secondary_url); ?>">
                            <?php echo esc_html((string) $args['secondaryButtonLabel']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($args['showPrice']) && $price !== '') : ?>
                <div class="mfs-product-card__price"><?php echo esc_html($price); ?></div>
            <?php endif; ?>

            <?php if (!empty($args['showDetails']) && !empty($details)) : ?>
                <div class="mfs-product-card__details"><?php echo esc_html(implode(' / ', $details)); ?></div>
            <?php endif; ?>

            <?php if (!empty($args['showTitle']) && $title !== '') : ?>
                <h3 class="mfs-product-card__title">
                    <?php if (!empty($args['linkTitle'])) : ?>
                        <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                    <?php else : ?>
                        <?php echo esc_html($title); ?>
                    <?php endif; ?>
                </h3>
            <?php endif; ?>

            <?php if (!empty($args['showExcerpt']) && $excerpt !== '') : ?>
                <div class="mfs-product-card__excerpt"><?php echo wp_kses_post(wpautop($excerpt)); ?></div>
            <?php endif; ?>
        </div>
    </article>
    <?php
    return ob_get_clean();
}
}

if (!function_exists('modfarm_store_block_related_offer_ids')) {
function modfarm_store_block_related_offer_ids(int $offer_id, array $args = []): array {
    $args = wp_parse_args($args, [
        'limit' => 3,
        'taxonomy' => '',
        'manualIds' => [],
        'contextType' => '',
        'contextId' => 0,
        'useCoreRelationships' => true,
    ]);

    $limit = max(1, min(24, (int) $args['limit']));
    $manual_ids = array_values(array_filter(array_map('absint', (array) $args['manualIds'])));
    if (!empty($manual_ids)) {
        $manual_ids = array_values(array_filter($manual_ids, static function ($id) use ($offer_id) {
            return $id !== $offer_id && get_post_type($id) === 'mf_offer' && get_post_status($id) === 'publish';
        }));
        return array_slice($manual_ids, 0, $limit);
    }

    $context_type = sanitize_key((string) $args['contextType']);
    $context_id = absint($args['contextId']);
    if (!empty($args['useCoreRelationships']) && $context_type !== '' && $context_id > 0 && function_exists('modfarm_get_promoted_display_ids')) {
        $core_ids = modfarm_get_promoted_display_ids($context_type, $context_id, 'mf_offer', 'promotes', [
            'limit' => $limit,
        ]);
        $core_ids = array_values(array_filter(array_map('absint', $core_ids), static function ($id) use ($offer_id) {
            return $id !== $offer_id && get_post_type($id) === 'mf_offer' && get_post_status($id) === 'publish';
        }));

        if (!empty($core_ids)) {
            return array_slice($core_ids, 0, $limit);
        }
    }

    $query_args = [
        'post_type' => 'mf_offer',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => $offer_id > 0 ? [$offer_id] : [],
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    ];

    $taxonomy = sanitize_key((string) $args['taxonomy']);
    if ($offer_id > 0 && $taxonomy !== '' && taxonomy_exists($taxonomy)) {
        $terms = wp_get_post_terms($offer_id, $taxonomy, ['fields' => 'ids']);
        if (!is_wp_error($terms) && !empty($terms)) {
            $query_args['tax_query'] = [[
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => array_map('absint', $terms),
            ]];
        }
    }

    $query = new WP_Query($query_args);
    return array_map('intval', wp_list_pluck($query->posts, 'ID'));
}
}
