<?php
return [
    'slug'        => 'modfarm/about-block-colorized',
    'title'       => 'About Block Colorized',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|80","right":"var:preset|spacing|20","bottom":"var:preset|spacing|80","left":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--20)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"textTransform":"capitalize"}},"fontSize":"xlarge"} -->
<h2 class="wp-block-heading has-text-align-center has-xlarge-font-size" style="text-transform:capitalize">About Me</h2>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:image {"id":159,"sizeSlug":"medium","linkDestination":"none","align":"center","className":"is-style-rounded"} -->
<figure class="wp-block-image aligncenter size-medium is-style-rounded"><img src="http://test1.modfarmdev.tempurl.host/wp-content/uploads/sites/2/2025/10/ModFarm-Icon-300x300.png" alt="" class="wp-image-159"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:paragraph -->
<p>A paragraph or two of information about the author</p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"size":"has-large-icon-size"} -->
<ul class="wp-block-social-links has-large-icon-size"><!-- wp:social-link {"url":"","service":"facebook"} /-->

<!-- wp:social-link {"service":"discord"} /-->

<!-- wp:social-link {"service":"patreon"} /-->

<!-- wp:social-link {"url":"","service":"instagram"} /-->

<!-- wp:social-link {"url":"","service":"youtube"} /-->

<!-- wp:social-link {"service":"x"} /-->

<!-- wp:social-link {"service":"bluesky"} /-->

<!-- wp:social-link {"service":"amazon"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];
