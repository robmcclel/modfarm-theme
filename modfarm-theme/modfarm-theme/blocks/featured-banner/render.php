<?php
defined('ABSPATH') || exit;

if (!function_exists('mfbb_buttons')) {
  function mfbb_buttons($book_id, $buttons, $opt = []) {
    $permalink  = $opt['permalink'] ?? get_permalink($book_id);
    $series_url = (string)($opt['series_url'] ?? '');

    $tracking  = !empty($opt['tracking']);
    $origin    = $opt['origin'] ?? 'FeaturedBanner';
    $context   = (string)($opt['context'] ?? 'featured-banner');

    $style_mode = (string)($opt['buttonStyleMode'] ?? 'inherit');

    $p_bg = trim((string)($opt['btnPrimaryBg'] ?? ''));
    $p_fg = trim((string)($opt['btnPrimaryText'] ?? ''));
    $p_bd = trim((string)($opt['btnPrimaryBorder'] ?? ''));

    $s_bg = trim((string)($opt['btnOutlineBg'] ?? ''));
    $s_fg = trim((string)($opt['btnOutlineText'] ?? ''));
    $s_bd = trim((string)($opt['btnOutlineBorder'] ?? ''));

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

      if ($variant === 'outline') $variant = 'secondary';
      $is_primary = ($variant === 'primary');

      $url = '';
      if ($src === 'permalink') {
        $url = $permalink;
        if ($label === '') $label = 'See The Book';
      } elseif ($src === 'series_permalink') {
        $url = $series_url;
        if ($label === '') $label = 'See The Full Series';
      } else {
        $url = (string)get_post_meta($book_id, $src, true);

        if (!$url && $src === 'kindle_url') {
          $url = (string)get_post_meta($book_id, 'amazon_paper', true);
          if (!$url) $url = (string)get_post_meta($book_id, 'amazon_hard', true);
          if (!$url) $url = $permalink;
        }

        if ($label === '') {
          $label = ucwords(str_replace(['_', 'url'], [' ', ''], $src));
        }
      }

      if (!$url) { $i++; continue; }

      // SmartLinks hook (Genius Quick Build Proxy; safe if Core is absent)
      $destination = (string)$url;
      $href = $destination;
      $smart_wrapped = 0;
      $smartlink_eligible_url = !function_exists('mfc_smartlinks_url_is_eligible') || mfc_smartlinks_url_is_eligible($destination);
      if ($destination !== '' && $src !== 'permalink' && $src !== 'series_permalink' && $smartlink_eligible_url && function_exists('mfc_smartlinks_wrap_url')) {
        $maybe = mfc_smartlinks_wrap_url($destination, $src);
        if (is_string($maybe) && $maybe !== '' && $maybe !== $destination) {
          $href = $maybe;
          $smart_wrapped = 1;
        }
      }

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
        'origin'         => $origin !== '' ? $origin : $context,
        'book_id'        => (int)$book_id,
        'book_title'     => get_the_title($book_id) ?: '',
        'meta_key'       => $src,
        'label'          => wp_strip_all_tags($label),
        'button_style'   => $is_primary ? 'primary' : 'secondary',
        'smartlinks'     => $smart_wrapped ? 'genius_quickbuild' : 'none',
        'block'          => $context,
      ];

      $attrs = [
        'class' => 'mfbb__btn book-page-button ' . ($is_primary ? 'is-primary' : 'is-secondary'),
        'href'  => esc_url($href),
        'data-mf-event'       => wp_json_encode($event_payload),
        'data-mf-href'        => $href,
        'data-mf-destination' => $destination,
        'data-mf-cta'         => $is_primary ? 'primary' : 'secondary',
        'data-mf-source'      => $src,
        'data-mf-link-type'   => ($src === 'permalink' || $src === 'series_permalink') ? 'permalink' : ($smart_wrapped ? 'genius_quickbuild' : 'direct'),
      ];

      if (!empty($style_bits)) $attrs['style'] = implode(';', $style_bits) . ';';

      if ($src !== 'permalink' && $src !== 'series_permalink') {
        $attrs['target'] = '_blank';
        $attrs['rel'] = 'noopener';
      }

      if ($tracking) {
        $attrs['data-event_category'] = 'BookCTA';
        $attrs['data-event_label']    = $book_id . ':' . $src;
        $attrs['data-event_origin']   = $origin;
      }

      $attr_html = '';
      foreach ($attrs as $k => $v) $attr_html .= ' ' . $k . '="' . esc_attr($v) . '"';

      $out[] = '<a' . $attr_html . '>' . esc_html($label) . '</a>';
      $i++;
    }

    return $out ? implode("\n", $out) : '';
  }
}

