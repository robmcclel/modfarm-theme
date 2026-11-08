<?php
return [
    'slug'        => 'modfarm/book-landing-hero',
    'title'       => 'Book Landing Hero',
    'description' => 'Launch header with the current book’s cover, short description, and reader links.',
    'categories'  => ['modfarm-book-header'],
    'content'     => <<<'HTML'
<!-- wp:modfarm/hero-cover {"structuredLayout":true,"minHeight":520,"dimRatio":35,"metadata":{"name":"Book Landing Hero"},"align":"full"} -->
<!-- wp:modfarm/hero-area {"area":"navigation"} -->
<!-- wp:modfarm/navigation-menu {"localStyle":true,"transparent":true,"align":"full"} /-->
<!-- /wp:modfarm/hero-area -->

<!-- wp:modfarm/hero-area {"area":"content"} -->
<!-- wp:modfarm/featured-banner {"bookSource":"current","heroSource":"none","headlineTag":"h1","buttonAlign":"left","descFontSize":28,"btn1Source":"kindle_url","btn1Variant":"primary","btn2Source":"text_sample_url","btn2Label":"Read Sample","btn3Source":"reviews_url","btn3Label":"Read Reviews","buttonStyleMode":"custom","btnOutlineText":"#ffffff","btnOutlineBorder":"#ffffff"} /-->
<!-- /wp:modfarm/hero-area -->
<!-- /wp:modfarm/hero-cover -->
HTML
];
