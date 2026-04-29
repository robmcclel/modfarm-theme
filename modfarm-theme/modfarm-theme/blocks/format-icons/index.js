/* global window */
(function (wp) {
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls, ColorPalette } = wp.blockEditor || wp.editor;
  const { registerBlockType } = wp.blocks;
  const {
    PanelBody,
    SelectControl,
    ToggleControl,
    RangeControl,
    TextControl
  } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/format-icons', {
    apiVersion: 2,
    title: 'Available Format Icons',
    icon: 'screenoptions',
    category: 'widgets',
    attributes: {
      mode:          { type: 'string',  default: 'auto' },
      showEbook:     { type: 'boolean', default: true },
      showAudio:     { type: 'boolean', default: true },
      showPaperback: { type: 'boolean', default: true },
      showHardcover: { type: 'boolean', default: true },
      size:          { type: 'number',  default: 20 },
      color:         { type: 'string',  default: '' },
      gap:           { type: 'number',  default: 8 },
      align:         { type: 'string',  default: 'left' },
      label:         { type: 'string',  default: '' }
    },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      return el(
        Fragment, {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/format-icons',
            attributes
          })
        ),
        el(InspectorControls, {},
          el(PanelBody, { title: __('Mode', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Behavior', 'modfarm'),
              value: attributes.mode,
              options: [
                { label: __('Auto (detect on books)', 'modfarm'), value: 'auto' },
                { label: __('Custom (select below)', 'modfarm'), value: 'custom' }
              ],
              onChange: (v) => setAttributes({ mode: v })
            })
          ),
          el(PanelBody, { title: __('Custom Selection', 'modfarm'), initialOpen: attributes.mode === 'custom' },
            el(ToggleControl, {
              label: __('eBook (tablet icon)', 'modfarm'),
              checked: !!attributes.showEbook,
              onChange: (v) => setAttributes({ showEbook: !!v }),
              disabled: attributes.mode !== 'custom'
            }),
            el(ToggleControl, {
              label: __('Audiobook (headphones icon)', 'modfarm'),
              checked: !!attributes.showAudio,
              onChange: (v) => setAttributes({ showAudio: !!v }),
              disabled: attributes.mode !== 'custom'
            }),
            el(ToggleControl, {
              label: __('Paperback (book icon)', 'modfarm'),
              checked: !!attributes.showPaperback,
              onChange: (v) => setAttributes({ showPaperback: !!v }),
              disabled: attributes.mode !== 'custom'
            }),
            el(ToggleControl, {
              label: __('Hardcover (book + spine icon)', 'modfarm'),
              checked: !!attributes.showHardcover,
              onChange: (v) => setAttributes({ showHardcover: !!v }),
              disabled: attributes.mode !== 'custom'
            })
          ),
          el(PanelBody, { title: __('Display', 'modfarm'), initialOpen: false },
            el(RangeControl, {
              label: __('Icon Size (px)', 'modfarm'),
              value: attributes.size,
              onChange: (v) => setAttributes({ size: parseInt(v || 0, 10) || 16 }),
              min: 12, max: 64
            }),
            el(RangeControl, {
              label: __('Gap (px)', 'modfarm'),
              value: attributes.gap,
              onChange: (v) => setAttributes({ gap: parseInt(v || 0, 10) || 0 }),
              min: 0, max: 24
            }),
            el(SelectControl, {
              label: __('Alignment', 'modfarm'),
              value: attributes.align,
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: (v) => setAttributes({ align: v })
            }),
            el('div', { className: 'mfi-color-control' },
              el('p', { style: { marginTop: 0, marginBottom: '8px', fontWeight: 600 } }, __('Color', 'modfarm')),
              el(ColorPalette, {
                value: attributes.color || undefined,
                onChange: (newColor) => setAttributes({ color: newColor || '' }),
                clearable: true
              })
            ),
            el(TextControl, {
              label: __('Optional Label (e.g., “Available:”)', 'modfarm'),
              value: attributes.label || '',
              onChange: (v) => setAttributes({ label: v })
            })
          )
        )
      );
    }
  });
})(window.wp);