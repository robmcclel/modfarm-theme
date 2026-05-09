<?php
defined('ABSPATH') || exit;

if (!function_exists('modfarm_render_featured_book_block')) {
  function modfarm_render_featured_book_block($attrs = [], $content = '', $block = null) {
    $a = is_array($attrs) ? $attrs : [];

    // ---- Mode / selection
    $mode      = isset($a['mode']) ? $a['mode'] : 'manual';
    $date_type = ($a['dateType'] ?? 'publication_date') === 'audiobook_publication_date'
      ? 'audiobook_publication_date' : 'publication_date';
    $pinned_id = (int)($a['pinnedId'] ?? 0);

    $book_id = ($mode === 'auto')
      ? mfb_pick_latest_by_date($date_type, $pinned_id)
      : (int)($a['bookId'] ?? 0);

    if ($book_id <= 0 || get_post_type($book_id) !== 'book') {
      return '<div class="mftb mftb--notice"><p>Pick a book (or switch to Auto mode). Invalid/missing Book ID.</p></div>';
    }

    // ---- Basic post bits
    $title     = get_the_title($book_id) ?: '';
    $permalink = get_permalink($book_id) ?: '#';

    $headline  = ($a['headline'] ?? '') !== '' ? (string)$a['headline'] : $title;
    $kicker    = (string)($a['kicker'] ?? '');
    $subhead   = (string)($a['subhead'] ?? '');

    // ---- ModFarm Core Event Context (wrapper-level; Core JS can read this for impressions + clicks)
    $asin = mfb_get_asin($book_id);

    $page_id   = (int)get_queried_object_id();
    $page_type = is_front_page() ? 'home' : (is_singular('book') ? 'book' : (is_page() ? 'page' : 'other'));

    // Allow optional override (keeps your "FeaturedBlock" naming)
    $event_origin = (string)($a['eventOrigin'] ?? 'FeaturedBlock');

    $cover_event_payload = [
      'event_type'     => 'click',
      'event_category' => 'book_card',
      'origin'         => $event_origin !== '' ? $event_origin : 'featured-book',
      'book_id'        => $book_id,
      'book_title'     => $title,
      'meta_key'       => 'permalink',
      'label'          => 'cover',
      'button_style'   => 'cover',
      'smartlinks'     => 'none',
      'block'          => 'featured-book',
    ];
    $headline_event_payload = $cover_event_payload;
    $headline_event_payload['label'] = 'headline';
    $headline_event_payload['button_style'] = 'headline';

    // ---- Description: override first; else BMS field, preserve formatting
    $desc_raw = (string)($a['descOverride'] ?? '');
    if ($desc_raw === '') {
      $desc_raw = (string)get_post_meta($book_id, 'book_description', true);
    }

    if (!empty($a['useExcerpt']) && $desc_raw !== '') {
      $len_chars    = max(40, (int)($a['excerptLen'] ?? 180));
      $approx_words = max(20, (int) floor($len_chars / 5));
      $text         = wp_trim_words(wp_strip_all_tags($desc_raw), $approx_words, '…');
      $desc_html    = wpautop(esc_html($text), true);
    } else {
      $desc_html = wpautop(wp_kses_post($desc_raw), true);
    }

    // ---- Cover
    $cover_url = mfb_cover_url($book_id, (string)($a['coverSource'] ?? 'cover_ebook'));
    $alt       = $title ? ('Cover: ' . $title) : 'Book Cover';

    // ---- Buttons (btn1/2/3)
    $buttons = [];
    for ($i = 1; $i <= 3; $i++) {
      $src = sanitize_key($a["btn{$i}Source"] ?? '');
      if ($src === '' || $src === '__none__') continue;
      $label   = sanitize_text_field($a["btn{$i}Label"] ?? '');
      $variant = sanitize_key($a["btn{$i}Variant"] ?? 'secondary'); // prefer secondary default
      $buttons[] = ['source' => $src, 'label' => $label, 'variant' => $variant];
    }

    $btn_html = mfb_buttons($book_id, $buttons, [
      'permalink' => $permalink,
      'tracking'  => !empty($a['tracking']),
      'origin'    => $event_origin,

      // style overrides (token-compatible)
      'buttonStyleMode'  => (string)($a['buttonStyleMode'] ?? 'inherit'),
      'btnPrimaryBg'     => (string)($a['btnPrimaryBg'] ?? ''),
      'btnPrimaryText'   => (string)($a['btnPrimaryText'] ?? ''),
      'btnPrimaryBorder' => (string)($a['btnPrimaryBorder'] ?? ''),
      'btnOutlineBg'     => (string)($a['btnOutlineBg'] ?? ''),
      'btnOutlineText'   => (string)($a['btnOutlineText'] ?? ''),
      'btnOutlineBorder' => (string)($a['btnOutlineBorder'] ?? ''),

      // radius override (optional)
      'buttonCorners'    => (string)($a['buttonCorners'] ?? 'square'),
    ]);

    // ---- Classes
    $classes = ['mftb'];
    if (!empty($a['darkHero'])) $classes[] = 'is-dark';
    if (($a['mediaSide'] ?? 'left') === 'right') $classes[] = 'is-right';
    if (($a['coverCorners'] ?? 'square') === 'rounded') $classes[] = 'has-cover-rounded';

    // keep these because your CSS uses them
    $btn_corners = ($a['buttonCorners'] ?? 'square');
    if ($btn_corners === 'rounded') $classes[] = 'has-btn-rounded';
    if ($btn_corners === 'pill')     $classes[] = 'has-btn-pill';

    // ---- Output
    ob_start(); ?>
    <div
      class="<?php echo esc_attr(implode(' ', $classes)); ?>"
      data-mf-impression="book_impression"
      data-mf-origin="<?php echo esc_attr($event_origin); ?>"
      data-mf-block="featured-book"
      data-mf-book-id="<?php echo esc_attr((string)$book_id); ?>"
      data-mf-asin="<?php echo esc_attr((string)$asin); ?>"
      data-mf-page-id="<?php echo esc_attr((string)$page_id); ?>"
      data-mf-page-type="<?php echo esc_attr((string)$page_type); ?>"
    >
      <div class="mftb__media">
        <?php if ($cover_url): ?>
          <a
            class="mftb__cover-link"
            href="<?php echo esc_url($permalink); ?>"
            data-mf-event="<?php echo esc_attr(wp_json_encode($cover_event_payload)); ?>"
            data-mf-href="<?php echo esc_attr($permalink); ?>"
            data-mf-destination="<?php echo esc_attr($permalink); ?>"
            data-mf-cta="cover"
            data-mf-source="permalink"
            data-mf-link-type="permalink"
          >
            <img class="mftb__cover" src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async" />
          </a>
        <?php else: ?>
          <div class="mftb__cover --missing" aria-hidden="true"></div>
        <?php endif; ?>
      </div>

      <div class="mftb__body">
        <?php if ($kicker !== ''): ?>
          <div class="mftb__kicker"><?php echo esc_html($kicker); ?></div>
        <?php endif; ?>

        <?php if ($headline !== ''): ?>
          <h2 class="mftb__headline">
            <a
              href="<?php echo esc_url($permalink); ?>"
              data-mf-event="<?php echo esc_attr(wp_json_encode($headline_event_payload)); ?>"
              data-mf-href="<?php echo esc_attr($permalink); ?>"
              data-mf-destination="<?php echo esc_attr($permalink); ?>"
              data-mf-cta="headline"
              data-mf-source="permalink"
              data-mf-link-type="permalink"
            >
              <?php echo esc_html($headline); ?>
            </a>
          </h2>
        <?php endif; ?>

        <?php if ($subhead !== ''): ?>
          <div class="mftb__subhead"><?php echo esc_html($subhead); ?></div>
        <?php endif; ?>

        <?php if (!empty($desc_html)): ?>
          <div class="mftb__desc"><?php echo $desc_html; ?></div>
        <?php endif; ?>

        <?php if ($btn_html): ?>
          <div class="mftb__actions"><?php echo $btn_html; ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }
}

