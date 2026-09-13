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

Choose **Icon color mode > Monochrome** to use transparent PNG masks from
`cbg-images`. Set **Icon Color** or clear it to inherit the surrounding text color.
The custom icon path applies only to native artwork. Shared retailer logos cover
multiple formats; enable retailer labels to identify formats visually.

Transparent assets are present for all supported retailers. Retailer filenames
are recognized automatically, with shared logos mapped to multiple formats.
If an asset is removed, its link falls back to a readable retailer name.
Keep the background transparent; the mask uses the image alpha channel, so white
artwork works with any chosen color.
