<?php
return [
    'slug'        => 'modfarm/book-header-image',
    'title'       => 'Book Header Image',
    'description' => '',
    'categories'  => [ 'modfarm-book-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:cover {"useFeaturedImage":true,"dimRatio":30,"overlayColor":"black","isUserOverlayColor":true,"focalPoint":{"x":0.51000000000000001,"y":0.46999999999999997},"minHeight":300,"isDark":false,"sizeSlug":"full","align":"full","layout":{"type":"default"}} -->
<div class="wp-block-cover alignfull is-light" style="min-height:300px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:post-title {"textAlign":"center","level":1,"className":"whiteheader","fontSize":"xlarge"} /-->

<!-- wp:modfarm/book-page-series {"alignment":"center","fontSize":30,"textColor":"#ffffff"} /--></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
',
];