/** Best-effort ASIN lookup (non-fatal if missing) */
if (!function_exists('mfb_get_asin')) {
  function mfb_get_asin($book_id) {
    $candidates = ['asin', 'ASIN', 'amazon_asin', 'kindle_asin'];
    foreach ($candidates as $k) {
      $v = (string)get_post_meta($book_id, $k, true);
      $v = trim($v);
      if ($v !== '') return $v;
    }
    return '';
  }
}

/** Latest by selected date key; pinned override wins if valid */
if (!function_exists('mfb_pick_latest_by_date')) {
  function mfb_pick_latest_by_date($key, $pinned_id = 0) {
    if ($pinned_id > 0 && get_post_type($pinned_id) === 'book') return (int)$pinned_id;

    $date_key = ($key === 'audiobook_publication_date') ? 'audiobook_publication_date' : 'publication_date';
    $today = current_time('Y-m-d');

    $q = new WP_Query([
      'post_type'      => 'book',
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'meta_key'       => $date_key,
      'orderby'        => 'meta_value',
      'order'          => 'DESC',
      'meta_type'      => 'DATE',
      'meta_query'     => [
        [
          'key'     => $date_key,
          'value'   => $today,
          'compare' => '<=',
          'type'    => 'DATE',
        ],
      ],
      'no_found_rows'  => true,
      'ignore_sticky_posts' => true,
    ]);
    if ($q->have_posts()) { $id = (int)$q->posts[0]->ID; wp_reset_postdata(); return $id; }
    wp_reset_postdata();

    $q2 = new WP_Query([
      'post_type'      => 'book',
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'no_found_rows'  => true,
      'ignore_sticky_posts' => true,
    ]);
    $id = $q2->have_posts() ? (int)$q2->posts[0]->ID : 0;
    wp_reset_postdata();
    return $id;
  }
}

