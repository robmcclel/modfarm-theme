<?php
/**
 * Pattern: Book Banner Book Card Left
 * Category: modfarm-page-elements
 */
return array(
  'title'      => 'Book Banner Book Card Left',
  'categories' => array( 'modfarm-page-elements' ),
  'content'    => <<<'HTML'
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:cover {"url":"https://test2.modfarmdev.tempurl.host/wp-content/uploads/sites/3/2026/01/wolf-trim-1024x512.jpg","id":201,"dimRatio":20,"overlayColor":"black","isUserOverlayColor":true,"focalPoint":{"x":0.5,"y":0.35999999999999999},"minHeight":600,"contentPosition":"center center","sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|50","left":"var:preset|spacing|50"}}},"layout":{"type":"default"}} -->
<div class="wp-block-cover alignfull" style="padding-right:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);min-height:600px"><img class="wp-block-cover__image-background wp-image-201 size-large" alt="" src="https://test2.modfarmdev.tempurl.host/wp-content/uploads/sites/3/2026/01/wolf-trim-1024x512.jpg" style="object-position:50% 36%" data-object-fit="cover" data-object-position="50% 36%"/><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:modfarm/handpicked-books {"books-per-page":1,"books-in-row":"100%"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Section Heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Section content, if any</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-mf-button","style":{"border":{"radius":{"topLeft":"0px","topRight":"0px","bottomLeft":"0px","bottomRight":"0px"}}}} -->
<div class="wp-block-button is-style-mf-button"><a class="wp-block-button__link wp-element-button" style="border-top-left-radius:0px;border-top-right-radius:0px;border-bottom-left-radius:0px;border-bottom-right-radius:0px">Linking Button</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:group -->
HTML
);
