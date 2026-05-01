# ModFarm Theme

`modfarm-theme` is a WordPress theme repository for the ModFarm platform.

Based on the current repository contents, this theme is not a simple single-mode theme. It appears to combine:

- classic PHP templates
- block templates and block template parts
- a custom block library
- a pattern-based layout system
- settings-driven pattern selection for pages, posts, books, and archives

In current ModFarm direction, that pattern system is increasingly paired with a "Skate" idea:

- PPB controls the visible layout and pattern selection
- Skate is the underlying structural platform that holds zones, data plumbing, and future control/discoverability layers
- visible templates can change while the underlying Skate remains stable

This README documents only behavior that is visible in the repository today. Unknowns are marked as `TODO`.

## Current State

The repo contains:

- theme bootstrap and runtime logic in `functions.php`
- 37 block directories under `blocks/`
- 116 PHP pattern files under `inc/patterns/`
- block-style templates under `templates/` and `parts/`
- classic templates at the theme root
- admin/settings code under `inc/`

There is currently no:

- `package.json`
- `composer.json`
- CI workflow
- setup guide
- contributor guide
- architecture guide outside the docs added here

## Key Paths

- Theme bootstrap: [functions.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/functions.php)
- Block registration: [blocks/register-blocks.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks/register-blocks.php)
- Blocks: [blocks](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/blocks)
- Pattern settings: [inc/modfarm-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/modfarm-settings.php)
- Archive term settings: [inc/archive-settings.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/archive-settings.php)
- Pattern library: [inc/patterns](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/inc/patterns)
- Block templates: [templates](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/templates)
- Block parts: [parts](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/parts)
- Shared book UI helpers: [template-parts/book/ui.php](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/template-parts/book/ui.php)

## Documentation Map

- Architecture overview: [docs/architecture.md](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/docs/architecture.md)
- PPB / Skate notes: [docs/ppb-skate-system.md](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/docs/ppb-skate-system.md)
- Block inventory: [docs/block-inventory.md](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/docs/block-inventory.md)
- Current priorities: [docs/current-priorities.md](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/docs/current-priorities.md)
- Agent guidance: [AGENTS.md](/C:/Users/robmc/Documents/Codex/2026-04-29/can-you-see-the-repositories-in/modfarm-theme/modfarm-theme/modfarm-theme/AGENTS.md)

## What Looks Live

These areas appear to be active:

- `functions.php` bootstrapping and registration
- `blocks/register-blocks.php`
- `blocks/*/block.json`, `index.js`, `render.php`
- `inc/modfarm-settings.php`
- `inc/archive-settings.php`
- `inc/patterns/**/*`
- `templates/archive.html`
- `singular-hybrid.php`

## Current Architectural Direction

The repo is moving toward a clearer separation between:

- PPB as the visible layout/pattern layer
- Skate as the mostly invisible structural base

The safest current interpretation is:

- pages, books, and offers are intended to favor Full PPB over time
- archives are intended to remain dynamic PPB
- posts are intended to default to Hybrid behavior unless explicitly moved further into Full PPB
- template files such as `single.php` and `singular-hybrid.php` should gradually behave more like stable Skate chassis than one-off layouts

The repository also now contains the beginning of a portable `content-slot` storage layer:

- manual slot content can be harvested from `post_content` and mirrored into post meta by slot ID
- this is intended to support future PPB redesigns where matching slot IDs can preserve content across pattern changes
- slot payloads should not be auto-deleted just because a visible slot is removed from the current layout

## What Looks Legacy Or Uncertain

- `archive-defunct.php`
- `templates/*-old.html`
- `parts/archive-header-old.html`
- zip files committed under `blocks/`
- several default pattern slug references that do not visibly match slugs in `inc/patterns/`

## TODO

- TODO: add a real setup section once local development requirements are confirmed.
- TODO: document how this theme is expected to be installed alongside other ModFarm repos.
- TODO: document whether any MU plugin behavior lives outside this repo.
