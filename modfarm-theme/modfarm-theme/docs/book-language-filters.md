# Book listing language filters

The Book language inspector panel is available on Taxonomy Grid, Featured Book,
Coming Soon List, Multi-Tax Format, Handpicked Books, Archive Book List and Book
Page Tax. The filter intersects the block's existing taxonomy, date and format
selection before pagination.

- All languages preserves existing behavior and is the default for saved blocks.
- English (including unassigned) matches English Book Language terms and books
  without any Book Language term. No backfill of English assignments is required.
- Other options match the selected Book Language term. Assign translations using
  the Book Language taxonomy; the legacy free-text Language field is not queried.

For a catalog grouped by series, select Taxonomy Grid's Books by Series mode,
then choose the language. Duplicate the block for each language. Empty series
sections are omitted, and the table of contents, counts and cover selection use
the selected language. Term cards with Hide Empty disabled can still show empty
terms. Archive links retain their normal destination.

For an English homepage feature, select Featured Book's Auto mode and English.
The fallback selection and editor description lookup use the same language.
Explicit manual selections and pinned books still take precedence.

The saved attribute is `bookLanguage`: empty string for all, `english` for the
English fallback, or a Book Language term ID as a string. Deleted selections
match no books. This is independent of text/phrase translation settings.

Validation: `php tests/book-language-filter.php` covers language combinations,
unassigned English, existing OR filters, filtering before limits, Featured Book
fallback/pinning, and Taxonomy Grid counts/paging. These are standalone fixtures;
check the block inspector and server previews in WordPress before deployment.
