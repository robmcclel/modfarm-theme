<?php
return [
    'slug'        => 'modfarm/book-page-marquee-right',
    'title'       => 'Book Page Marquee Right',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|20"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--20);padding-left:var(--wp--preset--spacing--0)"><!-- wp:modfarm/columns {"reverseMobile":true} -->
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
',
];