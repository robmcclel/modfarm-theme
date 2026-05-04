(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, TextControl, SelectControl, ToggleControl, RangeControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/theme-product-card', {
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
            { title: __('Product', 'modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Offer ID override', 'modfarm'),
              type: 'number',
              value: attributes.offerId || '',
              onChange: function (value) {
                setAttributes({ offerId: parseInt(value, 10) || 0 });
              }
            }),
            el(SelectControl, {
              label: __('Layout', 'modfarm'),
              value: attributes.layout || 'vertical',
              options: [
                { label: __('Vertical', 'modfarm'), value: 'vertical' },
                { label: __('Horizontal', 'modfarm'), value: 'horizontal' }
              ],
              onChange: function (value) {
                setAttributes({ layout: value || 'vertical' });
              }
            }),
            el(SelectControl, {
              label: __('Image aspect', 'modfarm'),
              value: attributes.imageAspect || '3 / 4',
              options: [
                { label: __('Portrait', 'modfarm'), value: '3 / 4' },
                { label: __('Square', 'modfarm'), value: '1 / 1' },
                { label: __('Landscape', 'modfarm'), value: '16 / 9' }
              ],
              onChange: function (value) {
                setAttributes({ imageAspect: value || '3 / 4' });
              }
            })
          ),
          el(
            PanelBody,
            { title: __('Content', 'modfarm'), initialOpen: false },
            ['showImage', 'showTitle', 'showPrice', 'showDetails', 'showExcerpt', 'showButton'].map(function (key) {
              const labels = {
                showImage: __('Show image', 'modfarm'),
                showTitle: __('Show title', 'modfarm'),
                showPrice: __('Show price', 'modfarm'),
                showDetails: __('Show details', 'modfarm'),
                showExcerpt: __('Show excerpt', 'modfarm'),
                showButton: __('Show button', 'modfarm')
              };
              return el(ToggleControl, {
                key,
                label: labels[key],
                checked: attributes[key] !== false,
                onChange: function (value) {
                  setAttributes({ [key]: !!value });
                }
              });
            }),
            el(RangeControl, {
              label: __('Excerpt words', 'modfarm'),
              value: attributes.excerptWords || 24,
              min: 8,
              max: 60,
              onChange: function (value) {
                setAttributes({ excerptWords: value || 24 });
              }
            })
          ),
          el(
            PanelBody,
            { title: __('Button', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Button label', 'modfarm'),
              value: attributes.buttonLabel || '',
              onChange: function (value) {
                setAttributes({ buttonLabel: value });
              }
            }),
            el(SelectControl, {
              label: __('Button type', 'modfarm'),
              value: attributes.buttonType || 'primary',
              options: [
                { label: __('Primary', 'modfarm'), value: 'primary' },
                { label: __('Secondary', 'modfarm'), value: 'secondary' }
              ],
              onChange: function (value) {
                setAttributes({ buttonType: value || 'primary' });
              }
            })
          )
        ),
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/theme-product-card',
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
