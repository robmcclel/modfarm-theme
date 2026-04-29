<?php
return [
    'slug'        => 'modfarm/about-block-monochrome',
    'title'       => 'About Block Monochrome',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size">About Me</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:image {"id":159,"sizeSlug":"medium","linkDestination":"none","align":"center","className":"is-style-rounded"} -->
<figure class="wp-block-image aligncenter size-medium is-style-rounded"><img src="http://test1.modfarmdev.tempurl.host/wp-content/uploads/sites/2/2025/10/ModFarm-Icon-300x300.png" alt="" class="wp-image-159"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:paragraph -->
<p>A paragraph or two about me.</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"left","level":3} -->
<h3 class="wp-block-heading has-text-align-left"><strong>You Can Find Me At These Links</strong></h3>
<!-- /wp:heading -->

<!-- wp:social-links {"iconColor":"text","iconColorValue":"var(\\u002d\\u002dmf-body-text, #111111)","size":"has-huge-icon-size","className":"is-style-logos-only","layout":{"type":"flex","justifyContent":"left"}} -->
<ul class="wp-block-social-links has-huge-icon-size has-icon-color is-style-logos-only"><!-- wp:social-link {"url":"https://discord.com/invite/68CwEXv","service":"discord"} /-->

<!-- wp:social-link {"service":"instagram"} /-->

<!-- wp:social-link {"url":"https://www.patreon.com/sunrisecv","service":"patreon"} /-->

<!-- wp:social-link {"url":"https://twitter.com/sunrise_CV","service":"x"} /-->

<!-- wp:social-link {"service":"amazon"} /-->

<!-- wp:social-link {"service":"youtube"} /-->

<!-- wp:social-link {"service":"bluesky"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];
