<?php
if (!defined('ABSPATH')) exit;

/**
 * MEDIA: cover image (linked) + primary CTA button
 * - Adds SmartLinks wrapping via mfc_smartlinks_wrap_url($destination, $meta_key) (if available)
 * - Adds ModFarm Core Events payload via data-mf-event (JSON) + data-mf-href + data-mf-destination
 * - Keeps legacy data-* attributes for backward compatibility
 */
function mfb_ui_media(array $args): string {
  $a = wp_parse_args($args, [
    'title'   => '',
    'link'    => '',
    'image'   => '',
    'aspect'  => '2 / 3',

    // primary button
    'btn_text' => '',
    'btn_url'  => '',
    'btn_tgt'  => '_self',
    'btn_bg'   => '',
    'btn_fg'   => '',
    'origin'   => '',
    'tracker'  => '',
    'series'   => '',
    'format'   => '',
    'id'       => 0,

    // NEW: meta key/source for SmartLinks + tracking context
    'meta_key' => '',
  ]);

  $id       = (int)($a['id'] ?? 0);
  $title    = (string)($a['title'] ?? '');
  $origin   = (string)($a['origin'] ?? '');
  $tracker  = (string)($a['tracker'] ?? '');
  $series   = (string)($a['series'] ?? '');
  $format   = (string)(($a['format'] ?? '') ?: '');
  $meta_key = (string)($a['meta_key'] ?? '');

  // ---- COVER click payload (internal)
  $cover_destination = (string)($a['link'] ?? '');
  $cover_href        = $cover_destination;

  $cover_payload = [
    'event_type'     => 'click',
    'event_category' => 'book_card',
    'origin'         => ($origin !== '' ? $origin : 'book_card'),
    'book_id'        => $id,
    'meta_key'       => 'permalink',
    'label'          => 'cover',
    'series'         => $series,
    'format'         => $format,
    'tracker'        => $tracker,
    'smartlinks'     => 'none',
  ];
  $cover_data = esc_attr(wp_json_encode($cover_payload));

  // ---- BUTTON: destination vs href + SmartLinks wrap
  $btn_destination = (string)($a['btn_url'] ?? '');
  $btn_href        = $btn_destination;
  $smart_wrapped   = 0;

  if ($btn_destination !== '' && function_exists('mfc_smartlinks_wrap_url')) {
    $mk = ($meta_key !== '' ? $meta_key : 'card_button');
    $maybe = mfc_smartlinks_wrap_url($btn_destination, $mk);
    if (is_string($maybe) && $maybe !== '' && $maybe !== $btn_destination) {
      $btn_href = $maybe;
      $smart_wrapped = 1;
    }
  }

  $btn_label = (string)($a['btn_text'] ?? '');
  $btn_payload = [
    'event_type'     => 'click',
    'event_category' => 'book_card_button',
    'origin'         => ($origin !== '' ? $origin : 'book_card'),
    'book_id'        => $id,
    'meta_key'       => ($meta_key !== '' ? $meta_key : 'card_button'),
    'label'          => wp_strip_all_tags($btn_label),
    'series'         => $series,
    'format'         => $format,
    'tracker'        => $tracker,
    'button_style'   => 'primary',
    'smartlinks'     => ($smart_wrapped ? 'genius_quickbuild' : 'none'),
  ];
  $btn_data = esc_attr(wp_json_encode($btn_payload));

  ob_start(); ?>
  <div class="mfb-media">
    <?php if (!empty($a['image']) && $cover_href !== ''): ?>
      <a class="mfb-image"
         href="<?php echo esc_url($cover_href); ?>"
         style="aspect-ratio: <?php echo esc_attr((string)$a['aspect']); ?>;"
         data-mf-event="<?php echo $cover_data; ?>"
         data-mf-href="<?php echo esc_attr($cover_href); ?>"
         data-mf-destination="<?php echo esc_attr($cover_destination); ?>">
        <img src="<?php echo esc_url((string)$a['image']); ?>"
             alt="<?php echo esc_attr($title); ?>"
             loading="lazy"
             decoding="async" />
      </a>
    <?php endif; ?>

    <?php if ($btn_destination !== ''): ?>
      <a class="mfb-button book-card-button"
         href="<?php echo esc_url($btn_href); ?>"
         target="<?php echo esc_attr((string)$a['btn_tgt']); ?>"
         style="<?php
           echo !empty($a['btn_bg']) ? 'background-color:' . esc_attr((string)$a['btn_bg']) . ';border-color:' . esc_attr((string)$a['btn_bg']) . ';' : '';
           echo !empty($a['btn_fg']) ? 'color:' . esc_attr((string)$a['btn_fg']) . ';' : '';
         ?>"
         data-mf-event="<?php echo $btn_data; ?>"
         data-mf-href="<?php echo esc_attr($btn_href); ?>"
         data-mf-destination="<?php echo esc_attr($btn_destination); ?>"

         data-event="book_click"
         data-origin="<?php echo esc_attr($origin); ?>"
         data-label="<?php echo esc_attr($title); ?>"
         data-series="<?php echo esc_attr($series); ?>"
         data-format="<?php echo esc_attr($format); ?>"
         data-book-id="<?php echo esc_attr($id); ?>"
         data-tracker="<?php echo esc_attr($tracker); ?>">
        <?php echo esc_html($btn_label); ?>
      </a>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
}


