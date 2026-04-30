# PPB / Skate System Notes

This document records what is visibly present in the repository about the PPB system and any apparent "Skate" or zone-wrapper concepts.

It does not assume platform behavior that is not visible in this repo.

## What PPB Appears To Mean Here

The repository contains a clear pattern-selection system for assembling layout regions through theme settings.

Evidence:

- pattern selector settings in [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php:377)
- lane/category mapping in [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php:479)
- recursive pattern registration in [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:432)
- post assembly logic in [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:564)
- archive pattern rendering logic in [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:652)
- explicit PPB comments in [singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php)

For documentation purposes, the safest visible description is:

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

## What "Skate" Appears To Mean Here

I did not find any explicit `Skate`-named class, namespace, function, module, or directory in this repository.

I did find "zone" language in:

- [functions.php:406](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:406)
- [functions.php:411](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:411)
- [functions.php:416](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:416)
- [functions.php:421](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:421)

The safest visible interpretation is:

- this repo contains a zone-based pattern lane system
- it may be related to what the wider ModFarm platform calls "Skate"
- this repo alone does not confirm that naming

## Visible Integration Points Relevant To PPB / Skate

- Settings UI: [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php)
- Pattern registration: [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php)
- Archive loader block: [blocks/archive-layout-loader](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-layout-loader)
- Content-slot block: [blocks/content-slot](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/content-slot)
- Hybrid singular rendering: [singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php)

## Known Unknowns

- TODO: define PPB acronym explicitly once confirmed from a source inside the ModFarm codebase.
- TODO: confirm whether "Skate" exists in another repository and only influences this repo via settings, slugs, or filters.
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
