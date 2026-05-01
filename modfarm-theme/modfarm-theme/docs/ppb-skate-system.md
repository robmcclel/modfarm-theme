# PPB / Skate System Notes

This document records what is visibly present in the repository about the PPB system and any apparent "Skate" or zone-wrapper concepts.

It does not assume platform behavior that is not visible in this repo.

## What PPB Means Here

The repository contains a clear pattern-selection system for assembling layout regions through theme settings.

Evidence:

- pattern selector settings in [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php:377)
- lane/category mapping in [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php:479)
- recursive pattern registration in [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:432)
- post assembly logic in [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:564)
- archive pattern rendering logic in [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:652)
- explicit PPB comments in [singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php)

For documentation purposes, the safest visible description is:

- PPB stands for `Pattern Page Builder`.
- PPB is a settings-driven pattern composition system for pages, posts, books, and archives.

## Visible PPB Concepts

### Pattern Lanes

Pattern lanes are mapped in [inc/modfarm-settings.php:479](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php:479).

Visible lanes include:

- `modfarm-book-header`
- `modfarm-book-body`
- `modfarm-book-footer`
- `modfarm-page-header`
- `modfarm-page-body`
- `modfarm-page-footer`
- `modfarm-post-header`
- `modfarm-post-body`
- `modfarm-post-footer`
- `modfarm-archive-header`
- `modfarm-archive-body`
- `modfarm-archive-footer`

### Pattern Registration

Patterns are registered from PHP files under [inc/patterns](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/patterns) in [functions.php:432](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:432).

User-created `wp_block` patterns are also re-registered under `user/*` slugs in [functions.php:459](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:459).

### New Content Assembly

[functions.php:564](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:564) assembles content for new posts/pages/books by concatenating selected header/body/footer patterns when:

- the post is newly inserted
- it is not an update, autosave, or import
- content is currently empty
- a hybrid/body bypass does not prevent assembly

Current Phase 2 direction now adds explicit `modfarm/zone` wrappers when new singular PPB content is assembled.

This does not migrate older content automatically.

### Hybrid Mode

[singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php) uses:

- PPB header
- classic body
- PPB footer

That is a strong sign that PPB can be used partially, not only as full-body composition.

### Archive PPB

Archives appear to use selected patterns for:

- archive header
- archive body
- archive footer

Resolution logic appears in:

- [functions.php:517](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:517)
- [functions.php:652](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:652)
- [blocks/archive-layout-loader/render.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-layout-loader/render.php)

## What "Skate" Means In ModFarm Direction

Authoritative design intent:

- Skate is the mostly invisible base platform beneath the visible layout
- it is analogous to an EV skateboard chassis: the control systems and structural base live underneath, while different visible bodies can be attached on top

In ModFarm terms, Skate should be understood as the layer that carries:

- explicit zones
- content/data plumbing
- future structured data behavior
- future SEO and AI discoverability support
- stable rendering foundations that visible PPB layouts can attach to

That means PPB and Skate are related but not identical:

- PPB chooses and swaps visible layout patterns
- Skate provides the underlying structural platform those patterns attach to

Visible repository signals that fit this direction include:

- zone/pattern lane registration in [functions.php:406](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:406)
- explicit `modfarm/zone` wrappers
- `modfarm/content-slot` as body/content plumbing
- hybrid and singular template shells that can act as stable structural bases

Current repo limitation:

- there is still no explicit `Skate`-named runtime in this repository
- the term is therefore architectural and directional here, not yet a fully formalized code namespace

## Visible Integration Points Relevant To PPB / Skate

- Settings UI: [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php)
- Pattern registration: [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php)
- Archive loader block: [blocks/archive-layout-loader](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-layout-loader)
- Content-slot block: [blocks/content-slot](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/content-slot)
- Zone wrapper block: [blocks/zone](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/zone)
- Zone detector helper: [inc/ppb-zone-detector.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/ppb-zone-detector.php)
- Hybrid singular rendering: [singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php)

## Template Chassis Direction

The current architectural direction is to treat singular templates more like Skate chassis than like final one-off layouts.

Practical implication:

- `single.php`
- `singular-hybrid.php`
- future variants such as a sidebar-capable hybrid singular template

should be thought of as stable structural bases that define zone and data behavior while allowing visible layout layers to vary above them.

## Explicit Zone Support

The repository now includes an explicit `modfarm/zone` block intended for Phase 2 PPB and future Skate workflows.

Visible zone attributes:

- `slot`
- `origin`
- `pattern`
- `locked`
- `version`

Supported slot values are intended to be:

- `header`
- `body`
- `footer`
- `data`

Current behavior:

