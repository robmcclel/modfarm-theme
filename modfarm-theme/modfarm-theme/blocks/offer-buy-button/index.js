(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, TextControl, SelectControl, ToggleControl, ColorPalette, BaseControl, Button } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;

  const typeOptions = [
    { label: __('Inherit (Primary)', 'modfarm'), value: 'inherit' },
    { label: __('Primary (Filled)', 'modfarm'), value: 'primary' },
    { label: __('Secondary (Outline)', 'modfarm'), value: 'secondary' }
  ];

  const displayOptions = [
    { label: __('Single button', 'modfarm'), value: 'single' },
    { label: __('Sibling button cluster', 'modfarm'), value: 'sibling_cluster' }
  ];

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
            el(SelectControl, {
              label: __('Display', 'modfarm'),
              value: attributes.displayMode || 'single',
              options: displayOptions,
              onChange: function (value) {
                setAttributes({ displayMode: value || 'single' });
              }
            }),
            el(TextControl, {
              label: __('Button label', 'modfarm'),
              value: attributes.label || '',
              onChange: function (value) {
                setAttributes({ label: value });
              }
            }),
            attributes.displayMode === 'sibling_cluster' &&
              el(TextControl, {
                label: __('Cluster heading', 'modfarm'),
                value: attributes.clusterHeading || '',
                onChange: function (value) {
                  setAttributes({ clusterHeading: value });
                }
              }),
            el(SelectControl, {
              label: __('Alignment', 'modfarm'),
              value: attributes.alignment || 'left',
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: function (value) {
                setAttributes({ alignment: value });
              }
            }),
            el(SelectControl, {
              label: __('Button type', 'modfarm'),
              value: attributes.type || 'inherit',
              options: typeOptions,
              onChange: function (value) {
                setAttributes({ type: value });
              }
            }),
            el(SelectControl, {
              label: __('Border radius', 'modfarm'),
              value: attributes.radiusMode || 'inherit',
              options: [
                { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
                { label: __('Custom (This Block)', 'modfarm'), value: 'custom' }
              ],
              onChange: function (value) {
                setAttributes({ radiusMode: value });
              }
            }),
            attributes.radiusMode === 'custom' &&
              el(TextControl, {
                label: __('Custom radius (px)', 'modfarm'),
                type: 'number',
                value: attributes.border_radius || 0,
                onChange: function (value) {
                  setAttributes({ border_radius: parseInt(value, 10) || 0 });
                }
              }),
            el(ToggleControl, {
              label: __('Advanced: color overrides', 'modfarm'),
              checked: !!attributes.showAdvanced,
              onChange: function (value) {
                setAttributes({ showAdvanced: !!value });
              }
            }),
            attributes.showAdvanced &&
              el(
                Fragment,
                null,
                el(BaseControl, { label: __('Override background', 'modfarm') },
                  el(ColorPalette, {
                    value: attributes.bg_color || '',
                    onChange: function (value) {
                      setAttributes({ bg_color: value || '' });
                    },
                    disableCustomColors: false,
                    clearable: true
                  })
                ),
                el(BaseControl, { label: __('Override text', 'modfarm') },
                  el(ColorPalette, {
                    value: attributes.text_color || '',
                    onChange: function (value) {
                      setAttributes({ text_color: value || '' });
                    },
                    disableCustomColors: false,
                    clearable: true
                  })
                ),
                el(BaseControl, { label: __('Override border', 'modfarm') },
                  el(ColorPalette, {
                    value: attributes.border_color || '',
                    onChange: function (value) {
                      setAttributes({ border_color: value || '' });
                    },
                    disableCustomColors: false,
                    clearable: true
                  })
                ),
                el(Button, {
                  variant: 'secondary',
                  onClick: function () {
                    setAttributes({ bg_color: '', text_color: '', border_color: '' });
                  }
                }, __('Clear overrides', 'modfarm'))
              ),
            el('hr', null),
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
