<?php
if ( ! defined('ABSPATH') ) exit;

function modfarm_render_generic_cards_block( $attributes ) {

  $items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : [];

  $books_per_row = $attributes['books-in-row'] ?? '25%';
  $display_layout = in_array(($attributes['display-layout'] ?? 'grid'), ['grid', 'horizontal'], true)
    ? $attributes['display-layout']
    : 'grid';
  $horizontal_cols = max(3, min(5, (int)($attributes['horizontal-columns'] ?? 4)));
  $horizontal_width = 'calc(' . round(100 / $horizontal_cols, 6) . '% - ' . round(10 * ($horizontal_cols - 1) / $horizontal_cols, 4) . 'px)';
  $show_title    = (($attributes['show-title']  ?? 'none') === 'block');
  $show_series   = (($attributes['show-series'] ?? 'none') === 'block');
  $show_button   = (($attributes['show-button'] ?? 'block') === 'block');

  $button_text   = $attributes['button-text']   ?? 'See The Book';
  $button_target = $attributes['button-target'] ?? '_self';

  $use_global    = !empty($attributes['use-global-style']);

  $btn_bg        = $attributes['buttonbg-color'] ?? '';
  $btn_fg        = $attributes['buttontx-color'] ?? '';

  $effect        = $attributes['effect'] ?? 'flat';
  $cover_shape   = $attributes['cover-shape'] ?? 'square';
  $button_shape  = $attributes['button-shape'] ?? 'square';
  $cta_join      = $attributes['cta-join'] ?? 'joined';

  $tracker_loc   = $attributes['tracker-loc'] ?? '';

  // Convert "25%" style to columns count
  $pct = floatval(str_replace('%','',$books_per_row));
  $cols = 4;
  if ($pct >= 99) $cols = 1;
  else if ($pct >= 49) $cols = 2;
  else if ($pct >= 32) $cols = 3;
  else $cols = 4;

  $wrap_classes = [
    'mfb-wrapper',
    'mfb-effect--' . sanitize_html_class($effect),
    'mfb-cover--' . sanitize_html_class($cover_shape),
    'mfb-button--' . sanitize_html_class($button_shape),
    'mfb-cta--' . sanitize_html_class($cta_join),
    'mfb-wrapper--' . sanitize_html_class($display_layout),
  ];

  // Only output local style vars when NOT using global styling
  $style_vars = '';
  if (!$use_global) {
    if (!empty($btn_bg)) $style_vars .= '--mfb-btn-bg:' . esc_attr($btn_bg) . ';';
    if (!empty($btn_fg)) $style_vars .= '--mfb-btn-fg:' . esc_attr($btn_fg) . ';';
  }

  if (empty($items)) {
    return '<div class="' . esc_attr(implode(' ', $wrap_classes)) . '"><p style="opacity:.75">No cards added yet.</p></div>';
  }

  ob_start();
  static $scroll_count = 0;
  $scroll_count++;
  $scroll_id = 'mfb-generic-cards-scroll-' . $scroll_count;
  ?>
  <div class="<?php echo esc_attr(implode(' ', $wrap_classes)); ?>"<?php echo $style_vars ? ' style="' . esc_attr($style_vars) . '"' : ''; ?><?php echo $display_layout === 'horizontal' ? ' data-mf-card-scroll-wrap' : ''; ?>>
    <?php if ($display_layout === 'horizontal'): ?>
      <div class="mfb-scroll-head">
        <div class="mfb-scroll-controls" aria-label="<?php esc_attr_e('Book carousel controls', 'modfarm'); ?>">
          <button type="button" class="mfb-scroll-control mfb-scroll-control--prev" data-mf-card-scroll-target="<?php echo esc_attr($scroll_id); ?>" data-mf-card-scroll-direction="-1" aria-label="<?php esc_attr_e('Previous books', 'modfarm'); ?>"><span aria-hidden="true">&larr;</span></button>
          <button type="button" class="mfb-scroll-control mfb-scroll-control--next" data-mf-card-scroll-target="<?php echo esc_attr($scroll_id); ?>" data-mf-card-scroll-direction="1" aria-label="<?php esc_attr_e('Next books', 'modfarm'); ?>"><span aria-hidden="true">&rarr;</span></button>
        </div>
      </div>
    <?php endif; ?>
    <div id="<?php echo esc_attr($scroll_id); ?>" class="mfb-grid<?php echo $display_layout === 'horizontal' ? ' mfb-grid--horizontal' : ''; ?>" style="--mfb-cols:<?php echo (int)$cols; ?>;--mfb-scroll-cols:<?php echo (int)$horizontal_cols; ?>;--mfb-scroll-card-width:<?php echo esc_attr($horizontal_width); ?>;"<?php echo $display_layout === 'horizontal' ? ' data-mf-card-scroll-rail' : ''; ?>>
      <?php foreach ($items as $item):

        $image_id  = isset($item['imageId']) ? (int)$item['imageId'] : 0;
        $image_url = isset($item['imageUrl']) ? trim((string)$item['imageUrl']) : '';
        $title     = isset($item['title']) ? (string)$item['title'] : '';
        $series    = isset($item['series']) ? (string)$item['series'] : '';
        $url       = isset($item['url']) ? trim((string)$item['url']) : '';
        $per_btn   = isset($item['buttonText']) ? trim((string)$item['buttonText']) : '';

        if ($image_id && empty($image_url)) {
          $resolved = wp_get_attachment_image_url($image_id, 'large');
          if ($resolved) $image_url = $resolved;
        }

        if (empty($image_url) && empty($title) && empty($url)) continue;

        $final_btn_text = !empty($per_btn) ? $per_btn : $button_text;

        $track_attrs = '';
        if (!empty($tracker_loc)) {
          $track_attrs .= ' data-event_category="book"';
          $track_attrs .= ' data-event_action="click"';
          $track_attrs .= ' data-event_origin="' . esc_attr($tracker_loc) . '"';
          if (!empty($title)) {
            $track_attrs .= ' data-event_label="' . esc_attr(sanitize_title($title)) . '"';
          }
        }

      ?>
        <div class="mfb-item">
          <div class="mfb-card">
            <div class="mfb-media">
              <div class="mfb-image">
                <?php if (!empty($url)): ?>
                  <a href="<?php echo esc_url($url); ?>" target="<?php echo esc_attr($button_target); ?>" rel="<?php echo ($button_target === '_blank') ? 'noopener noreferrer' : 'nofollow'; ?>"<?php echo $track_attrs; ?>>
                    <?php if (!empty($image_url)): ?>
                      <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title ?: ''); ?>" loading="lazy" />
                    <?php else: ?>
                      <div style="aspect-ratio:2/3;background:#eee;"></div>
                    <?php endif; ?>
                  </a>
                <?php else: ?>
                  <?php if (!empty($image_url)): ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title ?: ''); ?>" loading="lazy" />
                  <?php else: ?>
                    <div style="aspect-ratio:2/3;background:#eee;"></div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>

              <?php if ($show_button && !empty($url)): ?>
                <a class="mfb-button" href="<?php echo esc_url($url); ?>" target="<?php echo esc_attr($button_target); ?>" rel="<?php echo ($button_target === '_blank') ? 'noopener noreferrer' : 'nofollow'; ?>"<?php echo $track_attrs; ?>>
                  <?php echo esc_html($final_btn_text); ?>
                </a>
              <?php endif; ?>
            </div>

            <?php if ($show_title && !empty($title)): ?>
              <span class="mfb-title"><?php echo esc_html($title); ?></span>
            <?php endif; ?>

            <?php if ($show_series && !empty($series)): ?>
              <span class="mfb-series"><?php echo esc_html($series); ?></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php

  return ob_get_clean();
}
