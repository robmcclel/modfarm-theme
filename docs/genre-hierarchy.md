# Genre hierarchy browsing

Deploy the Core and Theme genre-hierarchy changes together (Core first).

Core derives series classifications from published member books. Each classified
book contributes at most one vote to a genre and each ancestor. More than half
of classified books must support a category for automatic browsing membership.
Unclassified books remain visible in the coverage total, but are not negative
votes. Less common categories remain in the evidence distribution.

The primary display genre is selected from direct assignments, with a more
specific genre winning an equal-count parent/child tie. Primary labels do not
limit browsing membership. Existing primary overrides remain supported; manual
subgenre choices can replace automatic choices. Disable overrides to return to
automatic detection. Schema-versioned caches refresh lazily, and genre hierarchy
edits invalidate derived counts.

Taxonomy Grid examples:

- All LitRPG series: Series grid, Genre = LitRPG, Include subgenres = on.
- LitRPG sections: Series grouped by genre, Genre = LitRPG, Group series by
  subgenre = on. Sections use immediate children; deeper descendants roll up.
  Series can appear in multiple sections. Broad-only series use Other LitRPG.
- Dungeon books by series: Books grouped by series, Genre = Dungeon LitRPG.
  Complete qualifying series are shown in reading order by default. Enable
  Only books matching this genre for a strict book-level selection.

Without a selected genre, existing primary-genre grouping is preserved. CIR
uses the same resolved series selection and optional book-level genre filter.
Analytics retains the existing section and entity click payloads.

Validation: focused PHP tests cover counting, overrides and hierarchy cache
invalidation in Core, and branch filters/grouping in Theme. An optional integration
run is available when sibling checkouts are present:
`MODFARM_TAXGRID_TEST=1 php modfarm-core/tests/series-genre-hierarchy.php`.

TODO: visually verify the editor controls, series classification panel and all
three layouts on Dev with real publisher data before production deployment.
