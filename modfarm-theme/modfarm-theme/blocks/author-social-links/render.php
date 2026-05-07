<?php
/**
 * Render callback for Author Social Links block.
 */

require_once get_template_directory() . '/blocks/shared/author-social-links.php';

if (!function_exists('modfarm_render_author_social_links_block')) {
  function modfarm_render_author_social_links_block($attributes = [], $content = '', $block = null) {
    $a = wp_parse_args($attributes, [
      'authorId'         => 0,
      'useArchiveAuthor' => true,
      'align'            => 'left',
      'iconSize'         => 36,
      'gap'              => 14,
      'colorMode'        => 'native',
      'monotoneColor'    => '',
      'openInNewTab'     => true,
      'hideIfEmpty'      => false,
    ]);

    $term = null;
    $author_id = absint($a['authorId']);

    if ($author_id > 0) {
      $candidate = get_term($author_id, 'book-author');
      if (!$candidate || is_wp_error($candidate)) {
        $candidate = get_term($author_id, 'book-authors');
      }
      if ($candidate instanceof WP_Term) {
        $term = $candidate;
      }
    }

    if (!$term && !empty($a['useArchiveAuthor'])) {
      $queried = get_queried_object();
      $author_taxonomies = function_exists('modfarm_author_taxonomies')
        ? modfarm_author_taxonomies()
        : ['book-author', 'book-authors'];
      if ($queried instanceof WP_Term && in_array($queried->taxonomy, $author_taxonomies, true)) {
        $term = $queried;
      }
    }

    if (!$term) {
      if (current_user_can('edit_posts') && empty($a['hideIfEmpty'])) {
        return '<div class="mfas-socials mfas-socials--empty"><em>Author Social Links:</em> Select an author or place this block on an author archive.</div>';
      }
      return '';
    }

    return modfarm_render_author_social_links($term, $a);
  }
}
