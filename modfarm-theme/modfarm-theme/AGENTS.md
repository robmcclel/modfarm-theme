# ModFarm Theme Agent Notes

This file is for coding agents and contributors working in `modfarm-theme`.

## Scope

This repository appears to be a hybrid WordPress theme for the ModFarm platform. It mixes:

- classic PHP templates
- block theme templates and template parts
- custom dynamic blocks under `blocks/`
- pattern-driven page composition under `inc/patterns/`
- ModFarm settings-driven pattern selection in `inc/modfarm-settings.php`

## Important Rules

- Treat this repo as a hybrid system, not a pure block theme and not a pure classic theme.
- Do not assume PPB and "Skate" are fully documented in code. Verify behavior locally before changing it.
- Do not remove "old", "defunct", duplicate, or zip files without confirming they are unused.
- Do not rename pattern slugs or block folders casually. Several systems appear to depend on string-based lookup.
- Mark uncertain behavior as `TODO` in docs or notes instead of guessing.

## Likely Architectural Centers

- Theme bootstrap and pattern registration: `functions.php`
- Block registration: `blocks/register-blocks.php`
- Pattern settings UI: `inc/modfarm-settings.php`
- Archive term settings: `inc/archive-settings.php`
- Shared book-card rendering: `template-parts/book/ui.php`
- Archive rendering block: `blocks/archive-layout-loader/`
- Hybrid singular template: `singular-hybrid.php`

## Known Risk Areas

- Default pattern slugs referenced in code may not match actual registered pattern slugs.
- Hybrid template fallback slugs may not match real template-part slugs.
- Block registrar and actual block directories are out of sync.
- There is no repo documentation, build metadata, or automated validation in the current tree.
- Old templates and zip artifacts make it hard to tell what is authoritative.

## Before Making Functional Changes

1. Read `README.md`.
2. Read `docs/architecture.md`.
3. Read `docs/ppb-skate-system.md`.
4. Read `docs/block-inventory.md`.
5. Verify the target file is part of the live path, not a legacy artifact.

## TODOs For Future Agents

- TODO: confirm which template path is authoritative for singular page/post/book rendering in production.
- TODO: confirm whether "Skate" logic exists in another ModFarm repo and only interfaces here through slugs/settings.
- TODO: confirm whether block zip files are archival exports or still part of an install workflow.
- TODO: confirm the intended canonical default pattern slugs.

## Development Intent

This repository is mid-transition.

It is moving from:

- ad-hoc template + pattern usage

Toward:

- a structured PPB + Skate architecture

Codex should:

- preserve working behavior
- avoid breaking existing sites
- but actively help move the codebase toward a more consistent architecture

When unsure:

- prefer small, safe structural improvements
- document assumptions
- do not block progress due to incomplete documentation
