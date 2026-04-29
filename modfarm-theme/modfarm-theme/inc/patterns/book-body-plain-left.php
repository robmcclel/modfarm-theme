<?php
/**
 * Pattern: Book Body Plain Left
 * IMPORTANT: This file must not output anything (no whitespace/BOM). Return array only.
 */
return [
	'slug'        => 'modfarm/book-body-plain-left',
	'title'       => 'Book Body Plain Left',
	'description' => 'Book Body layout with cover art and audio on the left, book buttons, complete series, also like, and book details',
	'categories'  => [ 'modfarm-book-body' ],
	'keywords'    => [ 'page', 'header', 'basic' ],
	'content'     => <<<'HTML'
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"right":"var:preset|spacing|20","left":"var:preset|spacing|20","top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"layout":{"type":"constrained","contentSize":"","wideSize":""}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--20)"><!-- wp:group {"metadata":{"categories":["book-page-element"],"patternName":"core/block/3290","name":"Book Info Plain Left v1"},"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1000px","wideSize":"1200px"}} -->
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

<!-- wp:group {"metadata":{"categories":["book-page-element"],"patternName":"core/block/3289","name":"Book Page Complete Series v1"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1000px","wideSize":"1200px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","fontSize":"large"} -->
<h2 class="wp-block-heading has-text-align-center has-large-font-size">The Complete Series</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"exclude-current":false,"display-order":"ASC","books-per-page":20,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["book-page-element"],"patternName":"user/book-page-complete-series","name":"Book Page Also Like v1"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1000px","wideSize":"1200px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","fontSize":"large"} -->
<h2 class="wp-block-heading has-text-align-center has-large-font-size">You Might Also Like</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"tax-source":"genre","display-order":"rand","books-per-page":4,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained","contentSize":"1000px","wideSize":"1200px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Book Details</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["tax:book-series","tax:book-author","meta:audiobook_narrator","meta:publisher"]} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["meta:page_count","meta:audiobook_duration","meta:publication_date","meta:audiobook_publication_date"]} /--></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/advanced-book-details {"rows":["meta:price_ebook","meta:price_paper","meta:price_hard","meta:price_audio","meta:isbn13","meta:asin"]} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
HTML
,
];