/* global window */
(function (wp) {
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor;
  const { registerBlockType } = wp.blocks;
  const {
    PanelBody,
    SelectControl,
    TextControl,
    ToggleControl,
    RangeControl
  } = wp.components;

  const { Fragment, createElement: el } = wp.element;
  const { addFilter } = wp.hooks;
  const ServerSideRender = wp.serverSideRender;

  const MAX_BUTTONS = 6;

  const RETAILER_OPTIONS = [
    { value: '', label: 'Select a retailer...' },
    { value: 'kindle_url', label: 'Kindle' },
    { value: 'amazon_paper', label: 'Amazon (Paperback)' },
    { value: 'amazon_hard', label: 'Amazon (Hardcover)' },
    { value: 'amazon_audio', label: 'Amazon (Audio)' },
    { value: 'audible_url', label: 'Audible' },
    { value: 'nook', label: 'B&N Nook' },
    { value: 'barnes_paper', label: 'B&N (Paperback)' },
    { value: 'barnes_hard', label: 'B&N (Hardcover)' },
    { value: 'barnes_audio', label: 'B&N (Audio)' },
    { value: 'ibooks', label: 'Apple Books' },
    { value: 'itunes', label: 'iTunes' },
    { value: 'kobo', label: 'Kobo' },
    { value: 'kobo_audio', label: 'Kobo Audio' },
    { value: 'googleplay', label: 'Google Play' },
    { value: 'googleplay_audio', label: 'Google Play Audio' },
    { value: 'bookshop_ebook', label: 'Bookshop (eBook)' },
    { value: 'bookshop_paper', label: 'Bookshop (Paperback)' },
    { value: 'bookshop_hard', label: 'Bookshop (Hardcover)' },
    { value: 'bam_paper', label: 'Books-A-Million (Paperback)' },
    { value: 'bam_hard', label: 'Books-A-Million (Hardcover)' },
    { value: 'indigo', label: 'Indigo' },
    { value: 'waterstones', label: 'Waterstones' },
    { value: 'brokenbinding', label: 'Broken Binding' },
    { value: 'librofm', label: 'Libro.fm' },
    { value: 'downpour', label: 'Downpour' },
    { value: 'target', label: 'Target' },
    { value: 'walmart', label: 'Walmart' },
    { value: 'audiobooks_com', label: 'audiobooks.com' },
    { value: 'spotify', label: 'Spotify' },
  ];

  // Optional inline editor CSS (like simple-gallery)
  const editorCSS = `
  .mfsales-admin{
    display:flex;
    flex-direction:column;
    gap:10px;
    padding:12px;
    margin:12px 0;
    border:1px dashed #c7c7c7;
    border-radius:8px;
    background:#fff;
  }
  `;
  (function injectOnce(){
    if (document.getElementById('mfsales-admin-css')) return;
    const style = document.createElement('style');
    style.id = 'mfsales-admin-css';
    style.innerHTML = editorCSS;
    document.head.appendChild(style);
  })();

  // ▶ Add linksAlign attribute non-destructively
  addFilter(
    'blocks.registerBlockType',
    'modfarm/sales-links-align-attr',
    (settings, name) => {
      if (name !== 'modfarm/book-page-sales-links') return settings;
      settings.attributes = Object.assign({}, settings.attributes, {
        linksAlign: { type: 'string', default: 'center' } // left | center | right
      });
      return settings;
    }
  );

  registerBlockType('modfarm/book-page-sales-links', {
    edit: (props) => {
      const blockProps = useBlockProps();
      const { attributes, setAttributes, isSelected } = props;

      const renderRetailerSelects = () => {
        // Manual UI should be "closed" unless block is selected (simple-gallery behavior)
        if (attributes.autoDetect || !isSelected) return null;

        return el(
          'div',
          { className: 'mfsales-admin' },
          Array.from({ length: MAX_BUTTONS }, (_, i) => {
            const key = `retailer${i + 1}`;
            return el(SelectControl, {
              key,
              label: `Retailer ${i + 1}`,
              value: attributes[key] || '',
              options: RETAILER_OPTIONS,
              onChange: (val) => setAttributes({ [key]: val })
            });
          })
        );
      };

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: "Settings", initialOpen: true },
            el(ToggleControl, {
              label: "Auto-detect retailer links",
              checked: !!attributes.autoDetect,
              onChange: (val) => setAttributes({ autoDetect: !!val })
            }),
            el(ToggleControl, {
              label: "Show Retailer Labels",
              checked: !!attributes.showLabels,
              onChange: (val) => setAttributes({ showLabels: !!val })
            }),
            el(TextControl, {
              label: "Intro Text",
              value: attributes.introText || '',
              onChange: (val) => setAttributes({ introText: val })
            }),
            el(SelectControl, {
              label: "Sales Links Alignment",
              value: attributes.linksAlign || 'center',
              options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
              ],
              onChange: (val) => setAttributes({ linksAlign: val || 'center' })
            }),
            el(RangeControl, {
              label: "Font Size (px)",
              value: Number.isFinite(parseInt(attributes.fontSize, 10)) ? attributes.fontSize : 16,
              onChange: (val) => setAttributes({ fontSize: parseInt(val, 10) || 16 }),
              min: 10,
              max: 40
            }),
            el(RangeControl, {
              label: "Font Weight",
              value: Number.isFinite(parseInt(attributes.fontWeight, 10)) ? attributes.fontWeight : 600,
              onChange: (val) => setAttributes({ fontWeight: parseInt(val, 10) || 600 }),
              min: 100,
              max: 900,
              step: 100
            }),
            el(wp.blockEditor.PanelColorSettings, {
              title: "Text Color",
              colorSettings: [{
                label: "Text Color",
                value: attributes.textColor || '',
                onChange: (val) => setAttributes({ textColor: val || '' })
              }]
            }),
            el(TextControl, {
              label: "Custom Icon Path",
              value: attributes.buttonPath || '',
              onChange: (val) => setAttributes({ buttonPath: val })
            }),
            el(RangeControl, {
              label: "Button Size (px)",
              value: Number.isFinite(parseInt(attributes.buttonSize, 10)) ? attributes.buttonSize : 60,
              min: 40,
              max: 100,
              onChange: (val) => setAttributes({ buttonSize: parseInt(val, 10) || 60 })
            }),
            el(RangeControl, {
              label: "Icon Border Radius (px)",
              value: Number.isFinite(parseInt(attributes.borderRadius, 10)) ? attributes.borderRadius : 0,
              onChange: (val) => setAttributes({ borderRadius: parseInt(val, 10) || 0 }),
              min: 0,
              max: 50
            })
          )
        ),
        el(
          'div',
          blockProps,
          renderRetailerSelects(),
          el(ServerSideRender, {
            block: "modfarm/book-page-sales-links",
            attributes: attributes
          })
        )
      );
    }
  });
})(window.wp);