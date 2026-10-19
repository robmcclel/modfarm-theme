(function (window) {
  window.ModFarmBookOptions = {
    BUTTON_OPTIONS: [
      { label: '- None -', value: '__none__' },
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
      { label: 'Walmart', value: 'walmart' },
      { label: 'Audiobooks.com', value: 'audiobooks_com' },
      { label: 'Spotify', value: 'spotify' },

      { label: 'Buy Direct (General)', value: 'buydirect' },
      { label: 'Buy Direct - eBook', value: 'ebook_buy_url' },
      { label: 'Buy Direct - Audiobook', value: 'audio_buy_url' },
      { label: 'Buy Direct - Signed Copy', value: 'signed_buy_url' },
      { label: 'Buy Direct - Paperback', value: 'paper_buy_url' },
      { label: 'Buy Direct - Hardcover', value: 'hard_buy_url' },
      { label: 'Custom Link 1', value: 'custom1' },
      { label: 'Custom Link 2', value: 'custom2' },
      { label: 'Custom Link 3', value: 'custom3' },
      { label: 'Custom Link 4', value: 'custom4' },
      { label: 'Custom Link 5', value: 'custom5' },
      { label: 'Custom Link 6', value: 'custom6' }
    ],

    COVER_OPTIONS: [
      { label: 'eBook Cover (BMS)', value: 'cover_ebook' },
      { label: 'Flat Image (BMS)', value: 'cover_image_flat' },
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
    ]
  };
  window.ModFarmBookOptions.FormatControl = function ({ attributes, setAttributes }) {
    const el = window.wp.element.createElement;
    const terms = window.wp.data.useSelect((select) => select('core').getEntityRecords(
      'taxonomy', 'book-format', { per_page: -1, hide_empty: false }
    ) || [], []);
    return el(window.wp.components.SelectControl, {
      label: window.wp.i18n.__('Book Format', 'modfarm'),
      value: terms.some((term) => term.id === Number(attributes['book-format']?.id))
        ? Number(attributes['book-format'].id) : 0,
      options: [{ label: window.wp.i18n.__('All Formats', 'modfarm'), value: 0 }].concat(
        terms.map((term) => ({ label: term.name, value: term.id }))
      ),
      onChange: (value) => setAttributes({ 'book-format': { id: parseInt(value, 10) || 0 } })
    });
  };

  // Used by the live, unbundled editors. Keep attribute names and labels identical.
  window.ModFarmBookOptions.VisibilityControls = function ({ attributes, setAttributes }) {
    const el = window.wp.element.createElement;
    const { ToggleControl } = window.wp.components;
    const __ = window.wp.i18n.__;
    if (attributes.cardUseGlobal !== false) {
      return el('p', { className: 'components-base-control__help' },
        __('Turn off "Use Global Card Styles" to customize visibility for this block.', 'modfarm'));
    }
    return el(window.wp.element.Fragment, {}, ...[
      ['cardShowTitle', 'Show Title'], ['cardShowSeries', 'Show Series'],
      ['cardShowPrimaryButton', 'Show Primary Button'], ['cardShowSampleButton', 'Show Sample Button']
    ].map(([key, label]) => el(ToggleControl, {
      key, label: __(label, 'modfarm'), checked: attributes[key] !== false,
      onChange: (value) => setAttributes({ [key]: !!value })
    })));
  };
})(window);
