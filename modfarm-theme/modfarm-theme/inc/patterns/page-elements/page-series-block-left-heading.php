<?php
return [
    'slug'        => 'modfarm/page-series-block-left-heading',
    'title'       => 'Page Series Block Left Heading',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"left","fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-left has-xlarge-font-size">Series Title</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/multi-tax-format {"tax-type":"series","book-format":{"id":2},"display-order":"ASC"} /--></div>
<!-- /wp:group -->
',
];