/**
 * AUDIO: decides UI (sample / constructed) and renders markup for cards + blocks.
 * - Adds SmartLinks wrapping for constructed URL (if available)
 * - Adds ModFarm Core Events payload via data-mf-event (JSON) + data-mf-href + data-mf-destination when href exists
 * - Keeps legacy data-* attributes for backward compatibility
 */
function mfb_ui_audio(array $args): string {
  $a = wp_parse_args($args, [
    'id'        => 0,
    'title'     => '',
    'series'    => '',
    'format'    => '',
    'origin'    => '',
    'tracker'   => '',

    // Modes: auto|player|sample|off
    'audio_mode'   => 'auto',
    'audio_sample' => '',      // mp3 URL
    'audio_embed'  => '',      // legacy / ignored
    'audible_asin' => '',
    'amazon_asin'  => '',
    'audio_date'   => null,    // 'Y-m-d' or null
    'store_tld'    => 'com',
  ]);

  $id      = (int)($a['id'] ?? 0);
  $title   = (string)($a['title'] ?? '');
  $series  = (string)($a['series'] ?? '');
  $origin  = (string)($a['origin'] ?? '');
  $tracker = (string)($a['tracker'] ?? '');

  // 1) Publication gate
  $audio_allowed = true;
  if (!empty($a['audio_date'])) {
    $today = current_time('Y-m-d');
    if ((string)$a['audio_date'] > $today) {
      $audio_allowed = false;
    }
  }

  // 2) Constructed player URL (Audible/Amazon)
  $constructed_url = null;
  if (function_exists('modfarm_get_audio_player_url')) {
    $constructed_url = modfarm_get_audio_player_url(
      (string)$a['audible_asin'],
      (string)$a['amazon_asin'],
      (string)$a['store_tld']
    );
  }

  $has_sample    = !empty($a['audio_sample']);
  $has_construct = !empty($constructed_url);

  // 3) Choose UI variant
  $audio_ui = null;

  if ($audio_allowed && ($a['audio_mode'] ?? 'auto') !== 'off') {
    switch ($a['audio_mode']) {
      case 'sample':
        $audio_ui = $has_sample ? 'sample' : null;
        break;

      case 'player':
        $audio_ui = $has_sample ? 'sample' : ($has_construct ? 'constructed' : null);
        break;

      case 'auto':
      default:
        $audio_ui = $has_sample ? 'sample' : ($has_construct ? 'constructed' : null);
        break;
    }
  }

  if (!$audio_ui) return '';

  // ---- For constructed link: destination vs href + SmartLinks wrap
  $destination   = ($audio_ui === 'constructed') ? (string)$constructed_url : '';
  $href          = $destination;
  $smart_wrapped = 0;

  if ($destination !== '' && function_exists('mfc_smartlinks_wrap_url')) {
    // Let Core decide eligibility (Audible excluded there, per your comment)
    $maybe = mfc_smartlinks_wrap_url($destination, 'audio_player_url');
    if (is_string($maybe) && $maybe !== '' && $maybe !== $destination) {
      $href = $maybe;
      $smart_wrapped = 1;
    }
  }

  // ---- Event payload
  $payload = [
    'event_type'     => 'click',
    'event_category' => 'book_audio',
    'origin'         => ($origin !== '' ? $origin : 'book_card'),
    'book_id'        => $id,
    'meta_key'       => ($audio_ui === 'constructed') ? 'audio_player_url' : 'audio_sample_url',
    'label'          => 'Play Sample',
    'series'         => $series,
    'format'         => 'audio',
    'tracker'        => $tracker,
    'smartlinks'     => ($smart_wrapped ? 'genius_quickbuild' : 'none'),
  ];
  $data_mf_event = esc_attr(wp_json_encode($payload));

  ob_start(); ?>
  <div class="mfb-audio">
    <?php if ($audio_ui === 'constructed' && $destination !== ''): ?>
      <a class="mfb-button mfb-audio-cta"
         href="<?php echo esc_url($href); ?>"
         target="_blank" rel="noopener"
         data-mf-event="<?php echo $data_mf_event; ?>"
         data-mf-href="<?php echo esc_attr($href); ?>"
         data-mf-destination="<?php echo esc_attr($destination); ?>"

         data-event="book_audio_play"
         data-origin="<?php echo esc_attr($origin); ?>"
         data-label="<?php echo esc_attr($title); ?>"
         data-series="<?php echo esc_attr($series); ?>"
         data-format="audio"
         data-book-id="<?php echo esc_attr($id); ?>"
         data-tracker="<?php echo esc_attr($tracker); ?>">
        <?php esc_html_e('Play Sample', 'modfarm'); ?>
      </a>

    <?php elseif ($audio_ui === 'sample' && $has_sample): ?>
      <details class="mfb-audio-reveal">
        <summary class="mfb-button mfb-audio-cta"
                 data-mf-event="<?php echo $data_mf_event; ?>"

                 data-event="book_audio_play"
                 data-origin="<?php echo esc_attr($origin); ?>"
                 data-label="<?php echo esc_attr($title); ?>"
                 data-series="<?php echo esc_attr($series); ?>"
                 data-format="audio"
                 data-book-id="<?php echo esc_attr($id); ?>"
                 data-tracker="<?php echo esc_attr($tracker); ?>"
                 aria-label="<?php esc_attr_e('Play audio sample', 'modfarm'); ?>">
          <?php esc_html_e('Play Sample', 'modfarm'); ?>
        </summary>
        <div class="mfb-audio-panel">
          <?php
          echo wp_audio_shortcode([
            'src' => esc_url((string)$a['audio_sample']),
          ]);
          ?>
        </div>
      </details>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
}

