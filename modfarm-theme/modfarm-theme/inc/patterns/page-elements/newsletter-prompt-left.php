<?php
return [
    'slug'        => 'modfarm/newsletter-prompt-left',
    'title'       => 'Newsletter Prompt Left',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|50","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--20)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"textTransform":"capitalize"}},"fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size" style="text-transform:capitalize">Newsletter Prompt!</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/columns -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile"><!-- wp:modfarm/column {"width":"49%"} -->
<div class="mf-column valign-top" style="width:49%"><!-- wp:image {"lightbox":{"enabled":false},"id":3322,"sizeSlug":"large","linkDestination":"custom","align":"center"} -->
<figure class="wp-block-image aligncenter size-large"><img src="https://test1.modfarmdev.tempurl.host/wp-content/uploads/sites/2/2025/12/Fantasy-Reader-HD-v1-647x1024.jpg" alt="" class="wp-image-3322"/></figure>
<!-- /wp:image --></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"verticalAlign":"middle","width":"49%"} -->
<div class="mf-column valign-middle" style="width:49%"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Prompt Secondary Line!</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Intro Text</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-mf-button","style":{"border":{"radius":"0px"}}} -->
<div class="wp-block-button is-style-mf-button"><a class="wp-block-button__link wp-element-button" href="/newsletter" style="border-radius:0px">Get Your Free Book!</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph -->
<p>More about the offer and why someone should sign up.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->
',
];
