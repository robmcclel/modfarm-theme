<?php
return [
    'slug'        => 'modfarm/post-header-basic-centered',
    'title'       => 'Post Header Basic Centered',
    'description' => '',
    'categories'  => [ 'modfarm-post-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:post-title {"textAlign":"center","level":1,"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|20"}}},"fontSize":"xlarge"} /-->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:post-author-name {"textAlign":"center"} /-->

<!-- wp:post-date {"textAlign":"center"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
',
];
