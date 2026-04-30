# Block Inventory

This inventory reflects the block directories and registration state visible in the repository at audit time.

It does not claim runtime correctness beyond what is present in the files.

## Registration Source

Primary registrar:

- [blocks/register-blocks.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/register-blocks.php)

## Summary

- Block directories found: 37
- Common block shape: `block.json` + `index.js` + `render.php` + styles
- Shared helper directory present: `blocks/shared`

## Block Directories Found

- `advanced-book-details`
- `archive-book-list`
- `archive-layout-loader`
- `book-author-credit`
- `book-cover-art`
- `book-details`
- `book-page-audio`
- `book-page-buttons`
- `book-page-description`
- `book-page-sales-links`
- `book-page-series`
- `book-page-short-description`
- `book-page-tax`
- `column`
- `columns`
- `coming-soon-list`
- `content-slot`
- `creator-credit`
- `featured-banner`
- `featured-book`
- `format-icons`
- `generic-cards`
- `handpicked-books`
- `hero-cover`
- `multi-tax-format`
- `navigation-menu`
- `series-nav`
- `shared`
- `simple-gallery`
- `simple-tab`
- `simple-tabs`
- `site-background`
- `tab-panel`
- `table-of-contents`
- `tax-description`
- `taxonomy-grid`
- `theme-icon`

## Registrar Drift

### Directory Exists But Not Registered In Registrar

- `simple-tab`

Notes:

- `simple-tab` is treated here as legacy and replaced in practice by `tab-panel`.
- `simple-tab` is not being re-added to the registrar.

### Helper-Only Directories

- `shared`

Notes:

- `shared` appears to be a helper-only directory, not a standalone block.
- Current visible helper file: [blocks/shared/book-card-controls.js](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/shared/book-card-controls.js)

## Structural Differences Observed

### Nonstandard Or Inconsistent Asset Naming

- [blocks/book-page-description/style-editor.css](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/book-page-description/style-editor.css) uses `style-editor.css` instead of the more common `editor.css`

### Missing Editor CSS

These block directories do not visibly contain `editor.css` or `style-editor.css`:

- `book-page-short-description`
- `creator-credit`
- `simple-gallery`
- `tax-description`

### Missing Frontend Style CSS

These block directories do not visibly contain `style.css`:

- `content-slot`

## Notable Blocks

### Archive Composition

- [blocks/archive-layout-loader](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-layout-loader)
- [blocks/archive-book-list](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/archive-book-list)

### PPB-Related Utility

- [blocks/content-slot](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/content-slot)

### Replacement / Consolidation Notes

- `book-page-tax` should be treated as the current replacement for older `book-page-series` / `book-page-author-books` use cases where taxonomy-driven output is needed.
- Footer layouts should be treated as pattern-driven layout concerns, using footer patterns and column-based composition rather than dedicated footer block directories.
- `simple-tab` should be treated as legacy / replaced by `tab-panel`.

### Shared Book/Card UI Consumers

Blocks that visibly reference shared book-card UI helpers include:

- `book-page-audio`
- `coming-soon-list`
- `handpicked-books`
- `multi-tax-format`

Shared helper paths:

- [template-parts/book/card.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/card.php)
- [template-parts/book/ui.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/ui.php)

## Archived / Suspicious Artifacts In Blocks Area

Committed zip files:

- [blocks/book-page-short-description.zip](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/book-page-short-description.zip)
- [blocks/featured-banner.zip](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/featured-banner.zip)
- [blocks/hero-cover.zip](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/hero-cover.zip)

These should be treated as uncertain until their purpose is confirmed.

## TODO

- TODO: confirm whether any old content still serializes `simple-tab` directly in saved post content.
- TODO: confirm which blocks are considered production-ready versus experimental.

## Expected Block Standard (Target)

All blocks SHOULD eventually conform to:

- `block.json`
- `index.js` (editor)
- `render.php` (dynamic render when needed)
- `editor.css`
- `style.css` (frontend)

Blocks should:

- use consistent naming
- use consistent meta access patterns
- avoid duplicating logic across blocks
- prefer shared helpers where possible
