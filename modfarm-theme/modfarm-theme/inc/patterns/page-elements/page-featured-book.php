<?php
return [
    'slug'        => 'modfarm/page-featured-book',
    'title'       => 'Page Featured Book',
    'description' => 'A Featured Book element',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => ['page', 'featured book'],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"0","right":"0"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-right:0;padding-bottom:var(--wp--preset--spacing--60);padding-left:0">
<!-- wp:heading {"textAlign":"center","fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size">Featured Release</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/featured-book {"mode":"auto","coverSource":"featured_image","kicker":"Available Now!","subhead":"A New Adventure","useExcerpt":false} /-->
</div>
<!-- /wp:group -->
',
];