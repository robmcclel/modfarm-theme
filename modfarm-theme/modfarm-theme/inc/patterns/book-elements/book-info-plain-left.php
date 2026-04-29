<?php
return [
    'slug'        => 'modfarm/book-info-plain-left',
    'title'       => 'Book Info Plain Left',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:modfarm/columns -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile"><!-- wp:modfarm/column {"width":"32%"} -->
<div class="mf-column valign-top" style="width:32%"><!-- wp:modfarm/book-cover-art {"coverType":"featured"} /-->

<!-- wp:modfarm/book-page-audio {"titleText":""} /-->

<!-- wp:modfarm/book-page-buttons {"buttons":[{"meta_key":"kindle_url","label":"Buy The Book","bg_color":"","text_color":"","border_color":"","new_tab":true},{"meta_key":"text_sample_url","label":"Read A Sample","bg_color":"","text_color":"","border_color":"","new_tab":true},{"meta_key":"reviews_url","label":"See The Reviews","type":"inherit","new_tab":true,"bg_color":"","text_color":"","border_color":""},{"meta_key":"audible_url","label":"Buy The Audiobook","type":"inherit","new_tab":true,"bg_color":"","text_color":"","border_color":""}],"border_radius":1} /--></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"width":"65%"} -->
<div class="mf-column valign-top" style="width:65%"><!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->
',
];
