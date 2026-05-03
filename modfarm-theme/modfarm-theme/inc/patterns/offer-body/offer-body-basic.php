<?php
return [
    'slug'        => 'modfarm/offer-body-basic',
    'title'       => 'Offer Body Basic',
    'description' => 'Basic Offer layout with image, content, price, details, and Buy Now.',
    'categories'  => [ 'modfarm-offer-body' ],
    'keywords'    => [ 'offer', 'store', 'product' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)"><!-- wp:columns {"verticalAlignment":"top"} -->
<div class="wp-block-columns are-vertically-aligned-top"><!-- wp:column {"verticalAlignment":"top","width":"38%"} -->
<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:38%"><!-- wp:post-featured-image {"aspectRatio":"3/4","width":"100%"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top","width":"62%"} -->
<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:62%"><!-- wp:modfarm/content-slot /-->

<!-- wp:modfarm/offer-price /-->

<!-- wp:modfarm/offer-details /-->

<!-- wp:modfarm/offer-buy-button /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];
