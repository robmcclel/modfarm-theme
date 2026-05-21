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
      const clearSocialColor = () => setAttributes({ socialMonotoneColor: '' });

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
            }),
            el(ToggleControl, {
              label: __('Show author social links', 'modfarm'),
              checked: !!attributes.showSocialLinks,
              onChange: v => setAttributes({ showSocialLinks: !!v })
            })
          ),

          attributes.showSocialLinks && el(PanelBody, { title: __('Social Links', 'modfarm'), initialOpen: false },
            el(RangeControl, {
              label: __('Icon size (px)', 'modfarm'),
              value: parseInt(attributes.socialIconSize, 10) || 28,
              min: 16, max: 96, step: 2,
              onChange: v => setAttributes({ socialIconSize: parseInt(v, 10) || 28 })
            }),
            el(RangeControl, {
              label: __('Icon gap (px)', 'modfarm'),
              value: parseInt(attributes.socialGap, 10) || 10,
              min: 0, max: 48, step: 1,
              onChange: v => setAttributes({ socialGap: parseInt(v, 10) || 0 })
            }),
            el(SelectControl, {
              label: __('Color mode', 'modfarm'),
              value: attributes.socialColorMode || 'native',
              options: [
                { label: __('Native colors', 'modfarm'), value: 'native' },
                { label: __('Monotone', 'modfarm'), value: 'monotone' }
              ],
              onChange: v => setAttributes({ socialColorMode: v })
            }),
            (attributes.socialColorMode || 'native') === 'monotone' && el('div', { style: { marginTop: '14px' } },
              el('label', { style: { display: 'block', marginBottom: '6px' } }, __('Icon color', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.socialMonotoneColor || undefined,
                onChange: v => setAttributes({ socialMonotoneColor: v || '' })
              }),
              el(Button, { isSecondary: true, onClick: clearSocialColor, style: { marginTop: '6px' } },
                __('Use inherited color', 'modfarm'))
            ),
            el(ToggleControl, {
              label: __('Open links in new tab', 'modfarm'),
              checked: attributes.socialOpenInNewTab !== false,
              onChange: v => setAttributes({ socialOpenInNewTab: !!v })
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
