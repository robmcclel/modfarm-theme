# Architecture Overview

This document summarizes the repository structure and visible architecture of `modfarm-theme`.

It intentionally avoids guessing. Anything not directly supported by the repository contents is marked `TODO`.

## High-Level Model

The theme appears to operate as a hybrid system with four overlapping layers:

1. Classic WordPress theme templates
2. Block-theme templates and block template parts
3. Custom dynamic blocks under `blocks/`
4. Pattern-driven layout assembly selected through ModFarm settings

The main integration point is [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php).

## Repository Map

- Theme root templates
  - [page.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/page.php)
  - [single.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/single.php)
  - [single-post.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/single-post.php)
  - [index.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/index.php)
  - [singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php)
- Block templates
  - [templates/archive.html](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/templates/archive.html)
  - [templates/blank.html](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/templates/blank.html)
- Template parts
  - [parts/header.html](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/parts/header.html)
  - [parts/footer.html](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/parts/footer.html)
  - [template-parts/book/card.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/card.php)
  - [template-parts/book/ui.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/ui.php)
- Theme services
  - [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php)
  - [inc/archive-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/archive-settings.php)
  - [inc/render-helpers.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/render-helpers.php)
  - [inc/query-books.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/query-books.php)
- Content building systems
  - [blocks](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks)
  - [inc/patterns](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/patterns)

## Template Layers

### Classic PHP Layer

Classic templates at the theme root mostly render `the_content()` inside wrapper markup:

- [page.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/page.php)
- [single.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/single.php)
- [single-post.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/single-post.php)

### Block Template Layer

Archive rendering is visibly block-template-based:

- [templates/archive.html](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/templates/archive.html)

That template delegates the archive page body to the custom block:

- [blocks/archive-layout-loader](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-layout-loader)

### Hybrid Layer

[singular-hybrid.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/singular-hybrid.php) is a visible integration point between classic content and pattern-driven chrome:

- PPB-selected header
- classic `the_content()` body
- PPB-selected footer

This file strongly suggests the theme supports selective use of pattern-driven layout chrome without pattern-driving the entire body.

## Block System

Custom blocks are stored under [blocks](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks) and registered centrally in [blocks/register-blocks.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/register-blocks.php).

Visible block groups include:

- book page blocks
- archive blocks
- taxonomy/listing blocks
- layout/utility blocks
- decorative/editor-facing blocks

Many are dynamic blocks using `render.php`.

## Pattern System

Pattern files live under [inc/patterns](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/patterns).

They are recursively registered from [functions.php:432](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php:432).

Visible pattern zones:

- `book-header`
- `book-body`
- `book-elements`
- `archive-header`
- `archive-body`
- `page-header`
- `page-body`
- `page-elements`
- `page-footer`
- `post-header`
- `post-body`
- `post-footer`

## Settings-Driven Composition

[inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php) contains pattern selector fields for:

- book header/body/footer
- page header/body/footer
- post header/body/footer
- archive header/body/footer
- selected archive taxonomy overrides

This is the clearest visible PPB control surface in the repo.

## Archive System

Archive rendering appears to combine three pieces:

1. block template shell: [templates/archive.html](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/templates/archive.html)
2. archive layout block: [blocks/archive-layout-loader/render.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-layout-loader/render.php)
3. settings-selected header/body/footer pattern slugs from [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php)

There is also archive term meta UI in [inc/archive-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/archive-settings.php).

## Shared UI Layer

Book-card UI composition appears centralized in:

- [template-parts/book/ui.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/ui.php)
- [template-parts/book/card.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/card.php)
- [inc/render-helpers.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/render-helpers.php)

Several blocks mention these helpers directly in comments or includes.

## Known Architectural Unknowns

- TODO: determine which runtime path is primary for singular books in production.
- TODO: determine whether archive routing uses `archive-defunct.php` anywhere or whether it is fully replaced by block templates.
- TODO: determine which slugs are intended to be canonical defaults for header/body/footer patterns.
- TODO: determine whether any "Skate" runtime exists outside this repo and only interfaces here through settings or filters.
