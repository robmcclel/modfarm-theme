(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, TextControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/offer-buy-button', {
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
            { title: __('Button', 'modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Button label', 'modfarm'),
              value: attributes.label || '',
              onChange: function (value) {
                setAttributes({ label: value });
              }
            }),
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
            block: 'modfarm/offer-buy-button',
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
