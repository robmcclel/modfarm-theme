# Current Priorities

This file translates the current audit into documentation-first priorities.

It is intentionally conservative and limited to what is visible in the repository.

## Highest Priority Risks

1. Default pattern slugs referenced in code do not visibly match the pattern slugs defined in `inc/patterns/`.
2. The hybrid singular template falls back to template-part slugs that do not visibly match the files in `parts/`.
3. Block registration and block directories are out of sync.
4. The repository contains legacy and defunct files without documentation explaining whether they are still part of runtime behavior.
5. There is no setup, architecture, or contribution documentation in the original repo state.

## Recommended Safe Work Queue

These are documentation and verification-oriented tasks that should be safe before deeper implementation:

1. Keep the new docs in sync with future discoveries.
2. Confirm the canonical default pattern slugs used by page/post/book/archive settings.
3. Confirm which template route is authoritative for:
   - pages
   - posts
   - books
   - archives
4. Confirm whether "Skate" behavior lives outside this repository.
5. Confirm whether block zip files and `*-old` templates are archival only.

## Implementation Priorities After Documentation

These are not done here, but they appear to be the first low-risk engineering follow-ups:

1. Reconcile block registrar names with actual `blocks/` directories.
2. Reconcile default PPB pattern slugs with actual registered pattern files.
3. Reconcile hybrid fallback template-part slugs with actual template-part names.
4. Remove or quarantine stale legacy artifacts after confirming they are unused.
5. Add a lightweight validation step for blocks and patterns.

## Canonical Fallback Defaults

The current canonical PPB fallback defaults should be treated as:

- archive header: `modfarm/archive-header-basic`
- archive body: `modfarm/basic-archive-layout`
- archive footer: `modfarm/footer-simple`
- page header: `modfarm/page-header-basic-left`
- page footer: `modfarm/footer-simple`
- post header: `modfarm/post-header-basic-left`
- post footer: `modfarm/post-footer-simple-comments`
- book header: `modfarm/book-header-basic-left`
- book footer: `modfarm/footer-simple`

Hybrid template-part fallbacks should be treated as:

- header part slug: `header`
- footer part slug: `footer`

Blank body defaults for page, post, and book remain unchanged.

## Areas That Need Confirmation Before Functional Refactors

- `functions.php` pattern defaults
- `singular-hybrid.php` template-part fallback logic
- archive rendering path between `templates/archive.html`, `archive-layout-loader`, and any legacy archive template
- interaction with external ModFarm repos, especially anything described informally as PPB or Skate

## TODO

- TODO: add confirmed owner-approved priorities once the ModFarm repo set is reviewed together.
- TODO: add a cross-repo dependency section if PPB/Skate behavior is documented elsewhere.
