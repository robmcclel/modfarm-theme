<?php
if (!function_exists('modfarm_render_creator_credit_block')) {
  function modfarm_render_creator_credit_block($attributes, $content = '', $block = null) {
    // --- Post context --------------------------------------------------------
    $post_id = 0;
    if (is_object($block) && isset($block->context['postId'])) {
      $post_id = intval($block->context['postId']);
    }
    if (!$post_id && get_the_ID()) $post_id = intval(get_the_ID());
    if (!$post_id) {
      if (current_user_can('edit_posts')) {
        return '<div class="mfc-team mfc--admin-note"><em>Creator Credit:</em> No post context available.</div>';
      }
      return '';
    }

    // --- Attributes ----------------------------------------------------------
    $heading     = trim((string)($attributes['heading'] ?? ''));
    $tax         = trim((string)($attributes['effectiveTax'] ?? $attributes['taxonomy'] ?? 'book-author'));
    $customTax   = trim((string)($attributes['customTax'] ?? ''));
    if ($tax === '__custom__') $tax = $customTax;
    $tax         = sanitize_key($tax);

    $layout      = in_array(($attributes['layout'] ?? 'auto'), ['auto','vertical','horizontal'], true)
                     ? $attributes['layout'] : 'auto';
    $image_shape = ($attributes['imageShape'] ?? 'circle') === 'square' ? 'square' : 'circle';
    $img_size    = max(80, min(600, intval($attributes['imgSize'] ?? 240)));

    $link_name   = !empty($attributes['linkToArchive']);
    $show_desc   = !empty($attributes['showDescription']);
    $hide_empty  = !empty($attributes['hideIfEmpty']);
    $term_id     = intval($attributes['termId'] ?? 0); // 👈 this is key

    $accentColor = trim((string)($attributes['accentColor'] ?? ''));
    $textColor   = trim((string)($attributes['textColor'] ?? ''));

    // --- Taxonomy sanity -----------------------------------------------------
    if (!$tax || !taxonomy_exists($tax)) {
      if (current_user_can('edit_posts') && !$hide_empty) {
        return '<div class="mfc-team mfc--admin-note"><em>Creator Credit:</em> Taxonomy <code>' .
               esc_html($tax ?: '(none)') . '</code> does not exist.</div>';
      }
      return '';
    }

    // --- Get attached terms --------------------------------------------------
    $terms = wp_get_post_terms($post_id, $tax);
    if (is_wp_error($terms) || empty($terms)) {
      if (current_user_can('edit_posts') && !$hide_empty) {
        return '<div class="mfc-team mfc--admin-note"><em>Creator Credit:</em> No terms attached for <code>' .
               esc_html($tax) . '</code>.</div>';
      }
      return '';
    }

    // --- Pick the selected term ---------------------------------------------
    $term = null;
    if ($term_id) {
      foreach ($terms as $t) {
        if ((int)$t->term_id === $term_id) {
          $term = $t;
          break;
        }
      }
    }
    if (!$term) $term = $terms[0]; // fallback to first

    // --- Role label ----------------------------------------------------------
    $tax_obj    = get_taxonomy($tax);
    $role_label = $tax_obj ? ($tax_obj->labels->singular_name ?: $tax_obj->label ?: ucfirst($tax)) : ucfirst($tax);

    // --- CSS vars ------------------------------------------------------------
    $style_vars = ['--mfc-img-size:' . $img_size . 'px'];
    if ($accentColor !== '') $style_vars[] = '--mfc-accent:' . esc_attr($accentColor);
    if ($textColor !== '')   $style_vars[] = '--mfc-text:'   . esc_attr($textColor);
    $style_attr = $style_vars ? ' style="' . esc_attr(implode(';', $style_vars)) . '"' : '';

    // --- Image resolver ------------------------------------------------------
    $resolve_img = function($t) {
      $tid = $t->term_id;
      $emit_id = function($id) use ($t) {
        return wp_get_attachment_image(
          intval($id), 'medium', false,
          ['class'=>'mfc-headshot','alt'=>esc_attr($t->name),'loading'=>'lazy','decoding'=>'async']
        ) ?: '';
      };
      $emit_url = function($url) use ($t) {
        $url = esc_url($url);
        return $url ? '<img class="mfc-headshot" src="'.$url.'" alt="'.esc_attr($t->name).'" loading="lazy" decoding="async" />' : '';
      };
      $decode = function($raw){
        if (is_array($raw)) return $raw;
        if (is_string($raw) && ($raw[0]==='{'||$raw[0]==='[')) {
          $d=json_decode($raw,true);
          return json_last_error()===JSON_ERROR_NONE?$d:null;
        }
        return null;
      };

      $primary = get_term_meta($tid, 'archive_default_image', true);
      if ($primary !== '' && $primary !== null) {
        if (is_numeric($primary)) { $o=$emit_id($primary); if ($o) return $o; }
        if (is_string($primary) && stripos($primary,'http')===0) { $o=$emit_url($primary); if ($o) return $o; }
        $arr=$decode($primary);
        if ($arr) {
          foreach (['id','ID','attachment_id','image_id'] as $k)
            if (!empty($arr[$k]) && is_numeric($arr[$k])) { $o=$emit_id($arr[$k]); if ($o) return $o; }
          foreach (['url','src'] as $k)
            if (!empty($arr[$k])) { $o=$emit_url($arr[$k]); if ($o) return $o; }
          if (!empty($arr['sizes']['medium'])) { $o=$emit_url($arr['sizes']['medium']); if ($o) return $o; }
        }
      }
      foreach (['archive_default_image_id','archive_default_image_url','archive_display_default','archive_image_id',
                'term_image_id','profile_image_id','image_id','_thumbnail_id'] as $k) {
        $v = get_term_meta($tid, $k, true);
        if (!$v) continue;
        if (is_numeric($v)) { $o=$emit_id($v); if ($o) return $o; }
        if (is_string($v))  { $o=$emit_url($v); if ($o) return $o; }
        $arr=$decode($v);
        if ($arr) {
          foreach (['id','ID','attachment_id'] as $kk)
            if (!empty($arr[$kk]) && is_numeric($arr[$kk])) { $o=$emit_id($arr[$kk]); if ($o) return $o; }
          if (!empty($arr['url'])) { $o=$emit_url($arr['url']); if ($o) return $o; }
        }
      }
      $letter = mb_substr($t->name, 0, 1);
      return '<div class="mfc-headshot mfc-fallback" aria-hidden="true">'.esc_html($letter).'</div>';
    };

    // --- Layout class --------------------------------------------------------
    $layout_class = $layout === 'auto' ? '' : (' mfc-layout-' . $layout);

    // --- Render output -------------------------------------------------------
    ob_start(); ?>
    <section class="mfc-team mfc-shape--<?php echo esc_attr($image_shape); ?><?php echo esc_attr($layout_class); ?>"<?php echo $style_attr; ?>>
      <?php if ($heading !== ''): ?>
        <h2 class="mfc-team__heading"><?php echo esc_html($heading); ?></h2>
      <?php endif; ?>

      <article class="mfc-card">
        <div class="mfc-card__image"><?php echo $resolve_img($term); ?></div>
        <div class="mfc-card__body">
          <h3 class="mfc-card__name">
            <?php $link = get_term_link($term); $has_link = $link && !is_wp_error($link); ?>
            <?php if ($link_name && $has_link): ?>
              <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($term->name); ?></a>
            <?php else: ?>
              <?php echo esc_html($term->name); ?>
            <?php endif; ?>
          </h3>
          <div class="mfc-card__role"><?php echo esc_html($role_label); ?></div>
          <?php if ($show_desc):
            $desc = term_description($term, $tax);
            if (!empty($desc)): ?>
              <div class="mfc-card__desc"><?php echo wp_kses_post($desc); ?></div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </article>
    </section>
    <?php
    return trim(ob_get_clean());
  }
}