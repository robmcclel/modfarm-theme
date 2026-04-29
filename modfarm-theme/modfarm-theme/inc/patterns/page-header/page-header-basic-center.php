<?php
return [
    'slug'        => 'modfarm/page-header-basic-center',
    'title'       => 'Page Header Basic Center',
    'description' => '',
    'categories'  => [ 'modfarm-page-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:post-title {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"48px"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} /--></div>
<!-- /wp:group -->
',
];
