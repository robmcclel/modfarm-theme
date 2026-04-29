<?php
return [
    'slug'        => 'modfarm/patreon-prompt-standard',
    'title'       => 'Patreon Prompt Standard',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"right":"var:preset|spacing|20","left":"var:preset|spacing|20","top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--20)"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}},"shadow":"var:preset|shadow|natural"}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);box-shadow:var(--wp--preset--shadow--natural)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Welcome, Adventurer!</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Intro Text</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>More explanatory text</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"shadow":"var:preset|shadow|natural","spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
<div class="wp-block-column" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);box-shadow:var(--wp--preset--shadow--natural)"><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><strong>Become A Patron</strong></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Benefit/Sell</h2>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Secondary Benefit</h2>
<!-- /wp:heading -->

<!-- wp:image {"id":159,"sizeSlug":"medium","linkDestination":"none","align":"center","className":"is-style-rounded"} -->
<figure class="wp-block-image aligncenter size-medium is-style-rounded"><img src="http://test1.modfarmdev.tempurl.host/wp-content/uploads/sites/2/2025/10/ModFarm-Icon-300x300.png" alt="" class="wp-image-159"/></figure>
<!-- /wp:image -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Auxiliary Info</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-mf-button","style":{"border":{"radius":"0px"}}} -->
<div class="wp-block-button is-style-mf-button"><a class="wp-block-button__link wp-element-button" href="https://www.patreon.com/edieskyeauthor" style="border-radius:0px">Become A Patron</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];
