<?php
return [
    'slug'        => 'modfarm/page-clear',
    'title'       => 'Page Clear',
    'description' => 'A blank body section for building a custom layout',
    'categories'  => [ 'modfarm-page-body', 'modfarm-book-body' ],
    'keywords'    => ['cear', 'page'],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)"></div>
<!-- /wp:group -->
',
];