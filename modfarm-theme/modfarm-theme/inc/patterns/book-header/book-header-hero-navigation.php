<?php
return [
    'slug' => 'modfarm/book-header-hero-navigation',
    'title' => 'Book Header Hero — Top Navigation',
    'description' => 'Local book hero or site default, with top navigation and independently aligned book details.',
    'categories' => ['modfarm-book-header'],
    'content' => '<!-- wp:modfarm/hero-cover {"align":"full","structuredLayout":true} -->
<!-- wp:modfarm/hero-area {"area":"navigation"} --><!-- wp:modfarm/navigation-menu {"localStyle":true,"transparent":true} /--><!-- /wp:modfarm/hero-area -->
<!-- wp:modfarm/hero-area {"area":"content"} -->
<!-- wp:post-title {"level":1,"fontSize":"xlarge"} /-->

<!-- wp:modfarm/book-page-series {"alignment":"center","fontSize":24} /-->

<!-- wp:modfarm/book-author-credit {"alignment":"center","fontSize":20} /-->
<!-- /wp:modfarm/hero-area -->
<!-- /wp:modfarm/hero-cover -->',
];
