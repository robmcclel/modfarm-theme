(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, TextControl, SelectControl, ToggleControl, RangeControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;

  function parseIds(value) {
    return String(value || '')
      .split(',')
      .map(function (id) { return parseInt(id.trim(), 10); })
      .filter(function (id, index, list) { return id > 0 && list.indexOf(id) === index; });
  }

  registerBlockType('modfarm/related-products', {
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Products', 'modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Current Offer ID override', 'modfarm'),
              type: 'number',
              value: attributes.offerId || '',
              onChange: function (value) {
                setAttributes({ offerId: parseInt(value, 10) || 0 });
              }
            }),
            el(TextControl, {
              label: __('Manual related Offer IDs', 'modfarm'),
              help: __('Comma-separated. When set, manual products are used before taxonomy matching.', 'modfarm'),
              value: (attributes.manualIds || []).join(', '),
              onChange: function (value) {
                setAttributes({ manualIds: parseIds(value) });
              }
            }),
            el(TextControl, {
              label: __('Match taxonomy', 'modfarm'),
              help: __('Optional taxonomy slug, such as product_cat, to match against the current Offer.', 'modfarm'),
              value: attributes.taxonomy || '',
              onChange: function (value) {
                setAttributes({ taxonomy: value || '' });
              }
            }),
            el(RangeControl, {
              label: __('Products to show', 'modfarm'),
              value: attributes.productsPerPage || 3,
              min: 1,
              max: 12,
              onChange: function (value) {
                setAttributes({ productsPerPage: value || 3 });
              }
            }),
            el(RangeControl, {
              label: __('Columns', 'modfarm'),
              value: attributes.columns || 3,
              min: 1,
              max: 6,
              onChange: function (value) {
                setAttributes({ columns: value || 3 });
              }
            })
          ),
          el(
            PanelBody,
            { title: __('Display', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Show heading', 'modfarm'),
              checked: attributes.showHeading !== false,
              onChange: function (value) {
                setAttributes({ showHeading: !!value });
              }
            }),
            el(TextControl, {
              label: __('Heading', 'modfarm'),
              value: attributes.heading || '',
              onChange: function (value) {
                setAttributes({ heading: value });
              }
            }),
            el(SelectControl, {
              label: __('Card layout', 'modfarm'),
              value: attributes.cardLayout || 'vertical',
              options: [
                { label: __('Vertical', 'modfarm'), value: 'vertical' },
                { label: __('Horizontal', 'modfarm'), value: 'horizontal' }
              ],
              onChange: function (value) {
                setAttributes({ cardLayout: value || 'vertical' });
              }
            }),
            el(ToggleControl, {
              label: __('Show details', 'modfarm'),
              checked: attributes.showDetails !== false,
              onChange: function (value) {
                setAttributes({ showDetails: !!value });
              }
            }),
            el(ToggleControl, {
              label: __('Show excerpt', 'modfarm'),
              checked: attributes.showExcerpt !== false,
              onChange: function (value) {
                setAttributes({ showExcerpt: !!value });
              }
            }),
            el(TextControl, {
              label: __('Button label', 'modfarm'),
              value: attributes.buttonLabel || '',
              onChange: function (value) {
                setAttributes({ buttonLabel: value });
              }
            })
          )
        ),
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/related-products',
            attributes: attributes
          })
        )
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp);
