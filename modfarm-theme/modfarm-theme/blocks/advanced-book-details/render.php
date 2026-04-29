<?php
// theme/blocks/advanced-book-details/render.php
defined('ABSPATH') || exit;

/**
 * Server render for modfarm/advanced-book-details
 */
if (!function_exists('modfarm_render_advanced_book_details_block')) {
  function modfarm_render_advanced_book_details_block($attributes = [], $content = '', $block = null) {
    // Resolve post context
    $post_id = 0;
    if (is_object($block) && isset($block->context['postId'])) {
      $post_id = (int) $block->context['postId'];
    }
    if (!$post_id && get_the_ID()) $post_id = (int) get_the_ID();

    if (!$post_id) {
      return current_user_can('edit_posts')
        ? '<div class="mfb-details mfb--admin-note"><em>Advanced Book Details:</em> Place inside a Book post.</div>'
        : '';
    }

    // Attributes
    $a = wp_parse_args($attributes, [
      'rows'      => [],
      'hideEmpty' => true,
      'title'     => '',
    ]);
    $rows = array_filter(is_array($a['rows']) ? $a['rows'] : []);
    $hide = !empty($a['hideEmpty']);

    // Helpers
    $render_tax = function ($post_id, $tax_slug) {
      $tax_slug = sanitize_key($tax_slug);
      if (!$tax_slug || !taxonomy_exists($tax_slug)) return '';
      $terms = wp_get_post_terms($post_id, $tax_slug);
      if (is_wp_error($terms) || empty($terms)) return '';
      $links = [];
      foreach ($terms as $t) {
        $url = get_term_link($t);
        if (!is_wp_error($url)) $links[] = '<a href="'.esc_url($url).'">'.esc_html($t->name).'</a>';
      }
      return implode(', ', $links);
    };

    $render_meta = function ($post_id, $key) {
      $key = sanitize_key($key);
      if (!$key) return '';
      $val = get_post_meta($post_id, $key, true);
      if (is_array($val)) {
        $val = array_filter(array_map('trim', array_map('wp_strip_all_tags', $val)));
        $val = implode(', ', $val);
      } else {
        $val = trim(wp_strip_all_tags((string) $val));
      }
      return $val;
    };

    $human_label = function($choice, $type = '') {
      if ($type === 'tax') {
        $tax = get_taxonomy($choice);
        if ($tax && !empty($tax->labels->singular_name)) {
          return $tax->labels->singular_name;
        }
      }
      $s = str_replace(['_', '-'], ' ', $choice);
      $s = ucwords($s);
      $map = [
        'Isbn 10' => 'ISBN-10',
        'Isbn 13' => 'ISBN-13',
        'Asin' => 'ASIN',
        'Ebook' => 'eBook',
      ];
      return $map[$s] ?? $s;
    };

    // Build rows
    $rows_html = '';
    foreach ($rows as $choice) {
      if (!is_string($choice) || $choice === '') continue;

      $label = '';
      $value = '';

      if (strpos($choice, 'tax:') === 0) {
        $slug  = substr($choice, 4);
        $label = $human_label($slug, 'tax');
        $value = $render_tax($post_id, $slug);
      } elseif (strpos($choice, 'meta:') === 0) {
        $key   = substr($choice, 5);
        $label = $human_label($key, 'meta');
        $value = $render_meta($post_id, $key);
      } else {
        // Unknown selector format
        continue;
      }

      if ($value === '' && $hide) continue;

      $rows_html .= '<div class="mfb-details__row">';
      $rows_html .=   '<div class="mfb-details__label">'.esc_html($label).'</div>';
      $rows_html .=   '<div class="mfb-details__value">'.($value !== '' ? $value : '<span class="mfb-details__empty">—</span>').'</div>';
      $rows_html .= '</div>';
    }

    if ($rows_html === '') {
      return current_user_can('edit_posts')
        ? '<div class="mfb-details mfb--admin-note"><em>Advanced Book Details:</em> No non-empty values. Add fields or disable “Hide Empty Rows”.</div>'
        : '';
    }

    $out  = '<div class="mfb-details">';
    if (!empty($a['title'])) {
      $out .= '<h3 class="mfb-details__heading">'.esc_html($a['title']).'</h3>';
    }
    $out .= $rows_html;
    $out .= '</div>';
    return $out;
  }
}

/**
 * Block registration (ensure callback is attached to this folder’s block.json)
 * If you have a central loader, you can skip this and register there — just make sure
 * you require this file so the callback exists.
 */
add_action('init', function () {
  $dir = __DIR__; // blocks/advanced-book-details
  if (file_exists($dir . '/block.json')) {
    register_block_type($dir, [
      'render_callback' => 'modfarm_render_advanced_book_details_block',
    ]);
  }
});

/**
 * REST route for auto-discovery — matches JS path: /modfarm/v1/advanced-book-details/discover
 */
add_action('rest_api_init', function () {
  register_rest_route('modfarm/v1', '/advanced-book-details/discover', [
    'methods'  => 'GET',
    'permission_callback' => function () { return current_user_can('edit_posts'); },
    'callback' => function () {
      $ptype = get_post_type() ?: 'book';

      // Taxonomy list
      $tax_objs = get_object_taxonomies($ptype, 'objects');
      $taxes = [];
      foreach ($tax_objs as $slug => $obj) {
        $taxes[] = [
          'slug'  => $slug,
          'label' => $obj->labels->singular_name ?? $slug,
        ];
      }

      // Meta keys from current post (if any), lightly filtered
      $metaKeys = [];
      $pid = get_the_ID();
      if ($pid) {
        $keys = get_post_custom_keys($pid);
        if (is_array($keys)) {
          $keys = array_unique(array_map('sanitize_key', $keys));
          $metaKeys = array_values(array_filter($keys, function ($k) {
            return (strpos($k, '_edit_lock') !== 0)
                && (strpos($k, '_edit_last') !== 0)
                && (strpos($k, '_thumbnail_id') !== 0)
                && (strpos($k, '_wp_') !== 0);
          }));
        }
      }

      return new WP_REST_Response([
        'taxonomies' => $taxes,
        'metaKeys'   => $metaKeys,
      ], 200);
    }
  ]);
});