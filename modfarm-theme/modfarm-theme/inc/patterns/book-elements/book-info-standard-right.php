<?php
return [
    'slug'        => 'modfarm/book-info-standard-right',
    'title'       => 'Book Info Standard Right',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:modfarm/columns {"reverseMobile":true} -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile reverse-mobile"><!-- wp:modfarm/column {"width":"65%"} -->
<div class="mf-column valign-top" style="width:65%"><!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"width":"32%"} -->
<div class="mf-column valign-top" style="width:32%"><!-- wp:modfarm/book-cover-art {"coverType":"featured"} /-->

<!-- wp:modfarm/book-page-audio {"titleText":""} /-->

<!-- wp:modfarm/book-page-sales-links {"autoDetect":false,"showLabels":true,"retailer1":"kindle_url","retailer2":"amazon_paper","retailer3":"audible_url","retailer4":"itunes"} /-->

<!-- wp:modfarm/book-page-buttons {"buttons":[{"meta_key":"text_sample_url","label":"Read A Sample","bg_color":"","text_color":"","border_color":"","new_tab":true},{"meta_key":"reviews_url","label":"See The Reviews","bg_color":"","text_color":"","border_color":"","new_tab":true}],"border_radius":1} /--></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->
',
];
