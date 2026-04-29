<?php
/**
 * Server-side render for Book Page Series block
 */

function modfarm_render_book_page_series_block($attributes, $content = null) {
    $post_id = get_the_ID();
    if (get_post_type($post_id) !== 'book') {
        return '';
    }
    //return '<div style="border:1px solid green; padding:10px;">🧪 Block rendered. Success.</div>';

    $post_id = get_the_ID();

    $display_mode = $attributes['displayMode'] ?? 'auto';
    $volume_label = trim($attributes['volumeLabel'] ?? 'Book');
    $custom_label = trim($attributes['customLabel'] ?? '');
    $alignment = $attributes['alignment'] ?? 'left';
    $font_size = intval($attributes['fontSize'] ?? 16);
    $text_color = esc_attr($attributes['textColor'] ?? '#222222');

    ob_start();

    // CASE: Display Mode 'none'
    if ($display_mode === 'none') {
        return '';
    }

    // CASE: Custom label
    if ($display_mode === 'custom') {
        if ($custom_label !== '') {
            ?>
            <div class="book-series" style="text-align:<?php echo esc_attr($alignment); ?>; font-size:<?php echo esc_attr($font_size); ?>px; color:<?php echo $text_color; ?>;">
                <?php echo esc_html($custom_label); ?>
            </div>
            <?php
        }
        return ob_get_clean();
    }

    // CASE: Auto – use taxonomy + meta
    $terms = get_the_terms($post_id, 'book-series');
    $series_name = (!empty($terms) && !is_wp_error($terms)) ? $terms[0]->name : '';
    $position = get_post_meta($post_id, 'series_position', true);
    
    if (!$series_name) {
        return '';
    }
    
    if ($position) {
        $output = '<strong>' . esc_html($series_name) . '</strong> ';
        $output .= esc_html($volume_label) . ' ';
        $output .= '<strong>' . esc_html($position) . '</strong>';
    } else {
        $output = '<strong>' . esc_html($series_name) . '</strong>';
    }
    
    ?>
    <div class="book-series" style="text-align:<?php echo esc_attr($alignment); ?>; font-size:<?php echo esc_attr($font_size); ?>px; color:<?php echo $text_color; ?>;">
        <?php echo $output; ?>
    </div>
    <?php
    
    return ob_get_clean();
}