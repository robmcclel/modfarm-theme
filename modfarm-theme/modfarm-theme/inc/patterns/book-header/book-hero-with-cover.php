<?php
return [
    'slug'        => 'modfarm/book-hero-with-cover',
    'title'       => 'Book Hero with Cover',
    'description' => 'Book hero with top navigation, featured cover art, and centered title, series, and author credit.',
    'categories'  => ['modfarm-book-header'],
    'keywords'    => ['hero', 'navigation', 'cover'],
    'content'     => '
<!-- wp:group {"metadata":{"name":"Book Hero with Cover"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:modfarm/hero-cover {"structuredLayout":true,"align":"full"} -->
<!-- wp:modfarm/hero-area {"area":"navigation","metadata":{"name":"Hero navigation"}} -->
<!-- wp:modfarm/navigation-menu {"localStyle":true,"transparent":true} /-->
<!-- /wp:modfarm/hero-area -->

<!-- wp:modfarm/hero-area {"area":"content","metadata":{"name":"Hero content"}} -->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)"><!-- wp:columns {"verticalAlignment":null} -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:modfarm/book-cover-art {"coverType":"featured"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"66.66%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66.66%"><!-- wp:post-title {"level":1,"style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}},"typography":{"textAlign":"center"}},"textColor":"base"} /-->

<!-- wp:modfarm/book-page-series {"alignment":"center","fontSize":24,"textColor":"#ffffff"} /-->

<!-- wp:modfarm/book-author-credit {"alignment":"center","fontSize":22} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
<!-- /wp:modfarm/hero-area -->
<!-- /wp:modfarm/hero-cover --></div>
<!-- /wp:group -->
',
];
