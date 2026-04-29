<?php

return [
    'title'      => 'Simple Single Row Footer',
    'slug'       => 'modfarm/footer-simple-single-row',
    'categories' => ['modfarm-footer'],
    'description'=> 'A clean, single-row footer with logo, nav, and social icons.',
    'content'    => '
<!-- wp:group {
    "align": "full",
    "backgroundColor": "modfarm-footer-bg",
    "textColor": "modfarm-footer-text",
    "layout": {
        "type": "constrained"
    },
    "style": {
        "spacing": {
            "padding": {
                "top": "2rem",
                "bottom": "2rem"
            }
        }
    }
} -->
<div class="wp-block-group alignfull has-modfarm-footer-bg-background-color has-modfarm-footer-text-color has-text-color has-background" style="padding-top:2rem;padding-bottom:2rem">
    <!-- wp:columns {"verticalAlignment":"center","isStackedOnMobile":true} -->
    <div class="wp-block-columns are-vertically-aligned-center is-stacked-on-mobile">
        
        <!-- wp:column {"verticalAlignment":"center","width":"25%"} -->
        <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:25%">
            <!-- wp:site-logo /-->
            <!-- wp:site-title {"level":0,"style":{"typography":{"fontSize":"1.25rem"}}} /-->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"verticalAlignment":"center","width":"75%"} -->
        <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:75%">
            <!-- wp:navigation {"layout":{"type":"flex","justifyContent":"right"}} /-->
            <!-- wp:social-links {"iconColor":"modfarm-footer-text","iconColorValue":"#ffffff","openInNewTab":true,"layout":{"type":"flex","justifyContent":"right"}} /-->
        </div>
        <!-- /wp:column -->

    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
',
];