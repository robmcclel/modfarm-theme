<?php
/**
 * Pattern: Book Plain Right Series Centered
 * Slug: modfarm/book-plain-right-series-centered
 */

return [
  'slug'       => 'modfarm/book-plain-right-series-centered',
  'title'      => 'Book Plain Right Series Centered',
  'categories' => [ 'modfarm-book-body' ],
  'content'    => <<<'HTML'
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--20)"><!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-info-plain-right","name":"Book Info Plain Right"},"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:modfarm/columns {"reverseMobile":true} -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile reverse-mobile"><!-- wp:modfarm/column {"width":"65%"} -->
<div class="mf-column valign-top" style="width:65%"><!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"width":"32%"} -->
<div class="mf-column valign-top" style="width:32%"><!-- wp:modfarm/book-cover-art {"coverType":"featured"} /-->

<!-- wp:modfarm/book-page-audio {"titleText":""} /-->

<!-- wp:modfarm/book-page-buttons {"buttons":[{"meta_key":"kindle_url","label":"Buy The Book","bg_color":"","text_color":"","border_color":"","new_tab":true},{"meta_key":"text_sample_url","label":"Read A Sample","bg_color":"","text_color":"","border_color":"","new_tab":true},{"meta_key":"reviews_url","label":"See The Reviews","type":"inherit","new_tab":true,"bg_color":"","text_color":"","border_color":""},{"meta_key":"audible_url","label":"Buy The Audiobook","type":"inherit","new_tab":true,"bg_color":"","text_color":"","border_color":""}],"border_radius":1} /--></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-page-series-center-heading","name":"Book Page Series Center Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","fontSize":"large"} -->
<h2 class="wp-block-heading has-text-align-center has-large-font-size">The Complete Series</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"exclude-current":false,"display-order":"ASC","books-per-page":20,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-details-2-column-center-heading","name":"Book Details 2 Column Center Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1000px","wideSize":"1200px"}} -->
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
