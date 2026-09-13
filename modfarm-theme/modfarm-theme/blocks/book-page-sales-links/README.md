# Book Page Sales Links

The block defaults to the current book and native retailer artwork, preserving
existing saved blocks. Block post context takes precedence over the global post.

To use purchase links on any page, choose **Book Source > Select a book**, then
search for a published book by title. The chosen book supplies retailer metadata
and the book ID in click tracking. This source selection is independent of
**Auto-detect retailer links**; disabling that option still lets editors choose
up to six retailers in order. An empty or invalid manual selection does not fall
back to the current book. Unpublished selections are hidden from visitors without
permission to read the book.

Choose **Icon color mode > Monochrome** for white logos on colored square tiles,
using the transparent PNG artwork from `cbg-images`. Set **Icon Background Color**
or clear it to use the surrounding text color for the tile background. Logos stay
white. Button size and icon border radius apply to the tiles.
The custom icon path applies only to native artwork. Shared retailer logos cover
multiple formats; enable retailer labels to identify formats visually.

Transparent assets are present for all supported retailers. Retailer filenames
are recognized automatically, with shared logos mapped to multiple formats.
If an asset is removed, its link falls back to a readable retailer name.
Keep the artwork background transparent; the mask uses the image alpha channel
to draw a white logo over the chosen tile color.
