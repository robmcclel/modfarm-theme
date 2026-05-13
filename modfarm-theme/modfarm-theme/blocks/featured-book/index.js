(function () {
  const { createElement: el, Fragment, useState, useEffect } = wp.element;
  const { InspectorControls, useBlockProps, RichText } = wp.blockEditor;
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
    return el('div', { className: 'mftb-color-field', style: { marginTop: '10px' } },
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
      if (t.length < 3) { setResults([]); return; }
      setLoading(true);
      apiFetch({ path: `/wp/v2/book?status=publish&search=${ encodeURIComponent(t) }&_fields=id,title&per_page=20` })
        .then(list => {
          const items = (list || []).map(it => ({
            id: it.id,
            title: it.title && it.title.rendered ? it.title.rendered : `#${it.id}`
          }));
          setResults(items);
        })
        .catch(() => setResults([]))
        .finally(() => setLoading(false));
    }

    return el(Fragment, {},
      el(TextControl, {
        label: 'Find Book by Title',
        help: 'Type 3+ characters to search',
        value: q,
        onChange: v => { setQ(v); runSearch(v); }
      }),
      loading && el(Notice, { status: 'info', isDismissible: false }, 'Searching…'),
      results.length > 0 && el('div', { className: 'mftb-search' },
        results.map(r => el(Button, {
          key: r.id, variant: 'secondary',
          onClick: () => { onPick(r.id); setQ(''); setResults([]); }
        }, `#${r.id} — ${r.title}`))
      ),
      el(TextControl, {
        label: 'ID (manual fallback)',
        type: 'number',
        value: value || 0,
        onChange: v => onPick(parseInt(v || '0', 10) || 0)
      })
    );
  }

  function ButtonRow(n, attributes, setAttributes) {
    const srcKey = `btn${n}Source`;
    const lblKey = `btn${n}Label`;
    const varKey = `btn${n}Variant`;
    const srcVal = attributes[srcKey] || '__none__';

    // Normalize old "outline" to "secondary" in UI (we still allow "outline" stored)
    const variantStored = attributes[varKey] || 'secondary';
    const variantUi = (variantStored === 'outline') ? 'secondary' : variantStored;

    return el(Fragment, {},
      el(SelectControl, {
        label: `Button ${n} – Source`,
        value: srcVal,
        options: BUTTON_OPTIONS,
        onChange: val => {
          const next = { [srcKey]: val };
          if (val === '__none__') {
            next[lblKey] = '';
            next[varKey] = 'secondary';
          }
          setAttributes(next);
        }
      }),

      srcVal !== '__none__' && el(TextControl, {
        label: `Button ${n} – Label`,
        value: attributes[lblKey] || '',
        onChange: val => setAttributes({ [lblKey]: val })
      }),

      srcVal !== '__none__' && el(SelectControl, {
        label: `Button ${n} – Style`,
        value: variantUi,
        options: [
          { label: 'Primary (filled)', value: 'primary' },
          { label: 'Secondary (outline)', value: 'secondary' }
        ],
        onChange: val => setAttributes({ [varKey]: val })
      })
    );
  }

  wp.blocks.registerBlockType('modfarm/featured-book', {
    title: 'Featured Book',
    icon: 'book',
    category: 'modfarm-book-page',

    // Keep attributes in block.json; JS definitions are not required for dynamic blocks.
    // We only reference them here.

    edit: (props) => {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const seedBookId =
        (attributes.mode === 'manual' ? attributes.bookId : (attributes.pinnedId || 0));

      function fetchSelectedBookDescription() {
        if (seedBookId) {
          return apiFetch({ path: `/wp/v2/book/${seedBookId}?_fields=id,title,meta.book_description` })
            .then(r => ({
              id: r?.id || seedBookId,
              title: r?.title?.rendered || '',
              description: r?.meta?.book_description || ''
            }));
        }

        if (attributes.mode === 'auto') {
          const dateType = attributes.dateType || 'publication_date';
          return apiFetch({
            path: `/modfarm/v1/featured-book-description?dateType=${encodeURIComponent(dateType)}`
          });
        }

        return Promise.resolve({ id: 0, title: '', description: '' });
      }

      // Seed headline ONLY when not following book title (existing behavior)
      useEffect(() => {
        const id = attributes.bookId;
        if (!id || attributes.followBookText) return;

        const needHeadline = !attributes.headline || attributes.headline.trim() === '';
        if (!needHeadline) return;

        apiFetch({ path: `/wp/v2/book/${id}?_fields=title` })
          .then(r => {
            if (needHeadline && r?.title?.rendered) {
              setAttributes({ headline: r.title.rendered });
            }
          })
          .catch(() => {});
      }, [attributes.bookId, attributes.followBookText]);

      // When enabling custom desc, seed from BMS description if empty (and optionally refresh on book change)
      useEffect(() => {
        if (!attributes.useCustomDesc) return;

        const shouldRefresh = !!attributes.refreshCustomDescOnBookChange;
        const isEmpty = ((attributes.descOverride || '').trim() === '');

        if (!shouldRefresh && !isEmpty) return;

        fetchSelectedBookDescription()
          .then(r => {
            const raw = r?.description || '';
            if (raw) setAttributes({ descOverride: String(raw) });
          })
          .catch(() => {});
      }, [attributes.useCustomDesc, attributes.refreshCustomDescOnBookChange, seedBookId, attributes.mode, attributes.dateType]);

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(PanelBody, { title: 'Mode & Date', initialOpen: true },
            el(SelectControl, {
              label: 'Mode',
              value: attributes.mode,
              options: [
                { label: 'Manual (pick a book)', value: 'manual' },
                { label: 'Most Recent Published', value: 'auto' }
              ],
              onChange: val => {
                const next = { mode: val };
                if (val === 'auto' && attributes.followBookText) next.headline = '';
                setAttributes(next);
              }
            }),
            el(SelectControl, {
              label: 'Date Type',
              value: attributes.dateType,
              options: [
                { label: 'Publication Date', value: 'publication_date' },
                { label: 'Hardcover Publication Date', value: 'hardcover_publication_date' },
                { label: 'Audiobook Publication Date', value: 'audiobook_publication_date' }
              ],
              onChange: val => setAttributes({ dateType: val })
            }),
            attributes.mode === 'auto' && el(BookSearch, {
              value: attributes.pinnedId,
              onPick: id => setAttributes({ pinnedId: id })
            }),
            attributes.mode === 'auto' && el(Notice, {
              status: 'info', isDismissible: false
            }, 'Most Recent Published picks the newest book with the selected date type on or before today, unless pinned.')
          ),

          el(PanelBody, { title: 'Book & Cover', initialOpen: false },
            attributes.mode === 'manual'
              ? el(BookSearch, { value: attributes.bookId, onPick: id => setAttributes({ bookId: id }) })
              : el(Notice, { status: 'info', isDismissible: false }, 'Most Recent Published ignores future publication dates and preorders.'),
            el(SelectControl, {
              label: 'Cover Source',
              value: attributes.coverSource,
              options: COVER_OPTIONS,
              onChange: val => setAttributes({ coverSource: val })
            }),
            el(SelectControl, {
              label: 'Cover Corners',
              value: attributes.coverCorners,
              options: [
                { label: 'Square', value: 'square' },
                { label: 'Rounded', value: 'rounded' }
              ],
              onChange: val => setAttributes({ coverCorners: val })
            })
          ),

          el(PanelBody, { title: 'Text', initialOpen: false },
            el(ToggleControl, {
              label: 'Follow selected book (title)',
              checked: !!attributes.followBookText,
              onChange: val => {
                const next = { followBookText: !!val };
                if (val) next.headline = '';
                setAttributes(next);
              }
            }),
            !attributes.followBookText && el(TextControl, {
              label: 'Headline (override)',
              value: attributes.headline,
              onChange: val => setAttributes({ headline: val })
            }),
            el(TextControl, {
              label: 'Kicker (e.g., “Available Now”)',
              value: attributes.kicker,
              onChange: val => setAttributes({ kicker: val })
            }),
            el(TextControl, {
              label: 'Subhead (optional)',
              value: attributes.subhead,
              onChange: val => setAttributes({ subhead: val })
            }),

            el(ToggleControl, {
              label: 'Use custom description (rich text)',
              checked: !!attributes.useCustomDesc,
              onChange: val => setAttributes({ useCustomDesc: !!val })
            }),

            attributes.useCustomDesc && el(ToggleControl, {
              label: 'Refresh custom description from book when changing selection',
              help: 'When ON, switching the selected book will re-seed your custom description from book_description.',
              checked: !!attributes.refreshCustomDescOnBookChange,
              onChange: val => setAttributes({ refreshCustomDescOnBookChange: !!val })
            }),

            attributes.useCustomDesc && el('div', { className: 'mftb-richtext' },
              el('div', { style: { marginTop: '8px', marginBottom: '6px', fontWeight: 600 } }, 'Description override (rich text)'),
              el(RichText, {
                tagName: 'div',
                value: attributes.descOverride || '',
                onChange: val => setAttributes({ descOverride: val }),
                placeholder: 'Type a shorter homepage-friendly description…',
                allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ]
              }),
              el('div', { style: { display: 'flex', gap: '8px', marginTop: '10px' } },
                  el(Button, {
                    variant: 'secondary',
                    onClick: () => {
                    fetchSelectedBookDescription()
                      .then(r => {
                        const raw = r?.description || '';
                        setAttributes({ descOverride: raw ? String(raw) : '' });
                      })
                      .catch(() => {});
                    }
                }, 'Refresh description from book'),
                el(Button, {
                  variant: 'secondary',
                  onClick: () => setAttributes({ descOverride: '' })
                }, 'Clear custom description')
              ),
              el('p', { className: 'components-base-control__help' },
                'If blank, the block uses BMS book_description. Supports paragraphs, links, bold/italic.'
              )
            ),

            el(ToggleControl, {
              label: 'Use excerpt (trim description)',
              checked: !!attributes.useExcerpt,
              onChange: val => setAttributes({ useExcerpt: !!val })
            }),
            !!attributes.useExcerpt && el(RangeControl, {
              label: 'Excerpt length (characters)',
              value: attributes.excerptLen,
              min: 40, max: 600, step: 10,
              onChange: val => setAttributes({ excerptLen: val })
            })
          ),

          el(PanelBody, { title: 'Buttons', initialOpen: false },
            ButtonRow(1, attributes, setAttributes),
            ButtonRow(2, attributes, setAttributes),
            ButtonRow(3, attributes, setAttributes),

            el('hr', { style: { margin: '14px 0' } }),

            el(SelectControl, {
              label: 'Button Style Mode',
              value: attributes.buttonStyleMode || 'inherit',
              options: [
                { label: 'Inherit (ModFarm Settings)', value: 'inherit' },
                { label: 'Custom (override colors)', value: 'custom' }
              ],
              onChange: val => setAttributes({ buttonStyleMode: val })
            }),

            (attributes.buttonStyleMode === 'custom') && el(Fragment, {},
              el('div', { style: { marginTop: '10px', fontWeight: 700 } }, 'Primary (filled) override'),
              el(ColorField, {
                label: 'Primary Background',
                value: attributes.btnPrimaryBg || '',
                onChange: v => setAttributes({ btnPrimaryBg: v }),
                help: 'Leave blank to inherit token.'
              }),
              el(ColorField, {
                label: 'Primary Text',
                value: attributes.btnPrimaryText || '',
                onChange: v => setAttributes({ btnPrimaryText: v }),
                help: 'Leave blank to inherit token.'
              }),
              el(ColorField, {
                label: 'Primary Border',
                value: attributes.btnPrimaryBorder || '',
                onChange: v => setAttributes({ btnPrimaryBorder: v }),
                help: 'Leave blank to inherit token.'
              }),

              el('div', { style: { marginTop: '14px', fontWeight: 700 } }, 'Secondary (outline) override'),
              el(ColorField, {
                label: 'Secondary Background',
                value: attributes.btnOutlineBg || '',
                onChange: v => setAttributes({ btnOutlineBg: v }),
                help: 'Leave blank to inherit token (usually transparent).'
              }),
              el(ColorField, {
                label: 'Secondary Text',
                value: attributes.btnOutlineText || '',
                onChange: v => setAttributes({ btnOutlineText: v }),
                help: 'Leave blank to inherit token.'
              }),
              el(ColorField, {
                label: 'Secondary Border',
                value: attributes.btnOutlineBorder || '',
                onChange: v => setAttributes({ btnOutlineBorder: v }),
                help: 'Leave blank to inherit token.'
              }),

              el(Button, {
                variant: 'secondary',
                style: { marginTop: '10px' },
                onClick: () => setAttributes({
                  btnPrimaryBg: '',
                  btnPrimaryText: '',
                  btnPrimaryBorder: '',
                  btnOutlineBg: '',
                  btnOutlineText: '',
                  btnOutlineBorder: ''
                })
              }, 'Clear all button color overrides')
            ),

            el(SelectControl, {
              label: 'Button Corners',
              value: attributes.buttonCorners || 'square',
              options: [
                { label: 'Inherit (token)', value: 'inherit' },
                { label: 'Square', value: 'square' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Pill', value: 'pill' }
              ],
              onChange: val => setAttributes({ buttonCorners: val })
            }),

            el(ToggleControl, {
              label: 'Add tracking data-* attributes',
              checked: !!attributes.tracking,
              onChange: val => setAttributes({ tracking: !!val })
            })
          ),

          el(PanelBody, { title: 'Layout & Theme', initialOpen: false },
            el(SelectControl, {
              label: 'Media Side',
              value: attributes.mediaSide,
              options: [
                { label: 'Left (cover left, text right)', value: 'left' },
                { label: 'Right (cover right, text left)', value: 'right' }
              ],
              onChange: val => setAttributes({ mediaSide: val })
            }),
            el(ToggleControl, {
              label: 'Dark Hero (invert text for dark backgrounds)',
              checked: !!attributes.darkHero,
              onChange: val => setAttributes({ darkHero: !!val })
            })
          )
        ),

        el('div', blockProps,
          el(ServerSideRender, { block: 'modfarm/featured-book', attributes })
        )
      );
    },

    save: () => null
  });
})();
