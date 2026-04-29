<?php
return [
    'slug'        => 'modfarm/post-featured-image',
    'title'       => 'Post Featured Image',
    'description' => 'Featured image section for post layouts with a ModFarm content slot.',
    'categories'  => [ 'modfarm-post-body' ],
    'keywords'    => [ 'post', 'featured', 'image', 'content', 'slot' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"right":"var:preset|spacing|20","left":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group" style="padding-right:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><!-- wp:post-featured-image /-->

<!-- wp:modfarm/content-slot /--></div>
<!-- /wp:group -->
',
];