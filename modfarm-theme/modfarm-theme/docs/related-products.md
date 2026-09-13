# Related Products on Books and Offers

Use the same **Related Products** block on both content types. It detects the Book or Offer containing it, including block context inside templates.

## Automatic recommendations

To recommend Offer B on Offer A:

1. Edit **Offer B** and open **Promoted By**.
2. Add a relationship, choose **Offer**, search for **Offer A**, and select it.
3. Use **Exact** scope and save Offer B.
4. On Offer A, use Related Products with **Automatic for this Book or Offer**.

This relationship is directional. To recommend A on B as well, add the reverse relationship separately.

To recommend an Offer on a Book, edit the recommended Offer and select that Book in **Promoted By**. Book pages also honor Core's existing Family-scoped Series and Author promotions.

On Offer pages, direct Offer-to-Offer promotions take priority. If none match, the block uses the Offer's **Related Book** (`mf_offer_related_book_id`) and that Book's applicable promotions. The current Offer is always excluded. Sources do not top up one another: a matching direct promotion list wins over the Book list, and a matching promotion list wins over taxonomy fallback.

## Manual recommendations

Choose **Choose Offers manually**, type at least three characters of an Offer title, and select a search result. Repeat and reorder the selections. This list belongs to the block; it does not create reusable relationships. An empty manual list displays no products. Switching to Automatic retains the picks for later but ignores them while Automatic is selected.

Older blocks with manual selections remain manual unless switched explicitly. Older blocks without manual selections remain automatic.

## Fallback and override

**If no promotions match** controls the existing fallback. An Offer taxonomy uses the source Offer's terms. Book pages have no source Offer taxonomy terms; use promotions, manual selection, or the explicit **Any published Offers** fallback.

**Advanced source override** is normally blank. Selecting an Offer there overrides the detected context for recommendations, supplies taxonomy terms, and excludes that source Offer from results. Existing saved Offer overrides are retained.

## Deployment and checks

Deploy the changed Related Products block from ModFarm Theme and the Offer relationship editor from ModFarm Store together. The separate ModFarm Core `editor-policy.php` and its MU loader change disable WordPress.org block installation suggestions in the inserter network-wide, including for super administrators.

Run `php tests/related-products.php` from the theme repository and `node --check modfarm-theme/modfarm-theme/blocks/related-products/index.js`.

On a WordPress test site, verify a Book promotion, a direct Offer cross-promotion, linked-Book fallback, a Family Series/Author promotion, manual selection and ordering, and switching modes in the editor and frontend. Search the block inserter as both a site editor and a super administrator; installed blocks should remain available and **Available to install** should be absent. TODO: complete these live-site checks before production rollout.
