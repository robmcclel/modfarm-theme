<?php
return [
    'slug'        => 'modfarm/book-details-3-column-left-heading',
    'title'       => 'Book Details 3 Column Left Heading',
    'description' => '',
    'categories'  => [ 'modfarm-book-elements' ],
    'keywords'    => [],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:heading {"textAlign":"left"} -->
<h2 class="wp-block-heading has-text-align-left">Book Details</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:modfarm/advanced-book-details {"rows":["tax:book-series","tax:book-author","meta:audiobook_narrator","meta:publisher"]} /-->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:modfarm/advanced-book-details {"rows":["meta:page_count","meta:audiobook_duration","meta:publication_date","meta:audiobook_publication_date"]} /-->
</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:modfarm/advanced-book-details {"rows":["meta:price_ebook","meta:price_paper","meta:price_hard","meta:price_audio","meta:isbn13","meta:asin"]} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
',
];