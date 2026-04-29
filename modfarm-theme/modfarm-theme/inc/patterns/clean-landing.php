<?php
return [
    'slug'        => 'modfarm/clean-landing',
    'title'       => 'Clean Landing Page',
    'description' => 'A minimalist layout with just the dynamic title block. Uses the default site width.',
    'categories'  => [ 'modfarm-pages' ],
    'keywords'    => [ 'landing', 'blank', 'minimal', 'starter', 'post title' ],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:post-title {"level":1} /-->
	<!-- wp:paragraph --><p>Start writing your page content here...</p><!-- /wp:paragraph -->
</div>
</div>
<!-- /wp:group -->
',
];