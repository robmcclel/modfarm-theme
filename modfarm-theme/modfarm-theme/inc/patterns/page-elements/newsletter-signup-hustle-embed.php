<?php
return [
    'slug'        => 'modfarm/newsletter-signup-hustle-embed',
    'title'       => 'Newsletter Signup Hustle Embed',
    'description' => '',
    'categories'  => [ 'modfarm-page-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"align":"full","className":"is-style-mf-primary","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-mf-primary" style="padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--30)"><!-- wp:heading {"textAlign":"center","align":"full","fontSize":"xlarge"} -->
<h2 class="wp-block-heading alignfull has-text-align-center has-xlarge-font-size">Join Newsletter Prompt</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:hustle/embedded {"id":"1"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:paragraph -->
<p>Couple of lines about the benefits of signing up for your newsletter.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
',
];
