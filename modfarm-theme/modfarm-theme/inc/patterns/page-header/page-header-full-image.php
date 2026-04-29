<?php
return [
    'slug'        => 'modfarm/page-header-full-image',
    'title'       => 'Page Header Full Image',
    'description' => '',
    'categories'  => [ 'modfarm-page-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"metadata":{"name":"Book Header Full Image v1"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:cover {"useFeaturedImage":true,"dimRatio":20,"overlayColor":"black","isUserOverlayColor":true,"minHeight":350,"contentPosition":"top center","isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
<div class="wp-block-cover alignfull is-light has-custom-content-position is-position-top-center" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30);min-height:350px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:modfarm/navigation-menu {"leftMenu":2,"align":"full"} /-->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:post-title {"textAlign":"center","level":1,"fontSize":"xlarge"} /-->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
',
];
