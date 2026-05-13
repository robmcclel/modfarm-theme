/* global window */
(function (wp) {
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const { registerBlockType } = wp.blocks;
  const {
    PanelBody,
    SelectControl,
    TextControl,
    RangeControl,
    ComboboxControl,
    ToggleControl,
    Button,
  } = wp.components;

  const { ColorPalette } = wp.blockEditor || wp.editor;

  const { Fragment, createElement: el } = wp.element;
  const { useSelect } = wp.data;
  const ServerSideRender = wp.serverSideRender;

  // Canonical option lists
  const EFFECT_OPTIONS = [
    { label: __('Flat', 'modfarm'),        value: 'flat' },
    { label: __('Shadow – Small', 'modfarm'),  value: 'shadow-sm' },
    { label: __('Shadow – Medium', 'modfarm'), value: 'shadow-md' },
    { label: __('Shadow – Large', 'modfarm'),  value: 'shadow-lg' },
    { label: __('Emboss', 'modfarm'),      value: 'emboss' },
  ];

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

  registerBlockType('modfarm/multi-tax-format', {
    apiVersion: 2,
    title: 'Multi-Tax Book Block',
    category: 'widgets',
    icon: 'book',

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const taxType = attributes['tax-type'] || '';
      const displayLayout = attributes['display-layout'] || 'grid';
      const isHorizontal = displayLayout === 'horizontal';

      // NEW: Card-level flags with sensible defaults
      const cardUseGlobal = attributes.cardUseGlobal !== false; // default true

      const cardCoverShape       = attributes.cardCoverShape       || 'inherit';
      const cardButtonShape      = attributes.cardButtonShape      || 'inherit';
      const cardSampleShape      = attributes.cardSampleShape      || 'inherit';
      const cardCtaMode          = attributes.cardCtaMode          || 'inherit';
      const cardShadowStyle      = attributes.cardShadowStyle      || 'inherit';

      const cardShowTitle        = attributes.cardShowTitle        !== false; // default true
      const cardShowSeries       = attributes.cardShowSeries       !== false; // default true
      const cardShowPrimaryButton= attributes.cardShowPrimaryButton!== false; // default true
      const cardShowSampleButton = attributes.cardShowSampleButton !== false; // default true

      const cardButtonBg         = attributes.cardButtonBg   || '';
      const cardButtonFg         = attributes.cardButtonFg   || '';
      const cardSampleBg         = attributes.cardSampleBg   || '';
      const cardSampleFg         = attributes.cardSampleFg   || '';

      // Taxonomy mapping
      const taxonomyMap = {
        series:   { attr: 'series-select',     taxonomy: 'book-series' },
        genre:    { attr: 'genre-select',      taxonomy: 'book-genre' },
        author:   { attr: 'bookauthor-select', taxonomy: 'book-author' },
        language: { attr: 'language-select',   taxonomy: 'book-language' },
        booktag:  { attr: 'booktag-select',    taxonomy: 'book-tags' }
      };

      const selectedTax = taxonomyMap[taxType] || null;

      const taxonomyTerms = useSelect((select) => {
        if (!selectedTax) return [];
        const terms = select('core').getEntityRecords('taxonomy', selectedTax.taxonomy, { per_page: -1 });
        return terms || [];
      }, [taxType]);

      const formatTerms = useSelect((select) => {
        const terms = select('core').getEntityRecords('taxonomy', 'book-format', { per_page: -1 });
        return terms || [];
      }, []);

      const themeColors = useSelect(
        (select) => {
          const settings = select('core/block-editor')?.getSettings?.() || {};
          return settings.colors || [];
        },
        []
      );

      const formatOptions = formatTerms.length
        ? [{ label: '— Select Format —', value: '' }].concat(
            formatTerms.map((term) => ({ label: term.name, value: term.id }))
          )
        : [{ label: 'Loading...', value: '' }];

      const selectedFormatId = attributes['book-format']?.id ?? '';
      const validFormatValue = formatOptions.some((opt) => opt.value === selectedFormatId)
        ? selectedFormatId
        : '';

      function getTaxonomySelector() {
        if (!selectedTax) {
          return el('p', {}, __('Select a taxonomy type to continue.', 'modfarm'));
        }
        const { attr } = selectedTax;

        const options = taxonomyTerms.map((term) => ({
          label: term.name,
          value: term.id
        }));

        const selectedTerm  = attributes[attr]?.id ?? '';
        const selectedValue = Number.isFinite(parseInt(selectedTerm, 10)) ? parseInt(selectedTerm, 10) : '';
        const validValue    = options.some((opt) => opt.value === selectedValue) ? selectedValue : '';

        return el(ComboboxControl, {
          label: __('Select Term', 'modfarm'),
          value: validValue,
          options,
          onChange: (val) => setAttributes({ [attr]: { id: parseInt(val, 10) || 0 } })
        });
      }

      const setCanon = (key, val, allowed, fallback) => {
        const v = allowed.includes(val) ? val : fallback;
        setAttributes({ [key]: v });
      };

      // Helpers for color overrides
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
            ? el(Button, {
                isSmall: true,
                variant: 'secondary',
                onClick: () => setAttributes({ [attrKey]: '' })
              }, __('Clear override', 'modfarm'))
            : null
        );
      };

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/multi-tax-format',
            attributes: Object.assign({}, attributes, {
              'display-layout': displayLayout,
              'horizontal-columns': parseInt(attributes['horizontal-columns'], 10) || 4
            })
          })
        ),

        el(InspectorControls, {},

          el(PanelBody, { title: __('Presentation', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Layout', 'modfarm'),
              value: displayLayout,
              options: [
                { label: __('Grid', 'modfarm'), value: 'grid' },
                { label: __('Horizontal Scroll', 'modfarm'), value: 'horizontal' }
              ],
              onChange: (val) => setAttributes({ 'display-layout': val || 'grid' })
            }),
            isHorizontal && el(SelectControl, {
              label: __('Visible Columns', 'modfarm'),
              value: String(attributes['horizontal-columns'] || 4),
              options: [
                { label: __('Three', 'modfarm'), value: '3' },
                { label: __('Four', 'modfarm'), value: '4' },
                { label: __('Five', 'modfarm'), value: '5' }
              ],
              onChange: (val) => setAttributes({ 'horizontal-columns': parseInt(val, 10) || 4 })
            })
          ),

          // Book Filters
          el(PanelBody, { title: __('Book Filters', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Select Display Taxonomy', 'modfarm'),
              value: taxType,
              options: [
                { label: 'Book Series',   value: 'series' },
                { label: 'Book Genre',    value: 'genre' },
                { label: 'Book Author',   value: 'author' },
                { label: 'Book Language', value: 'language' },
                { label: 'Book Tag',      value: 'booktag' }
              ],
              onChange: (val) => setAttributes({ 'tax-type': val })
            }),
            getTaxonomySelector(),
            el(SelectControl, {
              label: __('Book Format', 'modfarm'),
              value: validFormatValue,
              options: formatOptions,
              onChange: (val) => setAttributes({ 'book-format': { id: parseInt(val, 10) || 0 } })
            }),
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

          // Display Settings
          el(PanelBody, { title: __('Display Settings', 'modfarm'), initialOpen: false },
            !isHorizontal && el(SelectControl, {
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
            el(SelectControl, {
              label: __('Display Order', 'modfarm'),
              value: attributes['display-order'],
              options: [
                { label: 'Start With First Book', value: 'ASC' },
                { label: 'Most Recent First',     value: 'DESC' },
                { label: 'Random',                value: 'rand' }
              ],
              onChange: (val) => setAttributes({ 'display-order': val })
            }),
            attributes['display-order'] !== 'rand' && el(SelectControl, {
              label: __('Order Date Source', 'modfarm'),
              value: attributes['order-date-key'] || 'publication_date',
              options: [
                { label: __('Publication Date', 'modfarm'), value: 'publication_date' },
                { label: __('Hardcover Publication Date', 'modfarm'), value: 'hardcover_publication_date' },
                { label: __('Audiobook Publication Date', 'modfarm'), value: 'audiobook_publication_date' }
              ],
              onChange: (val) => setAttributes({ 'order-date-key': val })
            }),
            el(RangeControl, {
              label: isHorizontal ? __('Total Books', 'modfarm') : __('Books Per Page', 'modfarm'),
              value: attributes['books-per-page'],
              onChange: (val) => setAttributes({ 'books-per-page': val }),
              min: 1, max: 100
            }),
            !isHorizontal && el(ToggleControl, {
              label: __('Show Pagination', 'modfarm'),
              checked: !!attributes['show-pagination'],
              onChange: (val) => setAttributes({ 'show-pagination': !!val })
            })
          ),

          // Card Layout & Styles (global vs local overrides)
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
                  renderColorOverrideControl(
                    __('Primary Button Background', 'modfarm'),
                    'cardButtonBg',
                    cardButtonBg
                  ),
                  renderColorOverrideControl(
                    __('Primary Button Text', 'modfarm'),
                    'cardButtonFg',
                    cardButtonFg
                  ),
                  renderColorOverrideControl(
                    __('Sample Button Background', 'modfarm'),
                    'cardSampleBg',
                    cardSampleBg
                  ),
                  renderColorOverrideControl(
                    __('Sample Button Text', 'modfarm'),
                    'cardSampleFg',
                    cardSampleFg
                  )
                )
          ),

          // Display Toggles (title/series/buttons/sample override)
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
            // Audio Mode still applies in both global + local style modes
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

          // Button Style (non-visual behaviour)
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

          // Sample Button Style (text only; colors handled above)
          el(PanelBody, { title: __('Sample Button Style', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Sample Button Text', 'modfarm'),
              value: attributes['samplebtn-text'] || __('Play Sample', 'modfarm'),
              onChange: (v) => setAttributes({ 'samplebtn-text': v })
            })
          ),

          // Misc
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
    }
  });
})(window.wp);
