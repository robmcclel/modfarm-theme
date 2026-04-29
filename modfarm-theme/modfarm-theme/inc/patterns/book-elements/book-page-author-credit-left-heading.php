<?php
return [
    'slug'        => 'modfarm/book-page-author-credit-left-heading',
    'title'       => 'Book Page Author Credit Left Heading',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading -->
<h2 class="wp-block-heading">The Author</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/creator-credit /--></div>
<!-- /wp:group -->
',
];