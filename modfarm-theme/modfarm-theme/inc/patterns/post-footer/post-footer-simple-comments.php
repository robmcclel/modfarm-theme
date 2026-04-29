<?php
return [
    'slug'        => 'modfarm/post-footer-simple-comments',
    'title'       => 'Post Footer Simple Comments',
    'description' => '',
    'categories'  => [ 'modfarm-post-footer' ],
    'keywords'    => [ 'footer', 'post', 'comments' ],
    'content'     => '
<!-- wp:group {"metadata":{"categories":["modfarm-footer"],"patternName":"modfarm/footer-simple-centered","name":"Simple Post Footer v1"},"align":"full","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem"}}},"backgroundColor":"modfarm-footer-bg","textColor":"modfarm-footer-text","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-modfarm-footer-text-color has-modfarm-footer-bg-background-color has-text-color has-background" style="padding-top:2rem;padding-bottom:2rem"><!-- wp:group {"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group"><!-- wp:comments -->
<div class="wp-block-comments"><!-- wp:comments-title /-->

<!-- wp:comment-template -->
<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"40px"} -->
<div class="wp-block-column" style="flex-basis:40px"><!-- wp:avatar {"size":40,"style":{"border":{"radius":"20px"}}} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:comment-author-name {"fontSize":"small"} /-->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0px","bottom":"0px"}}},"layout":{"type":"flex"}} -->
<div class="wp-block-group" style="margin-top:0px;margin-bottom:0px"><!-- wp:comment-date {"fontSize":"small"} /-->

<!-- wp:comment-edit-link {"fontSize":"small"} /--></div>
<!-- /wp:group -->

<!-- wp:comment-content /-->

<!-- wp:comment-reply-link {"fontSize":"small"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:comment-template -->

<!-- wp:comments-pagination -->
<!-- wp:comments-pagination-previous /-->

<!-- wp:comments-pagination-numbers /-->

<!-- wp:comments-pagination-next /-->
<!-- /wp:comments-pagination -->

<!-- wp:post-comments-form /--></div>
<!-- /wp:comments --></div>
<!-- /wp:group -->

<!-- wp:modfarm/theme-icon {"shape":"circle"} /-->

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