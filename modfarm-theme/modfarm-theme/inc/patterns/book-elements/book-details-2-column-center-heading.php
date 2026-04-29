<?php
return [
    'slug'        => 'modfarm/book-details-2-column-center-heading',
    'title'       => 'Book Details 2 Column Center Heading',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center"} -->
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
<!-- /wp:group -->
',
];