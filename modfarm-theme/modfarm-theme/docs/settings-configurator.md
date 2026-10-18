# ModFarmOS Settings configurator

Settings uses a persistent visualizer on the left and the active controls on the right. Sections remain Site Basics, Theme & Fonts, Navigation, Book Presentation, Page Layouts, and Marketing; Navigation sits beside appearance controls.

The studio reuses the existing live theme and book previews and the PPB rendered-pattern iframe. Existing controls stay inside the original settings form; option keys, sanitation, save actions, font upload form, Apply All confirmations and permissions are unchanged. No settings are written by switching sections or devices. Without JavaScript, the original section forms remain accessible.

Theme and book previews are appearance samples, not a rendering of the current homepage. The site name is real; navigation profile behavior and menu assignments must be checked on the saved site. PPB preview renders selected patterns with the existing named sample-content selector. Marketing has no visual effect on the sample. Desktop, tablet and phone samples use 1200, 768 and 390 pixel widths scaled to fit. PPB retains its independent rendered iframe device controls. Narrow admin screens stack preview above controls.

Unsaved settings changes update the save bar and prompt on leaving. View Site opens the saved frontend in a new tab.

## Validation

JavaScript syntax and PHP parser checks passed. DOM integration checks against the extracted settings markup passed for section switching, preserving all named form controls, live color and book-title visibility changes, device widths, unsaved state and keyboard radio changes.

PHP CLI and a local Chromium executable were unavailable; visual browser acceptance and native PHP lint remain required on Dev.

## Dev acceptance

- Inspect a wide desktop, a Chromebook-size window, tablet and phone; preview must precede settings and neither column should spill horizontally.
- Switch every section, both by clicking and keyboard radio selection. Edit multiple sections before saving and confirm all changes persist.
- Change colors, fonts, content width and book card/button settings; check appearance sample updates.
- Switch sample devices; check phone proportions and desktop scaling.
- In Page Layouts choose named content, different types, patterns and iframe devices; check preview refresh and existing Apply All safeguards.
- Review Navigation shared profiles, save and check header, mobile dropdown and footer on the frontend.
- Confirm Marketing integrations and custom-font upload retain their native behavior.
- Disable JavaScript and verify the original radio-tab forms remain usable.

## October Settings review

Branding edits WordPress's canonical site title, tagline, site icon and theme logo
inside the Settings form. Save uses the Settings API nonce/capability checks and
an additional manage_options check; image IDs must reference images. Hero/Cover
defaults belong to Branding. Navigation typography belongs to Fonts.

Colors have Site/Accent and Desktop/Mobile/Footer subgroups. Clear and Default
remove the explicit color so the renderer's defaults can apply. The picker
notifies previews using the field rather than WordPress's Clear button target.

The Base visualizer now uses a sandboxed iframe. A nonce-protected read-only
endpoint renders the real navigation block with unsaved profiles, colors, and
identity overrides. Its temporary option filters are removed after rendering;
it never calls the settings sanitizer or writes options. Actual navigation CSS
and JavaScript run at the selected viewport width. Links are inert in the
sample, while menu controls work. Book samples show card and page buttons,
visibility, joined/gapped corners, and frontend shadow values. Book Cards keeps
its focused preview. This supersedes the fixed-menu limitation above.

Layouts explain future PPB defaults versus live dynamic archives/Hybrid chrome.
Archive labels are reader-facing; stored keys and pattern slugs are unchanged.
Book display defaults have a separate section with vertically stacked controls.
PPB Apply All retains preview/confirmation/lock behavior and has local Overview,
Guide, and Help disclosures. Custom Fonts has the same three help controls.
Collections remain separate content tabs and inactive collections stay hidden.
Hybrid Body pattern selectors are disabled with a hidden field preserving their
saved value. The existing Post template selector is shown under Posts.

Validation: PHP 8.3 WASM execution of identity/preview permission checks and the
archive regression harness; PHP parser checks; Chromium integration against
PHP-rendered Settings markup and real navigation renderer output; mobile menu
open/close; hidden Hybrid value submission; existing pattern mirror test.
CI repeats these checks using native PHP and Chromium. Dev still needs a saved
round trip with actual site menus, uploaded branding assets and selected fonts.
