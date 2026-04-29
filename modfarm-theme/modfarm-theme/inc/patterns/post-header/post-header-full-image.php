<?php
return [
    'slug'        => 'modfarm/post-header-full-image',
    'title'       => 'Post Header Full Image',
    'description' => '',
    'categories'  => [ 'modfarm-post-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"metadata":{"name":"Edie Page Header v1"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:cover {"useFeaturedImage":true,"dimRatio":20,"overlayColor":"black","isUserOverlayColor":true,"minHeight":350,"contentPosition":"top center","isDark":false,"sizeSlug":"full","align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull is-light has-custom-content-position is-position-top-center" style="min-height:350px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:post-title {"textAlign":"center","level":1,"fontSize":"xlarge"} /-->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:post-author-name /-->

<!-- wp:post-date {"metadata":{"bindings":{"datetime":{"source":"core/post-data","args":{"field":"date"}}}}} /--></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
',
];
