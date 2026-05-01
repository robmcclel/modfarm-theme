<?php
if (!function_exists('modfarm_render_content_slot_block')) {
  function modfarm_render_content_slot_block($attributes = [], $content = '', $block = null) {
    $a = wp_parse_args($attributes, [
      'slot'                   => 'main',
      'acceptImport'           => true,
      'autofillPostContent'    => true,
      'fallbackToExcerpt'      => true,
      'applyTheContentFilters' => true,
    ]);

    $slot = (string) ($a['slot'] ?? 'main');

    // Treat trivial InnerBlocks as empty (avoids <p></p> placeholder)
    $is_empty = function ($html) {
      if ($html === '' || $html === null) return true;
      $clean = preg_replace('/<!--.*?-->/s', '', (string)$html);
      $clean = str_replace("\xC2\xA0", ' ', $clean);
      $clean = preg_replace('/&nbsp;?/i', ' ', $clean);
      $clean = trim(strip_tags(html_entity_decode($clean, ENT_QUOTES|ENT_HTML5, 'UTF-8')));
      return $clean === '';
    };

    if (!$is_empty($content)) {
      return '<div class="mf-content-slot" data-slot="'.esc_attr($slot).'">'.$content.'</div>';
    }

    // Figure out post in context
    $post_id = 0;
    if (is_object($block) && isset($block->context['postId'])) $post_id = (int)$block->context['postId'];
    if (!$post_id && get_the_ID()) $post_id = (int) get_the_ID();

    $html = '';

    if ($post_id && !empty($a['acceptImport']) && !empty($a['autofillPostContent'])) {

      // --- 1) Prefer explicit body meta saved by Composer/Importer -------------
      // These keys let us bypass the PPB tree entirely.
      $body_meta = get_post_meta($post_id, '_mfc_body', true);
      if ($body_meta === '') $body_meta = get_post_meta($post_id, '_mfc_body_html', true); // alt
      if ($body_meta !== '') {
        $html = !empty($a['applyTheContentFilters']) ? apply_filters('the_content', $body_meta) : wp_kses_post($body_meta);
      }

      // --- 2) If no meta, render post_content WITHOUT any Content Slot blocks ---
      if ($html === '') {
        $raw = (string) get_post_field('post_content', $post_id);

        // Guard against re-entrancy (editor freeze)
        static $RENDERING_SLOT = false;
        if ($RENDERING_SLOT) {
          $raw = ''; // prevent recursion; fall through to excerpt
        } else {
          $RENDERING_SLOT = true;

          if ($raw !== '') {
            $blocks = parse_blocks($raw);
            if (function_exists('modfarm_ppb_normalize_parsed_blocks')) {
              $blocks = modfarm_ppb_normalize_parsed_blocks($blocks);
            }

            // Recursively drop any modfarm/content-slot blocks
            $strip_slots = function(array $blocks) use (&$strip_slots) {
              $out = [];
              foreach ($blocks as $b) {
                if (!is_array($b)) {
                  continue;
                }

                $name = isset($b['blockName']) ? $b['blockName'] : null;
                if ($name === 'modfarm/content-slot') {
                  // Skip the slot entirely (and its inner content), to avoid recursion
                  continue;
                }
                $b['attrs'] = is_array($b['attrs'] ?? null) ? $b['attrs'] : [];
                $b['innerBlocks'] = !empty($b['innerBlocks']) && is_array($b['innerBlocks']) ? $b['innerBlocks'] : [];
                $b['innerHTML'] = isset($b['innerHTML']) && is_string($b['innerHTML']) ? $b['innerHTML'] : '';
                $b['innerContent'] = !empty($b['innerContent']) && is_array($b['innerContent'])
                  ? array_values(array_filter($b['innerContent'], static function ($item) {
                    return $item === null || is_string($item);
                  }))
                  : [];

                if (!empty($b['innerBlocks'])) {
                  $b['innerBlocks'] = $strip_slots($b['innerBlocks']);
                }
                $out[] = $b;
              }
              return $out;
            };

            $filtered = $strip_slots($blocks);
            $serialized = function_exists('serialize_blocks') ? serialize_blocks($filtered) : '';
            if ($serialized !== '') {
              // Render the remaining tree (theme header/footer blocks may still appear, but no slot)
              $html = do_blocks($serialized);
            }
          }

          $RENDERING_SLOT = false;
        }
      }

      // --- 3) Final fallback: excerpt -----------------------------------------
      if ($html === '' && !empty($a['fallbackToExcerpt'])) {
        $ex = get_the_excerpt($post_id);
        if (!$ex) {
          $raw_ex = wp_strip_all_tags(get_post_field('post_content', $post_id), true);
          $ex     = wp_trim_words($raw_ex, 40, '…');
        }
        if ($ex) $html = '<p>'.esc_html($ex).'</p>';
      }
    }

    if ($html === '' && is_admin()) {
      $html = '<div class="mf-content-slot mf--admin-note">Content Slot “'.esc_html($slot).'” is empty. Add blocks or let Composer/Importer provide _mfc_body.</div>';
    }

    return '<div class="mf-content-slot" data-slot="'.esc_attr($slot).'">'.$html.'</div>';
  }
}
