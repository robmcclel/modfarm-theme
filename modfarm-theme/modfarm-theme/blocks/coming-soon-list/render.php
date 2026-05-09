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
  $btn_available_text = trim((string)($attributes['availableButtonText'] ?? ''));
  $btn_upcoming_text  = trim((string)($attributes['upcomingButtonText'] ?? ''));
  if ($btn_available_text === '') {
    $btn_available_text = trim((string)($attributes['ctaReleased'] ?? ''));
  }
  if ($btn_available_text === '') {
    $btn_available_text = trim((string)($attributes['ctaLaunch'] ?? ''));
  }
  if ($btn_available_text === '') {
    $btn_available_text = __('Available Now', 'modfarm');
  }
  if ($btn_upcoming_text === '') {
    $btn_upcoming_text = trim((string)($attributes['ctaUpcoming'] ?? ''));
  }
  if ($btn_upcoming_text === '') {
    $btn_upcoming_text = __('Coming Soon', 'modfarm');
  }
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

  // ===== Legacy publication timing attributes (retained for saved blocks) =====
  $include_launch = !empty($attributes['includeLaunchWindow']);
  $window_days    = max(1, (int)($attributes['launchWindowDays'] ?? 7));
  $post_mode      = $attributes['postReleaseMode'] ?? 'hide'; // hide|fade|keep

  $list_type          = isset($attributes['listType']) ? (string)$attributes['listType'] : 'coming-soon';
  if (!in_array($list_type, ['latest-releases', 'coming-soon', 'timeframe'], true)) {
    $list_type = 'coming-soon';
  }
  $latest_window_days = max(1, (int)($attributes['latestWindowDays'] ?? 30));

  // ===== Date Range Filter =====
  $date_filter_mode = isset($attributes['dateFilterMode']) ? (string)$attributes['dateFilterMode'] : 'month';
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

  if ($list_type === 'timeframe' && $date_filter_mode !== 'range' && ($filter_year <= 0 || $filter_month < 1 || $filter_month > 12)) {
    $filter_year = function_exists('wp_date') ? (int) wp_date('Y', $now_ts) : (int) date_i18n('Y', $now_ts);
    $filter_month = function_exists('wp_date') ? (int) wp_date('n', $now_ts) : (int) date_i18n('n', $now_ts);
  }

  if ($list_type === 'timeframe' && $date_filter_mode !== 'range' && $filter_year > 0 && $filter_month >= 1 && $filter_month <= 12) {
    $start = sprintf('%04d-%02d-01', $filter_year, $filter_month);
    $end_ts = strtotime($start . ' +1 month -1 day');
    $end = date('Y-m-d', $end_ts);

    $meta_query[] = [
      'key'     => $pub_date_key,
      'value'   => [$start, $end],
      'compare' => 'BETWEEN',
      'type'    => 'DATE',
    ];
  }
  elseif ($list_type === 'timeframe' && $date_filter_mode === 'range' && $is_ymd($filter_start) && $is_ymd($filter_end)) {
    $start = $filter_start;
    $end   = $filter_end;

    if (strtotime($start) > strtotime($end)) {
      $tmp = $start; $start = $end; $end = $tmp;
    }

    $meta_query[] = [
      'key'     => $pub_date_key,
      'value'   => [$start, $end],
      'compare' => 'BETWEEN',
      'type'    => 'DATE',
    ];
  }
  else {
    if ($list_type === 'latest-releases') {
      $end_dt   = $today($now_ts);

      $meta_query[] = [
        'key'     => $pub_date_key,
        'value'   => $end_dt,
        'compare' => '<=',
        'type'    => 'DATE',
      ];

      // Safety: default order for latest releases should be DESC
      if ($order_setting !== 'DESC' && $order_setting !== 'rand') {
        $order_setting = 'DESC';
      }
    } elseif ($list_type === 'coming-soon') {
      $min_dt = $today($now_ts);

      $meta_query[] = [
        'key'     => $pub_date_key,
        'value'   => $min_dt,
        'compare' => '>',
        'type'    => 'DATE',
      ];

      // Safety: default order for coming soon should be ASC
      if ($order_setting !== 'ASC' && $order_setting !== 'rand') {
        $order_setting = 'ASC';
      }
    } else {
      $meta_query[] = [
        'key'     => $pub_date_key,
        'compare' => 'EXISTS',
      ];
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
    'is-' . sanitize_html_class($list_type),
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

      $today_start = strtotime($today($now_ts) . ' 00:00:00');
      $state = ($pub_ts && $pub_ts <= $today_start) ? 'released' : 'upcoming';

      $item_classes = ['mfb-item'];
      if ($state === 'upcoming') $item_classes[] = 'is-upcoming';
      if ($state === 'released') $item_classes[] = 'is-released';

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

      $final_btn_text = ($state === 'upcoming') ? $btn_upcoming_text : $btn_available_text;
      if ($final_btn_text === '') $final_btn_text = $btn_text;

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
