<?php
return [
    'slug'        => 'modfarm/series-list-left-heading',
    'title'       => 'Series List Left Heading',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"left","style":{"typography":{"textTransform":"capitalize"}},"fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-left has-xlarge-font-size" style="text-transform:capitalize">Our Series</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"left"} -->
<p class="has-text-align-left"><strong>Click a cover to see the full series</strong></p>
<!-- /wp:paragraph -->

<!-- wp:modfarm/taxonomy-grid {"showTOC":false,"aspectRatioOpt":"2/3"} /--></div>
<!-- /wp:group -->
',
];
