<?php
/**
 * Pattern: Books Monthly Release
 * Category: modfarm-page-elements
 */
return array(
  'title'      => 'Books Monthly Release',
  'categories' => array( 'modfarm-page-elements' ),
  'content'    => <<<'HTML'
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Books Released This Month</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/coming-soon-list {"dateFilterMode":"month","filterYear":2026,"filterMonth":1} /--></div>
<!-- /wp:group -->
HTML
);
