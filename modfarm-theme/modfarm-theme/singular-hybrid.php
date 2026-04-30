<?php
/**
 * Hybrid singular template
 * - Uses PPB-selected Header/Footer (resolved at render time, like Archives)
 * - Body uses classic the_content() (no PPB body)
 * - Books never route here (router blocks them)
 */
 
 /**
 * Template Name: Hybrid
 * Template Post Type: post, page
 */
 
 
defined('ABSPATH') || exit;

/** Resolve pattern content from slug (supports user/* and registered patterns) */
if (!function_exists('mf_resolve_pattern_content')) {
  function mf_resolve_pattern_content(?string $slug): string {
    if (!$slug) return '';

    // 1) user/* patterns stored in wp_block
    if (str_starts_with($slug, 'user/')) {
      $post_name = substr($slug, 5);
      $post = get_page_by_path($post_name, OBJECT, 'wp_block');
      if ($post && has_blocks($post->post_content)) {
        return (string) $post->post_content;
      }
      return '';
    }

    // 2) Core/registered pattern slugs
    if (function_exists('get_block_pattern')) {
      $p = get_block_pattern($slug); // WP >= 6.3 returns ['content'] when found
      if (is_array($p) && !empty($p['content'])) return (string) $p['content'];
    }

    // 3) Registry fallback
    if (class_exists('WP_Block_Patterns_Registry')) {
      $reg = WP_Block_Patterns_Registry::get_instance();
      if ($reg && method_exists($reg, 'get_registered')) {
        $p = $reg->get_registered($slug);
        if (is_array($p) && !empty($p['content'])) return (string) $p['content'];
        if (is_object($p) && !empty($p->content))   return (string) $p->content;
      }
    }

    return '';
  }
}

/** Get PPB header/footer slugs for current post type from ModFarm Settings */
if (!function_exists('mf_get_ppb_chrome_slugs_for_post')) {
  function mf_get_ppb_chrome_slugs_for_post(WP_Post $post): array {
    $opts = get_option('modfarm_theme_settings', []);
    switch ($post->post_type) {
      case 'page':
        return [
          'header' => function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('page_header_pattern', $opts['page_header_pattern'] ?? null, $opts)
            : ($opts['page_header_pattern']  ?? 'modfarm/page-header-basic-left'),
          'footer' => function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('page_footer_pattern', $opts['page_footer_pattern'] ?? null, $opts)
            : ($opts['page_footer_pattern']  ?? 'modfarm/footer-simple'),
        ];
      case 'post':
      default:
        return [
          'header' => function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('post_header_pattern', $opts['post_header_pattern'] ?? null, $opts)
            : ($opts['post_header_pattern']  ?? 'modfarm/post-header-basic-left'),
          'footer' => function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('post_footer_pattern', $opts['post_footer_pattern'] ?? null, $opts)
            : ($opts['post_footer_pattern']  ?? 'modfarm/post-footer-simple-comments'),
        ];
    }
  }
}

get_header(); // ok if empty; theme may still use it for shell markup
?>
<main id="primary" class="site-main mf-hybrid-wrap" style="margin:0 auto;max-width:var(--mf-content-width,1200px);">
  <?php
  global $post;
  if ($post instanceof WP_Post) {
    $slugs = mf_get_ppb_chrome_slugs_for_post($post);

    // HEADER: PPB pattern, else fall back to template-part "header"
    $header = mf_resolve_pattern_content($slugs['header']);
    echo $header !== ''
      ? do_blocks($header)
      : do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');

    // BODY: classic content
    if (have_posts()) : while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('mf-hybrid-article'); ?>>
        <div class="entry-content">
          <?php
            the_content();
            wp_link_pages([
              'before' => '<div class="page-links">' . esc_html__('Pages:', 'modfarm'),
              'after'  => '</div>',
            ]);
          ?>
        </div>
      </article>
    <?php endwhile; endif;

    // FOOTER: PPB pattern, else fall back to template-part "footer"
    $footer = mf_resolve_pattern_content($slugs['footer']);
    echo $footer !== ''
      ? do_blocks($footer)
      : do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
  }
  ?>
</main>
<?php get_footer();
