<?php
return [
    'slug'        => 'modfarm/patreon-prompt-basic',
    'title'       => 'Patreon Prompt Basic',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"metadata":{"name":"Patreon Type 1"},"align":"full","className":"is-style-mf-secondary","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull is-style-mf-secondary" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)"><!-- wp:heading {"textAlign":"center","fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size">Patreon Prompt Line</h2>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"20px"} -->
<div style="height:20px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"id":160,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="http://test1.modfarmdev.tempurl.host/wp-content/uploads/sites/2/2025/10/cropped-ModFarm-Icon.png" alt="" class="wp-image-160"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","fontSize":"xlarge"} -->
<p class="has-text-align-center has-xlarge-font-size"><strong>Support On Patreon</strong></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-mf-button","style":{"border":{"radius":"0px"}}} -->
<div class="wp-block-button is-style-mf-button"><a class="wp-block-button__link wp-element-button" href="https://www.patreon.com/sunrisecv" style="border-radius:0px">Click Here To Be A Patron!</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><strong>Any support is much appreciated.</strong></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph -->
<p>A paragraph or two introducing yourself, explaining the benefits of your Patreon, and encouraging people to join.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];
