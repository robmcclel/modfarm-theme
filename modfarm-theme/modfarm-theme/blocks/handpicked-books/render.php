<?php
if ( ! defined('ABSPATH') ) exit;

function modfarm_render_handpicked_books_block( $attributes ) {

  $books = isset($attributes['books']) && is_array($attributes['books']) ? $attributes['books'] : [];
  $books = array_values(array_filter(array_map('intval', $books)));

  if (empty($books)) {
    return '<div class="mfb-wrapper"><p style="text-align:center;opacity:.7">No books selected.</p></div>';
  }

  $books_per_row   = $attributes['books-in-row'] ?? '25%';
  $display_layout  = in_array(($attributes['display-layout'] ?? 'grid'), ['grid', 'horizontal'], true)
    ? $attributes['display-layout']
    : 'grid';
  $horizontal_cols = max(3, min(5, (int)($attributes['horizontal-columns'] ?? 4)));
  $horizontal_width = 'calc(' . round(100 / $horizontal_cols, 6) . '% - ' . round(10 * ($horizontal_cols - 1) / $horizontal_cols, 4) . 'px)';
  $books_per_page  = max(1, min(50, (int)($attributes['books-per-page'] ?? 12)));
  $show_pagination = $display_layout === 'horizontal' ? false : !empty($attributes['show-pagination']);

  $image_type      = $attributes['image-type'] ?? 'featured';

  $btn_text   = $attributes['button-text']   ?? __('See The Book', 'modfarm');

  // IMPORTANT:
  // - Older UI: button-link = 'meta' + button-meta-key = 'kindle_url'
  // - Newer UI: button-link may directly be 'kindle_url' or 'kindle' etc
  $btn_link   = $attributes['button-link']   ?? 'bookpage';
  $btn_target = $attributes['button-target'] ?? '_self';
  $btn_meta   = $attributes['button-meta-key'] ?? '';

  $tracker    = $attributes['tracker-loc']   ?? '';
  $volume_txt = $attributes['volume-text']   ?? 'Book';

  $audio_mode = $attributes['audio-mode']    ?? 'auto';

  // Global/local card settings compatibility with existing ModFarm system
  $opts            = get_option('modfarm_theme_settings', []);
  $card_use_global = array_key_exists('cardUseGlobal', $attributes) ? (bool)$attributes['cardUseGlobal'] : true;

  $pick_token = function($local, $allowed, $global, $default) {
    $local  = (string)$local;
    $global = (string)$global;
    if ($local && $local !== 'inherit' && in_array($local, $allowed, true)) return $local;
    if ($global && in_array($global, $allowed, true)) return $global;
    return $default;
  };

  $cover_shape  = $pick_token(
    $card_use_global ? 'inherit' : ($attributes['cardCoverShape'] ?? 'inherit'),
    ['square','rounded'],
    $opts['book_card_cover_shape'] ?? '',
    'square'
  );

  $button_shape = $pick_token(
    $card_use_global ? 'inherit' : ($attributes['cardButtonShape'] ?? 'inherit'),
    ['square','rounded','pill'],
    $opts['book_card_button_shape'] ?? '',
    'square'
  );

  $sample_shape = $pick_token(
    $card_use_global ? 'inherit' : ($attributes['cardSampleShape'] ?? 'inherit'),
    ['square','rounded','pill'],
    $opts['book_card_sample_shape'] ?? '',
    'square'
  );

  $cta_mode = $pick_token(
    $card_use_global ? 'inherit' : ($attributes['cardCtaMode'] ?? 'inherit'),
    ['joined','gap'],
    $opts['book_card_cta_mode'] ?? '',
    'joined'
  );

  $effect = $pick_token(
    $card_use_global ? 'inherit' : ($attributes['cardShadowStyle'] ?? 'inherit'),
    ['flat','shadow-sm','shadow-md','shadow-lg','emboss'],
    $opts['book_card_shadow_style'] ?? '',
    'flat'
  );

  // Visibility
  if ($card_use_global) {
    $show_title       = true;
    $show_series      = true;
    $show_primary_btn = true;
    $show_sample_btn  = true;
  } else {
    $show_title       = !empty($attributes['cardShowTitle']);
    $show_series      = !empty($attributes['cardShowSeries']);
    $show_primary_btn = !empty($attributes['cardShowPrimaryButton']);
    $show_sample_btn  = !empty($attributes['cardShowSampleButton']);
  }

  // Card extras
  $show_author   = !empty($attributes['showAuthor']);
  $show_pub_date = !empty($attributes['showPubDate']);
  $pub_date_key  = isset($attributes['pubDateKey']) ? (string)$attributes['pubDateKey'] : 'publication_date';

  // New: short_description toggle
  $show_short_desc = !empty($attributes['showShortDescription']);
  if (!$show_short_desc && !empty($attributes['showBlurb'])) $show_short_desc = true;

  $mf_format_date = function($raw) {
    $raw = (string)$raw;
    if ($raw === '') return '';
    $ts = strtotime($raw);
    if (!$ts) return '';
    return date_i18n(get_option('date_format'), $ts);
  };

  // Grid columns from percent
  $pct  = floatval(str_replace('%','',(string)$books_per_row));
  $cols = ($pct > 0) ? max(1, (int)round(100 / $pct)) : 4;

  $wrapper_classes = [
    'mfb-wrapper',
    'is-handpicked',
    'mfb-effect--' . sanitize_html_class($effect),
    'mfb-cover--'  . sanitize_html_class($cover_shape),
    'mfb-button--' . sanitize_html_class($button_shape),
    'mfb-sample--' . sanitize_html_class($sample_shape),
    'mfb-cta--'    . sanitize_html_class($cta_mode),
    'mfb-wrapper--' . sanitize_html_class($display_layout),
  ];

  $custom_vars = [];
  if (!empty($attributes['cardButtonBg'])) {
    $custom_vars[] = '--mfb-btn-bg:' . $attributes['cardButtonBg'];
    $custom_vars[] = '--mfb-btn-border:' . $attributes['cardButtonBg'];
  }
  if (!empty($attributes['cardButtonFg'])) {
    $custom_vars[] = '--mfb-btn-fg:' . $attributes['cardButtonFg'];
  }
  if (!empty($attributes['cardSampleBg'])) {
    $custom_vars[] = '--mfb-sample-bg:' . $attributes['cardSampleBg'];
    $custom_vars[] = '--mfb-sample-border:' . $attributes['cardSampleBg'];
  }
  if (!empty($attributes['cardSampleFg'])) {
    $custom_vars[] = '--mfb-sample-fg:' . $attributes['cardSampleFg'];
  }

  $wrapper_style = '--mfb-cols:' . (int)$cols . ';--mfb-scroll-cols:' . (int)$horizontal_cols . ';--mfb-scroll-card-width:' . $horizontal_width . ';' . implode(';', $custom_vars) . ';';

  // Pagination
  $paged = max(1, (int)(get_query_var('paged') ?: get_query_var('page') ?: 1));

  $q_args = [
    'post_type'           => 'book',
    'post_status'         => 'publish',
    'post__in'            => $books,
    'orderby'             => 'post__in',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => $show_pagination ? false : true,
  ];

  if ($show_pagination) {
    $q_args['posts_per_page'] = $books_per_page;
    $q_args['paged'] = $paged;
  } else {
    $q_args['posts_per_page'] = $books_per_page;
    $q_args['paged'] = 1;
    $q_args['no_found_rows'] = true;
  }

  // Normalize button link selection into a meta key (or 'permalink')
  $normalize_btn_meta_key = function($btn_link, $btn_meta) {
    $btn_link = (string)$btn_link;
    $btn_meta = (string)$btn_meta;

    if ($btn_link === '' || $btn_link === 'bookpage' || $btn_link === 'permalink') {
      return 'permalink';
    }

    // Old mode: explicit 'meta' + separate key
    if ($btn_link === 'meta') {
      return $btn_meta !== '' ? $btn_meta : 'permalink';
    }

    // New mode: btn_link directly is the key or alias
    $alias = [
      'kindle'    => 'kindle_url',
      'amazon'    => 'kindle_url',
      'paperback' => 'amazon_paper',
      'hardcover' => 'amazon_hard',
      'audible'   => 'audible_url',
      'apple'     => 'ibooks',
      'bn'        => 'nook',
    ];

    return isset($alias[$btn_link]) ? $alias[$btn_link] : $btn_link;
  };

  $q = new WP_Query($q_args);

  ob_start();

  static $scroll_count = 0;
  $scroll_count++;
  $scroll_id = 'mfb-handpicked-books-scroll-' . $scroll_count;

  echo '<div class="' . esc_attr(implode(' ', array_filter($wrapper_classes))) . '"' . ($display_layout === 'horizontal' ? ' data-mf-card-scroll-wrap' : '') . '>';
  if ($display_layout === 'horizontal') {
    echo '<div class="mfb-scroll-head"><div class="mfb-scroll-controls" aria-label="' . esc_attr__('Book carousel controls', 'modfarm') . '">';
    echo '<button type="button" class="mfb-scroll-control mfb-scroll-control--prev" data-mf-card-scroll-target="' . esc_attr($scroll_id) . '" data-mf-card-scroll-direction="-1" aria-label="' . esc_attr__('Previous books', 'modfarm') . '"><span aria-hidden="true">&larr;</span></button>';
    echo '<button type="button" class="mfb-scroll-control mfb-scroll-control--next" data-mf-card-scroll-target="' . esc_attr($scroll_id) . '" data-mf-card-scroll-direction="1" aria-label="' . esc_attr__('Next books', 'modfarm') . '"><span aria-hidden="true">&rarr;</span></button>';
    echo '</div></div>';
  }
  echo '<div id="' . esc_attr($scroll_id) . '" class="mfb-grid' . ($display_layout === 'horizontal' ? ' mfb-grid--horizontal' : '') . '" style="' . esc_attr($wrapper_style) . '"' . ($display_layout === 'horizontal' ? ' data-mf-card-scroll-rail' : '') . '>';

  if ($q->have_posts()) {
    while ($q->have_posts()) {
      $q->the_post();
      $book_id = get_the_ID();

      $title     = get_the_title($book_id);
      $permalink = get_permalink($book_id);

      // Determine desired destination meta key
      $btn_meta_key = $normalize_btn_meta_key($btn_link, $btn_meta);

      // Resolve destination URL
      $button_url = $permalink;
      if ($show_primary_btn && $btn_meta_key !== 'permalink') {
        $maybe = (string)get_post_meta($book_id, $btn_meta_key, true);
        if ($maybe !== '') {
          $button_url = $maybe;
        } else {
          // fallback: if selected key missing, stay internal
          $btn_meta_key = 'permalink';
          $button_url   = $permalink;
        }
      }

      // If destination is external, prefer _blank unless user explicitly set something else
      $resolved_target = $btn_target;
      if ($btn_meta_key !== 'permalink' && ($resolved_target === '' || $resolved_target === '_self')) {
        $resolved_target = '_blank';
      }

      // Image URL
      $img_url = '';
      if ($image_type === 'featured') {
        $img_url = get_the_post_thumbnail_url($book_id, 'full') ?: '';
      } else {
        $val = get_post_meta($book_id, $image_type, true);
        if ($val) {
          $img_url = is_numeric($val) ? (wp_get_attachment_image_url((int)$val, 'full') ?: '') : (string)$val;
        }
      }
      if (!$img_url) $img_url = get_the_post_thumbnail_url($book_id, 'full') ?: '';

      // Aspect hint
      $aspect = '2 / 3';
      switch ($image_type) {
        case 'cover_image_audio': $aspect = '1 / 1'; break;
        case 'cover_image_3d': $aspect = '4 / 3'; break;
        case 'cover_image_composite':
        case 'hero_image': $aspect = '16 / 9'; break;
      }

      // Series
      $series_name = '';
      $series_pos  = '';
      if ($show_series) {
        $series_terms = get_the_terms($book_id, 'book-series');
        if (!empty($series_terms) && !is_wp_error($series_terms)) $series_name = (string)$series_terms[0]->name;
        $series_pos = (string)get_post_meta($book_id, 'series_position', true);
      }

      // Author
      $author_name = '';
      if ($show_author) {
        $author_terms = get_the_terms($book_id, 'book-author');
        if (!empty($author_terms) && !is_wp_error($author_terms)) {
          $author_name = implode(', ', array_map(function($t){ return $t->name; }, $author_terms));
        }
      }

      // Pub date
      $pub_date_label = '';
      if ($show_pub_date) {
        $raw = get_post_meta($book_id, $pub_date_key, true);
        $pub_date_label = $mf_format_date($raw);
      }

      // Short description from BMS meta
      $short_desc = '';
      if ($show_short_desc) {
        $short_desc = (string)get_post_meta($book_id, 'short_description', true);
      }

      $asin = (string)get_post_meta($book_id, 'asin_kindle', true);

      $card = [
        'id'        => $book_id,
        'title'     => $title,
        'permalink' => $permalink,
        'image_url' => $img_url,
        'aspect'    => $aspect,
        'format'    => null,

        // helps ui.php embed per-book identity in event payloads
        'asin'      => $asin,

        'show_title'      => $show_title,
        'series_name'     => $series_name,
        'series_position' => $series_pos,
        'volume_text'     => $volume_txt,

        'audio_mode'                 => $show_sample_btn ? $audio_mode : 'off',
        'audio_player_embed'         => get_post_meta($book_id, 'audio_player_embed', true),
        'audio_sample_url'           => get_post_meta($book_id, 'audio_sample_url', true),
        'audible_asin'               => get_post_meta($book_id, 'audible_asin', true),
        'amazon_asin'                => $asin,
        'audiobook_publication_date' => get_post_meta($book_id, 'audiobook_publication_date', true) ?: null,

        'button' => $show_primary_btn ? [
          'text'     => $btn_text,
          'url'      => $button_url,
          'target'   => $resolved_target,
          'bg'       => $attributes['cardButtonBg'] ?? '',
          'fg'       => $attributes['cardButtonFg'] ?? '',
          'tracker'  => $tracker,
          'origin'   => 'handpicked',

          // CRITICAL: tell ui.php what this button represents
          'meta_key' => $btn_meta_key,
        ] : [
          'text' => '', 'url' => '', 'origin' => 'handpicked'
        ],

        // Extras (match ui.php keys)
        'show_author'    => $show_author,
        'author_name'    => $author_name,

        'show_pub_date'  => $show_pub_date,
        'pub_date_label' => $pub_date_label,

        'show_blurb'     => $show_short_desc,
        'blurb'          => $short_desc
      ];

      echo '<div class="mfb-item">';
      if (function_exists('modfarm_render_book_card')) {
        modfarm_render_book_card($card);
      }
      echo '</div>';
    }
    wp_reset_postdata();
  }

  echo '</div>'; // grid

  // Pagination links
  if ($show_pagination && $q->max_num_pages > 1) {
    $pagination = paginate_links([
      'total'   => (int)$q->max_num_pages,
      'current' => $paged,
      'type'    => 'list'
    ]);
    if ($pagination) {
      echo '<div class="mfb-pagination">' . $pagination . '</div>';
    }
  }

  echo '</div>'; // wrapper

  return ob_get_clean();
}
