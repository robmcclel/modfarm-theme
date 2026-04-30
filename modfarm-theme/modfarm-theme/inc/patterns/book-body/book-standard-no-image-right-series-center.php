<?php
/**
 * Pattern: Book Standard No Image Right Series Center
 * Slug: modfarm/book-standard-no-image-right-series-center
 */

return [
  'slug'       => 'modfarm/book-standard-no-image-right-series-center',
  'title'      => 'Book Standard No Image Right Series Center',
  'categories' => [ 'modfarm-book-body' ],
  'content'    => <<<'HTML'
<!-- wp:group {"metadata":{"patternName":"core/block/634","name":"Book Plain Left Series Also Left"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--20)"><!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-info-standard-right-no-image","name":"Book Info Standard Right No Image"},"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:modfarm/columns {"reverseMobile":true} -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile reverse-mobile"><!-- wp:modfarm/column {"width":"65%"} -->
<div class="mf-column valign-top" style="width:65%"><!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"width":"32%"} -->
<div class="mf-column valign-top" style="width:32%"><!-- wp:modfarm/book-page-audio {"titleText":""} /-->

<!-- wp:modfarm/book-page-sales-links {"autoDetect":false,"showLabels":true,"retailer1":"kindle_url","retailer2":"amazon_paper","retailer3":"audible_url","retailer4":"itunes"} /-->

<!-- wp:modfarm/book-page-buttons {"buttons":[{"meta_key":"text_sample_url","label":"Read A Sample","bg_color":"","text_color":"","border_color":"","new_tab":true},{"meta_key":"reviews_url","label":"See The Reviews","bg_color":"","text_color":"","border_color":"","new_tab":true}],"border_radius":1} /--></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-page-series-left-heading","name":"Book Page Series Left Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","fontSize":"large"} -->
<h2 class="wp-block-heading has-text-align-center has-large-font-size">The Complete Series</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"exclude-current":false,"display-order":"ASC","books-per-page":20,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-details-2-column-left-heading","name":"Book Details 2 Column Left Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1000px","wideSize":"1200px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Book Details</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["tax:book-series","tax:book-author","meta:audiobook_narrator","meta:publisher","meta:page_count","meta:audiobook_duration"]} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["meta:price_ebook","meta:price_paper","meta:price_hard","meta:price_audio","meta:isbn13","meta:asin","meta:publication_date","meta:audiobook_publication_date"]} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
HTML
];
