<?php
if (!function_exists('modfarm_render_tax_description_block')) {
  function modfarm_render_tax_description_block($attributes = [], $content = '', $block = null) {

    // --- Term archive context ---
    $qo = get_queried_object();
    if (!($qo instanceof WP_Term)) {
      if (current_user_can('edit_posts')) {
        return '<div class="mf-taxdesc mf-taxdesc--admin-note"><em>Tax Description:</em> Displays only on taxonomy archives.</div>';
      }
      return '';
    }

    $term     = $qo;
    $taxonomy = $term->taxonomy;
    $term_id  = (int) $term->term_id;

    // --- Attributes (all defined) ---
    $a = wp_parse_args($attributes, [
      'heading'         => '',
      'layout'          => 'auto',      // auto|vertical|horizontal
      'imageShape'      => 'circle',    // circle|square|rounded
      'imgSize'         => 240,
      'linkToArchive'   => false,
      'showDescription' => true,
      'hideIfEmpty'     => false,
      'accentColor'     => '',
      'textColor'       => '',
    ]);

    $layout      = in_array($a['layout'], ['auto','vertical','horizontal'], true) ? $a['layout'] : 'auto';
    $image_shape = in_array($a['imageShape'], ['circle','square','rounded'], true) ? $a['imageShape'] : 'circle';
    $img_size    = max(80, min(600, (int)$a['imgSize']));

    // --- CSS vars (scoped to this block) ---
    $style_vars = ['--mf-taxdesc-img-size:' . $img_size . 'px'];
    if ($a['accentColor'] !== '') $style_vars[] = '--mf-taxdesc-accent:' . esc_attr($a['accentColor']);
    if ($a['textColor']   !== '') $style_vars[] = '--mf-taxdesc-text:'   . esc_attr($a['textColor']);
    $style_attr = $style_vars ? ' style="' . esc_attr(implode(';', $style_vars)) . '"' : '';

    // --- Helpers ---
    $decode = function($raw){
      if (is_array($raw)) return $raw;
      if (is_string($raw) && $raw !== '' && ($raw[0]==='{'||$raw[0]==='[')) {
        $d = json_decode($raw, true);
        return json_last_error()===JSON_ERROR_NONE ? $d : null;
      }
      return null;
    };

    // --- Image resolver (uses same meta keys, but outputs mf-taxdesc-avatar) ---
    $resolve_img = function($t) use ($decode) {
      $tid = (int) $t->term_id;

      $emit_id = function($id) use ($t) {
        return wp_get_attachment_image(
          (int)$id,
          'medium',
          false,
          [
            'class'    => 'mf-taxdesc-avatar',
            'alt'      => esc_attr($t->name),
            'loading'  => 'lazy',
            'decoding' => 'async'
          ]
        ) ?: '';
      };

      $emit_url = function($url) use ($t) {
        $url = esc_url($url);
        return $url ? '<img class="mf-taxdesc-avatar" src="'.$url.'" alt="'.esc_attr($t->name).'" loading="lazy" decoding="async" />' : '';
      };

      // Primary (preferred)
      $primary = get_term_meta($tid, 'archive_default_image', true);
      if ($primary !== '' && $primary !== null) {
        if (is_numeric($primary)) { if ($o=$emit_id($primary)) return $o; }
        if (is_string($primary) && stripos($primary,'http')===0) { if ($o=$emit_url($primary)) return $o; }
        if ($arr=$decode($primary)) {
          foreach (['id','ID','attachment_id','image_id'] as $k) {
            if (!empty($arr[$k]) && is_numeric($arr[$k])) { if ($o=$emit_id($arr[$k])) return $o; }
          }
          foreach (['url','src'] as $k) {
            if (!empty($arr[$k])) { if ($o=$emit_url($arr[$k])) return $o; }
          }
          if (!empty($arr['sizes']['medium'])) { if ($o=$emit_url($arr['sizes']['medium'])) return $o; }
        }
      }

      // Secondary keys (fallback scan)
      foreach ([
        'archive_default_image_id','archive_default_image_url','archive_display_default',
        'archive_image_id','term_image_id','profile_image_id','image_id','_thumbnail_id'
      ] as $k) {
        $v = get_term_meta($tid, $k, true);
        if (!$v) continue;
        if (is_numeric($v)) { if ($o=$emit_id($v)) return $o; }
        if (is_string($v))  { if ($o=$emit_url($v)) return $o; }
        if ($arr=$decode($v)) {
          foreach (['id','ID','attachment_id'] as $kk) {
            if (!empty($arr[$kk]) && is_numeric($arr[$kk])) { if ($o=$emit_id($arr[$kk])) return $o; }
          }
          if (!empty($arr['url'])) { if ($o=$emit_url($arr['url'])) return $o; }
        }
      }

      // No image found
      return '';
    };

    $img_html = $resolve_img($term);
    $desc     = $a['showDescription'] ? term_description($term_id, $taxonomy) : '';

    // Hide if empty (no image AND no description)
    if ($a['hideIfEmpty']) {
      $has_img  = !empty($img_html);
      $has_desc = !empty(trim(wp_strip_all_tags($desc)));
      if (!$has_img && !$has_desc) return '';
    }

    // --- Layout classes ---
    $classes = ['mf-taxdesc', 'mf-taxdesc--shape-' . $image_shape];
    if ($layout !== 'auto') $classes[] = 'mf-taxdesc--layout-' . $layout;

    // Link target (optional)
    $link_open  = '';
    $link_close = '';
    if (!empty($a['linkToArchive'])) {
      $url = get_term_link($term, $taxonomy);
      if (!is_wp_error($url)) {
        $link_open  = '<a class="mf-taxdesc__image-link" href="' . esc_url($url) . '">';
        $link_close = '</a>';
      }
    }

    // --- Render ---
    ob_start(); ?>
      <section class="<?php echo esc_attr(implode(' ', $classes)); ?>"<?php echo $style_attr; ?>>
        <?php if ($a['heading'] !== ''): ?>
          <h2 class="mf-taxdesc__heading"><?php echo esc_html($a['heading']); ?></h2>
        <?php endif; ?>

        <div class="mf-taxdesc__inner">

          <?php if (!empty($img_html)): ?>
            <div class="mf-taxdesc__image">
              <?php echo $link_open; ?>
              <?php echo $img_html; ?>
              <?php echo $link_close; ?>
            </div>
          <?php else: ?>
            <div class="mf-taxdesc__image">
              <div class="mf-taxdesc-avatar mf-taxdesc-avatar--fallback" aria-hidden="true">
                <?php echo esc_html(mb_substr($term->name, 0, 1)); ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="mf-taxdesc__content">
            <?php if (!empty($desc)): ?>
              <div class="mf-taxdesc__description"><?php echo wp_kses_post($desc); ?></div>
            <?php endif; ?>
          </div>

        </div>
      </section>
    <?php
    return trim(ob_get_clean());
  }
}