if (!function_exists('modfarm_render_featured_banner_block')) {
  function modfarm_render_featured_banner_block($attrs = [], $content = '', $block = null) {
    $a = is_array($attrs) ? $attrs : [];

    $book_id = (int)($a['bookId'] ?? 0);
    if ($book_id <= 0 || get_post_type($book_id) !== 'book') {
      $wrapper = function_exists('get_block_wrapper_attributes')
        ? get_block_wrapper_attributes(['class' => 'mfbb mfbb--notice'])
        : 'class="mfbb mfbb--notice"';
      return '<div ' . $wrapper . '><p>Pick a book. Invalid/missing Book ID.</p></div>';
    }

    $title     = get_the_title($book_id) ?: '';
    $permalink = get_permalink($book_id) ?: '#';
    $alt       = $title ? ('Cover: ' . $title) : 'Book Cover';

    // Headline
    $show_headline = !empty($a['showHeadline']);
    $headline = '';
    if ($show_headline) {
      $source = (string)($a['headlineSource'] ?? 'book');
      if ($source === 'custom') {
        $headline = trim((string)($a['headline'] ?? ''));
        if ($headline === '') $headline = $title;
      } else {
        $headline = $title;
      }
    }

    $headline_tag = preg_match('/^h[1-6]$/', (string)($a['headlineTag'] ?? 'h2')) ? (string)$a['headlineTag'] : 'h2';
    $headline_size = (int)($a['headlineFontSize'] ?? 42);

    $kicker  = (string)($a['kicker'] ?? '');
    $subhead = (string)($a['subhead'] ?? '');

    // Event context
    $asin = function_exists('mfb_get_asin') ? (string)mfb_get_asin($book_id) : '';
    $page_id   = (int)get_queried_object_id();
    $page_type = is_front_page() ? 'home' : (is_singular('book') ? 'book' : (is_page() ? 'page' : 'other'));
    $event_origin = (string)($a['eventOrigin'] ?? 'FeaturedBanner');

    $cover_event_payload = [
      'event_type'     => 'click',
      'event_category' => 'book_card',
      'origin'         => $event_origin !== '' ? $event_origin : 'featured-banner',
      'book_id'        => $book_id,
      'book_title'     => $title,
      'meta_key'       => 'permalink',
      'label'          => 'cover',
      'button_style'   => 'cover',
      'smartlinks'     => 'none',
      'block'          => 'featured-banner',
    ];

    // Description
    $desc_raw = '';
    if (!empty($a['useCustomDesc'])) $desc_raw = (string)($a['descOverride'] ?? '');
    if (trim((string)$desc_raw) === '') {
      $desc_raw = (string)get_post_meta($book_id, 'short_description', true);
      if ($desc_raw === '') $desc_raw = (string)get_post_meta($book_id, 'book_description', true);
    }
    if (!empty($a['useExcerpt']) && $desc_raw !== '') {
      $len_chars    = max(40, (int)($a['excerptLen'] ?? 180));
      $approx_words = max(20, (int) floor($len_chars / 5));
      $desc_raw     = wp_trim_words(wp_strip_all_tags($desc_raw), $approx_words, '…');
    }
    $desc_html = (trim((string)$desc_raw) !== '') ? wp_kses_post(wpautop($desc_raw)) : '';

    $desc_size      = max(12, (int)($a['descFontSize'] ?? 18));
    $desc_weight    = max(100, min(900, (int)($a['descFontWeight'] ?? 500)));
    $desc_transform = (string)($a['descTextTransform'] ?? 'none');
    if (!in_array($desc_transform, ['none', 'uppercase', 'lowercase', 'capitalize'], true)) $desc_transform = 'none';

    // Hero background (FIX)
    // Hero background (robust like Featured Book)
    $hero_source = (string)($a['heroSource'] ?? 'book_meta');
    $hero_url = '';
    
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
    
    if ($hero_source === 'custom') {
      $hero_url = $val_to_url((string)($a['heroUrl'] ?? ''));
    } elseif ($hero_source === 'featured_image') {
      $thumb_id = get_post_thumbnail_id($book_id);
      if ($thumb_id) $hero_url = wp_get_attachment_image_url((int)$thumb_id, 'full') ?: '';
    } else {
      // book_meta: prefer hero_image, then fall back to composite/ebook cover, then featured image
      $hero_url = $val_to_url(get_post_meta($book_id, 'hero_image', true));
    
      if (!$hero_url) {
        foreach (['cover_image_composite','cover_ebook','cover_image_flat'] as $try) {
          $hero_url = $val_to_url(get_post_meta($book_id, $try, true));
          if ($hero_url) break;
        }
      }
    
      if (!$hero_url) {
        $thumb_id = get_post_thumbnail_id($book_id);
        if ($thumb_id) $hero_url = wp_get_attachment_image_url((int)$thumb_id, 'full') ?: '';
      }
    }
    
    // Normalize Amazon sizing suffixes if present
    if ($hero_url && strpos($hero_url, 'm.media-amazon.com') !== false) {
      $hero_url = preg_replace('~\._[A-Z0-9_,-]+(?:_)?\.~', '.', $hero_url);
      $hero_url = preg_replace('~\._[A-Z0-9_,-]+$~', '', $hero_url);
    }
    
    $hero_url = esc_url_raw($hero_url);

    
    // ============================================================
    // Overlay (hero-cover contract)
    // ============================================================
    $dim_ratio = max(0, min(100, (int)($a['dimRatio'] ?? 30)));
    $overlay_opacity = $dim_ratio / 100;
    
    $overlay_color    = trim((string)($a['overlayColor'] ?? '#000000')) ?: '#000000';
    $overlay_gradient = trim((string)($a['overlayGradient'] ?? ''));
    
    // If gradient is set, use it. Otherwise fall back to solid color.
    $overlay_bg = ($overlay_gradient !== '') ? $overlay_gradient : $overlay_color;
    



    // Min height
    $min_height = max(240, (int)($a['minHeight'] ?? 620));

    // Cover selection (ROBUST)
    $cover_url = '';
    $cover_source = sanitize_key($a['coverSource'] ?? 'cover_ebook');
    
    if ($cover_source === 'featured_image') {
      $thumb_id = get_post_thumbnail_id($book_id);
      if ($thumb_id) {
        $cover_url = wp_get_attachment_image_url((int)$thumb_id, 'large') ?: '';
      }
    } else {
      // meta may be URL, attachment ID, or array
      $cover_url = $val_to_url(get_post_meta($book_id, $cover_source, true));
    }
    
    $cover_url = esc_url_raw($cover_url);

    // Series permalink (first series term)
    $series_url = '';
    foreach (['series', 'book-series'] as $tax) {
      $terms = get_the_terms($book_id, $tax);
      if (is_array($terms) && !empty($terms) && !empty($terms[0]->term_id)) {
        $link = get_term_link($terms[0]);
        if (!is_wp_error($link)) { $series_url = (string)$link; break; }
      }
    }

    // Buttons
    $buttons = [];
    for ($i = 1; $i <= 3; $i++) {
      $src = sanitize_key($a["btn{$i}Source"] ?? '');
      if ($src === '' || $src === '__none__') continue;
      if ($src === 'series_permalink' && !$series_url) continue;
      $label   = sanitize_text_field($a["btn{$i}Label"] ?? '');
      $variant = sanitize_key($a["btn{$i}Variant"] ?? 'secondary');
      $buttons[] = ['source' => $src, 'label' => $label, 'variant' => $variant];
    }

    $btn_html = mfbb_buttons($book_id, $buttons, [
      'permalink'  => $permalink,
      'series_url' => $series_url,
      'tracking'   => !empty($a['tracking']),
      'origin'     => $event_origin,
      'context'    => 'featured-banner',

      'buttonStyleMode'  => (string)($a['buttonStyleMode'] ?? 'inherit'),
      'btnPrimaryBg'     => (string)($a['btnPrimaryBg'] ?? ''),
      'btnPrimaryText'   => (string)($a['btnPrimaryText'] ?? ''),
      'btnPrimaryBorder' => (string)($a['btnPrimaryBorder'] ?? ''),
      'btnOutlineBg'     => (string)($a['btnOutlineBg'] ?? ''),
      'btnOutlineText'   => (string)($a['btnOutlineText'] ?? ''),
      'btnOutlineBorder' => (string)($a['btnOutlineBorder'] ?? ''),
      'buttonCorners'    => (string)($a['buttonCorners'] ?? 'inherit'),
    ]);

    // Layout / media options (FIX)
    $media_mode = sanitize_key($a['mediaMode'] ?? 'cover');
    if (!in_array($media_mode, ['none', 'cover', 'card'], true)) $media_mode = 'cover';

    $media_side = (($a['mediaSide'] ?? 'right') === 'left') ? 'left' : 'right';

    $classes = ['mfbb', 'mfbb--media-' . $media_side];
    if ($media_mode === 'none') $classes[] = 'mfbb--no-media';
    if (($a['coverCorners'] ?? 'square') === 'rounded') $classes[] = 'has-cover-rounded';

    $btn_corners = (string)($a['buttonCorners'] ?? 'inherit');
    if ($btn_corners === 'rounded') $classes[] = 'has-btn-rounded';
    if ($btn_corners === 'pill')    $classes[] = 'has-btn-pill';

    $text_align = strtolower((string)($a['textAlign'] ?? 'left'));
    if (!in_array($text_align, ['left','center','right'], true)) $text_align = 'left';
    $classes[] = 'is-text-' . $text_align;

    $btn_align = strtolower((string)($a['buttonAlign'] ?? 'center'));
    if (!in_array($btn_align, ['left','center','right'], true)) $btn_align = 'center';
    $classes[] = 'is-btn-' . $btn_align;

    $media_width = max(350, (int)($a['mediaWidth'] ?? 350));

    // IMPORTANT: background-image must be on the root element style
    $style  = 'min-height:' . (int)$min_height . 'px;';
    $style .= '--mfbb-media-width:' . (int)$media_width . 'px;';
    if ($hero_url) {
      $style .= 'background-image:url(' . esc_url($hero_url) . ');';
      $style .= 'background-size:cover;';
      $style .= 'background-position:50% 50%;';
      $style .= 'background-repeat:no-repeat;';
    }

    $wrapper = function_exists('get_block_wrapper_attributes')
      ? get_block_wrapper_attributes([
          'class' => implode(' ', $classes),
          'style' => $style,
        ])
      : 'class="' . esc_attr(implode(' ', $classes)) . '" style="' . esc_attr($style) . '"';

    ob_start(); ?>
      <section
        <?php echo $wrapper; ?>
        data-mf-impression="book_impression"
        data-mf-origin="<?php echo esc_attr($event_origin); ?>"
        data-mf-block="featured-banner"
        data-mf-book-id="<?php echo esc_attr((string)$book_id); ?>"
        data-mf-asin="<?php echo esc_attr((string)$asin); ?>"
        data-mf-page-id="<?php echo esc_attr((string)$page_id); ?>"
        data-mf-page-type="<?php echo esc_attr((string)$page_type); ?>"
      >
        <div class="mfbb__overlay" style="background:<?php echo esc_attr($overlay_bg); ?>;opacity:<?php echo esc_attr($overlay_opacity); ?>;"></div>


        <div class="mfbb__inner">
          <div class="mfbb__body">
            <?php if ($kicker !== ''): ?>
              <div class="mfbb__kicker"><?php echo esc_html($kicker); ?></div>
            <?php endif; ?>

            <?php if ($show_headline && $headline !== ''): ?>
              <<?php echo esc_html($headline_tag); ?> class="mfbb__headline" style="font-size:<?php echo esc_attr((string)$headline_size); ?>px;">
                <?php echo esc_html($headline); ?>
              </<?php echo esc_html($headline_tag); ?>>
            <?php endif; ?>

            <?php if ($subhead !== ''): ?>
              <div class="mfbb__subhead"><?php echo esc_html($subhead); ?></div>
            <?php endif; ?>

            <?php if (!empty($desc_html)): ?>
              <div class="mfbb__desc"
                style="font-size:<?php echo esc_attr((string)$desc_size); ?>px;font-weight:<?php echo esc_attr((string)$desc_weight); ?>;text-transform:<?php echo esc_attr($desc_transform); ?>;">
                <?php echo $desc_html; ?>
              </div>
            <?php endif; ?>


            <?php if (!empty($btn_html)): ?>
              <div class="mfbb__actions"><?php echo $btn_html; ?></div>
            <?php endif; ?>
          </div>

          <?php if ($media_mode !== 'none'): ?>
            <div class="mfbb__media">
              <?php if ($media_mode === 'card' && function_exists('mfb_ui_card')): ?>
                <?php
                  echo mfb_ui_card([
                    'id'        => (int)$book_id,
                    'title'     => $title,
                    'permalink' => $permalink,
                    'image_url' => $cover_url,
                    'show_title'=> false,
                    'button'    => [
                      'text'   => 'See The Book',
                      'url'    => $permalink,
                      'target' => '_self',
                      'origin' => $event_origin,
                    ]
                  ]);
                ?>
              <?php else: ?>
                <?php if ($cover_url): ?>
                  <a
                    class="mfbb__cover-link"
                    href="<?php echo esc_url($permalink); ?>"
                    data-mf-event="<?php echo esc_attr(wp_json_encode($cover_event_payload)); ?>"
                    data-mf-href="<?php echo esc_attr($permalink); ?>"
                    data-mf-destination="<?php echo esc_attr($permalink); ?>"
                    data-mf-cta="cover"
                    data-mf-source="permalink"
                    data-mf-link-type="permalink"
                  >
                    <img class="mfbb__cover" src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async" />
                  </a>
                <?php else: ?>
                  <div class="mfbb__cover --missing" aria-hidden="true"></div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    <?php
    return ob_get_clean();
  }
}
