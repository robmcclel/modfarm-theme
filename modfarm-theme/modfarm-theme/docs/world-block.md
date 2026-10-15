# World block

Place **World** (`modfarm/world`) in a shared Series archive body pattern, a Book layout, or a landing page. It does not modify existing patterns or publish content when inserted.

## Series controls

Open a Series → World Bible → Publishing & display. Set the complete world to Published when ready. Each entry separately needs Published status and Public visibility. Existing worlds and entries without a status stay Draft until explicitly published. Internal and Members entries remain excluded.

Choose Sections or Tabs, enable and label sections, order them with Move up / Move down, choose the initial tab, and select full formatted descriptions or short context. Empty or disabled sections have no panel or tab. An unavailable initial tab falls back to the first visible section. Optional artwork can be hidden.

Books placement: Separate leaves Books to the layout's existing Book Archive List block. Inside reuses the archive renderer with an explicit Series context, preserving Series archive card controls and the main query. Remove the separate Books block from the chosen pattern when using Inside to avoid duplicate Books displays. The World block does not delete or suppress other blocks. Publishing the World is required even for its included Books section.

Spoiler-free is the default. Reader chooses finished book adds a GET form that renders eligible content after selection. Only published Series books with positive numeric series positions can unlock spoiler-bound entries. Withheld descriptions are never sent as hidden HTML or JSON. If all enabled content requires a finished book, the selector remains available without embedding that content. Hosting caches must respect query strings, as with other query-dependent archive views.

## Placement controls

Automatic uses the current `book-series` archive, otherwise a Book with exactly one assigned Series (including block post context in a query loop). If the Book has several Series, Automatic renders nothing; choose Manual for that placement. Manual selects the Series by name with search, and works on landing pages. Invalid manual selections never fall back to a different Series.

Block overrides are intentionally narrow: Sections/Tabs, Books placement, Background, Text, and Accent. Remaining settings live with the Series. Precedence is block override → Series override → ModFarm Settings semantic CSS tokens. Clearing a color restores inheritance. Series colors also include inactive tab background and active tab background/text.

The editor shows server-rendered published content for Manual placements and Book context. A shared archive pattern has no concrete Series while editing, so Automatic shows a context explanation. Switch to Manual to preview a particular published Series, then return to Automatic before saving the shared pattern. Draft/empty worlds show an editor message and no frontend output. The Series chooser requires the matching Core update.

## Rendering and accessibility

Rich text is sanitized using `wp_kses_post` and rendered with paragraph formatting; it is never escaped into literal HTML. Short context remains plain text. The block never renders authoring notes or raw source JSON.

Without JavaScript, all eligible sections remain visible. JavaScript enhances Tabs with tab/tabpanel roles, roving focus, Left/Right/Home/End navigation, RTL handling, and unique IDs per instance. Long tab rows scroll horizontally on narrow screens. Keyboard handling stays on tab buttons, leaving content keyboard interaction alone.

`contract.json` accompanies block metadata with human use guidance and machine-readable placement, privacy, dependency, and control rules. The editor uses a compact wireframe tab/section icon.

## Release / acceptance

Deploy matching Core and Theme updates. Review draft publishing, rich-text save/reload, Automatic Series and Book contexts, Manual landing-page placement, multi-Series ambiguity, section labels/order, default tab fallback, separate/included Books, colors/reset, mobile overflow, keyboard navigation, and spoiler selection. No archive pattern changes are applied automatically.

Automated checks: Core World Bible storage/privacy/authoring suite; Theme `php tests/world-block.php` and `node tests/world-tabs.cjs /tmp/world-block.html` against PHP-rendered fixtures, with `jsdom` installed.
