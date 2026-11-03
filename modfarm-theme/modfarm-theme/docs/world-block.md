# World Regions

The World block still resolves its Series automatically on a Series archive or unambiguous Book, or through Manual name selection on landing pages. Series World Bible controls supply an independent list of Regions.

Each Region chooses content (selected mixed-type entries in explicit order, a dynamic entry type, overview, or Books) and a layout. Illustrated columns, Character lineup, Alternating features, Immersive feature, Editorial story, Wide banner, and Compact reference are available. Navigation stacks these Regions or presents them as tabs, preserving their layouts and data selections.

Books Regions reuse the existing archive card renderer with explicit Series context. The main query is not mutated. A block override of Separate suppresses Books Regions; Included appends Books if there is no enabled Books Region. Inherit follows the Region list. The World must be published for any of its Regions to appear.

The renderer selects entries only from modfarm_world_bible_public. Published/Public/spoiler checks run before Region selection; selection cannot bypass them. Repeated references reuse content without copying it. Empty Regions and tab labels are omitted. A spoiler selector remains reachable when all configured content is spoiler-bound.

Large artwork uses the full attachment source with responsive WordPress image markup. Default image treatment preserves proportions and displays the complete image. Fixed aspect ratios and fill/cropping are explicit choices. No-image entries collapse to text. Region colors inherit World defaults and ModFarm Settings unless overridden. Typography and button tokens follow the active theme.

Legacy Core retains its category renderer. Updated Core adapts legacy sections to equivalent compact Regions without saving or changing order. Deploy both components before using new Region controls. No archive patterns or public source records are rewritten by installation.

The shared initializer enhances both reader and block-editor previews into accessible tabs, with unique IDs, keyboard/RTL handling, and independent instances. Without JavaScript, eligible Regions remain visible in sequence. Mobile layouts stack features/columns and constrain tab scrolling.

Validation covers the legacy renderer, mixed-type selection and ordering, entry reuse, private/draft/spoiler omission, empty Regions, Books overrides, navigation parity, layout presets, existing tab behavior, and real Chromium layouts at phone/tablet/desktop widths. Workflow artifacts contain review screenshots with deterministic test artwork. Live site artwork/typography acceptance remains part of Dev review.

First acceptance layout: Hell Divers — three illustrated world entries, two alternating editorial entries, then Books. Switch the same Regions to Tabs and confirm layouts, colors, content, and ordering remain.