/**
 * TITLE (span.mfb-title) and SERIES (span.mfb-series)
 */
function mfb_ui_title(string $title, bool $show = true): string {
  if (!$show || $title === '') return '';
  return '<span class="mfb-title">' . esc_html($title) . '</span>';
}

function mfb_ui_series(string $series, $pos = '', string $vol_text = 'Book'): string {
  if ($series === '') return '';
  $suffix = ($pos !== '' ? ' ' . esc_html($vol_text) . ' ' . esc_html($pos) : '');
  return '<span class="mfb-series">' . esc_html($series) . $suffix . '</span>';
}

function mfb_ui_author(string $author, bool $show = true): string {
  if (!$show || $author === '') return '';
  return '<span class="mfb-author">' . esc_html($author) . '</span>';
}

function mfb_ui_pubdate(string $label, bool $show = true): string {
  if (!$show || $label === '') return '';
  return '<span class="mfb-pubdate">' . esc_html($label) . '</span>';
}

/**
 * Blurb can allow basic inline HTML.
 */
function mfb_ui_blurb(string $blurb, bool $show = true): string {
  $blurb = trim((string)$blurb);
  if (!$show || $blurb === '') return '';
  return '<div class="mfb-blurb">' . wp_kses_post(wpautop($blurb)) . '</div>';
}


/**
 * CARD COMPOSER — stitches atoms together
 * NOTE: Passes button meta_key/source down to UI so SmartLinks wrapping matches other blocks.
 */
