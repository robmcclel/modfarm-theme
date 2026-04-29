<?php
if ( ! defined('ABSPATH') ) exit;

function modfarm_render_coming_soon_list_block( $attributes ) {

  // ===== Attributes (defaults) =====
  $tax_type        = $attributes['tax-type']        ?? '';
  $order_setting   = $attributes['display-order']   ?? 'ASC';
  $books_per_page  = max(1, min(100, (int)($attributes['books-per-page'] ?? 12)));
  $books_per_row   = $attributes['books-in-row']    ?? '25%';
  $show_pagination = !empty($attributes['show-pagination']);

  $image_type      = $attributes['image-type']      ?? 'featured';

  $show_title   = (($attributes['show-title']  ?? 'block') === 'block');
  $show_series  = (($attributes['show-series'] ?? 'block') === 'block');
  $show_audio   = (($attributes['show-audio']  ?? 'none')  === 'block');
  $show_button  = (($attributes['show-button'] ?? 'block') === 'block');
  $audio_mode   = $attributes['audio-mode'] ?? 'auto';

  $btn_text   = $attributes['button-text']   ?? __('See The Book', 'modfarm');
  $btn_link   = $attributes['button-link']   ?? 'bookpage'; // can be 'bookpage' or meta key (e.g. kindle_url)
  $btn_target = $attributes['button-target'] ?? '_self';
  $btn_bg     = $attributes['buttonbg-color'] ?? '';
  $btn_fg     = $attributes['buttontx-color'] ?? '';
  $tracker    = $attributes['tracker-loc']   ?? '';
  $volume_txt = $attributes['volume-text']   ?? 'Book';

  // ===== Card Extras (must match ui.php keys used by Handpicked) =====
  $show_author   = !empty($attributes['showAuthor']);
  $show_pub_date = !empty($attributes['showPubDate']);
  $pub_date_key  = isset($attributes['pubDateKey']) ? (string)$attributes['pubDateKey'] : 'publication_date';

  // RESTORED: short description toggle (BMS: short_description)
  $show_short_desc = !empty($attributes['showShortDescription']);

  // ===== Publication timing (auto mode only) =====
  $include_launch = !empty($attributes['includeLaunchWindow']);
  $window_days    = max(1, (int)($attributes['launchWindowDays'] ?? 7));
  $post_mode      = $attributes['postReleaseMode'] ?? 'hide'; // hide|fade|keep

  $smart_cta      = !empty($attributes['smartCta']);
  $cta_upcoming   = $attributes['ctaUpcoming'] ?? __('Coming Soon', 'modfarm');
  $cta_launch     = $attributes['ctaLaunch']   ?? __('Out Now', 'modfarm');
  $cta_released   = $attributes['ctaReleased'] ?? '';

  // ===== Option A: list type =====
  $list_type          = isset($attributes['listType']) ? (string)$attributes['listType'] : 'coming-soon';
  $latest_window_days = max(1, (int)($attributes['latestWindowDays'] ?? 30));

  // ===== Date Range Filter =====
  $date_filter_mode = isset($attributes['dateFilterMode']) ? (string)$attributes['dateFilterMode'] : 'auto';
  $filter_year      = (int)($attributes['filterYear'] ?? 0);
  $filter_month     = (int)($attributes['filterMonth'] ?? 0);
  $filter_start     = isset($attributes['filterStart']) ? (string)$attributes['filterStart'] : '';
  $filter_end       = isset($attributes['filterEnd']) ? (string)$attributes['filterEnd'] : '';

  // ===== Date formatter (match Handpicked behavior) =====
  $mf_format_date = function($raw) {
    $raw = (string)$raw;
    if ($raw === '') return '';
    $ts = strtotime($raw);
    if (!$ts) return '';
    return date_i18n(get_option('date_format'), $ts);
  };

  // ===== Design controls =====
  $map_effect = [
    'flat' => 'flat',
    'shadow' => 'shadow-md',
    'shadow-sm' => 'shadow-sm',
    'shadow-md' => 'shadow-md',
    'shadow-lg' => 'shadow-lg',
    'lift' => 'shadow-md',
    'glow' => 'shadow-lg',
    'emboss' => 'emboss',
    'elevated' => 'shadow-md',
  ];
  $effect_raw   = $attributes['effect'] ?? ($attributes['card-style'] ?? 'flat');
  $effect       = $map_effect[$effect_raw] ?? 'flat';

  $cover_shape  = in_array(($attributes['cover-shape'] ?? 'square'), ['square','rounded'], true) ? $attributes['cover-shape'] : 'square';
  $button_shape = in_array(($attributes['button-shape'] ?? 'square'), ['square','rounded','pill','partial'], true) ? $attributes['button-shape'] : 'square';
  $sample_shape = in_array(($attributes['sample-shape'] ?? 'square'), ['square','rounded','pill'], true) ? $attributes['sample-shape'] : 'square';
  $cta_join     = ($attributes['cta-join'] ?? 'joined') === 'gap' ? 'gap' : 'joined';

  // ===== Tax filters =====
  $term_map = [
    'series'   => ['attr' => 'series-select',     'taxonomy' => 'book-series'],
    'genre'    => ['attr' => 'genre-select',      'taxonomy' => 'book-genre'],
    'author'   => ['attr' => 'bookauthor-select', 'taxonomy' => 'book-author'],
    'language' => ['attr' => 'language-select',   'taxonomy' => 'book-language'],
    'booktag'  => ['attr' => 'booktag-select',    'taxonomy' => 'book-tags'],
  ];

  $tax_query = [];
  if (!empty($term_map[$tax_type])) {
    $attr_key = $term_map[$tax_type]['attr'];
    $tax_slug = $term_map[$tax_type]['taxonomy'];
    $term_id  = (!empty($attributes[$attr_key]['id'])) ? (int)$attributes[$attr_key]['id'] : 0;
    if ($term_id) $tax_query[] = ['taxonomy' => $tax_slug, 'field' => 'term_id', 'terms' => $term_id];
  }

  $format_term = (isset($attributes['book-format']['id']) && $attributes['book-format']['id'])
    ? (int)$attributes['book-format']['id'] : null;
  if ($format_term) {
    $tax_query[] = ['taxonomy' => 'book-format', 'field' => 'term_id', 'terms' => $format_term];
  }

  // ===== Build meta_query (robust, string-based) =====
  $meta_query = [];
  $now_ts = current_time('timestamp');

  // Use wp_date for site timezone. These strings sort correctly lexicographically for ISO-like meta.
  $today = function($ts) {
    if (function_exists('wp_date')) return wp_date('Y-m-d', $ts);
    return date_i18n('Y-m-d', $ts);
  };

  $is_ymd = function($s) {
    if (!is_string($s) || $s === '') return false;
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $s);
  };

  if ($date_filter_mode === 'month' && $filter_year > 0 && $filter_month >= 1 && $filter_month <= 12) {
    $start = sprintf('%04d-%02d-01', $filter_year, $filter_month);
    $end_ts = strtotime($start . ' +1 month -1 day');
    $end = date('Y-m-d', $end_ts);

    $meta_query[] = [
      'key'     => $pub_date_key,
      'value'   => [$start, $end],
      'compare' => 'BETWEEN',
    ];
  }
  elseif ($date_filter_mode === 'range' && $is_ymd($filter_start) && $is_ymd($filter_end)) {
    $start = $filter_start;
    $end   = $filter_end;

    if (strtotime($start) > strtotime($end)) {
      $tmp = $start; $start = $end; $end = $tmp;
    }

    $meta_query[] = [
      'key'     => $pub_date_key,
      'value'   => [$start, $end],
      'compare' => 'BETWEEN',
    ];
  }
  else {
    // AUTO MODE depends on listType
    if ($list_type === 'latest-releases') {
      $end_dt   = $today($now_ts);
      $start_ts = $now_ts - ($latest_window_days * DAY_IN_SECONDS);
      $start_dt = $today($start_ts);

      $meta_query[] = [
        'key'     => $pub_date_key,
        'value'   => [$start_dt, $end_dt],
        'compare' => 'BETWEEN',
      ];

      // Safety: default order for latest releases should be DESC
      if ($order_setting !== 'DESC' && $order_setting !== 'rand') {
        $order_setting = 'DESC';
      }
    } else {
      $min_ts = $include_launch ? ($now_ts - ($window_days * DAY_IN_SECONDS)) : $now_ts;
      $min_dt = $today($min_ts);

      $meta_query[] = [
        'key'     => $pub_date_key,
        'value'   => $min_dt,
        'compare' => '>',
      ];

      // Safety: default order for coming soon should be ASC
      if ($order_setting !== 'ASC' && $order_setting !== 'rand') {
        $order_setting = 'ASC';
      }
    }
  }

  // ===== Query =====
  $paged = max(1, (int)(get_query_var('paged') ?: get_query_var('page') ?: 1));

  $orderby = ($order_setting === 'rand') ? 'rand' : 'meta_value';
  $order   = ($order_setting === 'DESC') ? 'DESC' : 'ASC';

  $q_args = [
    'post_type'           => 'book',
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'tax_query'           => $tax_query,
    'paged'               => $paged,
    'posts_per_page'      => $books_per_page,
    'no_found_rows'       => $show_pagination ? false : true,
    'meta_query'          => $meta_query,
  ];

  if ($orderby === 'rand') {
    $q_args['orderby'] = 'rand';
  } else {
    $q_args['orderby']  = 'meta_value';
    $q_args['meta_key'] = $pub_date_key;
    $q_args['order']    = $order;
    // NOTE: intentionally NOT setting meta_type => 'DATE' for compatibility
  }

  $q = new WP_Query($q_args);

  // ===== Grid columns from percent =====
  $pct  = floatval(str_replace('%','',(string)$books_per_row));
  $cols = ($pct > 0) ? max(1, (int)round(100 / $pct)) : 4;

  // ===== Wrapper classes + CSS vars =====
  $wrapper_classes = [
    'mfb-wrapper',
    ($list_type === 'latest-releases') ? 'is-latest-releases' : 'is-coming-soon',
    'mfb-effect--' . sanitize_html_class($effect),
    'mfb-cover--'  . sanitize_html_class($cover_shape),
    'mfb-button--' . sanitize_html_class($button_shape),
    'mfb-sample--' . sanitize_html_class($sample_shape),
    'mfb-cta--'    . sanitize_html_class($cta_join),
  ];

  $custom_vars = [];
  if (!empty($attributes['samplebtn-bg']))     $custom_vars[] = '--mfb-sample-bg:' . $attributes['samplebtn-bg'];
  if (!empty($attributes['samplebtn-fg']))     $custom_vars[] = '--mfb-sample-fg:' . $attributes['samplebtn-fg'];
  if (!empty($attributes['samplebtn-border'])) $custom_vars[] = '--mfb-sample-border:' . $attributes['samplebtn-border'];

  $wrapper_style = '--mfb-cols:' . (int)$cols . ';' . implode(';', $custom_vars) . ';';

  ob_start();

  echo '<div class="' . esc_attr(implode(' ', array_filter($wrapper_classes))) . '">';
  echo '<div class="mfb-grid" style="' . esc_attr($wrapper_style) . '">';

  if ($q->have_posts()) {
    while ($q->have_posts()) {
      $q->the_post();
      $book_id = get_the_ID();

      $title     = get_the_title($book_id);
      $permalink = get_permalink($book_id);

      $asin = (string)get_post_meta($book_id, 'asin_kindle', true);

      // RESTORED: Short description from BMS meta
      $short_desc = '';
      if ($show_short_desc) {
        $short_desc = (string) get_post_meta($book_id, 'short_description', true);
      }

      // === Pub date raw + label ===
      $raw_date = $show_pub_date ? (string)get_post_meta($book_id, $pub_date_key, true) : '';
      $pub_ts   = $raw_date ? strtotime($raw_date . ' 00:00:00') : 0;

      // Optional fallback for display when audiobook selected but empty
      if ($show_pub_date && $raw_date === '' && $pub_date_key === 'audiobook_publication_date') {
        $raw_date = (string)get_post_meta($book_id, 'publication_date', true);
        $pub_ts   = $raw_date ? strtotime($raw_date . ' 00:00:00') : 0;
      }

      $pub_date_label = $show_pub_date ? $mf_format_date($raw_date) : '';

      // classify timing (used mainly for coming-soon visuals/CTA)
      $state = 'upcoming';
      if ($pub_ts && $pub_ts <= $now_ts) {
        $cutoff = $now_ts - ($window_days * DAY_IN_SECONDS);
        $state  = ($pub_ts >= $cutoff) ? 'launch' : 'released';
      }

      // Post-release behavior should only impact coming-soon auto
      if ($date_filter_mode === 'auto' && $list_type !== 'latest-releases') {
        if ($state === 'released' && $post_mode === 'hide') continue;
      }

      $item_classes = ['mfb-item'];
      if ($state === 'upcoming') $item_classes[] = 'is-upcoming';
      if ($state === 'launch')   $item_classes[] = 'is-launch';
      if ($state === 'released') $item_classes[] = 'is-released';
      if ($date_filter_mode === 'auto' && $state === 'released' && $post_mode === 'fade') $item_classes[] = 'is-faded';

      // ============================================================
      // Button URL + meta_key (for SmartLinks in ui.php)
      $btn_meta_key = 'permalink';
      $button_url   = $permalink;

      if ($show_button && $btn_link && $btn_link !== 'bookpage') {
        $btn_meta_key = (string)$btn_link;
        $maybe = (string)get_post_meta($book_id, $btn_meta_key, true);
        if ($maybe !== '') {
          $button_url = $maybe;
        } else {
          $btn_meta_key = 'permalink';
          $button_url   = $permalink;
        }
      }

      // Prefer _blank for external unless explicitly overridden
      $resolved_target = $btn_target;
      if ($btn_meta_key !== 'permalink' && ($resolved_target === '' || $resolved_target === '_self')) {
        $resolved_target = '_blank';
      }
      // ============================================================

      // Smart CTA label (time-aware)
      $final_btn_text = $btn_text;
      if ($smart_cta) {
        if ($state === 'upcoming' && $cta_upcoming !== '') $final_btn_text = $cta_upcoming;
        if ($state === 'launch'   && $cta_launch   !== '') $final_btn_text = $cta_launch;
        if ($state === 'released' && $cta_released !== '') $final_btn_text = $cta_released;
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
          $author_name = implode(', ', array_map(fn($t) => $t->name, $author_terms));
        }
      }

      $card = [
        'id'        => $book_id,
        'title'     => $title,
        'permalink' => $permalink,
        'image_url' => $img_url,
        'aspect'    => $aspect,
        'format'    => null,

        // used by ui.php to include book identity in events
        'asin'      => $asin,

        'show_title'      => $show_title,
        'series_name'     => $series_name,
        'series_position' => $series_pos,
        'volume_text'     => $volume_txt,

        // RESTORED: Short Description (Handpicked-compatible keys)
        'show_blurb' => $show_short_desc,
        'blurb'      => $short_desc,

        'audio_mode'                 => $show_audio ? $audio_mode : 'off',
        'audio_player_embed'         => get_post_meta($book_id, 'audio_player_embed', true),
        'audio_sample_url'           => get_post_meta($book_id, 'audio_sample_url', true),
        'audible_asin'               => get_post_meta($book_id, 'audible_asin', true),
        'amazon_asin'                => $asin,
        'audiobook_publication_date' => get_post_meta($book_id, 'audiobook_publication_date', true) ?: null,

        'button' => $show_button ? [
          'text'     => $final_btn_text,
          'url'      => $button_url,
          'target'   => $resolved_target,
          'bg'       => $btn_bg,
          'fg'       => $btn_fg,
          'tracker'  => $tracker,
          'origin'   => 'coming-soon',

          // enables Genius wrapping in ui.php
          'meta_key' => $btn_meta_key,
        ] : [
          'text' => '', 'url' => '', 'origin' => 'coming-soon'
        ],

        'show_author'    => $show_author,
        'author_name'    => $author_name,

        'show_pub_date'  => $show_pub_date,
        'pub_date_label' => $pub_date_label,

        'timing_state'   => $state,
      ];

      echo '<div class="' . esc_attr(implode(' ', $item_classes)) . '">';
      if (function_exists('modfarm_render_book_card')) {
        modfarm_render_book_card($card);
      }
      echo '</div>';
    }
    wp_reset_postdata();
  }

  echo '</div>'; // grid

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