(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const {
    PanelBody, SelectControl, TextControl, RangeControl,
    ToggleControl, ColorPalette, Button
  } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  // Theme color palette
  const THEME_COLORS = wp.data.select('core/block-editor')?.getSettings()?.colors || [];

  registerBlockType('modfarm/tax-description', {
    apiVersion: 2,
    title: 'Taxonomy Description',
    icon: 'align-left',
    category: 'modfarm-theme',

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const clearAccent = () => setAttributes({ accentColor: '' });
      const clearText   = () => setAttributes({ textColor: '' });

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/tax-description',
            attributes
          })
        ),
        el(InspectorControls, {},

          /* === Layout & Display Panel === */
          el(PanelBody, { title: __('Layout & Display', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Layout', 'modfarm'),
              value: attributes.layout,
              options: [
                { label: __('Auto', 'modfarm'), value: 'auto' },
                { label: __('Vertical', 'modfarm'), value: 'vertical' },
                { label: __('Horizontal', 'modfarm'), value: 'horizontal' }
              ],
              onChange: v => setAttributes({ layout: v })
            }),
            el(SelectControl, {
              label: __('Image Shape', 'modfarm'),
              value: attributes.imageShape,
              options: [
                { label: __('Circle', 'modfarm'), value: 'circle' },
                { label: __('Square', 'modfarm'), value: 'square' },
                { label: __('Rounded', 'modfarm'), value: 'rounded' }
              ],
              onChange: v => setAttributes({ imageShape: v })
            }),
            el(RangeControl, {
              label: __('Image size (px)', 'modfarm'),
              value: attributes.imgSize,
              min: 120, max: 360, step: 10,
              onChange: v => setAttributes({ imgSize: v })
            }),
            el(ToggleControl, {
              label: __('Show series genre profile', 'modfarm'),
              checked: attributes.showSeriesGenreProfile !== false,
              onChange: v => setAttributes({ showSeriesGenreProfile: !!v })
            })
          ),

          /* === Colors Panel === */
          el(PanelBody, { title: __('Colors', 'modfarm'), initialOpen: false },
            el('div', { style: { marginTop: '14px' } },
              el('label', { style: { display: 'block', marginBottom: '6px' } }, __('Text color', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.textColor || undefined,
                onChange: v => setAttributes({ textColor: v || '' })
              }),
              el(Button, { isSecondary: true, onClick: clearText, style: { marginTop: '6px' } },
                __('Use theme default', 'modfarm'))
            )
          )
        )
      );
    },

    save: function () { return null; }
  });
})(window.wp);
