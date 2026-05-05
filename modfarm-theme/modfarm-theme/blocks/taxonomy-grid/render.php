<?php
if (!function_exists('modfarm_render_taxonomy_grid_block')) {
  function modfarm_render_taxonomy_grid_block($attributes, $content = '', $block = null) {

    $a = wp_parse_args($attributes, [
      'taxonomy'           => 'book-series',
      'displayMode'        => 'all',     // all | top | children
      'parentId'           => 0,
      'hideParents'        => false,
      'seriesGenreSlug'    => '',

      'columns'            => 4,
      'perPage'            => 24,
      'enablePagination'   => true,

      'orderBy'            => 'name_asc', // name_asc | name_desc | count_desc
      'showTOC'            => true,

      'primaryImageSource' => 'archive_default_image',  // archive_default_image | first_cover_in_series | archive_hero_image | initials
      'fallbackImageSource'=> 'first_cover_in_series',

      'shape'              => 'square',  // square|rounded|circle
      'aspectRatioOpt'     => '1/1',     // 'auto' or 'W/H' (e.g., '3/4')

      'hideEmpty'          => true,
      'showCounts'         => false,
      'gutter'             => 16,

      'anchor'             => '',

      // Tracking
      'trackerLoc'         => 'taxonomy-grid', // can be overridden by block UI later
      'trackEvent'         => 'taxonomy_click', // swap to your Core standard if needed
    ]);

    $tax = sanitize_key($a['taxonomy']);
    if (!taxonomy_exists($tax)) {
      return current_user_can('edit_posts')
        ? '<div class="mfb-taxgrid mfb-warn">Taxonomy “'.esc_html($tax).'” does not exist.</div>'
        : '';
    }

    // --- Build base args (UNPAGED) so we can filter THEN paginate ----------
    $base = [
      'taxonomy'   => $tax,
      'hide_empty' => !empty($a['hideEmpty']),
      'fields'     => 'all',
    ];
    if ($a['displayMode'] === 'top') {
      $base['parent'] = 0;
    } elseif ($a['displayMode'] === 'children') {
      $base['parent'] = max(0, intval($a['parentId']));
    }

    // Sorting to apply on the full set (stable pagination)
    $orderby = 'name'; $order = 'ASC';
    switch ($a['orderBy']) {
      case 'name_desc':  $orderby = 'name';  $order = 'DESC'; break;
      case 'count_desc': $orderby = 'count'; $order = 'DESC'; break;
      default:           $orderby = 'name';  $order = 'ASC';  break;
    }
    $base['orderby'] = $orderby;
    $base['order']   = $order;

    // Fetch ALL matching terms (unpaged)
    $all_terms = get_terms($base);
    if (is_wp_error($all_terms)) $all_terms = [];

    // Optionally filter out parents first (so pagination slices a clean list)
    $terms_for_paging = $all_terms;
    if ($a['hideParents'] && is_taxonomy_hierarchical($tax) && !empty($all_terms)) {
      $has_children = [];
      foreach ($all_terms as $t) {
        if (!empty($t->parent) && $t->parent > 0) {
          $has_children[$t->parent] = true;
        }
      }
      $terms_for_paging = array_values(array_filter($all_terms, function($t) use ($has_children) {
        return empty($has_children[$t->term_id]); // keep leaves only
      }));
    }

    $series_genre_slug = sanitize_title($a['seriesGenreSlug']);
    if ('book-series' === $tax && $series_genre_slug !== '' && function_exists('modfarm_get_series_genre_profile')) {
      $terms_for_paging = array_values(array_filter($terms_for_paging, function($t) use ($series_genre_slug) {
        if (!($t instanceof WP_Term)) {
          return false;
        }

        $profile = modfarm_get_series_genre_profile((int)$t->term_id);
        return $series_genre_slug === sanitize_title((string)($profile['primary_genre_slug'] ?? ''));
      }));
    }

    // --- Build TOC across the FINAL list (so letters reflect what users can reach)
    $toc = [];
    if (!empty($a['showTOC'])) {
      foreach ($terms_for_paging as $t) {
        $first = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/u', '', $t->name), 0, 1));
        if ($first === '') $first = '#';
        $toc[$first] = true;
      }
      ksort($toc, SORT_NATURAL);
    }

    // --- Pagination over the final list -----------------------------------
    $per_page   = max(1, intval($a['perPage']));
    $total_terms= count($terms_for_paging);
    $total_pages= !empty($a['enablePagination']) ? max(1, (int)ceil($total_terms / $per_page)) : 1;
    $paged      = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;
    if (empty($a['enablePagination'])) $paged = 1;
    if ($paged > $total_pages) $paged = $total_pages;

    if (!empty($a['enablePagination'])) {
      $offset = ($paged - 1) * $per_page;
      $terms  = array_slice($terms_for_paging, $offset, $per_page);
    } else {
      $terms  = array_slice($terms_for_paging, 0, $per_page);
    }

    // --- Image resolver (primary + fallback) -------------------------------
    $emit_id = function($id, $alt = '') {
      return wp_get_attachment_image(intval($id), 'large', false,
        ['class'=>'mfc-headshot','alt'=>$alt,'loading'=>'lazy','decoding'=>'async']) ?: '';
    };
    $emit_url = function($url, $alt = '') {
      $url = esc_url($url);
      return $url ? '<img class="mfc-headshot" src="'.$url.'" alt="'.esc_attr($alt).'" loading="lazy" decoding="async" />' : '';
    };
    $decode = function($raw){
      if (is_array($raw)) return $raw;
      if (is_string($raw) && $raw !== '' && ($raw[0]==='{' || $raw[0]==='[')) {
        $d = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $d : null;
      }
      return null;
    };

    $term_primary_img = function($term) use ($emit_id, $emit_url, $decode) {
      $tid = $term->term_id;
      $primary = get_term_meta($tid, 'archive_default_image', true);
      if ($primary !== '' && $primary !== null) {
        if (is_numeric($primary)) { $o=$emit_id($primary, $term->name); if ($o) return $o; }
        if (is_string($primary) && stripos($primary,'http')===0) { $o=$emit_url($primary, $term->name); if ($o) return $o; }
        $arr=$decode($primary);
        if ($arr) {
          foreach (['id','ID','attachment_id','image_id'] as $k)
            if (!empty($arr[$k]) && is_numeric($arr[$k])) { $o=$emit_id($arr[$k], $term->name); if ($o) return $o; }
          foreach (['url','src'] as $k)
            if (!empty($arr[$k])) { $o=$emit_url($arr[$k], $term->name); if ($o) return $o; }
          if (!empty($arr['sizes']['large']))   { $o=$emit_url($arr['sizes']['large'], $term->name); if ($o) return $o; }
          if (!empty($arr['sizes']['medium']))  { $o=$emit_url($arr['sizes']['medium'], $term->name); if ($o) return $o; }
        }
      }
      foreach (['archive_default_image_id','archive_default_image_url','archive_display_default','archive_image_id',
                'term_image_id','profile_image_id','image_id','_thumbnail_id'] as $k) {
        $v = get_term_meta($tid, $k, true);
        if (!$v) continue;
        if (is_numeric($v)) { $o=$emit_id($v, $term->name); if ($o) return $o; }
        if (is_string($v))  { $o=$emit_url($v, $term->name); if ($o) return $o; }
        $arr=$decode($v);
        if ($arr) {
          foreach (['id','ID','attachment_id'] as $kk)
            if (!empty($arr[$kk]) && is_numeric($arr[$kk])) { $o=$emit_id($arr[$kk], $term->name); if ($o) return $o; }
          if (!empty($arr['url'])) { $o=$emit_url($arr['url'], $term->name); if ($o) return $o; }
        }
      }
      return '';
    };

    $first_book_id_for_term = function($term_id) use ($tax) {
      $keys = ['publisher_date','publication_date','pub_date','release_date','published_on'];
      foreach ($keys as $key) {
        $ids = get_posts([
          'post_type'      => 'book',
          'tax_query'      => [[ 'taxonomy' => $tax, 'terms' => $term_id ]],
          'posts_per_page' => 1,
          'meta_key'       => $key,
          'orderby'        => 'meta_value ID',
          'order'          => 'ASC',
          'fields'         => 'ids',
          'no_found_rows'  => true,
          'meta_type'      => 'DATETIME'
        ]);
        if (!empty($ids)) return intval($ids[0]);
      }
      $ids = get_posts([
        'post_type'      => 'book',
        'tax_query'      => [[ 'taxonomy' => $tax, 'terms' => $term_id ]],
        'posts_per_page' => 1,
        'orderby'        => 'date ID',
        'order'          => 'ASC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
      ]);
      return !empty($ids) ? intval($ids[0]) : 0;
    };

    $first_cover_img = function($term) use ($first_book_id_for_term, $emit_id, $emit_url) {
      $pid = $first_book_id_for_term($term->term_id);
      if (!$pid) return '';
      $cover = get_post_meta($pid, 'cover_image_kindle', true);
      if ($cover) {
        if (is_numeric($cover)) { $o=$emit_id($cover, get_the_title($pid)); if ($o) return $o; }
        if (is_string($cover) && stripos($cover,'http')===0) { $o=$emit_url($cover, get_the_title($pid)); if ($o) return $o; }
      }
      $thumb = get_post_thumbnail_id($pid);
      if ($thumb) { $o=$emit_id($thumb, get_the_title($pid)); if ($o) return $o; }
      return '';
    };

    $hero_img = function($term) use ($emit_id) {
      $id = intval(get_term_meta($term->term_id, 'archive_hero_image', true));
      if ($id) { $o=$emit_id($id, $term->name); if ($o) return $o; }
      return '';
    };

    $letters_box = function($term) {
      $letter = mb_substr($term->name, 0, 1);
      return '<div class="mfc-headshot mfc-fallback" aria-hidden="true">'.esc_html($letter).'</div>';
    };

    $resolve_html = function($term, $primary, $fallback) use ($term_primary_img, $first_cover_img, $hero_img, $letters_box) {
      $map = [
        'archive_default_image' => fn($t) => $term_primary_img($t),
        'first_cover_in_series' => fn($t) => $first_cover_img($t),
        'archive_hero_image'    => fn($t) => $hero_img($t),
        'initials'              => fn($t) => $letters_box($t),
      ];
      $try = function($src) use ($map, $term) {
        return isset($map[$src]) ? (string)$map[$src]($term) : '';
      };
      $html = $try($primary);
      if ($html !== '') return $html;
      $html = $try($fallback);
      return $html !== '' ? $html : $letters_box($term);
    };

    // --- Style hooks + responsive cols via CSS vars -------------------------
    $columns = max(2, min(8, intval($a['columns'])));
    $gutter  = max(0, intval($a['gutter']));
    $ratio   = ($a['aspectRatioOpt'] === 'auto') ? '' : 'aspect-ratio:' . esc_attr(str_replace('/', ' / ', $a['aspectRatioOpt'])) . ';';
    $shape   = in_array($a['shape'], ['square','rounded','circle'], true) ? $a['shape'] : 'square';
    $anchor  = empty($a['anchor']) ? '' : ' id="'. esc_attr($a['anchor']) .'"';

    // Responsive caps: phone ≤2, small tab ≤3, large tab/desktop ≤4
    $cols_sel     = $columns;
    $cols_phone   = min($cols_sel, 2);
    $cols_smtab   = min($cols_sel, 3);
    $cols_lgtab   = min($cols_sel, 4);

    $tracker = !empty($a['trackerLoc']) ? sanitize_key($a['trackerLoc']) : 'taxonomy-grid';
    $event   = !empty($a['trackEvent']) ? sanitize_key($a['trackEvent']) : 'taxonomy_click';

    ob_start(); ?>
    <div class="mfb-taxgrid-wrapper<?php echo ' mfb-shape--'.esc_attr($shape); ?>"<?php echo $anchor; ?>>

      <?php if (!empty($a['showTOC']) && !empty($toc)): ?>
        <nav class="mfb-taxgrid-toc" aria-label="<?php echo esc_attr(get_taxonomy($tax)->labels->name); ?> index">
          <?php foreach (array_keys($toc) as $letter): ?>
            <a href="#mfb-taxgrid-letter-<?php echo esc_attr($letter); ?>"><?php echo esc_html($letter); ?></a>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>

      <div
        class="mfb-taxgrid"
        style="<?php
          printf(
            '--mfb-cols:%d;--mfb-cols-phone:%d;--mfb-cols-smtab:%d;--mfb-cols-lgtab:%d;--mfb-gutter:%dpx;',
            $cols_sel, $cols_phone, $cols_smtab, $cols_lgtab, $gutter
          );
        ?>"
      >
        <?php
        $current_letter = null;
        foreach ($terms as $term):
          $first = strtoupper(mb_substr(preg_replace('/[^A-Za-z0-9]/u', '', $term->name), 0, 1));
          if ($first === '') $first = '#';

          if (!empty($a['showTOC']) && $first !== $current_letter):
            $current_letter = $first; ?>
            <div class="mfb-taxgrid-letter" id="mfb-taxgrid-letter-<?php echo esc_attr($first); ?>">
              <span><?php echo esc_html($first); ?></span>
            </div>
          <?php endif;

          $thumb_html = $resolve_html($term, $a['primaryImageSource'], $a['fallbackImageSource']);

          $link  = get_term_link($term);
          if (is_wp_error($link)) $link = '#';

          $common_attrs =
            ' data-event="' . esc_attr($event) . '"' .
            ' data-origin="taxonomy-grid"' .
            ' data-taxonomy="' . esc_attr($tax) . '"' .
            ' data-term-id="' . esc_attr((int)$term->term_id) . '"' .
            ' data-term="' . esc_attr($term->slug) . '"' .
            ' data-label="' . esc_attr($term->name) . '"' .
            ' data-tracker="' . esc_attr($tracker) . '"' .
            ' data-permalink="' . esc_attr($link) . '"';

          $thumb_attrs = $common_attrs . ' data-slot="thumb"';
          $title_attrs = $common_attrs . ' data-slot="title"';
          ?>
          <div class="mfb-taxgrid-card">
            <a class="mfb-taxgrid-thumb"
               href="<?php echo esc_url($link); ?>"
               style="<?php echo $ratio; ?>"
               <?php echo $thumb_attrs; ?>>
              <?php echo $thumb_html; ?>
            </a>

            <div class="mfb-taxgrid-meta">
              <a class="mfb-taxgrid-title"
                 href="<?php echo esc_url($link); ?>"
                 <?php echo $title_attrs; ?>>
                 <?php echo esc_html($term->name); ?>
              </a>

              <?php if (!empty($a['showCounts'])): ?>
                <span class="mfb-taxgrid-count"><?php echo intval($term->count); ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php
      // Bottom-only pagination using ?pg=
      if (!empty($a['enablePagination']) && $total_pages > 1) {
        $base_url = remove_query_arg('pg');
        $format   = (strpos($base_url, '?') === false) ? '?pg=%#%' : '&pg=%#%';

        $pagination = paginate_links([
          'total'     => $total_pages,
          'current'   => $paged,
          'type'      => 'list',
          'prev_text' => '« Prev',
          'next_text' => 'Next »',
          'base'      => $base_url . '%_%',
          'format'    => $format
        ]);
        if ($pagination) echo '<nav class="mfb-pagination" aria-label="Books pagination">'.$pagination.'</nav>';
      }
      ?>
    </div>
    <?php
    return ob_get_clean();
  }
}
