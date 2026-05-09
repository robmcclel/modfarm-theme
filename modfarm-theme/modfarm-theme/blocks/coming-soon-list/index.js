/* global window */
(function (wp) {
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const { registerBlockType } = wp.blocks;
  const {
    PanelBody,
    SelectControl,
    TextControl,
    ColorPicker,
    RangeControl,
    ComboboxControl,
    ToggleControl
  } = wp.components;

  const { Fragment, createElement: el } = wp.element;
  const { useSelect } = wp.data;
  const ServerSideRender = wp.serverSideRender;

  const EFFECT_OPTIONS = [
    { label: __('Flat', 'modfarm'), value: 'flat' },
    { label: __('Shadow – Small', 'modfarm'), value: 'shadow-sm' },
    { label: __('Shadow – Medium', 'modfarm'), value: 'shadow-md' },
    { label: __('Shadow – Large', 'modfarm'), value: 'shadow-lg' },
    { label: __('Emboss', 'modfarm'), value: 'emboss' }
  ];

  const SHAPE_OPTIONS_MAIN = [
    { label: __('Square', 'modfarm'), value: 'square' },
    { label: __('Rounded', 'modfarm'), value: 'rounded' },
    { label: __('Pill (gap only)', 'modfarm'), value: 'pill' },
    { label: __('Partial (bottom only)', 'modfarm'), value: 'partial' }
  ];

  const SHAPE_OPTIONS_SAMPLE = [
    { label: __('Square', 'modfarm'), value: 'square' },
    { label: __('Rounded', 'modfarm'), value: 'rounded' },
    { label: __('Pill', 'modfarm'), value: 'pill' }
  ];

  const CTA_OPTIONS = [
    { label: __('Joined (no gap)', 'modfarm'), value: 'joined' },
    { label: __('Gap', 'modfarm'), value: 'gap' }
  ];

  registerBlockType('modfarm/coming-soon-list', {
    apiVersion: 2,
    title: __('Coming Soon List', 'modfarm'),
    category: 'widgets',
    icon: 'calendar-alt',

    attributes: {
      listType: { type: 'string', default: 'coming-soon' },          // coming-soon | latest-releases | timeframe
      latestWindowDays: { type: 'number', default: 30 },             // legacy; retained for saved blocks

      'tax-type': { type: 'string', default: '' },
      'series-select': { type: 'object', default: { id: 0 } },
      'genre-select': { type: 'object', default: { id: 0 } },
      'bookauthor-select': { type: 'object', default: { id: 0 } },
      'language-select': { type: 'object', default: { id: 0 } },
      'booktag-select': { type: 'object', default: { id: 0 } },
      'book-format': { type: 'object', default: { id: 0 } },

      'image-type': { type: 'string', default: 'featured' },

      'books-in-row': { type: 'string', default: '25%' },
      'display-order': { type: 'string', default: 'ASC' },
      'books-per-page': { type: 'number', default: 12 },
      'show-pagination': { type: 'boolean', default: false },

      'show-title': { type: 'string', default: 'block' },
      'show-series': { type: 'string', default: 'block' },
      'show-button': { type: 'string', default: 'block' },
      'show-audio': { type: 'string', default: 'none' },
      'audio-mode': { type: 'string', default: 'auto' },

      'button-text': { type: 'string', default: __('See The Book', 'modfarm') },
      availableButtonText: { type: 'string', default: __('Available Now', 'modfarm') },
      upcomingButtonText: { type: 'string', default: __('Coming Soon', 'modfarm') },
      'button-link': { type: 'string', default: 'bookpage' },
      'button-target': { type: 'string', default: '_self' },
      'buttonbg-color': { type: 'string', default: '' },
      'buttontx-color': { type: 'string', default: '' },
      'tracker-loc': { type: 'string', default: '' },
      'volume-text': { type: 'string', default: 'Book' },

      'samplebtn-text': { type: 'string', default: __('Play Sample', 'modfarm') },
      'samplebtn-bg': { type: 'string', default: '' },
      'samplebtn-fg': { type: 'string', default: '' },
      'sample-shape': { type: 'string', default: 'square' },
      'samplebtn-border': { type: 'string', default: '' },

      effect: { type: 'string', default: 'flat' },
      'cover-shape': { type: 'string', default: 'square' },
      'button-shape': { type: 'string', default: 'square' },
      'cta-join': { type: 'string', default: 'joined' },
      'card-style': { type: 'string', default: 'flat' },

      showAuthor: { type: 'boolean', default: true },
      showPubDate: { type: 'boolean', default: true },
      pubDateKey: { type: 'string', default: 'publication_date' },
      showShortDescription: { type: 'boolean', default: false },

      dateFilterMode: { type: 'string', default: 'auto' },
      filterYear: { type: 'number', default: 0 },
      filterMonth: { type: 'number', default: 0 },
      filterStart: { type: 'string', default: '' },
      filterEnd: { type: 'string', default: '' },

      includeLaunchWindow: { type: 'boolean', default: true },
      launchWindowDays: { type: 'number', default: 7 },
      postReleaseMode: { type: 'string', default: 'hide' },

      smartCta: { type: 'boolean', default: true },
      ctaUpcoming: { type: 'string', default: __('Coming Soon', 'modfarm') },
      ctaLaunch: { type: 'string', default: __('Out Now', 'modfarm') },
      ctaReleased: { type: 'string', default: '' }
    },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const taxType = attributes['tax-type'] || '';
      const listType = attributes.listType || 'coming-soon';
      const dateFilterMode = attributes.dateFilterMode || 'month';
      const isTimeframe = listType === 'timeframe';

      const taxonomyMap = {
        series: { attr: 'series-select', taxonomy: 'book-series' },
        genre: { attr: 'genre-select', taxonomy: 'book-genre' },
        author: { attr: 'bookauthor-select', taxonomy: 'book-author' },
        language: { attr: 'language-select', taxonomy: 'book-language' },
        booktag: { attr: 'booktag-select', taxonomy: 'book-tags' }
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

      const formatOptions = formatTerms.length
        ? [{ label: '— Select Format —', value: '' }].concat(formatTerms.map((t) => ({ label: t.name, value: t.id })))
        : [{ label: 'Loading...', value: '' }];

      const selectedFormatId = attributes['book-format']?.id ?? '';
      const validFormatValue = formatOptions.some((opt) => opt.value === selectedFormatId) ? selectedFormatId : '';

      function getTaxonomySelector() {
        if (!selectedTax) return el('p', {}, __('Select a taxonomy type to continue.', 'modfarm'));
        const { attr } = selectedTax;

        const options = taxonomyTerms.map((term) => ({ label: term.name, value: term.id }));

        const selectedTerm = attributes[attr]?.id ?? '';
        const selectedValue = Number.isFinite(parseInt(selectedTerm, 10)) ? parseInt(selectedTerm, 10) : '';
        const validValue = options.some((opt) => opt.value === selectedValue) ? selectedValue : '';

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

      const onChangeListType = (v) => {
        const next = v || 'coming-soon';
        const nextAttrs = { listType: next };
        nextAttrs['display-order'] = next === 'latest-releases' ? 'DESC' : 'ASC';
        if (next === 'timeframe' && (attributes.dateFilterMode || 'auto') === 'auto') {
          const now = new Date();
          nextAttrs.dateFilterMode = 'month';
          if (!attributes.filterYear) nextAttrs.filterYear = now.getFullYear();
          if (!attributes.filterMonth) nextAttrs.filterMonth = now.getMonth() + 1;
        }
        setAttributes(nextAttrs);
      };

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, { block: 'modfarm/coming-soon-list', attributes })
        ),

        el(InspectorControls, {},

          el(PanelBody, { title: __('Book Filters', 'modfarm'), initialOpen: true },

            el(SelectControl, {
              label: __('Release Selection', 'modfarm'),
              value: listType,
              options: [
                { label: __('Latest Releases', 'modfarm'), value: 'latest-releases' },
                { label: __('Coming Soon', 'modfarm'), value: 'coming-soon' },
                { label: __('Timeframe', 'modfarm'), value: 'timeframe' }
              ],
              onChange: onChangeListType
            }),

            el(SelectControl, {
              label: __('Select Display Taxonomy', 'modfarm'),
              value: taxType,
              options: [
                { label: 'Book Series', value: 'series' },
                { label: 'Book Genre', value: 'genre' },
                { label: 'Book Author', value: 'author' },
                { label: 'Book Language', value: 'language' },
                { label: 'Book Tag', value: 'booktag' }
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
                { label: 'Featured Image (Default)', value: 'featured' },
                { label: 'eBook Cover', value: 'cover_ebook' },
                { label: 'Audiobook Cover (Square)', value: 'cover_image_audio' },
                { label: 'Paperback Cover', value: 'cover_paperback' },
                { label: 'Hardcover Cover', value: 'cover_hardcover' },
                { label: 'Hero Image', value: 'hero_image' },
                { label: 'Flat Cover Image', value: 'cover_image_flat' },
                { label: '3D Mockup Cover', value: 'cover_image_3d' },
                { label: 'Composite Marketing Image', value: 'cover_image_composite' }
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
                { label: 'Buy Direct (General)', value: 'buydirect' }
              ],
              onChange: (val) => setAttributes({ 'button-link': val })
            })
          ),

          el(PanelBody, { title: __('Display Settings', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Books Per Row', 'modfarm'),
              value: attributes['books-in-row'],
              options: [
                { label: 'One', value: '100%' },
                { label: 'Two', value: '50%' },
                { label: 'Three', value: '33.33%' },
                { label: 'Four', value: '25%' }
              ],
              onChange: (val) => setAttributes({ 'books-in-row': val })
            }),
            el(SelectControl, {
              label: __('Display Order', 'modfarm'),
              value: attributes['display-order'],
              options: [
                { label: (listType === 'latest-releases') ? __('Most Recent First (Recommended)', 'modfarm') : __('Soonest First (Recommended)', 'modfarm'), value: (listType === 'latest-releases') ? 'DESC' : 'ASC' },
                { label: (listType === 'latest-releases') ? __('Oldest First', 'modfarm') : __('Latest First', 'modfarm'), value: (listType === 'latest-releases') ? 'ASC' : 'DESC' },
                { label: 'Random', value: 'rand' }
              ],
              onChange: (val) => setAttributes({ 'display-order': val })
            }),
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

          el(PanelBody, { title: __('Card Extras', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Show Author', 'modfarm'),
              checked: !!attributes.showAuthor,
              onChange: (v) => setAttributes({ showAuthor: !!v })
            }),
            el(ToggleControl, {
              label: __('Show Publication Date', 'modfarm'),
              checked: !!attributes.showPubDate,
              onChange: (v) => setAttributes({ showPubDate: !!v })
            }),
            el(ToggleControl, {
              label: __('Show Short Description', 'modfarm'),
              checked: !!attributes.showShortDescription,
              onChange: (v) => setAttributes({ showShortDescription: !!v })
            })
          ),

          el(PanelBody, { title: __('Publication Timing', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Date Source', 'modfarm'),
              value: attributes.pubDateKey,
              options: [
                { label: __('Publication Date (eBook)', 'modfarm'), value: 'publication_date' },
                { label: __('Audiobook Publication Date', 'modfarm'), value: 'audiobook_publication_date' }
              ],
              onChange: (v) => setAttributes({ pubDateKey: v })
            }),

            isTimeframe && el(SelectControl, {
              label: __('Timeframe Type', 'modfarm'),
              value: attributes.dateFilterMode === 'range' ? 'range' : 'month',
              options: [
                { label: __('Specific Month', 'modfarm'), value: 'month' },
                { label: __('Custom Range', 'modfarm'), value: 'range' }
              ],
              onChange: (v) => setAttributes({ dateFilterMode: v })
            }),

            !isTimeframe && el('p', { className: 'components-base-control__help' },
              listType === 'latest-releases'
                ? __('Shows books published today and earlier, newest first.', 'modfarm')
                : __('Shows books publishing tomorrow and later, soonest first.', 'modfarm')
            ),

            isTimeframe && (attributes.dateFilterMode || 'month') === 'month' && el(RangeControl, {
              label: __('Year', 'modfarm'),
              value: attributes.filterYear || 0,
              onChange: (v) => setAttributes({ filterYear: parseInt(v, 10) || 0 }),
              min: 2020, max: 2035
            }),

            isTimeframe && (attributes.dateFilterMode || 'month') === 'month' && el(SelectControl, {
              label: __('Month', 'modfarm'),
              value: attributes.filterMonth || 0,
              options: [
                { label: '—', value: 0 },
                { label: __('January', 'modfarm'), value: 1 },
                { label: __('February', 'modfarm'), value: 2 },
                { label: __('March', 'modfarm'), value: 3 },
                { label: __('April', 'modfarm'), value: 4 },
                { label: __('May', 'modfarm'), value: 5 },
                { label: __('June', 'modfarm'), value: 6 },
                { label: __('July', 'modfarm'), value: 7 },
                { label: __('August', 'modfarm'), value: 8 },
                { label: __('September', 'modfarm'), value: 9 },
                { label: __('October', 'modfarm'), value: 10 },
                { label: __('November', 'modfarm'), value: 11 },
                { label: __('December', 'modfarm'), value: 12 }
              ],
              onChange: (v) => setAttributes({ filterMonth: parseInt(v, 10) || 0 })
            }),

            isTimeframe && attributes.dateFilterMode === 'range' && el(TextControl, {
              label: __('Start (YYYY-MM-DD)', 'modfarm'),
              value: attributes.filterStart || '',
              onChange: (v) => setAttributes({ filterStart: v })
            }),

            isTimeframe && attributes.dateFilterMode === 'range' && el(TextControl, {
              label: __('End (YYYY-MM-DD)', 'modfarm'),
              value: attributes.filterEnd || '',
              onChange: (v) => setAttributes({ filterEnd: v })
            })
          ),

          el(PanelBody, { title: __('Design', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Effect', 'modfarm'),
              value: attributes.effect,
              options: EFFECT_OPTIONS,
              onChange: (v) => setCanon('effect', v, EFFECT_OPTIONS.map(o => o.value), 'flat')
            }),
            el(SelectControl, {
              label: __('Cover Shape', 'modfarm'),
              value: attributes['cover-shape'],
              options: [
                { label: __('Square', 'modfarm'), value: 'square' },
                { label: __('Rounded', 'modfarm'), value: 'rounded' }
              ],
              onChange: (v) => setCanon('cover-shape', v, ['square', 'rounded'], 'square')
            }),
            el(SelectControl, {
              label: __('Button Shape', 'modfarm'),
              value: attributes['button-shape'],
              options: SHAPE_OPTIONS_MAIN,
              onChange: (v) => setCanon('button-shape', v, ['square','rounded','pill','partial'], 'square')
            }),
            el(SelectControl, {
              label: __('Sample Button Shape', 'modfarm'),
              value: attributes['sample-shape'],
              options: SHAPE_OPTIONS_SAMPLE,
              onChange: (v) => setCanon('sample-shape', v, ['square','rounded','pill'], 'square')
            }),
            el(SelectControl, {
              label: __('CTA Spacing', 'modfarm'),
              value: attributes['cta-join'],
              options: CTA_OPTIONS,
              onChange: (v) => setCanon('cta-join', v, ['joined','gap'], 'joined')
            })
          ),

          el(PanelBody, { title: __('Button Style', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Available Now Button Text', 'modfarm'),
              value: attributes.availableButtonText || '',
              onChange: (val) => setAttributes({ availableButtonText: val })
            }),
            el(TextControl, {
              label: __('Upcoming Button Text', 'modfarm'),
              value: attributes.upcomingButtonText || '',
              onChange: (val) => setAttributes({ upcomingButtonText: val })
            }),
            el(SelectControl, {
              label: __('Button Target', 'modfarm'),
              value: attributes['button-target'],
              options: [
                { label: __('Same Tab', 'modfarm'), value: '_self' },
                { label: __('New Tab', 'modfarm'), value: '_blank' }
              ],
              onChange: (val) => setAttributes({ 'button-target': val })
            }),
            el(ColorPicker, {
              color: attributes['buttonbg-color'] || '',
              onChangeComplete: (val) => setAttributes({ 'buttonbg-color': val?.hex || '' }),
              label: __('Button Background Color', 'modfarm')
            }),
            el(ColorPicker, {
              color: attributes['buttontx-color'] || '',
              onChangeComplete: (val) => setAttributes({ 'buttontx-color': val?.hex || '' }),
              label: __('Button Text Color', 'modfarm')
            })
          )
        )
      );
    }
  });
})(window.wp);
