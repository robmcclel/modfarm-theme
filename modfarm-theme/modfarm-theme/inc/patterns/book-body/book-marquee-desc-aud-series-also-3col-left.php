<?php
/**
 * Pattern: Book Marquee Desc Aud Series Also 3col Left
 * Slug: modfarm/book-marquee-desc-aud-series-also-3col-left
 */

return [
  'slug'       => 'modfarm/book-marquee-desc-aud-series-also-3col-left',
  'title'      => 'Book Marquee Desc Aud Series Also 3col Left',
  'categories' => [ 'modfarm-book-body' ],
  'content'    => <<<'HTML'
<!-- wp:group {"metadata":{"patternName":"core/block/633","name":"Book Standard Left Series Also Centered","categories":["book-layouts"]},"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--20)"><!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-page-marquee-right","name":"Book Page Marquee Right"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|20","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--20)"><!-- wp:modfarm/columns {"reverseMobile":true} -->
<div class="wp-block-modfarm-columns mf-columns stack-mobile reverse-mobile"><!-- wp:modfarm/column {"verticalAlign":"middle","width":"49%"} -->
<div class="mf-column valign-middle" style="width:49%"><!-- wp:post-title {"level":1,"style":{"typography":{"fontSize":"56px","textTransform":"uppercase"}}} /-->

<!-- wp:modfarm/book-page-series {"fontSize":32} /-->

<!-- wp:modfarm/book-author-credit {"fontSize":28} /-->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:modfarm/book-page-sales-links {"introText":"BUY THE BOOK HERE","linksAlign":"left","autoDetect":false,"retailer1":"kindle_url","retailer2":"amazon_paper","retailer3":"audible_url"} /-->

<!-- wp:modfarm/book-page-sales-links {"introText":"","linksAlign":"left","buttonSize":40,"autoDetect":false,"retailer1":"nook","retailer2":"barnes_paper","retailer3":"ibooks","retailer4":"itunes","retailer5":"kobo","retailer6":"bookshop_paper"} /-->

<!-- wp:spacer {"height":"15px"} -->
<div style="height:15px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:modfarm/format-icons {"size":32,"gap":4,"label":"AVAILABLE FORMATS"} /--></div>
<!-- /wp:modfarm/column -->

<!-- wp:modfarm/column {"width":"49%"} -->
<div class="mf-column valign-top" style="width:49%"><!-- wp:modfarm/book-cover-art {"coverType":"featured"} /--></div>
<!-- /wp:modfarm/column --></div>
<!-- /wp:modfarm/columns --></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-description-left-heading","name":"Book Description Left Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading -->
<h2 class="wp-block-heading">Description</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-description /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements","audiobook"],"patternName":"modfarm/book-page-available-audio-left","name":"Book Page Available Audio Left"},"style":{"spacing":{"padding":{"right":"0","left":"0","top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:0;padding-bottom:var(--wp--preset--spacing--50);padding-left:0"><!-- wp:heading {"className":"wp-block-heading has-text-align-left","style":{"typography":{"textTransform":"capitalize"}}} -->
<h2 class="wp-block-heading has-text-align-left" style="text-transform:capitalize">Available In Audio</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:modfarm/book-cover-art {"coverType":"cover_image_audio"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:modfarm/book-page-audio /-->

<!-- wp:modfarm/advanced-book-details {"rows":["meta:audiobook_narrator","meta:audiobook_duration","meta:audiobook_publication_date",""]} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-page-series-left-heading","name":"Book Page Series Left Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|0","right":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--0)"><!-- wp:heading {"textAlign":"left","fontSize":"large"} -->
<h2 class="wp-block-heading has-text-align-left has-large-font-size">The Complete Series</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"exclude-current":false,"display-order":"ASC","books-per-page":20,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-page-also-like-left-heading","name":"Book Page Also Like Left Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|0","right":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--0)"><!-- wp:heading {"className":"wp-block-heading has-text-align-left has-large-font-size"} -->
<h2 class="wp-block-heading has-text-align-left has-large-font-size">You Might Also Like</h2>
<!-- /wp:heading -->

<!-- wp:modfarm/book-page-tax {"tax-source":"genre","display-order":"rand","books-per-page":4,"book-format":{"id":2}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"categories":["modfarm-book-elements"],"patternName":"modfarm/book-details-3-column-left-heading","name":"Book Details 3 Column Left Heading"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"left"} -->
<h2 class="wp-block-heading has-text-align-left">Book Details</h2>
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
];
