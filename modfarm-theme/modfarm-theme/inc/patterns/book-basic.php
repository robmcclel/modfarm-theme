<?php
return [
    'slug'        => 'modfarm/book-basic',
    'title'       => 'Basic Book Page Layout (Aethon Style)',
    'description' => 'A placeholder layout based on Aethon’s book page design. Includes top section, two-column layout, complete series, recommendations, and book details.',
    'categories'  => [ 'modfarm-book-body' ],
    'keywords'    => [ 'book', 'aethon', 'series', 'template', 'modfarm' ],
    'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":1} --><h1>Book Title</h1><!-- /wp:heading -->
	<!-- wp:paragraph --><p><strong>Series Name</strong></p><!-- /wp:paragraph -->
	<!-- wp:paragraph --><p>by Author Name</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:columns -->
<div class="wp-block-columns">
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":2} --><h2>Description</h2><!-- /wp:heading -->
		<!-- wp:paragraph --><p>This is the book description. Replace this text with your actual book blurb.</p><!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:image {"width":300,"height":450} -->
		<figure class="wp-block-image size-full"><img src="https://via.placeholder.com/300x450?text=Cover+Art" alt="Cover Art"/></figure>
		<!-- /wp:image -->

		<!-- wp:paragraph --><p><strong>Audio Sample:</strong> Embed optional player here.</p><!-- /wp:paragraph -->
		<!-- wp:paragraph --><p><strong>Price:</strong> Starting at $4.99</p><!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"backgroundColor":"black","textColor":"white"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-black-background-color has-text-color has-background">Buy on Kindle</a></div>
			<!-- /wp:button -->
			<!-- wp:button {"backgroundColor":"gray","textColor":"white"} -->
			<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-gray-background-color has-text-color has-background">Listen on Audible</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":3} --><h3>The Complete Series</h3><!-- /wp:heading -->
	<!-- wp:paragraph --><p>[Placeholder for Series Block]</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":3} --><h3>You Might Also Like</h3><!-- /wp:heading -->
	<!-- wp:paragraph --><p>[Placeholder for Recommendations]</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":3} --><h3>Book Details</h3><!-- /wp:heading -->
	<!-- wp:list -->
	<ul>
		<li>Series: [Series Name]</li>
		<li>Format: [eBook/Print/Audio]</li>
		<li>ISBN: [978-1234567890]</li>
		<li>ASIN: [B0XXXXXXX]</li>
		<li>Narrator: [Narrator Name]</li>
		<li>Pages: [123]</li>
		<li>Duration: [8 hrs]</li>
	</ul>
	<!-- /wp:list -->
</div>
<!-- /wp:group -->
',
];