<?php
return [
    'slug'        => 'modfarm/book-page-also-like-center-heading',
    'title'       => 'Book Page Also Like Center Heading',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|0","right":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--0)"><!-- wp:heading {"textAlign":"center","fontSize":"large"} -->
<h2 class="wp-block-heading has-text-align-center has-large-font-size">You Might Also Like</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"tax-source":"genre","display-order":"rand","books-per-page":4,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->
',
];