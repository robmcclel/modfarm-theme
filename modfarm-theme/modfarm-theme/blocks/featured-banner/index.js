/* global window */
(function () {
  if (!window.wp) return;

  const { createElement: el, Fragment, useState, useEffect } = wp.element;

  const be = wp.blockEditor || wp.editor;
  if (!be) return;

  const { InspectorControls, useBlockProps } = be;

  // Match hero-cover: native gradient panel (name varies by WP version)
  const PanelColorGradientSettings =
    be.PanelColorGradientSettings ||
    be.__experimentalPanelColorGradientSettings ||
    null;

  const {
    PanelBody,
    SelectControl,
    TextControl,
    RangeControl,
    ToggleControl,
    Button,
    Notice,
    ColorPalette
  } = wp.components;

  const ServerSideRender = wp.serverSideRender;
  const apiFetch = wp.apiFetch;

  const BUTTON_OPTIONS = [
    { label: '— None —', value: '__none__' },
    { label: 'See The Book (Permalink)', value: 'permalink' },
    { label: 'Series Permalink (Series page)', value: 'series_permalink' },

    { label: 'Buy Now (Kindle)', value: 'kindle_url' },
    { label: 'Audible', value: 'audible_url' },
    { label: 'Amazon Paperback', value: 'amazon_paper' },
    { label: 'Amazon Hardcover', value: 'amazon_hard' },
    
    { label: 'Read Sample', value: 'text_sample_url' },
    { label: 'Reviews', value: 'reviews_url' },
    
    { label: 'Nook', value: 'nook' },
    { label: 'B&N (Paperback)', value: 'barnes_paper' },
    { label: 'B&N (Hardcover)', value: 'barnes_hard' },
    { label: 'B&N (Audiobook)', value: 'barnes_audio' },
    
    { label: 'Kobo', value: 'kobo' },
    { label: 'Kobo Audio', value: 'kobo_audio' },
    
    { label: 'Google Play', value: 'googleplay' },
    
    { label: 'Bookshop (eBook)', value: 'bookshop_ebook' },
    { label: 'Bookshop (Paperback)', value: 'bookshop_paper' },
    { label: 'Bookshop (Hardcover)', value: 'bookshop_hard' },
    
    { label: 'BAM (Paperback)', value: 'bam_paper' },
    { label: 'BAM (Hardcover)', value: 'bam_hard' },
    
    { label: 'Indigo', value: 'indigo' },
    { label: 'Waterstones', value: 'waterstones' },
    { label: 'The Broken Binding', value: 'brokenbinding' },
    { label: 'Target', value: 'target' },
    { label: 'Libro.fm', value: 'librofm' },
    { label: 'Downpour', value: 'downpour' },
    { label: 'Target', value: 'target' },
    { label: 'Walmart', value: 'walmart' },
    { label: 'Audiobooks.com', value: 'audiobooks_com' },
    { label: 'Spotify', value: 'spotify' },

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

  ];

  const COVER_OPTIONS = [
    { label: 'eBook Cover (BMS)', value: 'cover_ebook' },
    { label: 'Audiobook Cover (BMS)', value: 'cover_image_audio' },
    { label: 'Paperback Cover', value: 'cover_paperback' },
    { label: 'Hardcover Cover', value: 'cover_hardcover' },
    { label: '3D eBook Cover', value: 'cover_ebook_3d' },
    { label: '3D Paperback Cover', value: 'cover_paperback_3d' },
    { label: '3D Hardcover Cover', value: 'cover_hardcover_3d' },
    { label: '3D Audiobook Cover', value: 'cover_image_audio_3d' },
    { label: 'Composite (BMS)', value: 'cover_image_composite' },
    { label: '3D Mockup (BMS)', value: 'cover_image_3d' },
    { label: 'Featured Image (Book Page)', value: 'featured_image' }
  ];

  function ColorField({ label, value, onChange, help }) {
    return el(
      'div',
      { className: 'mfbb-color-field', style: { marginTop: '10px' } },
      el('div', { style: { fontWeight: 600, marginBottom: '6px' } }, label),
      el(ColorPalette, {
        value: value || '',
        onChange: (c) => onChange(c || '')
      }),
      help ? el('p', { className: 'components-base-control__help' }, help) : null
    );
  }

  function BookSearch({ value, onPick }) {
    const [q, setQ] = useState('');
    const [loading, setLoading] = useState(false);
    const [results, setResults] = useState([]);

    function runSearch(term) {
      const t = (term || '').trim();
      if (t.length < 3) {
        setResults([]);
        return;
      }
      setLoading(true);
      apiFetch({
        path: `/wp/v2/book?status=publish&search=${encodeURIComponent(
          t
        )}&_fields=id,title&per_page=20`
      })
        .then((list) => {
          const items = (list || []).map((it) => ({
            id: it.id,
            title: it.title && it.title.rendered ? it.title.rendered : `#${it.id}`
          }));
          setResults(items);
        })
        .catch(() => setResults([]))
        .finally(() => setLoading(false));
    }

    return el(
      Fragment,
      {},
      el(TextControl, {
        label: 'Find Book by Title',
        help: 'Type 3+ characters to search',
        value: q,
        onChange: (v) => {
          setQ(v);
          runSearch(v);
        }
      }),
      loading && el(Notice, { status: 'info', isDismissible: false }, 'Searching…'),
      results.length > 0 &&
        el(
          'div',
          { className: 'mfbb-search' },
          results.map((r) =>
            el(
              Button,
              {
                key: r.id,
                variant: 'secondary',
                onClick: () => {
                  onPick(r.id);
                  setQ('');
                  setResults([]);
                }
              },
              `#${r.id} — ${r.title}`
            )
          )
        ),
      el(TextControl, {
        label: 'ID (manual fallback)',
        type: 'number',
        value: value || 0,
        onChange: (v) => onPick(parseInt(v || '0', 10) || 0)
      })
    );
  }

  function ButtonRow(n, attributes, setAttributes) {
    const srcKey = `btn${n}Source`;
    const lblKey = `btn${n}Label`;
    const varKey = `btn${n}Variant`;
    const srcVal = attributes[srcKey] || '__none__';

    const variantStored = attributes[varKey] || 'secondary';
    const variantUi = variantStored === 'outline' ? 'secondary' : variantStored;

    return el(
      Fragment,
      {},
      el(SelectControl, {
        label: `Button ${n} – Source`,
        value: srcVal,
        options: BUTTON_OPTIONS,
        onChange: (val) => {
          const next = { [srcKey]: val };
          if (val === '__none__') {
            next[lblKey] = '';
            next[varKey] = 'secondary';
          }
          setAttributes(next);
        }
      }),

      srcVal !== '__none__' &&
        el(TextControl, {
          label: `Button ${n} – Label`,
          value: attributes[lblKey] || '',
          onChange: (val) => setAttributes({ [lblKey]: val })
        }),

      srcVal !== '__none__' &&
        el(SelectControl, {
          label: `Button ${n} – Style`,
          value: variantUi,
          options: [
            { label: 'Primary (filled)', value: 'primary' },
            { label: 'Secondary (outline)', value: 'secondary' }
          ],
          onChange: (val) => setAttributes({ [varKey]: val })
        })
    );
  }

  wp.blocks.registerBlockType('modfarm/featured-banner', {
    apiVersion: 3,
    title: 'Featured Banner',
    icon: 'cover-image',
    category: 'modfarm-theme',

    edit: (props) => {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const bookId = attributes.bookId || 0;

      useEffect(() => {
        if (!bookId) return;
        if (!attributes.showHeadline) return;
        if ((attributes.headlineSource || 'book') !== 'custom') return;

        const need = !attributes.headline || String(attributes.headline).trim() === '';
        if (!need) return;

        apiFetch({ path: `/wp/v2/book/${bookId}?_fields=title` })
          .then((r) => {
            const t = r?.title?.rendered ? String(r.title.rendered) : '';
            if (t) setAttributes({ headline: t });
          })
          .catch(() => {});
      }, [bookId, attributes.showHeadline, attributes.headlineSource]);

      useEffect(() => {
        if (!attributes.useCustomDesc) return;
        if (!attributes.refreshCustomDescOnBookChange) return;
        if (!bookId) return;

        apiFetch({ path: `/wp/v2/book/${bookId}?_fields=meta` })
          .then((r) => {
            const meta = r && r.meta ? r.meta : {};
            const sd = meta.short_description || '';
            const bd = meta.book_description || '';
            const next = sd && String(sd).trim() !== '' ? sd : bd;
            if (next && String(next).trim() !== '') {
              setAttributes({ descOverride: String(next) });
            }
          })
          .catch(() => {});
      }, [bookId, attributes.useCustomDesc, attributes.refreshCustomDescOnBookChange]);

      const mediaMode = attributes.mediaMode || 'cover';
      const heroSource = attributes.heroSource || 'book_meta';

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: 'Select Book', initialOpen: true },
            el(BookSearch, {
              value: attributes.bookId,
              onPick: (id) => setAttributes({ bookId: id })
            }),
            !attributes.bookId &&
              el(
                Notice,
                { status: 'info', isDismissible: false },
                'Pick a book to populate cover/card, hero background (hero_image), and description.'
              )
          ),

          el(
            PanelBody,
            { title: 'Hero Background', initialOpen: false },
            el(SelectControl, {
              label: 'Hero Source',
              value: heroSource,
              options: [
                { label: 'Book Meta: hero_image', value: 'book_meta' },
                { label: 'Book Featured Image', value: 'featured_image' },
                { label: 'Custom URL', value: 'custom' }
              ],
              onChange: (v) => setAttributes({ heroSource: v })
            }),

            heroSource === 'custom' &&
              el(TextControl, {
                label: 'Custom Hero Image URL',
                value: attributes.heroUrl || '',
                onChange: (v) => setAttributes({ heroUrl: v })
              }),

            el(RangeControl, {
              label: 'Min Height (px)',
              value: attributes.minHeight || 620,
              min: 240,
              max: 900,
              step: 10,
              onChange: (val) => setAttributes({ minHeight: val })
            })
          ),

          // Overlay: hero-cover contract (dimRatio + overlayColor + overlayGradient)
          el(
            PanelBody,
            { title: 'Overlay', initialOpen: false },

            el(RangeControl, {
              label: 'Dim Ratio',
              value: typeof attributes.dimRatio === 'number' ? attributes.dimRatio : 30,
              min: 0,
              max: 100,
              step: 1,
              onChange: (v) => setAttributes({ dimRatio: v })
            }),

            PanelColorGradientSettings
              ? el(PanelColorGradientSettings, {
                  title: 'Overlay',
                  settings: [
                    {
                      label: 'Overlay color',
                      colorValue: attributes.overlayColor || '#000000',
                      onColorChange: (v) =>
                        setAttributes({
                          overlayColor: v || '#000000',
                          overlayGradient: ''
                        })
                    },
                    {
                      label: 'Overlay gradient',
                      gradientValue:
                        attributes.overlayGradient && String(attributes.overlayGradient).trim()
                          ? attributes.overlayGradient
                          : undefined,
                      onGradientChange: (v) =>
                        setAttributes({
                          overlayGradient: v || ''
                        })
                    }
                  ]
                })
              : el(
                  Fragment,
                  {},
                  el(ColorField, {
                    label: 'Overlay Color (fallback)',
                    value: attributes.overlayColor || '#000000',
                    onChange: (v) =>
                      setAttributes({
                        overlayColor: v || '#000000',
                        overlayGradient: ''
                      })
                  }),
                  el(TextControl, {
                    label: 'Overlay Gradient CSS (fallback)',
                    value: attributes.overlayGradient || '',
                    placeholder: 'linear-gradient(180deg, rgba(0,0,0,.6), rgba(0,0,0,0))',
                    onChange: (v) => setAttributes({ overlayGradient: v || '' })
                  })
                )
          ),

          el(
            PanelBody,
            { title: 'Media', initialOpen: false },
            el(SelectControl, {
              label: 'Media Mode',
              value: mediaMode,
              options: [
                { label: 'None (text only)', value: 'none' },
                { label: 'Cover image', value: 'cover' },
                { label: 'ModFarm Book Card', value: 'card' }
              ],
              onChange: (val) => setAttributes({ mediaMode: val })
            }),

            mediaMode !== 'none' &&
              el(SelectControl, {
                label: 'Cover Source',
                value: attributes.coverSource || 'cover_ebook',
                options: COVER_OPTIONS,
                onChange: (val) => setAttributes({ coverSource: val })
              }),

            el(SelectControl, {
              label: 'Media Side',
              value: attributes.mediaSide || 'right',
              options: [
                { label: 'Left (media left, text right)', value: 'left' },
                { label: 'Right (media right, text left)', value: 'right' }
              ],
              onChange: (val) => setAttributes({ mediaSide: val })
            }),

            mediaMode !== 'none' &&
              el(RangeControl, {
                label: 'Media Width (px)',
                value: attributes.mediaWidth || 350,
                min: 350,
                max: 520,
                step: 10,
                onChange: (val) => setAttributes({ mediaWidth: val })
              }),

            mediaMode !== 'none' &&
              el(SelectControl, {
                label: 'Cover Corners',
                value: attributes.coverCorners || 'square',
                options: [
                  { label: 'Square', value: 'square' },
                  { label: 'Rounded', value: 'rounded' }
                ],
                onChange: (val) => setAttributes({ coverCorners: val })
              })
          ),

          el(
            PanelBody,
            { title: 'Alignment', initialOpen: false },
            el(SelectControl, {
              label: 'Text Alignment',
              value: attributes.textAlign || 'left',
              options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
              ],
              onChange: (v) => setAttributes({ textAlign: v })
            }),
            el(SelectControl, {
              label: 'Button Alignment',
              value: attributes.buttonAlign || 'center',
              options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
              ],
              onChange: (v) => setAttributes({ buttonAlign: v })
            })
          ),

          el(
            PanelBody,
            { title: 'Text', initialOpen: false },
            el(ToggleControl, {
              label: 'Show Headline',
              checked: !!attributes.showHeadline,
              onChange: (v) => setAttributes({ showHeadline: !!v })
            }),

            !!attributes.showHeadline &&
              el(SelectControl, {
                label: 'Headline Source',
                value: attributes.headlineSource || 'book',
                options: [
                  { label: 'Book Title', value: 'book' },
                  { label: 'Custom', value: 'custom' }
                ],
                onChange: (v) => setAttributes({ headlineSource: v })
              }),

            !!attributes.showHeadline &&
              attributes.headlineSource === 'custom' &&
              el(TextControl, {
                label: 'Custom Headline',
                value: attributes.headline || '',
                onChange: (v) => setAttributes({ headline: v })
              }),

            el(SelectControl, {
              label: 'Headline Tag',
              value: attributes.headlineTag || 'h2',
              options: [
                { label: 'H1', value: 'h1' },
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' },
                { label: 'H4', value: 'h4' }
              ],
              onChange: (v) => setAttributes({ headlineTag: v })
            }),

            el(RangeControl, {
              label: 'Headline Font Size (px)',
              value: attributes.headlineFontSize || 42,
              min: 22,
              max: 70,
              step: 1,
              onChange: (v) => setAttributes({ headlineFontSize: v })
            }),

            el(TextControl, {
              label: 'Kicker (optional)',
              value: attributes.kicker || '',
              onChange: (v) => setAttributes({ kicker: v })
            }),

            el(TextControl, {
              label: 'Subhead (optional)',
              value: attributes.subhead || '',
              onChange: (v) => setAttributes({ subhead: v })
            })
          ),

          el(
            PanelBody,
            { title: 'Description', initialOpen: false },
            el(ToggleControl, {
              label: 'Use Custom Description Override',
              checked: !!attributes.useCustomDesc,
              onChange: (v) => setAttributes({ useCustomDesc: !!v })
            }),

            !!attributes.useCustomDesc &&
              el(ToggleControl, {
                label: 'Auto-fill custom desc when book changes',
                checked: !!attributes.refreshCustomDescOnBookChange,
                onChange: (v) => setAttributes({ refreshCustomDescOnBookChange: !!v })
              }),

            !!attributes.useCustomDesc &&
              el(TextControl, {
                label: 'Custom Description',
                value: attributes.descOverride || '',
                onChange: (v) => setAttributes({ descOverride: v }),
                help:
                  'If empty, block falls back to short_description, then book_description.'
              }),

            el(RangeControl, {
              label: 'Description Font Size (px)',
              value: attributes.descFontSize || 18,
              min: 12,
              max: 28,
              step: 1,
              onChange: (v) => setAttributes({ descFontSize: v })
            }),

            el(RangeControl, {
              label: 'Description Font Weight',
              value: attributes.descFontWeight || 500,
              min: 100,
              max: 900,
              step: 100,
              onChange: (v) => setAttributes({ descFontWeight: v })
            }),

            el(SelectControl, {
              label: 'Text Transform',
              value: attributes.descTextTransform || 'none',
              options: [
                { label: 'None', value: 'none' },
                { label: 'Uppercase', value: 'uppercase' },
                { label: 'Lowercase', value: 'lowercase' },
                { label: 'Capitalize', value: 'capitalize' }
              ],
              onChange: (v) => setAttributes({ descTextTransform: v })
            }),

            el(ToggleControl, {
              label: 'Use excerpt (trim words)',
              checked: !!attributes.useExcerpt,
              onChange: (v) => setAttributes({ useExcerpt: !!v })
            }),

            !!attributes.useExcerpt &&
              el(RangeControl, {
                label: 'Excerpt Length (approx chars)',
                value: attributes.excerptLen || 180,
                min: 60,
                max: 420,
                step: 10,
                onChange: (v) => setAttributes({ excerptLen: v })
              })
          ),

          el(
            PanelBody,
            { title: 'Buttons', initialOpen: true },
            ButtonRow(1, attributes, setAttributes),
            ButtonRow(2, attributes, setAttributes),
            ButtonRow(3, attributes, setAttributes),

            el(SelectControl, {
              label: 'Button Corners',
              value: attributes.buttonCorners || 'inherit',
              options: [
                { label: 'Inherit', value: 'inherit' },
                { label: 'Square', value: 'square' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Pill', value: 'pill' }
              ],
              onChange: (v) => setAttributes({ buttonCorners: v })
            }),

            el(SelectControl, {
              label: 'Button Style Mode',
              value: attributes.buttonStyleMode || 'inherit',
              options: [
                { label: 'Inherit theme', value: 'inherit' },
                { label: 'Custom (override colors)', value: 'custom' }
              ],
              onChange: (v) => setAttributes({ buttonStyleMode: v })
            }),

            attributes.buttonStyleMode === 'custom' &&
              el(
                Fragment,
                {},
                el(TextControl, {
                  label: 'Primary BG',
                  value: attributes.btnPrimaryBg || '',
                  onChange: (v) => setAttributes({ btnPrimaryBg: v })
                }),
                el(TextControl, {
                  label: 'Primary Text',
                  value: attributes.btnPrimaryText || '',
                  onChange: (v) => setAttributes({ btnPrimaryText: v })
                }),
                el(TextControl, {
                  label: 'Primary Border',
                  value: attributes.btnPrimaryBorder || '',
                  onChange: (v) => setAttributes({ btnPrimaryBorder: v })
                }),

                el(TextControl, {
                  label: 'Outline BG',
                  value: attributes.btnOutlineBg || '',
                  onChange: (v) => setAttributes({ btnOutlineBg: v })
                }),
                el(TextControl, {
                  label: 'Outline Text',
                  value: attributes.btnOutlineText || '',
                  onChange: (v) => setAttributes({ btnOutlineText: v })
                }),
                el(TextControl, {
                  label: 'Outline Border',
                  value: attributes.btnOutlineBorder || '',
                  onChange: (v) => setAttributes({ btnOutlineBorder: v })
                })
              )
          )
        ),

        // Preview wrapper: SSR only (no editor-only overlay layer)
        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'modfarm/featured-banner',
            attributes: attributes
          })
        )
      );
    },

    save: () => null
  });
})();