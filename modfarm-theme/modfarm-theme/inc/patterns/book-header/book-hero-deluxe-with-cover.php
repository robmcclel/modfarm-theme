<?php
return [
    'slug'        => 'modfarm/book-hero-deluxe-with-cover',
    'title'       => 'Book Hero Deluxe with Cover',
    'description' => 'Book hero with top navigation, cover art, title, format icons, series, and author credit.',
    'categories'  => ['modfarm-book-header'],
    'keywords'    => ['hero', 'navigation', 'cover'],
    'content'     => '
<!-- wp:modfarm/hero-cover {"metadata":{"name":"Book Hero Deluxe with Cover"},"structuredLayout":true,"minHeight":520,"dimRatio":35,"align":"full"} -->
<!-- wp:modfarm/hero-area {"area":"navigation"} -->
<!-- wp:modfarm/navigation-menu {"localStyle":true,"transparent":true} /-->
<!-- /wp:modfarm/hero-area -->

<!-- wp:modfarm/hero-area {"area":"content"} -->
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:modfarm/book-cover-art /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"66.66%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66.66%"><!-- wp:post-title {"level":1,"fontSize":"xlarge"} /-->

<!-- wp:modfarm/format-icons {"size":26,"align":"center"} /-->

<!-- wp:modfarm/book-page-series {"alignment":"center","fontSize":24} /-->

<!-- wp:modfarm/book-author-credit {"alignment":"center","fontSize":20} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
<!-- /wp:modfarm/hero-area -->
<!-- /wp:modfarm/hero-cover -->
',
];
