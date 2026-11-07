<?php
return [
    'slug'        => 'modfarm/landing-page-header-v1',
    'title'       => 'Landing Page Header v1',
    'description' => 'Full-width landing page Hero with transparent navigation and a selectable featured book banner.',
    'categories'  => ['modfarm-page-header'],
    'content'     => <<<'HTML'
<!-- wp:modfarm/hero-cover {"metadata":{"name":"Landing Page Header v1"},"useFeaturedImage":true,"structuredLayout":true,"minHeight":620,"dimRatio":30,"align":"full"} -->
<!-- wp:modfarm/hero-area {"area":"navigation"} -->
<!-- wp:modfarm/navigation-menu {"localStyle":true,"transparent":true,"align":"full"} /-->
<!-- /wp:modfarm/hero-area -->

<!-- wp:modfarm/hero-area {"area":"content"} -->
<!-- wp:modfarm/featured-banner {"dimRatio":0,"heroSource":"custom","buttonAlign":"left","descFontSize":28,"btn1Source":"permalink","btn1Variant":"primary","btn2Source":"kindle_url","btn2Label":"Buy The Book","btn3Source":"audible_url","btn3Label":"Buy The Audiobook","buttonStyleMode":"custom","btnOutlineText":"#ffffff","btnOutlineBorder":"#ffffff"} /-->
<!-- /wp:modfarm/hero-area -->
<!-- /wp:modfarm/hero-cover -->
HTML
];
