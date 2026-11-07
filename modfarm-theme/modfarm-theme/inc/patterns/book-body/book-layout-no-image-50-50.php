<?php
return [
    'slug'        => 'modfarm/book-layout-no-image-50-50',
    'title'       => 'Book Layout No Image 50-50',
    'description' => 'Equal columns for the book description and reader links with a series grid, followed by book details.',
    'categories'  => ['modfarm-book-body'],
    'content'     => <<<'HTML'
<!-- wp:group {"metadata":{"name":"Book Layout No Image 50-50"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull"><!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--20)"><!-- wp:modfarm/columns {"reverseMobile":true} -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile reverse-mobile"><!-- wp:modfarm/column {"width":"49%"} -->
<div class="mf-column valign-top" style="width:49%"><!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center">About</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"width":"49%"} -->
<div class="mf-column valign-top" style="width:49%"><!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center">Learn More</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-buttons {"buttons":[{"meta_key":"kindle_url","label":"Read On Kindle Unlimited","bg_color":"#ca0007","text_color":"#ffffff","border_color":"#ca0007","new_tab":true},{"meta_key":"text_sample_url","label":"Read A Sample","bg_color":"#000000","text_color":"#ffffff","border_color":"#ffffff","new_tab":true},{"meta_key":"reviews_url","label":"See The Reviews","bg_color":"#000000","text_color":"#ffffff","border_color":"#ffffff","new_tab":true}],"showAdvanced":true} /-->

<!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center">The Complete Series</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"exclude-current":false,"books-in-row":"33.33%","display-order":"ASC","cardUseGlobal":false,"cardShowTitle":false,"cardShowSeries":false,"cardShowPrimaryButton":false,"cardShowSampleButton":false} /--></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"700px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--20)"><!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center">Book Details</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["tax:book-series","tax:book-genre","tax:book-format","meta:audiobook_narrator"]} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["meta:publisher","meta:publication_date","meta:audiobook_publication_date","meta:audiobook_duration"]} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
HTML
];
