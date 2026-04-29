<?php
return [
    'slug'        => 'modfarm/upcoming-releases',
    'title'       => 'Upcoming Releases',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:heading {"textAlign":"center","fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size">New and Upcoming Releases</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/multi-tax-format {"tax-type":"series","book-format":{"id":2},"books-per-page":8} /--></div>
<!-- /wp:group -->
',
];
