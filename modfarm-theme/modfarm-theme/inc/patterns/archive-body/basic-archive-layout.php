<?php
return [
    'slug'        => 'modfarm/basic-archive-layout',
    'title'       => 'Basic Archive Layout',
    'description' => 'A basic archive body layout with term description and archive book list.',
    'categories'  => [ 'modfarm-archive-body' ],
    'keywords'    => [ 'archive', 'layout', 'books', 'term', 'description' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)"><!-- wp:term-description {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}}} /-->

<!-- wp:modfarm/archive-book-list {"display-order":"ASC","books-per-page":24,"show-pagination":true, "book-format":{"id":2}} /--></div>
<!-- /wp:group -->
',
];