function mfb_ui_card(array $card): string {
  $id        = (int)($card['id'] ?? 0);
  $title     = (string)($card['title'] ?? '');
  $link      = (string)($card['permalink'] ?? '');
  $image     = (string)($card['image_url'] ?? '');
  $aspect    = (string)($card['aspect'] ?? '2 / 3');
  $format    = $card['format'] ?? null;

  $series    = (string)($card['series_name'] ?? '');
  $pos       = (string)($card['series_position'] ?? '');
  $vol_text  = (string)($card['volume_text'] ?? 'Book');

  $button    = (isset($card['button']) && is_array($card['button'])) ? $card['button'] : [];
  $btn_text  = (string)($button['text']   ?? __('See The Book', 'modfarm'));
  $btn_url   = (string)($button['url']    ?? $link);
  $btn_tgt   = (string)($button['target'] ?? '_self');
  $btn_bg    = (string)($button['bg']     ?? '');
  $btn_fg    = (string)($button['fg']     ?? '');
  $origin    = (string)($button['origin'] ?? 'multi-tax');
  $tracker   = (string)($button['tracker']?? '');

  // Meta key/source for SmartLinks + event context (best-effort, non-breaking)
  $btn_meta_key = '';
  if (isset($button['meta_key']) && is_string($button['meta_key'])) $btn_meta_key = $button['meta_key'];
  elseif (isset($button['source']) && is_string($button['source'])) $btn_meta_key = $button['source'];
  elseif (isset($button['key']) && is_string($button['key']))       $btn_meta_key = $button['key'];

  $show_title = isset($card['show_title']) ? (bool)$card['show_title'] : true;

  $audio_mode   = (string)($card['audio_mode'] ?? 'auto');
  $audio_embed  = (string)($card['audio_player_embed'] ?? '');
  $audio_sample = (string)($card['audio_sample_url']   ?? '');
  $audible_asin = (string)($card['audible_asin']       ?? '');
  $amazon_asin  = (string)($card['amazon_asin']        ?? '');
  $audio_date   = $card['audiobook_publication_date'] ?? null;

  $author_name    = (string)($card['author_name'] ?? '');
  $pub_date_label = (string)($card['pub_date_label'] ?? '');
  $blurb          = (string)($card['blurb'] ?? '');

  $show_author = isset($card['show_author']) ? (bool)$card['show_author'] : false;
  $show_date   = isset($card['show_pub_date']) ? (bool)$card['show_pub_date'] : false;
  $show_blurb  = isset($card['show_blurb']) ? (bool)$card['show_blurb'] : false;

  ob_start(); ?>
  <article class="mfb-card<?php
    $has_audio = false;
    if ($audio_mode !== 'off') {
      $today = current_time('Y-m-d');
      $allowed = (!$audio_date || (string)$audio_date <= $today);
      if ($allowed) {
        if ($audio_mode === 'player') $has_audio = (!empty($audio_embed) || !empty($audible_asin) || !empty($amazon_asin));
        elseif ($audio_mode === 'sample') $has_audio = !empty($audio_sample);
        elseif ($audio_mode === 'auto') $has_audio = (!empty($audio_embed) || !empty($audio_sample) || !empty($audible_asin) || !empty($amazon_asin));
      }
    }
    echo $has_audio ? ' has-audio' : '';
  ?>" data-book-id="<?php echo esc_attr($id); ?>">

    <?php echo mfb_ui_media([
      'title'    => $title,
      'link'     => $link,
      'image'    => $image,
      'aspect'   => $aspect,
      'btn_text' => $btn_text,
      'btn_url'  => $btn_url,
      'btn_tgt'  => $btn_tgt,
      'btn_bg'   => $btn_bg,
      'btn_fg'   => $btn_fg,
      'origin'   => $origin,
      'tracker'  => $tracker,
      'series'   => $series,
      'format'   => ($format ?: ''),
      'id'       => $id,
      'meta_key' => (string)$btn_meta_key,
    ]); ?>

    <?php echo mfb_ui_audio([
      'id'           => $id,
      'title'        => $title,
      'series'       => $series,
      'format'       => 'audio',
      'origin'       => $origin,
      'tracker'      => $tracker,
      'audio_mode'   => $audio_mode,
      'audio_embed'  => $audio_embed,
      'audio_sample' => $audio_sample,
      'audible_asin' => $audible_asin,
      'amazon_asin'  => $amazon_asin,
      'audio_date'   => $audio_date,
      'store_tld'    => 'com',
    ]); ?>

    <?php echo mfb_ui_title($title, $show_title); ?>
    <?php echo mfb_ui_series($series, $pos, $vol_text); ?>
    <?php echo mfb_ui_author($author_name, $show_author); ?>
    <?php echo mfb_ui_blurb($blurb, $show_blurb); ?>
    <?php echo mfb_ui_pubdate($pub_date_label, $show_date); ?>

  </article>
  <?php
  return ob_get_clean();
}