<?php
return [
    'slug'        => 'modfarm/book-page-by-author-center-heading',
    'title'       => 'Book Page By Author Center Heading',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">By The Author</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"tax-source":"author","display-order":"rand","books-per-page":4,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->
',
];