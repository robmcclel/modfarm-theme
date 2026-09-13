# Navigation search

Enable **Customizer → Menus → Navigation Search → Show navigation search**.
The checkbox defaults off and applies to header navigation blocks.
In the Navigation Menu block's Navigation Settings, **Navigation Search** can
inherit that setting, show search, or hide search. Footer blocks do not inherit
the header setting but can explicitly show search.

Search appears as a magnifying-glass button opening a compact form on desktop
and beside the mobile Menu button. The mobile panel also includes a search form.
Forms submit a native GET request with the WordPress `s` parameter to the site
home URL, using the existing search results template. No search plugin is required.
Escape dismisses the dropdown and restores focus; clicking outside dismisses it.
Search fields and buttons participate in the mobile dialog's keyboard focus loop.

## Deployment

Upload the complete theme to keep dependencies together. For a selective upload,
all seven runtime files for this feature are required, relative to the theme root:

- `functions.php`
- `inc/navigation-search.php` (new)
- `assets/js/navigation-toggle.js`
- `blocks/navigation-menu/block.json`
- `blocks/navigation-menu/index.js`
- `blocks/navigation-menu/render.php`
- `blocks/navigation-menu/style.css`

## Validation

`php tests/navigation.php` checks default/override behavior, unique search labels,
native form markup, Customizer registration, and existing navigation behavior.
`node tests/navigation-browser.cjs` uses Playwright with installed Edge on Windows
to check responsive navigation, dropdown bounds/focus/dismissal, and mobile form
submission. The standalone fixtures stub WordPress; live Customizer publishing
and database-backed search results require a WordPress installation.
