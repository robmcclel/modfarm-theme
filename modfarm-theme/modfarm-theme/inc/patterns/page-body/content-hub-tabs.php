<?php
return [
    'slug'        => 'modfarm/content-hub-tabs',
    'title'       => 'Content Hub Tabs',
    'description' => 'A tabbed hub with recent posts, updates, and events.',
    'categories'  => [ 'modfarm-page-body' ],
    'keywords'    => [ 'tabs', 'posts', 'updates', 'events', 'activity', 'hub' ],
    'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|20","right":"var:preset|spacing|20"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--20)">
<!-- wp:modfarm/simple-tabs {"tabs":[{"id":"posts","title":"Posts"},{"id":"updates","title":"Updates"},{"id":"events","title":"Events"}],"activeIndex":0,"defaultActiveIndex":0,"navAlign":"left","variant":"underline","equalWidth":false,"tabShape":"rounded","showChevron":false} -->
<div class="wp-block-modfarm-simple-tabs mf-tabs mf-tabs--underline mf-tabs--align-left mf-tabs--shape-rounded"><div class="mf-tabs__nav" role="tablist"><button class="mf-tabs__btn is-active" type="button" data-tab="posts" role="tab" aria-selected="true">Posts</button><button class="mf-tabs__btn" type="button" data-tab="updates" role="tab" aria-selected="false">Updates</button><button class="mf-tabs__btn" type="button" data-tab="events" role="tab" aria-selected="false">Events</button></div><div class="mf-tabs__panels" data-active-index="0">
<!-- wp:modfarm/tab-panel {"title":"Posts","tabId":"posts"} -->
<div class="wp-block-modfarm-tab-panel mf-tab-panel is-active" data-tab-id="posts">
<!-- wp:query {"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false},"layout":{"type":"constrained"}} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
<!-- wp:post-title {"isLink":true,"level":2} /-->
<!-- wp:post-date /-->
<!-- wp:post-excerpt /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>No posts found.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:modfarm/tab-panel -->

<!-- wp:modfarm/tab-panel {"title":"Updates","tabId":"updates"} -->
<div class="wp-block-modfarm-tab-panel mf-tab-panel" data-tab-id="updates">
<!-- wp:modfarm/activity-stream {"layout":"grid","limit":6,"heading":"Updates","showHeading":false,"source":"composer","activityType":"updates"} /--></div>
<!-- /wp:modfarm/tab-panel -->

<!-- wp:modfarm/tab-panel {"title":"Events","tabId":"events"} -->
<div class="wp-block-modfarm-tab-panel mf-tab-panel" data-tab-id="events">
<!-- wp:modfarm/activity-stream {"layout":"grid","limit":6,"heading":"Events","showHeading":false,"source":"composer","activityType":"events"} /--></div>
<!-- /wp:modfarm/tab-panel --></div></div>
<!-- /wp:modfarm/simple-tabs --></div>
<!-- /wp:group -->
',
];
