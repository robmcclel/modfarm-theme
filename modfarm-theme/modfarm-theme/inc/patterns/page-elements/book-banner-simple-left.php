<?php
return [
    'slug'        => 'modfarm/book-banner-simple-left',
    'title'       => 'Book Banner Simple Left',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:cover {"url":"https://test2.modfarmdev.tempurl.host/wp-content/uploads/sites/3/2026/01/wolf-trim-1024x512.jpg","id":201,"dimRatio":20,"overlayColor":"black","isUserOverlayColor":true,"focalPoint":{"x":0.5,"y":0.35999999999999999},"minHeight":600,"contentPosition":"center left","sizeSlug":"large","align":"full","layout":{"type":"default"}} -->
<div class="wp-block-cover alignfull has-custom-content-position is-position-center-left" style="min-height:600px"><img class="wp-block-cover__image-background wp-image-201 size-large" alt="" src="https://test2.modfarmdev.tempurl.host/wp-content/uploads/sites/3/2026/01/wolf-trim-1024x512.jpg" style="object-position:50% 36%" data-object-fit="cover" data-object-position="50% 36%"/><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading -->
<h2 class="wp-block-heading">Section Heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Section content, if any</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-mf-button","style":{"border":{"radius":{"topLeft":"0px","topRight":"0px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button is-style-mf-button"><a class="wp-block-button__link wp-element-button" style="border-top-left-radius:0px;border-top-right-radius:0px;border-bottom-left-radius:0px;border-bottom-right-radius:0px">Linking Button</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
',
];
