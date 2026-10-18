# Content type archive layouts

Layout sections belong to Books, Pages, Posts, Offers, Updates, Events, and registered Collection types. Archives appear as expandable controls within their owner's section. Search and unmatched archives remain under Other Archives.

Every archive context has independent Header, Body, and Footer selections. All use the shared `modfarm-archive-header`, `modfarm-archive-body`, and `modfarm-archive-footer` pattern libraries. Public taxonomies are discovered from their registered content types; Collection definitions and the `modfarm_archive_layout_contexts` filter allow additional PPB contexts.

`inc/archive-layouts.php` owns context discovery, field naming, resolution, and archive controls. Both archive rendering paths call its resolver. Term-level pattern overrides appear in native taxonomy editing and the ModFarmOS Presentation form, using the theme's existing nonce and save callback.

Existing shared header/footer and domain-specific body choices seed new controls until each is saved. Saving captures those values independently. Choosing Default thereafter selects that context's canonical layout. Inactive module selections are retained. Existing locally composed Posts-page zones remain local overrides; unzoned Posts pages use the Posts Index chrome after its independent settings are saved, unless local Hybrid overrides exist.

The previously added Hero/Cover resolver is retained: archives use a local Hero Image, then the site default. The term Hero field explains this fallback. A companion Core change exposes the existing field in Purpose & Meta as well as Presentation.

Archive preview buttons render the selected three patterns with placeholder context. Review the actual archive for its live query, content, and images. These changes do not bulk-rewrite existing post content or deploy a network update.

Validation: `php tests/archive-layouts.php` exercises context discovery, independent settings sanitization, legacy fallback, term precedence, reset behavior, Collection compatibility, Posts Index, and Search. `--fixture` renders the actual archive controls for DOM checks. PHP tokenizer must be enabled for the standalone harness.

Development review: check all six sections, Books taxonomies including Languages and Book Tags, Offer Types versus Collections, save/reload, a local term override and reset, and the designated Posts page with and without local zones. Then review actual archives on desktop and mobile before production rollout.
