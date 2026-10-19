# Hero and Cover presentation

ModFarm Settings → Theme & Fonts → Hero & Cover defaults holds the site image,
gradation preset, and semantic fade/border color. Colors reference current ModFarm
Settings, rather than a saved hex snapshot. Presets are clean edge (`none`), border,
short bottom fade (`bottom-soft`), deep bottom fade (`bottom-deep`), and top/bottom
fade (`both`). The fade is separate from the readability overlay and stays behind
text, buttons, and navigation. `edgePreset` and `edgeColor` default to `site` on
both `modfarm/hero-cover` and `core/cover`; explicit `none` disables an inherited fade.

## Image contract

- Singular: when `useFeaturedImage` is enabled and a featured image exists, it
  overrides the selected/local hero. Otherwise use the selected image (`manualUrl`)
  or the local `hero_image`, followed by `hero_default_image` from theme settings.
- Archives, Blog, search, date, author, and taxonomy listings never read a queried
  post's featured image. Explicit archive/term/collection hero → site default.
- No configured image still renders inner content and navigation, without a public
  error message. Deleted attachments fall through to the default.
- The old explicitly saved `fallbackFeatured: true` remains supported as a legacy
  fallback. Its former implicit default changes to false so new book heroes do not
  unexpectedly use a portrait book cover as their background. Review existing
  heroes that relied on that implicit fallback during development rollout.
- Core Cover preserves saved HTML. Enable **Use ModFarm image defaults** on a Cover
  to opt into resolution; videos are left intact. Standard Cover image handling is
  otherwise unchanged. The editor resolves inherited images through Hero's existing
  authenticated block-renderer endpoint and uses the URL only for preview. It is
  never saved into the Cover. Native Featured Image controls and unsaved featured
  image changes remain active; the inherited image appears behind them as a fallback.
  A saved local hero change is reflected when the editor is reloaded. Template
  editing without a specific archive context previews the site default.

## Independent navigation

Insert **Hero with top navigation**, or select one of the new **Header Hero — Top
Navigation** PPB patterns. `structuredLayout: true` uses two `modfarm/hero-area`
children (`area: navigation` and `area: content`). Their contents are editable.
Navigation stays in normal flow at the top; content position (`top`, `center`,
`bottom`) and horizontal text alignment (`left`, `center`, `right`) affect the main
area. Mobile overrides use a 781px breakpoint. Height is a minimum: growing mobile
content expands the Hero rather than overlapping the menu. Existing Hero inner
blocks are not automatically wrapped, moved, or migrated.

The variation and PPB patterns supply insertion-time defaults only. Hero has no
live locked template: both areas retain added blocks, including series details,
author credit, format icons, and custom groups, through save/reload. A live nested
template would recursively reset those additions to its initial title/menu layout.

## Onboarder integration

The shared option registry is `modfarm_hero_presentation_options()`. The companion
Content update adds `hero[hero_edge_preset]` and `hero[hero_edge_color]` to Site
Starter recipes, previews their effect, and copies/remaps the source default image.
No site default or existing page pattern selection is changed automatically.

## Validation

`php tests/hero-presentation.php /path/to/wordpress` tests image precedence,
archive isolation, deleted media, live settings colors, content preservation,
preview layers, and the real WordPress HTML Tag Processor integration for Cover.
`node tests/hero-layout.cjs` checks desktop/mobile layout using Playwright.
`node tests/cover-editor-preview.cjs` exercises the editor hooks in React 18 and
Chromium, including inheritance, native Featured Image controls, unsaved featured
changes, request races, failure handling, and saved-attribute preservation.
A development multisite editor smoke test remains required before production.