/** Cover URL via BMS keys (your names) or featured image; handles ID/array/URL */
if (!function_exists('mfb_cover_url')) {
  function mfb_cover_url($book_id, $source) {
    $source = (string)$source;

    if ($source === 'featured_image') {
      $thumb_id = get_post_thumbnail_id($book_id);
      if ($thumb_id) {
        $img = wp_get_attachment_image_src($thumb_id, 'full');
        if (!empty($img[0])) return $img[0];
      }
      return '';
    }

    $valid = [
      'cover_image_flat',
      'cover_image_audio',
      'cover_image_3d',
      'cover_image_composite',
      'cover_ebook',
      'cover_paperback',
      'cover_hardcover',
      'hero_image',
    ];
    $key = in_array($source, $valid, true) ? $source : 'cover_ebook';

    $val_to_url = function($val) {
      if (is_numeric($val)) return wp_get_attachment_image_url((int)$val, 'full') ?: '';
      if (is_array($val)) {
        if (!empty($val['ID']) && is_numeric($val['ID'])) return wp_get_attachment_image_url((int)$val['ID'], 'full') ?: '';
        if (!empty($val['url'])) return (string)$val['url'];
        if (!empty($val['sizes']) && is_array($val['sizes'])) {
          foreach (['full','large','medium_large','medium'] as $sz) {
            if (!empty($val['sizes'][$sz])) return (string)$val['sizes'][$sz];
          }
        }
        return '';
      }
      if (is_string($val)) {
        $v = trim($val);
        if ($v === '') return '';
        if (is_numeric($v)) return wp_get_attachment_image_url((int)$v, 'full') ?: '';
        return $v;
      }
      return '';
    };

    $url = $val_to_url(get_post_meta($book_id, $key, true));

    if (!$url) {
      foreach (['cover_ebook','cover_image_flat','cover_image_composite','cover_image_audio','cover_image_3d','cover_paperback','cover_hardcover','hero_image'] as $try) {
        $url = $val_to_url(get_post_meta($book_id, $try, true));
        if ($url) break;
      }
    }

    if (!$url) {
      $thumb_id = get_post_thumbnail_id($book_id);
      if ($thumb_id) {
        $img = wp_get_attachment_image_src($thumb_id, 'full');
        if (!empty($img[0])) $url = $img[0];
      }
    }

    if ($url && strpos($url, 'm.media-amazon.com') !== false) {
      $url = preg_replace('~\._[A-Z0-9_,-]+(?:_)?\.~', '.', $url);
      $url = preg_replace('~\._[A-Z0-9_,-]+$~', '', $url);
    }
    return $url ?: '';
  }
}

/**
 * Buttons from btn1/2/3 with optional token-compatible overrides:
 * - class uses: book-page-button is-primary / is-secondary
 * - inline style injects: --mfb-bp-override-bg/fg/border/radius
 *
 * Adds normalized click tracking attributes:
 * - data-mf-event="book_click"
 * - data-mf-cta="primary|secondary"
 * - data-mf-source="<meta key>"
 * - data-mf-link-type / data-mf-destination (if SmartLinks resolver provides)
 */
