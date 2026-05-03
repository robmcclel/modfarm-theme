<?php
return [
    'slug'        => 'modfarm/offer-footer-simple',
    'title'       => 'Offer Footer Simple',
    'description' => 'Simple footer for Offer pages.',
    'categories'  => [ 'modfarm-offer-footer' ],
    'keywords'    => [ 'offer', 'store', 'footer' ],
    'content'     => '
<!-- wp:group {"align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--20)"><!-- wp:paragraph {"align":"center","fontSize":"small"} -->
<p class="has-text-align-center has-small-font-size">[modfarm_footer_login]</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
',
];
