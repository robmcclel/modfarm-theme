<?php
return [
    'slug'        => 'modfarm/basic-author-archive-layout',
    'title'       => 'Basic Author Archive Layout',
    'description' => 'A basic author archive body layout with taxonomy description and archive book list.',
    'categories'  => [ 'modfarm-archive-body' ],
    'keywords'    => [ 'author', 'archive', 'layout', 'books', 'taxonomy' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)"><!-- wp:modfarm/tax-description {"imgSize":200} /-->

<!-- wp:modfarm/archive-book-list {"display-order":"ASC","books-per-page":24, "show-pagination":true,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->
',
];