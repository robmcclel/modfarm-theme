<?php
/**
 * Hybrid singular template with right sidebar.
 * - Uses PPB-selected Header/Footer
 * - Body uses classic the_content()
 * - Sidebar uses the Post Sidebar widget area
 *
 * Template Name: Hybrid - Right Sidebar
 * Template Post Type: post, page, mf_offer, mf_update, mf_event, modfarm_event
 */

defined('ABSPATH') || exit;

if (!function_exists('mf_resolve_pattern_content')) {
  function mf_resolve_pattern_content(?string $slug): string {
    if (function_exists('modfarm_ppb_get_pattern_content_by_slug')) {
      return modfarm_ppb_get_pattern_content_by_slug((string) $slug);
    }

    if (!$slug) return '';

    if (str_starts_with($slug, 'user/')) {
      $post_name = substr($slug, 5);
      $post = get_page_by_path($post_name, OBJECT, 'wp_block');
      if ($post && has_blocks($post->post_content)) {
        return (string) $post->post_content;
      }
      return '';
    }

    if (function_exists('get_block_pattern')) {
      $p = get_block_pattern($slug);
      if (is_array($p) && !empty($p['content'])) return (string) $p['content'];
    }

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

if (!function_exists('mf_get_ppb_chrome_slugs_for_post')) {
  function mf_get_ppb_chrome_slugs_for_post(WP_Post $post): array {
    if (function_exists('modfarm_ppb_get_effective_hybrid_chrome_slugs_for_post')) {
      return modfarm_ppb_get_effective_hybrid_chrome_slugs_for_post($post->ID, $post->post_type);
    }

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
      case 'mf_offer':
      case 'offer':
        return [
          'header' => function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('offer_header_pattern', $opts['offer_header_pattern'] ?? null, $opts)
            : ($opts['offer_header_pattern']  ?? 'modfarm/offer-header-basic-left'),
          'footer' => function_exists('modfarm_ppb_resolve_pattern_slug')
            ? modfarm_ppb_resolve_pattern_slug('offer_footer_pattern', $opts['offer_footer_pattern'] ?? null, $opts)
            : ($opts['offer_footer_pattern']  ?? 'modfarm/offer-footer-simple'),
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

get_header();
?>
<main id="primary" class="site-main mf-hybrid-wrap mf-hybrid-wrap--sidebar">
  <?php
  global $post;
  if ($post instanceof WP_Post) {
    $slugs = mf_get_ppb_chrome_slugs_for_post($post);

    $header = mf_resolve_pattern_content($slugs['header']);
    echo $header !== ''
      ? do_blocks($header)
      : do_blocks('<!-- wp:template-part {"slug":"header","area":"header"} /-->');

    if (have_posts()) : ?>
      <div class="mf-hybrid-sidebar-layout">
        <div class="mf-hybrid-sidebar-layout__content">
          <?php while (have_posts()) : the_post(); ?>
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
          <?php endwhile; ?>
        </div>

        <?php if (is_active_sidebar('post-sidebar')) : ?>
          <aside class="mf-hybrid-sidebar-layout__sidebar" aria-label="<?php esc_attr_e('Post Sidebar', 'modfarm-author'); ?>">
            <?php dynamic_sidebar('post-sidebar'); ?>
          </aside>
        <?php endif; ?>
      </div>
    <?php endif;

    $footer = mf_resolve_pattern_content($slugs['footer']);
    echo $footer !== ''
      ? do_blocks($footer)
      : do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer"} /-->');
  }
  ?>
</main>
<?php get_footer();
