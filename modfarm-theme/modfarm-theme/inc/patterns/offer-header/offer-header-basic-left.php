<?php
return [
    'slug'        => 'modfarm/offer-header-basic-left',
    'title'       => 'Offer Header Basic Left',
    'description' => 'Basic Offer header with navigation and title.',
    'categories'  => [ 'modfarm-offer-header' ],
    'keywords'    => [ 'offer', 'store', 'header' ],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:post-title {"textAlign":"left","level":1,"style":{"typography":{"fontSize":"48px"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|40"}}}} /--></div>
<!-- /wp:group -->
',
];
