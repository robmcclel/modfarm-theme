(function () {
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor;
  const { registerBlockType } = wp.blocks;
  const {
    PanelBody,
    ToggleControl,
    TextControl,
    SelectControl,
    ColorPalette,
    BaseControl,
    Button
  } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/book-author-credit', {
    apiVersion: 3,
    title: 'Book Author Credit',
    icon: 'id',
    category: 'modfarm-book-page',
    description: 'Displays all authors for the current book, with alignment, font size, and color options.',
    attributes: {
      alignment: { type: 'string', default: 'left' },
      fontSize: { type: 'integer' },
      textColor: { type: 'string', default: '' }, // empty = inherit
      showAvatar: { type: 'boolean', default: false }
    },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const clearTextColor = () => setAttributes({ textColor: '' });

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: 'Display Options', initialOpen: true },

            el(SelectControl, {
              label: 'Alignment',
              value: attributes.alignment,
              options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
              ],
              onChange: (value) => setAttributes({ alignment: value })
            }),

            el(TextControl, {
              label: 'Font Size (px)',
              type: 'number',
              value: (typeof attributes.fontSize === 'number') ? attributes.fontSize : '',
              onChange: (value) => {
                const parsed = parseInt(value, 10);
                setAttributes({ fontSize: isNaN(parsed) ? undefined : parsed });
              }
            }),

            el('p', { style: { fontSize: '13px', marginBottom: '6px', color: '#555' } },
              'Optional: Override the default text color. Leave empty to inherit.'
            ),

            el(BaseControl, { label: 'Text Color (override)' },
              el(ColorPalette, {
                value: attributes.textColor || '',
                onChange: (val) => setAttributes({ textColor: val || '' })
              })
            ),

            el(Button, {
              variant: 'secondary',
              onClick: clearTextColor,
              style: { marginTop: '6px' }
            }, __('Clear Color (Inherit)', 'modfarm')),

            el(ToggleControl, {
              label: 'Show author avatars',
              checked: !!attributes.showAvatar,
              onChange: (value) => setAttributes({ showAvatar: value })
            })
          )
        ),

        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'modfarm/book-author-credit',
            attributes: attributes
          })
        )
      );
    },

    save: () => null
  });
})();