<?php
/**
 * Pattern: Latest Releases Centered
 * Category: modfarm-page-elements
 */
return array(
  'title'      => 'Latest Releases Centered',
  'categories' => array( 'modfarm-page-elements' ),
  'content'    => <<<'HTML'
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"0","right":"0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:0;padding-bottom:var(--wp--preset--spacing--50);padding-left:0"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Coming Soon</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/coming-soon-list {"listType":"latest-releases","book-format":{"id":2},"display-order":"DESC","books-per-page":4,"launchWindowDays":3} /--></div>
<!-- /wp:group -->
HTML
);
