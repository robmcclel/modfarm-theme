# Navigation menu

This block renders native WordPress menus. Images live on menu items, so a menu
can be reused by multiple blocks without copying image choices into block data.

## Menu editing

In **Customizer > Menus**, enable **Image** using the advanced properties gear.
In **Appearance > Menus**, enable **Image** under Screen Options. Each item has a
Media Library picker, preview, replacement/removal controls, and a Cover/Icon
selector. The selector maintains `menu-cover` or `menu-icon` in the native CSS
Classes field and preserves other classes. Images default to Cover.

- `menu-cover`: portrait image next to the label; hidden at mobile widths.
- `menu-icon`: compact image beside the label on desktop and mobile.
- Navigation labels always remain visible. Images use empty alt text to avoid
  repeating the link name. Deleted/missing attachments leave the text link intact.
- The native Description field supplies optional secondary text. Enable **Show
  descriptions** in the block to display it.

The attachment ID is stored in `_mfs_menu_image_id` on the `nav_menu_item` post.
Customizer choices travel in the native menu-item setting as `mfs_image_id` and
are saved only when its changeset publishes. The save hook runs after WordPress
resolves temporary negative IDs. Discarded previews do not change saved images.
Native Menus form saves use a per-item nonce and the menu-editing capability.

## Block presentation

**Mobile Navigation > Presentation** offers:

- **Below header**: expands in page flow and pushes following content down.
- **Side drawer**: full width below 600px, 50% at 600–899px, and 35% at
  900–1024px, with a 320px minimum. Choose left or right; the opener follows it.
- **Full-screen overlay**: fills the mobile viewport.

The existing 1024px collapse breakpoint is preserved. Existing blocks default
to overlay. **Do not collapse on mobile** still keeps native menus expanded.
All mobile presentations use left-aligned rows, separate submenu buttons,
indented nested lists, and the configured submenu background/text colors.
Modal panels trap focus, make the background inert, lock page scrolling, close
on backdrop click/Escape, and restore opener focus. Resizing to desktop closes
the panel and releases scroll/focus restrictions. Below-header menus remain
non-modal. Parent links navigate independently from submenu buttons.

**Menu Images and Descriptions** controls cover width, icon size, image gap,
dropdown width, and descriptions. Nested desktop menus open as flyouts and flip
left when they would overflow the right edge. Long mobile submenus can scroll
without a fixed height cap. Reduced-motion preferences disable drawer animation.

The enhancement filters are scoped to block-generated menus through the
`mfs_enhanced` argument. No migration or mega-menu plugin is required.

## Validation

- `php tests/navigation.php` runs standalone hook and rendering checks.
- `node tests/navigation-browser.cjs` uses Playwright and PHP to exercise the
  actual frontend renderer/CSS/JS in a fixture at phone, tablet and desktop sizes.
  Install/provide Playwright through `NODE_PATH`; Windows uses installed Edge.
- These fixtures do not boot WordPress or exercise its authenticated Media
  Library. Before release, check Customizer select/replace/remove, Publish,
  discard, new menu items, and reopening the editor on a WordPress test site.
