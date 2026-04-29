<?php
return [
    'slug'        => 'modfarm/post-body-basic',
    'title'       => 'Post Body Basic',
    'description' => 'Standard post body wrapper with a ModFarm content slot.',
    'categories'  => [ 'modfarm-post-body' ],
    'keywords'    => [ 'post', 'body', 'basic', 'content', 'slot' ],
    'content'     => '
<!-- wp:group {"metadata":{"name":"Post Body v1"},"align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|20","left":"var:preset|spacing|20","top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--20)"><!-- wp:modfarm/content-slot /--></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"60px"} -->
<div style="height:60px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->
',
];