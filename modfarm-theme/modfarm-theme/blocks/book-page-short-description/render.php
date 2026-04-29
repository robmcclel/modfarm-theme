<?php
if (!defined('ABSPATH')) { exit; }

function modfarm_render_book_page_short_description_block($attributes, $content, $block) {

    $post_id = isset($block->context['postId'])
        ? (int) $block->context['postId']
        : (int) get_the_ID();

    if (!$post_id) {
        return '';
    }

    $override = $attributes['overrideText'] ?? '';
    $show_if_empty = !empty($attributes['showIfEmpty']);

    $align_in = $attributes['textAlign'] ?? 'left';
    $text_align = in_array($align_in, ['left','center','right'], true) ? $align_in : 'left';

    $meta_short = get_post_meta($post_id, 'short_description', true);

    $font_size   = isset($attributes['fontSize']) ? (int) $attributes['fontSize'] : 18;
    if ($font_size < 10) { $font_size = 10; }
    if ($font_size > 80) { $font_size = 80; }

    $font_weight = isset($attributes['fontWeight']) ? (string) $attributes['fontWeight'] : '400';
    $allowed_weights = ['300','400','500','600','700','800','900'];
    if (!in_array($font_weight, $allowed_weights, true)) {
        $font_weight = '400';
    }

    $output = $override !== '' ? $override : $meta_short;

    if (!$output && !$show_if_empty) {
        return '';
    }

    $classes = [
        'mfb-short-description',
        'mfb-short-description--align-' . $text_align,
    ];

    ob_start(); ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>"
             style="font-size: <?php echo esc_attr($font_size); ?>px; font-weight: <?php echo esc_attr($font_weight); ?>;">
            <?php echo wpautop(wp_kses_post($output)); ?>
        </div>
    <?php
    return ob_get_clean();
}