/* global window */
(function (wp) {
  if (!wp || !wp.blocks || !wp.element || !wp.components) return;

  const { __ } = wp.i18n || { __: (s) => s };
  const { registerBlockType } = wp.blocks;

  const blockEditor = wp.blockEditor || wp.editor;
  const { useBlockProps, InspectorControls } = blockEditor;

  const {
    PanelBody,
    SelectControl,
    TextControl,
    RangeControl,
    ToggleControl,
    Button,
    Notice,
  } = wp.components;

  const { ColorPalette } = blockEditor;
  const { Fragment, createElement: el, useState, useEffect, useMemo, useRef } = wp.element;
  const ServerSideRender = wp.serverSideRender;
  const apiFetch = wp.apiFetch;

  const MAX_BOOKS = 50;

  // === Canonical option lists (match multi-tax) =============================
  const CTA_OPTIONS = [
    { label: __('Joined (no gap)', 'modfarm'), value: 'joined' },
    { label: __('Gap', 'modfarm'),             value: 'gap' },
  ];

  const COVER_SHAPE_OPTIONS = [
    { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
    { label: __('Square', 'modfarm'),           value: 'square' },
    { label: __('Rounded', 'modfarm'),          value: 'rounded' },
  ];

  const BUTTON_SHAPE_OPTIONS = [
    { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
    { label: __('Square', 'modfarm'),           value: 'square' },
    { label: __('Rounded', 'modfarm'),          value: 'rounded' },
    { label: __('Pill', 'modfarm'),             value: 'pill' },
  ];

  const SAMPLE_SHAPE_OPTIONS = [
    { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
    { label: __('Square', 'modfarm'),           value: 'square' },
    { label: __('Rounded', 'modfarm'),          value: 'rounded' },
    { label: __('Pill', 'modfarm'),             value: 'pill' },
  ];

  const SHADOW_OPTIONS = [
    { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
    { label: __('Flat (no extra shadow)', 'modfarm'), value: 'flat' },
    { label: __('Small shadow', 'modfarm'),           value: 'shadow-sm' },
    { label: __('Medium shadow', 'modfarm'),          value: 'shadow-md' },
    { label: __('Large shadow', 'modfarm'),           value: 'shadow-lg' },
    { label: __('Embossed', 'modfarm'),               value: 'emboss' },
  ];

  // === Helpers ==============================================================
  const toInt = (v) => {
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : 0;
  };

  const uniq = (arr) => Array.from(new Set(arr));
  const sanitizeIdArray = (arr) =>
    uniq((Array.isArray(arr) ? arr : []).map(toInt).filter((n) => n > 0));

  function arrayMove(list, from, to) {
    const a = (list || []).slice();
    if (from === to) return a;
    if (from < 0 || to < 0) return a;
    if (from >= a.length || to >= a.length) return a;
    const [item] = a.splice(from, 1);
    a.splice(to, 0, item);
    return a;
  }

  // === Featured Book style search (kept) ===================================
  function BookSearch({ onPick, disabled }) {
    const [q, setQ] = useState('');
    const [loading, setLoading] = useState(false);
    const [results, setResults] = useState([]);

    useEffect(() => {
      if (disabled) return;

      const term = (q || '').trim();
      if (term.length < 3) {
        setResults([]);
        return;
      }

      const t = setTimeout(() => {
        setLoading(true);
        apiFetch({
          path: `/wp/v2/book?status=publish&search=${encodeURIComponent(term)}&_fields=id,title&per_page=20`,
        })
          .then((list) => {
            const items = (list || []).map((it) => ({
              id: it.id,
              title: it && it.title && it.title.rendered ? it.title.rendered : `#${it.id}`,
            }));
            setResults(items);
          })
          .catch(() => setResults([]))
          .finally(() => setLoading(false));
      }, 250);

      return () => clearTimeout(t);
    }, [q, disabled]);

    return el(
      Fragment,
      {},
      el(TextControl, {
        label: __('Find Book by Title', 'modfarm'),
        help: __('Type 3+ characters to search', 'modfarm'),
        value: q,
        disabled: !!disabled,
        onChange: (v) => setQ(v || ''),
      }),
      loading && el(Notice, { status: 'info', isDismissible: false }, __('Searching…', 'modfarm')),
      results.length > 0 &&
        el(
          'div',
          { className: 'mftb-search' },
          results.map((r) =>
            el(
              Button,
              {
                key: r.id,
                variant: 'secondary',
                disabled: !!disabled,
                onClick: () => {
                  onPick(r.id);
                  setQ('');
                  setResults([]);
                },
                style: { display: 'block', marginBottom: '6px' },
              },
              `#${r.id} — ${r.title}`
            )
          )
        ),
      el(TextControl, {
        label: __('Add by ID (manual)', 'modfarm'),
        type: 'number',
        value: '',
        disabled: !!disabled,
        onChange: (v) => {
          const id = toInt(v);
          if (id > 0) onPick(id);
        },
      })
    );
  }

  // === Drag chip picked list (kept) ========================================
  function PickedList({ ids, titlesById, onRemove, onClear, onMove }) {
    if (!ids.length) return null;

    return el(
      'div',
      { style: { marginTop: '8px' } },
      el('div', { style: { marginBottom: '6px', fontWeight: 600 } }, __('Picked (drag to reorder):', 'modfarm')),

      el(
        'div',
        { style: { display: 'flex', gap: '6px', flexWrap: 'wrap' } },
        ids.map((id, index) => {
          const title = titlesById && titlesById[id] ? titlesById[id] : '';
          const label = title ? `#${id} — ${title}` : `#${id}`;

          return el(
            'span',
            {
              key: id,
              draggable: true,
              onDragStart: (e) => {
                try {
                  e.dataTransfer.setData('text/plain', String(index));
                  e.dataTransfer.effectAllowed = 'move';
                } catch (_) {}
              },
              onDragOver: (e) => {
                e.preventDefault();
                try {
                  e.dataTransfer.dropEffect = 'move';
                } catch (_) {}
              },
              onDrop: (e) => {
                e.preventDefault();
                const from = toInt(e.dataTransfer.getData('text/plain'));
                const to = index;
                if (Number.isFinite(from) && from >= 0) onMove(from, to);
              },
              title: __('Drag to reorder', 'modfarm'),
              style: {
                display: 'inline-flex',
                alignItems: 'center',
                gap: '6px',
                border: '1px solid #ccc',
                borderRadius: '9999px',
                padding: '3px 10px',
                cursor: 'grab',
                userSelect: 'none',
              },
            },
            el(
              'span',
              { style: { whiteSpace: 'nowrap', maxWidth: '380px', overflow: 'hidden', textOverflow: 'ellipsis' } },
              label
            ),
            el(Button, { isSmall: true, isDestructive: true, onClick: () => onRemove(id) }, '×')
          );
        })
      ),

      el(
        'div',
        { style: { display: 'flex', gap: '8px', marginTop: '10px' } },
        el(Button, { isDestructive: true, onClick: onClear }, __('Clear All', 'modfarm'))
      ),

      el(
        'p',
        { className: 'components-base-control__help', style: { marginTop: '8px' } },
        __('Tip: Drag chips to set the exact book order shown in the grid.', 'modfarm')
      )
    );
  }

  registerBlockType('modfarm/handpicked-books', {
    apiVersion: 2,
    title: __('Handpicked Books', 'modfarm'),
    category: 'modfarm-theme',
    icon: 'book',
    supports: { html: false, align: false },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      // Migration safety: older attribute "ids" -> "books"
      useEffect(() => {
        if (attributes.ids && Array.isArray(attributes.ids) && (!attributes.books || !attributes.books.length)) {
          setAttributes({ books: sanitizeIdArray(attributes.ids), ids: undefined });
        }
      }, []);

      // Always maintain sanitized books array
      const books = useMemo(() => sanitizeIdArray(attributes.books), [attributes.books]);
      useEffect(() => {
        const raw = Array.isArray(attributes.books) ? attributes.books : [];
        if (books.length !== raw.length) setAttributes({ books });
      }, [books]);

      const addId = (id) => {
        if (!id) return;
        if (books.length >= MAX_BOOKS) return;
        setAttributes({ books: sanitizeIdArray([].concat(books, [id])) });
      };

      const removeId = (id) => setAttributes({ books: books.filter((x) => x !== id) });
      const clearAll = () => setAttributes({ books: [] });
      const moveByIndex = (from, to) => setAttributes({ books: arrayMove(books, from, to) });
      const isAtCap = books.length >= MAX_BOOKS;

      // Title cache for chips
      const [titlesById, setTitlesById] = useState({});
      const fetchingRef = useRef({});
      useEffect(() => {
        const missing = books.filter((id) => !titlesById[id] && !fetchingRef.current[id]);
        if (!missing.length) return;

        const batch = missing.slice(0, 8);
        batch.forEach((id) => {
          fetchingRef.current[id] = true;
          apiFetch({ path: `/wp/v2/book/${id}?_fields=title` })
            .then((r) => {
              const t = r && r.title && r.title.rendered ? String(r.title.rendered) : '';
              setTitlesById((prev) => (prev[id] ? prev : Object.assign({}, prev, { [id]: t || `#${id}` })));
            })
            .catch(() => {
              setTitlesById((prev) => Object.assign({}, prev, { [id]: `#${id}` }));
            })
            .finally(() => {
              delete fetchingRef.current[id];
            });
        });
      }, [books, titlesById]);

      // Card style defaults (same as multi-tax)
      const cardUseGlobal = attributes.cardUseGlobal !== false;

      const cardCoverShape        = attributes.cardCoverShape        || 'inherit';
      const cardButtonShape       = attributes.cardButtonShape       || 'inherit';
      const cardSampleShape       = attributes.cardSampleShape       || 'inherit';
      const cardCtaMode           = attributes.cardCtaMode           || 'inherit';
      const cardShadowStyle       = attributes.cardShadowStyle       || 'inherit';

      const cardShowTitle         = attributes.cardShowTitle         !== false;
      const cardShowSeries        = attributes.cardShowSeries        !== false;
      const cardShowPrimaryButton = attributes.cardShowPrimaryButton !== false;
      const cardShowSampleButton  = attributes.cardShowSampleButton  !== false;

      const cardButtonBg          = attributes.cardButtonBg || '';
      const cardButtonFg          = attributes.cardButtonFg || '';
      const cardSampleBg          = attributes.cardSampleBg || '';
      const cardSampleFg          = attributes.cardSampleFg || '';

      // Theme colors (same approach as multi-tax)
      const themeColors = (wp.data && wp.data.useSelect)
        ? wp.data.useSelect((select) => {
            const settings = select('core/block-editor')?.getSettings?.() || {};
            return settings.colors || [];
          }, [])
        : [];

      const renderColorOverrideControl = (label, attrKey, currentValue) => {
        return el('div', { className: 'mf-card-color-control' },
          el('div', { className: 'components-base-control__field' },
            el('label', { className: 'components-base-control__label' }, label)
          ),
          el(ColorPalette, {
            value: currentValue || '',
            colors: themeColors,
            onChange: (val) => setAttributes({ [attrKey]: val || '' })
          }),
          currentValue
            ? el(Button, { isSmall: true, variant: 'secondary', onClick: () => setAttributes({ [attrKey]: '' }) }, __('Clear override', 'modfarm'))
            : null
        );
      };

      // Card Extras (Handpicked-specific, but simple + consistent)
      const showAuthor = !!attributes.showAuthor;
      const showPubDate = !!attributes.showPubDate;
      const pubDateKey = attributes.pubDateKey || 'publication_date';
      const showShortDescription = !!attributes.showShortDescription;

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, { block: 'modfarm/handpicked-books', attributes })
        ),

        el(InspectorControls, {},

          // === Book Filters (Handpicked variant) ============================
          el(PanelBody, { title: __('Book Filters', 'modfarm'), initialOpen: true },
            isAtCap &&
              el(Notice, { status: 'warning', isDismissible: false },
                __('You’ve reached the 50-book cap for this block. Add another Handpicked Books block to continue.', 'modfarm')
              ),
            el(BookSearch, { onPick: addId, disabled: isAtCap }),
            el(PickedList, { ids: books, titlesById, onRemove: removeId, onClear: clearAll, onMove: moveByIndex }),

            el(SelectControl, {
              label: __('Image Type', 'modfarm'),
              value: attributes['image-type'],
              options: [
                { label: 'Featured Image (Default)',    value: 'featured' },
                { label: 'eBook Cover',                 value: 'cover_ebook' },
                { label: 'Audiobook Cover (Square)',    value: 'cover_image_audio' },
                { label: 'Paperback Cover',             value: 'cover_paperback' },
                { label: 'Hardcover Cover',             value: 'cover_hardcover' },
                { label: 'Hero Image',                  value: 'hero_image' },
                { label: 'Flat Cover Image',            value: 'cover_image_flat' },
                { label: '3D Mockup Cover',             value: 'cover_image_3d' },
                { label: 'Composite Marketing Image',   value: 'cover_image_composite' }
              ],
              onChange: (val) => setAttributes({ 'image-type': val })
            }),

            el(SelectControl, {
              label: __('Button / Cover Link Destination', 'modfarm'),
              value: attributes['button-link'],
              options: [
                { label: 'Book Page (Default)', value: 'bookpage' },
                { label: 'Kindle', value: 'kindle_url' },
                { label: 'Amazon Paperback', value: 'amazon_paper' },
                { label: 'Amazon Hardcover', value: 'amazon_hard' },
                { label: 'Amazon Audiobook', value: 'amazon_audio' },
                { label: 'Audible', value: 'audible_url' },
                { label: 'Nook', value: 'nook' },
                { label: 'B&N Paperback', value: 'barnes_paper' },
                { label: 'B&N Hardcover', value: 'barnes_hard' },
                { label: 'B&N Audiobook', value: 'barnes_audio' },
                { label: 'Apple Books', value: 'ibooks' },
                { label: 'iTunes', value: 'itunes' },
                { label: 'Kobo', value: 'kobo' },
                { label: 'Kobo Audiobook', value: 'kobo_audio' },
                { label: 'Google Play eBook', value: 'googleplay' },
                { label: 'Google Play Audiobook', value: 'googleplay_audio' },
                { label: 'Bookshop eBook', value: 'bookshop_ebook' },
                { label: 'Bookshop Paperback', value: 'bookshop_paper' },
                { label: 'Bookshop Hardcover', value: 'bookshop_hard' },
                { label: 'BAM Paperback', value: 'bam_paper' },
                { label: 'BAM Hardcover', value: 'bam_hard' },
                { label: 'Indigo', value: 'indigo' },
                { label: 'Waterstones', value: 'waterstones' },
                { label: 'The Broken Binding', value: 'brokenbinding' },
                { label: 'Libro.fm', value: 'librofm' },
                { label: 'Downpour', value: 'downpour' },
                { label: 'Target', value: 'target' },
                { label: 'Walmart', value: 'walmart' },
                { label: 'Audiobooks.com', value: 'audiobooks_com' },
                { label: 'Spotify (Audiobooks)', value: 'spotify' },
                { label: 'Buy Direct (General)', value: 'buydirect' },
                { label: 'Buy Direct – eBook', value: 'ebook_buy_url' },
                { label: 'Buy Direct – Audiobook', value: 'audio_buy_url' },
                { label: 'Buy Direct – Signed Copy', value: 'signed_buy_url' },
                { label: 'Buy Direct – Paperback', value: 'paper_buy_url' },
                { label: 'Buy Direct – Hardcover', value: 'hard_buy_url' },
                { label: 'Custom Link 1', value: 'custom1' },
                { label: 'Custom Link 2', value: 'custom2' },
                { label: 'Custom Link 3', value: 'custom3' },
                { label: 'Custom Link 4', value: 'custom4' },
                { label: 'Custom Link 5', value: 'custom5' },
                { label: 'Custom Link 6', value: 'custom6' }
              ],
              onChange: (val) => setAttributes({ 'button-link': val })
            })
          ),

          // === Display Settings =============================================
          el(PanelBody, { title: __('Display Settings', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Books Per Row', 'modfarm'),
              value: attributes['books-in-row'],
              options: [
                { label: 'One',   value: '100%' },
                { label: 'Two',   value: '50%' },
                { label: 'Three', value: '33.33%' },
                { label: 'Four',  value: '25%' }
              ],
              onChange: (val) => setAttributes({ 'books-in-row': val })
            }),
            // Handpicked has fixed order (manual), so we intentionally do NOT show Display Order here.
            el(RangeControl, {
              label: __('Books Per Page', 'modfarm'),
              value: attributes['books-per-page'],
              onChange: (val) => setAttributes({ 'books-per-page': val }),
              min: 1, max: 100
            }),
            el(ToggleControl, {
              label: __('Show Pagination', 'modfarm'),
              checked: !!attributes['show-pagination'],
              onChange: (val) => setAttributes({ 'show-pagination': !!val })
            })
          ),

          // === Card Extras (Handpicked-specific, but standardized) ===========
          el(PanelBody, { title: __('Card Extras', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Show Author', 'modfarm'),
              checked: showAuthor,
              onChange: (v) => setAttributes({ showAuthor: !!v })
            }),
            el(ToggleControl, {
              label: __('Show Publication Date', 'modfarm'),
              checked: showPubDate,
              onChange: (v) => setAttributes({ showPubDate: !!v })
            }),
            showPubDate &&
              el(SelectControl, {
                label: __('Date Source', 'modfarm'),
                value: pubDateKey,
                options: [
                  { label: __('Publication Date (eBook)', 'modfarm'), value: 'publication_date' },
                  { label: __('Audiobook Publication Date', 'modfarm'), value: 'audiobook_publication_date' }
                ],
                onChange: (v) => setAttributes({ pubDateKey: v })
              }),
            el(ToggleControl, {
              label: __('Show Short Description', 'modfarm'),
              checked: showShortDescription,
              onChange: (v) => setAttributes({ showShortDescription: !!v })
            })
          ),

          // === Card Layout & Styles (global vs local overrides) ==============
          el(PanelBody, { title: __('Card Layout & Styles', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Use Global Card Styles (ModFarm Settings)', 'modfarm'),
              checked: cardUseGlobal,
              onChange: (val) => setAttributes({ cardUseGlobal: !!val })
            }),
            cardUseGlobal
              ? el('p', { className: 'components-base-control__help' },
                  __('Using global shapes, spacing, and shadows from ModFarm Settings. Turn this off to override styles for this block only.', 'modfarm')
                )
              : el(Fragment, {},
                  el(SelectControl, {
                    label: __('Cover Shape', 'modfarm'),
                    value: cardCoverShape,
                    options: COVER_SHAPE_OPTIONS,
                    onChange: (v) => setAttributes({ cardCoverShape: v })
                  }),
                  el(SelectControl, {
                    label: __('Primary Button Shape', 'modfarm'),
                    value: cardButtonShape,
                    options: BUTTON_SHAPE_OPTIONS,
                    onChange: (v) => setAttributes({ cardButtonShape: v })
                  }),
                  el(SelectControl, {
                    label: __('Sample Button Shape', 'modfarm'),
                    value: cardSampleShape,
                    options: SAMPLE_SHAPE_OPTIONS,
                    onChange: (v) => setAttributes({ cardSampleShape: v })
                  }),
                  el(SelectControl, {
                    label: __('CTA Spacing', 'modfarm'),
                    value: cardCtaMode,
                    options: [
                      { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
                      ...CTA_OPTIONS
                    ],
                    onChange: (v) => setAttributes({ cardCtaMode: v })
                  }),
                  el(SelectControl, {
                    label: __('Shadow Style', 'modfarm'),
                    value: cardShadowStyle,
                    options: SHADOW_OPTIONS,
                    onChange: (v) => setAttributes({ cardShadowStyle: v })
                  }),
                  el('hr', {}),
                  el('p', { className: 'components-base-control__label' },
                    __('Per-Block Colors (optional)', 'modfarm')
                  ),
                  renderColorOverrideControl(__('Primary Button Background', 'modfarm'), 'cardButtonBg', cardButtonBg),
                  renderColorOverrideControl(__('Primary Button Text', 'modfarm'),       'cardButtonFg', cardButtonFg),
                  renderColorOverrideControl(__('Sample Button Background', 'modfarm'),  'cardSampleBg', cardSampleBg),
                  renderColorOverrideControl(__('Sample Button Text', 'modfarm'),        'cardSampleFg', cardSampleFg)
                )
          ),

          // === Display Toggles ==============================================
          el(PanelBody, { title: __('Display Toggles', 'modfarm'), initialOpen: false },
            cardUseGlobal
              ? el('p', { className: 'components-base-control__help' },
                  __('Visibility is using the global Book Card settings. Turn off "Use Global Card Styles" to override for this block.', 'modfarm')
                )
              : el(Fragment, {},
                  el(ToggleControl, {
                    label: __('Show Title', 'modfarm'),
                    checked: cardShowTitle,
                    onChange: (val) => setAttributes({ cardShowTitle: !!val })
                  }),
                  el(ToggleControl, {
                    label: __('Show Series', 'modfarm'),
                    checked: cardShowSeries,
                    onChange: (val) => setAttributes({ cardShowSeries: !!val })
                  }),
                  el(ToggleControl, {
                    label: __('Show Primary Button', 'modfarm'),
                    checked: cardShowPrimaryButton,
                    onChange: (val) => setAttributes({ cardShowPrimaryButton: !!val })
                  }),
                  el(ToggleControl, {
                    label: __('Show Sample Button', 'modfarm'),
                    checked: cardShowSampleButton,
                    onChange: (val) => setAttributes({ cardShowSampleButton: !!val })
                  })
                ),
            el(SelectControl, {
              label: __('Audio Mode', 'modfarm'),
              value: attributes['audio-mode'] || 'auto',
              options: [
                { label: __('Auto (prefer player)', 'modfarm'), value: 'auto' },
                { label: __('Player only', 'modfarm'),          value: 'player' },
                { label: __('Sample only', 'modfarm'),          value: 'sample' },
                { label: __('Off', 'modfarm'),                  value: 'off' }
              ],
              onChange: (val) => setAttributes({ 'audio-mode': val })
            })
          ),

          // === Button Style ==================================================
          el(PanelBody, { title: __('Button Style', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Button Text', 'modfarm'),
              value: attributes['button-text'],
              onChange: (val) => setAttributes({ 'button-text': val })
            }),
            el(SelectControl, {
              label: __('Button Target', 'modfarm'),
              value: attributes['button-target'],
              options: [
                { label: __('Same Tab', 'modfarm'), value: '_self' },
                { label: __('New Tab', 'modfarm'),  value: '_blank' }
              ],
              onChange: (val) => setAttributes({ 'button-target': val })
            })
          ),

          // === Sample Button Style ==========================================
          el(PanelBody, { title: __('Sample Button Style', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Sample Button Text', 'modfarm'),
              value: attributes['samplebtn-text'] || __('Play Sample', 'modfarm'),
              onChange: (v) => setAttributes({ 'samplebtn-text': v })
            })
          ),

          // === Misc ==========================================================
          el(PanelBody, { title: __('Misc', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Tracker Location', 'modfarm'),
              value: attributes['tracker-loc'],
              onChange: (val) => setAttributes({ 'tracker-loc': val })
            }),
            el(SelectControl, {
              label: __('Book Series Volume', 'modfarm'),
              value: attributes['volume-text'],
              options: [
                { label: 'Book', value: 'Book' },
                { label: 'Part', value: 'Part' },
                { label: 'vol',  value: 'vol' },
                { label: 't.',   value: 't.' },
                { label: 'buch', value: 'buch' }
              ],
              onChange: (val) => setAttributes({ 'volume-text': val })
            })
          )
        )
      );
    },

    save: function () {
      return null;
    }
  });
})(window.wp);