- frontend renders only inner blocks
- no visible frontend wrapper, label, spacing, or PPB terminology is added
- editor view shows a visible boundary and management information

This block exists as infrastructure for future zone-based workflows. Existing content is not automatically rewritten to use it, but newly assembled singular PPB content can store explicit zones.

## Zone Detection

The zone detector helper parses `post_content` and reports:

- whether content is zoned
- which zones exist
- whether the body zone contains `modfarm/content-slot`
- whether content appears legacy PPB or plain

Current intended use:

- planning and admin tooling
- future local PPB manager behavior
- future Apply All safety checks

It does not alter rendering or migrate content by itself.

## Portable Content-Slot Storage

The repository now includes a first-pass slot payload storage layer in:

- [inc/content-slot-payloads.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/content-slot-payloads.php)

Current scope:

- harvest manual `content-slot` `InnerBlocks` from `post_content` on save
- store payloads in post meta by slot ID
- preserve payloads even if the current visible pattern no longer contains a matching `content-slot`
- when an active `content-slot` is empty, render-time logic can now rehydrate matching stored payloads by slot ID without rewriting `post_content`

Current hard rule:

- Never auto-delete slot payloads when a `content-slot` block is removed.

This establishes the hidden Skate-side storage needed for future PPB zone replacement and Apply All behavior, where matching slot IDs should eventually allow manual content to survive redesigns and reappear in a new location.

## Local PPB Manager

The repository now includes an early local PPB manager in the block editor sidebar.

Current visible scope:

- Pages
- Books
- Posts
- Offers, if the `offer` post type exists on the site

Current visible fields:

- content state: `Zoned`, `Legacy PPB`, or `Plain`
- layout mode
- Header Zone status
- Body Zone status
- Footer Zone status
- whether the body zone contains `modfarm/content-slot`
- Data Zone marked as future/not active

Current safe actions:

- Header Zone replace for Zoned content
- Footer Zone replace for Zoned content
- Header/Footer replace for Hybrid templates through local dynamic overrides
- no Body Zone replace
- no Apply All actions
- no migration
- no legacy/plain auto-conversion

Current safety model:

- Zoned content can be changed surgically by zone
- Legacy PPB content is detectable but not yet locally rewritten
- Plain content is left alone
- Hybrid treats the body as authored content and only exposes local PPB control for header/footer chrome

## Canonical Fallback Defaults

The repository now has a concrete canonical fallback set for unresolved or unset PPB defaults:

- archive header: `modfarm/archive-header-basic`
- archive body: `modfarm/basic-archive-layout`
- archive footer: `modfarm/footer-simple`
- page header: `modfarm/page-header-basic-left`
- page body: `modfarm/page-clear`
- page footer: `modfarm/footer-simple`
- post header: `modfarm/post-header-basic-left`
- post body: `modfarm/post-body-basic`
- post footer: `modfarm/post-footer-simple-comments`
- book header: `modfarm/book-header-basic-left`
- book body: `modfarm/book-plain-left-series-left`
- book footer: `modfarm/footer-simple`

For hybrid singular rendering, the template-part fallback slugs are:

- `header`
- `footer`

The current canonical fallback model includes usable body defaults for page, post, book, and archive PPB rendering.

In the ModFarm Settings UI, the intended user-facing fallback choice is `Default`.

Legacy blank values and older `none`-style placeholders should be treated the same as `Default` and resolve to the canonical PPB defaults above.

Legacy unprefixed book body slugs should resolve to the canonical `modfarm/...` forms for compatibility with older settings values.

## Known Unknowns

- TODO: confirm whether a formal `Skate` runtime exists in another repository and only influences this repo via settings, slugs, or filters.
- TODO: confirm whether `modfarm_is_hybrid_post()` and `modfarm_set_template_origin()` are defined outside this repository.
- TODO: confirm the intended relationship between pattern lanes and any broader zone-wrapper runtime.

## ModFarm Direction (Authoritative)

PPB is not just a settings-driven pattern system.

It is intended to evolve into a full Pattern-Based Page Builder that:

- Applies patterns to specific zones (header, body, footer, data)
- Supports rapid layout switching across entire content types
- Allows site-wide redesign via pattern selection (e.g., "Apply All")
- Enables consistent layout across books, pages, posts, archives, offers, and webcomic content
- Integrates with a structured data layer ("Skate") for SEO and AI discoverability

The current implementation is partial and inconsistent.

Future work SHOULD:

- move toward explicit zone-based template structure
- unify pattern application logic across content types
- reduce reliance on ad-hoc template rendering
- introduce consistent header/body/footer/data zones

Codex is expected to help evolve the system toward this direction, not only preserve the current behavior.
