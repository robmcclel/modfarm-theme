<?php
/**
 * Template for single CPTs like book, modfarm_book, etc.
 */

get_header();
?>

<div id="page" class="site">
  <div id="primary" class="site-main wp-site-blocks">
    <main id="main" class="site-content">
      <?php
      while ( have_posts() ) :
        the_post();
        the_content();
      endwhile;
      ?>
    </main>
  </div>
</div>

<?php get_footer(); ?>