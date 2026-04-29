<?php
return [
    'slug'        => 'modfarm/footer-simple',
    'title'       => 'Footer Simple',
    'description' => '',
    'categories'  => [ 'modfarm-book-footer', 'modfarm-page-footer', 'modfarm-archive-footer'],
    'keywords'    => [ 'footer', 'simple' ],
    'content'     => '
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem"}}},"backgroundColor":"modfarm-footer-bg","textColor":"modfarm-footer-text","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-modfarm-footer-text-color has-modfarm-footer-bg-background-color has-text-color has-background" style="padding-top:2rem;padding-bottom:2rem"><!-- wp:modfarm/theme-icon {"shape":"circle"} /-->

<!-- wp:site-title {"level":0,"textAlign":"center","style":{"typography":{"fontSize":"1.25rem"}}} /-->

<!-- wp:modfarm/navigation-menu {"leftMenu":9,"centerContent":"none","simpleAlign":"center","mode":"footer"} /-->

<!-- wp:paragraph {"align":"center","style":{"spacing":{"margin":{"top":"1rem"}}},"fontSize":"small"} -->
<p class="has-text-align-center has-small-font-size" style="margin-top:1rem">
        [modfarm_footer_login]
    </p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
',
];