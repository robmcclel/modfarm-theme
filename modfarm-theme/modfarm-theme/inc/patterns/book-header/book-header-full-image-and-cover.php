<?php
return [
    'slug'        => 'modfarm/book-header-full-image-and-cover',
    'title'       => 'Book Header Full Image and Cover',
    'description' => '',
    'categories'  => [ 'modfarm-book-header' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"metadata":{"name":"Book Header Full Image with Cover"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:cover {"dimRatio":10,"overlayColor":"black","isUserOverlayColor":true,"minHeight":50,"contentPosition":"center center","isDark":false,"sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","right":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30"}}}} -->
<div class="wp-block-cover alignfull is-light" style="padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--30);min-height:50px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-10 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:modfarm/navigation-menu {"leftMenu":7,"align":"full"} /-->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:modfarm/book-cover-art {"coverType":"featured"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:post-title {"textAlign":"center","level":1,"style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"textColor":"base"} /-->

<!-- wp:modfarm/book-page-series {"alignment":"center","fontSize":24,"textColor":"#ffffff"} /-->

<!-- wp:modfarm/book-author-credit {"alignment":"center","fontSize":22} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
',
];
