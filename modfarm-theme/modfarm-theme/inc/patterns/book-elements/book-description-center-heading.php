<?php
return [
    'slug'        => 'modfarm/book-description-center-heading',
    'title'       => 'Book Description Center Heading',
    'description' => 'Book description with heading centered on the page',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => ['book page', 'description', 'book description'],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Description</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:group -->
',
];