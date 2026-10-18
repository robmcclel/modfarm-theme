<?php
return [
    'slug' => 'modfarm/archive-header-hero-navigation',
    'title' => 'Archive Header Hero — Top Navigation',
    'description' => 'Explicit archive hero or site default, with top navigation and independently aligned archive title.',
    'categories' => ['modfarm-archive-header'],
    'content' => '<!-- wp:modfarm/hero-cover {"align":"full","structuredLayout":true,"contentVerticalAlign":"bottom","contentAlign":"left"} -->
<!-- wp:modfarm/hero-area {"area":"navigation"} --><!-- wp:modfarm/navigation-menu /--><!-- /wp:modfarm/hero-area -->
<!-- wp:modfarm/hero-area {"area":"content"} --><!-- wp:query-title {"type":"archive","showPrefix":false} /--><!-- /wp:modfarm/hero-area -->
<!-- /wp:modfarm/hero-cover -->',
];
