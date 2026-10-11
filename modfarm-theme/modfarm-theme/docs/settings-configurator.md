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
