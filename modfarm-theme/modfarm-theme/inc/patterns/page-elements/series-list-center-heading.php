<?php
return [
    'slug'        => 'modfarm/series-list-center-heading',
    'title'       => 'Series List Center Heading',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"textTransform":"capitalize"}},"fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size" style="text-transform:capitalize">Our Series</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><strong>Click a cover to see the full series</strong></p>
<!-- /wp:paragraph -->

<!-- wp:modfarm/taxonomy-grid {"showTOC":false,"aspectRatioOpt":"2/3"} /--></div>
<!-- /wp:group -->
',
];
