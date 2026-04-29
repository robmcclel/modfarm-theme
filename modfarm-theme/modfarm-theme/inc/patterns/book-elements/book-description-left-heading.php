<?php
return [
    'slug'        => 'modfarm/book-description-left-heading',
    'title'       => 'Book Description Left Heading',
    'description' => 'Book description with heading left aligned on the page',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => ['book page', 'book description', 'description'],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading -->
<h2 class="wp-block-heading">Description</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:group -->
',
];