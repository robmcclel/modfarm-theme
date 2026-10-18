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
  otherwise unchanged. ModFarm image fallback is applied at frontend render time;
  the native Cover placeholder may still appear in the editor before an image is selected.

## Independent navigation

Insert **Hero with top navigation**, or select one of the new **Header Hero — Top
Navigation** PPB patterns. `structuredLayout: true` uses two `modfarm/hero-area`
children (`area: navigation` and `area: content`). Their contents are editable.
Navigation stays in normal flow at the top; content position (`top`, `center`,
`bottom`) and horizontal text alignment (`left`, `center`, `right`) affect the main
area. Mobile overrides use a 781px breakpoint. Height is a minimum: growing mobile
content expands the Hero rather than overlapping the menu. Existing Hero inner
blocks are not automatically wrapped, moved, or migrated.

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
A development multisite editor smoke test remains required before production.
