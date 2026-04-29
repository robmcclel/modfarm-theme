<?php
if (!defined('ABSPATH')) { exit; }

require_once get_stylesheet_directory() . '/template-parts/book/ui.php';

function modfarm_render_book_page_audio_block( $attributes, $content, $block ) {
    // Shared CSS
    wp_enqueue_style('modfarm-book-cards');

    $post_id = isset($block->context['postId']) ? (int)$block->context['postId'] : (int)get_the_ID();

    // Attributes (unchanged across blocks, plus heading size/weight & alignment for this block)
    $a = wp_parse_args($attributes, [
        'titleText'        => 'Listen To A Sample',
        'samplebtn-text'   => 'Play Sample',
        'samplebtn-bg'     => '',
        'samplebtn-fg'     => '',
        'samplebtn-border' => '',
        'sample-shape'     => 'pill', // square|rounded|pill
        'button-align'     => 'center',   // left|center|right

        // heading typography for this block
        'label-size'       => '16px',       // e.g. "1rem", "18px"
        'label-weight'     => '600',       // e.g. "600", "bold"

        'className'        => '',
    ]);

    // === EXACT SAME META KEYS AS OTHER BLOCKS ===
    $audio_mode   = 'auto'; // this block always auto
    $audio_embed  = (string) get_post_meta($post_id, 'audio_player_embed', true);
    $audio_sample = (string) get_post_meta($post_id, 'audio_sample_url',   true);
    $audible_asin = (string) get_post_meta($post_id, 'audible_asin',       true);
    $amazon_asin  = (string) get_post_meta($post_id, 'asin_kindle',        true);
    $audio_date   = get_post_meta($post_id, 'audiobook_publication_date',  true) ?: null;

    // For tracking parity with cards
    $title  = get_the_title($post_id);
    $series = (string) get_post_meta($post_id, 'series_name', true);

    // Build audio HTML via helper (same decision tree as card.php: embed → sample → constructed)
    $audio_html = mfb_ui_audio([
        'id'           => $post_id,
        'title'        => $title,
        'series'       => $series,
        'format'       => 'audio',
        'origin'       => 'book-page-audio',
        'tracker'      => '',

        'audio_mode'   => $audio_mode,
        'audio_embed'  => $audio_embed,   // ✅ correct key
        'audio_sample' => $audio_sample,  // ✅ correct key
        'audible_asin' => $audible_asin,  // ✅ same meta key as others
        'amazon_asin'  => $amazon_asin,   // ✅ same meta key as others
        'audio_date'   => $audio_date,    // ✅ correct key
        'store_tld'    => 'com',
    ]);

    // If helper returns nothing (no valid audio), render nothing on FE
    if ($audio_html === '') {
        return '';
    }

    // Heading label (optional) with size/weight and alignment
    $heading_html = '';
    $heading = trim((string)$a['titleText']);
    if ($heading !== '') {
        $h_styles = [];
        if ($a['label-size']   !== '') $h_styles[] = 'font-size:' . esc_attr($a['label-size']);
        if ($a['label-weight'] !== '') $h_styles[] = 'font-weight:' . esc_attr($a['label-weight']);
        $h_style_attr = $h_styles ? ' style="' . esc_attr(implode(';', $h_styles)) . '"' : '';
        $heading_html = sprintf(
            '<div class="mfb-audio__heading mfb-audio--align-%s"%s>%s</div>',
            esc_attr($a['button-align'] ?: 'left'),
            $h_style_attr,
            esc_html($heading)
        );
    }

    // Color vars cascade to the .mfb-audio element inside $audio_html
    $wrap_style = [];
    if ($a['samplebtn-bg']     !== '') $wrap_style[] = '--mfb-sample-bg:' . $a['samplebtn-bg'];
    if ($a['samplebtn-fg']     !== '') $wrap_style[] = '--mfb-sample-fg:' . $a['samplebtn-fg'];
    if ($a['samplebtn-border'] !== '') $wrap_style[] = '--mfb-sample-border:' . $a['samplebtn-border'];
    $wrap_style_attr = $wrap_style ? ' style="' . esc_attr(implode(';', $wrap_style)) . '"' : '';

    $align_class = 'mfb-audio--align-' . sanitize_html_class($a['button-align'] ?: 'left');

    /**
     * ===== ModFarm Core Events tracking (click) =====
     * We attach data-mf-event to the wrapper so any click inside (button, player, etc.)
     * gets captured by the global click listener.
     *
     * We also provide explicit data-mf-href/data-mf-destination so the JS can record
     * something meaningful even if the clicked element isn't an <a>.
     */
    $track_href = '';
    if ($audio_sample !== '') {
        $track_href = $audio_sample; // best “destination” for sample play
    } elseif ($audible_asin !== '') {
        // Fallback: track Audible product page when sample URL isn't present
        $track_href = 'https://www.audible.com/pd/' . rawurlencode($audible_asin);
    } elseif ($amazon_asin !== '') {
        // Last fallback
        $track_href = 'https://www.amazon.com/dp/' . rawurlencode($amazon_asin);
    }

    $event_payload = [
        'event_type'       => 'click',             // keep as click so it shows in Top Clicks MVP
        'origin'           => 'book_page_audio',
        'block'            => 'book-page-audio',
        'meta_key'         => 'audio_sample_url',  // consistent key for reporting
        'post_id'          => $post_id,
        'book_title'       => is_string($title) ? $title : '',
        'audible_asin'     => $audible_asin ?: '',
        'amazon_asin'      => $amazon_asin ?: '',
        'has_embed'        => $audio_embed !== '' ? 1 : 0,
        'has_sample_url'   => $audio_sample !== '' ? 1 : 0,
    ];

    $mf_event_attr = esc_attr( wp_json_encode($event_payload) );
    $mf_href_attr  = $track_href ? esc_url($track_href) : '';
    $mf_dest_attr  = $track_href ? esc_url($track_href) : '';
    $mf_block_uid = 'mfb_audio_' . $post_id . '_' . wp_generate_uuid4();


    ob_start(); ?>
      <div
        class="<?php echo esc_attr(trim($align_class . ' ' . ($a['className'] ?: ''))); ?>"
        <?php echo $wrap_style_attr; ?>
        data-mf-event="<?php echo $mf_event_attr; ?>"
        data-mf-audio-block="<?php echo esc_attr($mf_block_uid); ?>"
        <?php if ($mf_href_attr): ?>data-mf-href="<?php echo esc_attr($mf_href_attr); ?>"<?php endif; ?>
        <?php if ($mf_dest_attr): ?>data-mf-destination="<?php echo esc_attr($mf_dest_attr); ?>"<?php endif; ?>
      >
        <?php echo $heading_html; ?>
        <?php
          // Shape must be an ancestor of .mfb-audio to match book-cards.css
          if ($a['sample-shape'] === 'rounded') echo '<div class="mfb-sample--rounded">';
          if ($a['sample-shape'] === 'pill')    echo '<div class="mfb-sample--pill">';
          echo $audio_html;
          if ($a['sample-shape'] === 'rounded') echo '</div>';
          if ($a['sample-shape'] === 'pill')    echo '</div>';
        ?>
      </div>
    <?php
    
    $event_payload_play = $event_payload;
    $event_payload_play['event_type'] = 'audio_play'; // richer type
    $event_payload_play['meta_key']   = 'audio_sample_url';
    
    $event_payload_click = $event_payload;
    $event_payload_click['event_type'] = 'click'; // MVP parity, charts already built for click
    $event_payload_click['meta_key']   = 'audio_sample_url';
    
    wp_add_inline_script('modfarm-core-events', '(function(){
      var uid = ' . wp_json_encode($mf_block_uid) . ';
      var wrap = document.querySelector("[data-mf-audio-block=\'" + uid + "\']");
      if (!wrap) return;
    
      // Find audio element inside this block
      var audio = wrap.querySelector("audio");
      if (!audio) return;
    
      var fired = { play:false, click:false };
    
      function send(detail){
        // Preferred: if your core exposes a function
        if (window.ModFarmCore && typeof window.ModFarmCore.trackEvent === "function") {
          window.ModFarmCore.trackEvent(detail.event_type || "click", detail);
          return;
        }
        // Fallback: if your core listens for this custom event
        window.dispatchEvent(new CustomEvent("mf-track", { detail: detail }));
      }
    
      audio.addEventListener("play", function(){
        // Always log an audio_play (once per page load)
        if (!fired.play) {
          fired.play = true;
          var d = ' . wp_json_encode($event_payload_play) . ';
          d.clicked_href = audio.currentSrc || "";
          d.destination_url = audio.currentSrc || "";
          send(d);
        }
    
        // Also log a click (once) so it shows up in your current MVP click charts
        if (!fired.click) {
          fired.click = true;
          var c = ' . wp_json_encode($event_payload_click) . ';
          c.clicked_href = audio.currentSrc || "";
          c.destination_url = audio.currentSrc || "";
          send(c);
        }
      }, { passive:true });
    
    })();', 'after');

    
    return ob_get_clean();
}