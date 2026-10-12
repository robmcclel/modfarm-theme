# Settings navigation

Design: Branding, Colors, Fonts. Colors includes site, navigation and book colors. Fonts includes navigation typography and custom font uploads. Branding opens the existing native WordPress title/icon and Site Identity controls; inline unified branding editing is not implemented in this release.

Layout: existing named PPB content types, archive/taxonomy defaults and layout modes. Archive body defaults move here from Site Basics. Taxonomy settings remain grouped with archives in this release. Existing Apply All and Safe Convert actions retain their confirmations.

Function: Navigation, Book Cards, Marketing, Site Sizing. Book Cards contains shapes, effects, visibility and button geometry; its colors belong to Design. Site Sizing exposes the existing content-width control. A separate wide-width setting is not introduced until frontend support is confirmed.

Two-level navigation remembers the selected section. Disclosure groups keep inputs enabled and inside the original native forms. Native font upload targets and Settings API option keys remain intact. Color swatches and book choice cards use the existing setting inputs. Without JavaScript the original sections remain available.

Validation: JS syntax and DOM integration checks passed for category/section switching, focusable native buttons, disclosure creation, all named form controls preserved, live color/book updates, viewport selection, keyboard legacy radio activation and dirty-state feedback. Local Chromium unavailable; inspect on ModFarm Dev at desktop, tablet and mobile widths, save edits across sections, test color pickers and selection cards, and test PPB content selection/preview refresh before production.
