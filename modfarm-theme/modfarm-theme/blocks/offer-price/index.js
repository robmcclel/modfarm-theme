(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, SelectControl, TextControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/offer-price', {
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
            { title: __('Display', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Alignment', 'modfarm'),
              value: attributes.alignment || 'left',
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: function (value) {
                setAttributes({ alignment: value || 'left' });
              }
            }),
            el(SelectControl, {
              label: __('Size', 'modfarm'),
              value: attributes.size || 'inherit',
              options: [
                { label: __('Inherit', 'modfarm'), value: 'inherit' },
                { label: __('Small', 'modfarm'), value: 'small' },
                { label: __('Medium', 'modfarm'), value: 'medium' },
                { label: __('Large', 'modfarm'), value: 'large' },
                { label: __('Extra Large', 'modfarm'), value: 'xlarge' },
                { label: __('Custom', 'modfarm'), value: 'custom' }
              ],
              onChange: function (value) {
                setAttributes({ size: value || 'inherit' });
              }
            }),
            (attributes.size === 'custom') && el(TextControl, {
              label: __('Custom size (px)', 'modfarm'),
              type: 'number',
              value: attributes.customSize || '',
              onChange: function (value) {
                setAttributes({ customSize: parseInt(value, 10) || 0 });
              }
            }),
            el(SelectControl, {
              label: __('Weight', 'modfarm'),
              value: attributes.weight || '700',
              options: [
                { label: __('Inherit', 'modfarm'), value: 'inherit' },
                { label: __('Regular', 'modfarm'), value: '400' },
                { label: __('Semibold', 'modfarm'), value: '600' },
                { label: __('Bold', 'modfarm'), value: '700' },
                { label: __('Extra Bold', 'modfarm'), value: '800' }
              ],
              onChange: function (value) {
                setAttributes({ weight: value || '700' });
              }
            })
          ),
          el(
            PanelBody,
            { title: __('Offer', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Offer ID override', 'modfarm'),
              type: 'number',
              value: attributes.offerId || '',
              onChange: function (value) {
                setAttributes({ offerId: parseInt(value, 10) || 0 });
              }
            })
          )
        ),
        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'modfarm/offer-price',
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
