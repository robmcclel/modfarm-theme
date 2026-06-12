<?php
defined('ABSPATH') || exit;

/**
 * Blog index template.
 *
 * @package ModFarm
 */

get_header();

if (function_exists('modfarm_render_archive_page')) {
    modfarm_render_archive_page();
    get_footer();
    return;
}
?>
<main id="primary" class="site-main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php the_title('<h2>', '</h2>'); ?>
            <?php the_excerpt(); ?>
        <?php endwhile; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