if (!function_exists('mfb_buttons')) {
  function mfb_buttons($book_id, $buttons, $opt = []) {
    $permalink = $opt['permalink'] ?? get_permalink($book_id);
    $tracking  = !empty($opt['tracking']);
    $origin    = $opt['origin'] ?? 'FeaturedBlock';

    $style_mode = (string)($opt['buttonStyleMode'] ?? 'inherit');

    // Optional per-variant override colors
    $p_bg = trim((string)($opt['btnPrimaryBg'] ?? ''));
    $p_fg = trim((string)($opt['btnPrimaryText'] ?? ''));
    $p_bd = trim((string)($opt['btnPrimaryBorder'] ?? ''));

    $s_bg = trim((string)($opt['btnOutlineBg'] ?? ''));
    $s_fg = trim((string)($opt['btnOutlineText'] ?? ''));
    $s_bd = trim((string)($opt['btnOutlineBorder'] ?? ''));

    // Optional radius override (works with your existing CSS mapping too)
    $corners = (string)($opt['buttonCorners'] ?? 'inherit');
    $radius_override = '';
    if ($corners === 'square')  $radius_override = '0px';
    if ($corners === 'rounded') $radius_override = '6px';
    if ($corners === 'pill')    $radius_override = '9999px';

    $out = [];
    $i = 0;

    foreach ((array)$buttons as $btn) {
      if ($i >= 3) break;

      $src     = sanitize_key($btn['source'] ?? '');
      $label   = sanitize_text_field($btn['label'] ?? '');
      $variant = sanitize_key($btn['variant'] ?? 'secondary');

      if ($src === '' || $src === '__none__') continue;

      // Back-compat: treat "outline" as "secondary"
      if ($variant === 'outline') $variant = 'secondary';
      $is_primary = ($variant === 'primary');

      $url = '';
      if ($src === 'permalink') {
        $url = $permalink;
        if ($label === '') $label = 'See The Book';
      } else {
        $url = (string)get_post_meta($book_id, $src, true);
        if (!$url && $src === 'kindle_url') {
          $url = (string)get_post_meta($book_id, 'amazon_paper', true);
          if (!$url) $url = (string)get_post_meta($book_id, 'amazon_hard', true);
          if (!$url) $url = $permalink;
        }
        if ($label === '') $label = ucwords(str_replace(['_', 'url'], [' ', ''], $src));
      }
      if (!$url) { $i++; continue; }

      // ---- SmartLinks hook (Genius Quick Build Proxy; safe if Core is absent)
      $destination = (string)$url;
      $href = $destination;
      $smart_wrapped = 0;
      $smartlink_eligible_url = !function_exists('mfc_smartlinks_url_is_eligible') || mfc_smartlinks_url_is_eligible($destination);
      if ($destination !== '' && $src !== 'permalink' && $smartlink_eligible_url && function_exists('mfc_smartlinks_wrap_url')) {
        $maybe = mfc_smartlinks_wrap_url($destination, $src);
        if (is_string($maybe) && $maybe !== '' && $maybe !== $destination) {
          $href = $maybe;
          $smart_wrapped = 1;
        }
      }

      // Build inline override style (only when custom + non-empty)
      $style_bits = [];

      if ($style_mode === 'custom') {
        if ($is_primary) {
          if ($p_bg !== '') $style_bits[] = '--mfb-bp-override-bg:' . $p_bg;
          if ($p_fg !== '') $style_bits[] = '--mfb-bp-override-fg:' . $p_fg;
          if ($p_bd !== '') $style_bits[] = '--mfb-bp-override-border:' . $p_bd;
        } else {
          if ($s_bg !== '') $style_bits[] = '--mfb-bp-override-bg:' . $s_bg;
          if ($s_fg !== '') $style_bits[] = '--mfb-bp-override-fg:' . $s_fg;
          if ($s_bd !== '') $style_bits[] = '--mfb-bp-override-border:' . $s_bd;
        }
      }

      if ($radius_override !== '') {
        $style_bits[] = '--mfb-bp-override-radius:' . $radius_override;
      }

      $event_payload = [
        'event_type'     => 'click',
        'event_category' => 'book_card_button',
        'origin'         => $origin !== '' ? $origin : 'featured-book',
        'book_id'        => (int)$book_id,
        'book_title'     => get_the_title($book_id) ?: '',
        'meta_key'       => $src,
        'label'          => wp_strip_all_tags($label),
        'button_style'   => $is_primary ? 'primary' : 'secondary',
        'smartlinks'     => $smart_wrapped ? 'genius_quickbuild' : 'none',
        'block'          => 'featured-book',
      ];

      $attrs = [
        'class' => 'mftb__btn book-page-button ' . ($is_primary ? 'is-primary' : 'is-secondary'),
        'href'  => esc_url($href),

        'data-mf-event'       => wp_json_encode($event_payload),
        'data-mf-href'        => $href,
        'data-mf-destination' => $destination,
        'data-mf-cta'         => $is_primary ? 'primary' : 'secondary',
        'data-mf-source'      => $src,
        'data-mf-link-type'   => $src === 'permalink' ? 'permalink' : ($smart_wrapped ? 'genius_quickbuild' : 'direct'),
      ];

      if (!empty($style_bits)) {
        $attrs['style'] = implode(';', $style_bits) . ';';
      }

      if ($src !== 'permalink') { $attrs['target'] = '_blank'; $attrs['rel'] = 'noopener'; }

      // Keep your legacy tracking intact
      if ($tracking) {
        $attrs['data-event_category'] = 'BookCTA';
        $attrs['data-event_label']    = $book_id . ':' . $src;
        $attrs['data-event_origin']   = $origin;
      }

      $attr_html = '';
      foreach ($attrs as $k => $v) {
        $attr_html .= ' ' . $k . '="' . esc_attr($v) . '"';
      }

      $out[] = '<a' . $attr_html . '>' . esc_html($label) . '</a>';
      $i++;
    }

    return implode("\n", $out);
  }
}
