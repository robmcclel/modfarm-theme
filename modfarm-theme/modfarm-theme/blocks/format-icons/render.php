<?php
if (!function_exists('modfarm_render_format_icons_block')) {
  function modfarm_render_format_icons_block($attributes = [], $content = '', $block = null) {

    // Defaults
    $a = wp_parse_args($attributes, [
      'mode'          => 'auto', // auto | custom
      'showEbook'     => true,
      'showAudio'     => true,
      'showPaperback' => true,
      'showHardcover' => true,
      'size'          => 20,
      'color'         => '',
      'gap'           => 8,
      'align'         => 'left', // left|center|right
      'label'         => '',
    ]);

    // Resolve post context (works in SSR loops and single views)
    $post_id = 0;
    if (is_object($block) && isset($block->context['postId'])) {
      $post_id = intval($block->context['postId']);
    }
    if (!$post_id && get_the_ID()) $post_id = intval(get_the_ID());

    // --- AUTO DETECTION (books) --------------------------------------------
    $autoFlags = [
      'ebook'     => false,
      'audio'     => false,
      'paperback' => false,
      'hardcover' => false,
    ];

    if ($a['mode'] === 'auto' && $post_id) {
      // Look for any known meta/links that imply the format exists
      // (uses your canonical keys from BMS + sales links list)
      $check_any = function(array $keys) use ($post_id) {
        foreach ($keys as $k) {
          $v = get_post_meta($post_id, $k, true);
          if (!empty($v)) return true;
        }
        return false;
      };

      $autoFlags['ebook'] = $check_any([
        'kindle_url','ebook_buy_url','asin_kindle','asin','kobo','googleplay'
      ]);

      $autoFlags['audio'] = $check_any([
        'audible_url','amazon_audio','kobo_audio','googleplay_audio',
        'audiobooks_com','spotify','audio_buy_url','asin_audiobook',
        'audible_asin','audio_sample_url','downpour','librofm'
      ]);

      $autoFlags['paperback'] = $check_any([
        'amazon_paper','barnes_paper','bookshop_paper','bam_paper',
        'paper_buy_url','asin_paperback','target','walmart'
      ]);

      $autoFlags['hardcover'] = $check_any([
        'amazon_hard','barnes_hard','bookshop_hard','bam_hard',
        'hard_buy_url','asin_hardcover','waterstones','indigo','brokenbinding'
      ]);

      // If nothing was detected, fall back to custom toggles (all true by default)
      if (!$autoFlags['ebook'] && !$autoFlags['audio'] && !$autoFlags['paperback'] && !$autoFlags['hardcover']) {
        $autoFlags['ebook']     = !!$a['showEbook'];
        $autoFlags['audio']     = !!$a['showAudio'];
        $autoFlags['paperback'] = !!$a['showPaperback'];
        $autoFlags['hardcover'] = !!$a['showHardcover'];
      }
    }

    // Final visibility (in custom mode, the toggles are source of truth)
    $visible = [
      'ebook'     => ($a['mode'] === 'custom') ? !!$a['showEbook']     : !!$autoFlags['ebook'],
      'audio'     => ($a['mode'] === 'custom') ? !!$a['showAudio']     : !!$autoFlags['audio'],
      'paperback' => ($a['mode'] === 'custom') ? !!$a['showPaperback'] : !!$autoFlags['paperback'],
      'hardcover' => ($a['mode'] === 'custom') ? !!$a['showHardcover'] : !!$autoFlags['hardcover'],
    ];

    // Nothing to show?
    if (!$visible['ebook'] && !$visible['audio'] && !$visible['paperback'] && !$visible['hardcover']) {
      if (current_user_can('edit_posts')) {
        return '<div class="mfi-icons mfi--empty"><em>Format Icons:</em> No formats detected/selected.</div>';
      }
      return '';
    }

    // Inline style vars
    $style = sprintf(
      '--mfi-size:%dpx;--mfi-gap:%dpx;%s',
      max(12, intval($a['size'])),
      max(0,  intval($a['gap'])),
      $a['color'] ? '--mfi-color:' . esc_attr($a['color']) . ';' : ''
    );

    $alignClass = 'mfi--align-' . (in_array($a['align'], ['left','center','right'], true) ? $a['align'] : 'left');

    // SVG icons (stroke-based, inherit currentColor)
   $svg = [
      // eBook — TABLET
      'ebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tablet-icon lucide-tablet"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>',
    
      // Audiobook — HEADPHONES
      'audio' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-headphones-icon lucide-headphones"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>',
    
      // Paperback — OPEN BOOK
      'paperback' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open"><path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>',
    
      // Hardcover — CLOSED BOOK
      'hardcover' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-icon lucide-book"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg>',
    ];

    // Build HTML
    $out  = '<div class="mfi-icons '.$alignClass.'" style="'.esc_attr($style).'">';
    if (!empty($a['label'])) {
      $out .= '<span class="mfi-label">'.esc_html($a['label']).'</span>';
    }
    $map = [
      'ebook'     => 'eBook',
      'audio'     => 'Audiobook',
      'paperback' => 'Paperback',
      'hardcover' => 'Hardcover',
    ];
    foreach (['ebook','audio','paperback','hardcover'] as $key) {
      if (!$visible[$key]) continue;
      $out .= '<span class="mfi mfi--'.$key.'" aria-label="'.esc_attr($map[$key]).'" title="'.esc_attr($map[$key]).'">'.$svg[$key].'</span>';
    }
    $out .= '</div>';

    return $out;
  }
}