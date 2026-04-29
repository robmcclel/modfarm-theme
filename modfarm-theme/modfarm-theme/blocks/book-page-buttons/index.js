(function () {
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor;
  const { registerBlockType } = wp.blocks;
  const {
    PanelBody,
    SelectControl,
    TextControl,
    ToggleControl,
    Button,
    ColorPalette,
    BaseControl
  } = wp.components;

  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  const MAX_BUTTONS = 6;

  const metaKeyOptions = [
    'text_sample_url', 'audio_sample_url', 'reviews_url', 'serieslink', 'review',
    'ebook_buy_url', 'audio_buy_url', 'signed_buy_url', 'buydirect',
    'kindle_url', 'amazon_paper', 'amazon_hard', 'amazon_audio',
    'audible_url', 'nook', 'barnes_paper', 'barnes_hard', 'barnes_audio',
    'ibooks', 'itunes', 'kobo', 'kobo_audio',
    'googleplay', 'googleplay_audio',
    'bookshop_ebook', 'bookshop_paper', 'bookshop_hard',
    'bam_paper', 'bam_hard',
    'indigo', 'waterstones', 'brokenbinding',
    'librofm', 'downpour', 'target', 'walmart',
    'audiobooks_com', 'spotify'
  ];

  const TYPE_OPTIONS = [
    { label: __('Inherit (Auto)', 'modfarm'), value: 'inherit' },
    { label: __('Primary (Filled)', 'modfarm'), value: 'primary' },
    { label: __('Secondary (Outline)', 'modfarm'), value: 'secondary' }
  ];

  registerBlockType('modfarm/book-page-buttons', {
    title: __('Book Page Buttons', 'modfarm'),
    icon: 'button',
    category: 'modfarm-book-page',
    description: __('Display customizable purchase or sample buttons for a book.', 'modfarm'),

    edit: ({ attributes, setAttributes }) => {
      const {
        buttons = [],
        alignment = 'center',
        radiusMode = 'inherit',
        border_radius = 0,
        showAdvanced = false
      } = attributes;

      const setButtons = (next) => setAttributes({ buttons: next });

      const updateButton = (index, key, value) => {
        const updated = [...buttons];
        updated[index] = { ...updated[index], [key]: value };
        setButtons(updated);
      };

      const addButton = () => {
        if (buttons.length >= MAX_BUTTONS) return;

        setButtons([
          ...buttons,
          {
            meta_key: '',
            label: '',
            type: 'inherit',     // NEW
            new_tab: true,

            // Advanced overrides (optional)
            bg_color: '',
            text_color: '',
            border_color: ''
          }
        ]);
      };

      const removeButton = (index) => {
        const updated = [...buttons];
        updated.splice(index, 1);
        setButtons(updated);
      };

      const clearOverrides = (index) => {
        updateButton(index, 'bg_color', '');
        updateButton(index, 'text_color', '');
        updateButton(index, 'border_color', '');
      };

      return el(
        Fragment,
        null,

        el(
          InspectorControls,
          null,

          // Layout / global controls
          el(
            PanelBody,
            { title: __('Layout', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Alignment', 'modfarm'),
              value: alignment,
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: (val) => setAttributes({ alignment: val })
            }),

            el(SelectControl, {
              label: __('Border Radius', 'modfarm'),
              value: radiusMode,
              options: [
                { label: __('Inherit (Global)', 'modfarm'), value: 'inherit' },
                { label: __('Custom (This Block)', 'modfarm'), value: 'custom' }
              ],
              onChange: (val) => setAttributes({ radiusMode: val })
            }),

            radiusMode === 'custom' &&
              el(TextControl, {
                label: __('Custom Radius (px)', 'modfarm'),
                type: 'number',
                value: border_radius,
                onChange: (val) =>
                  setAttributes({ border_radius: parseInt(val, 10) || 0 })
              }),

            el(ToggleControl, {
              label: __('Advanced: per-button color overrides', 'modfarm'),
              checked: !!showAdvanced,
              onChange: (val) => setAttributes({ showAdvanced: !!val })
            }),

            el('p', { className: 'description' },
              __('Tip: Set global Primary/Secondary button colors in ModFarm Settings → Book Presentation → Book Page Buttons.', 'modfarm')
            )
          ),

          // Buttons
          el(
            PanelBody,
            { title: __('Buttons', 'modfarm'), initialOpen: true },

            buttons.map((btn, index) =>
              el(
                PanelBody,
                {
                  key: index,
                  title: `${__('Button', 'modfarm')} ${index + 1}`,
                  initialOpen: false
                },

                el(SelectControl, {
                  label: __('Meta Field', 'modfarm'),
                  value: btn.meta_key || '',
                  options: [
                    { label: __('Select Meta Field', 'modfarm'), value: '' },
                    ...metaKeyOptions.map((key) => ({ label: key, value: key }))
                  ],
                  onChange: (val) => updateButton(index, 'meta_key', val)
                }),

                el(TextControl, {
                  label: __('Label', 'modfarm'),
                  value: btn.label || '',
                  onChange: (val) => updateButton(index, 'label', val)
                }),

                el(SelectControl, {
                  label: __('Button Type', 'modfarm'),
                  value: btn.type || 'inherit',
                  options: TYPE_OPTIONS,
                  onChange: (val) => updateButton(index, 'type', val)
                }),

                el(ToggleControl, {
                  label: __('Open in new tab', 'modfarm'),
                  checked: !!btn.new_tab,
                  onChange: (val) => updateButton(index, 'new_tab', !!val)
                }),

                // Advanced overrides (optional)
                showAdvanced &&
                  el(
                    Fragment,
                    null,
                    el('hr', null),

                    el(BaseControl, { label: __('Override Background', 'modfarm') },
                      el(ColorPalette, {
                        value: btn.bg_color || '',
                        onChange: (val) => updateButton(index, 'bg_color', val || ''),
                        disableCustomColors: false,
                        clearable: true
                      })
                    ),

                    el(BaseControl, { label: __('Override Text', 'modfarm') },
                      el(ColorPalette, {
                        value: btn.text_color || '',
                        onChange: (val) => updateButton(index, 'text_color', val || ''),
                        disableCustomColors: false,
                        clearable: true
                      })
                    ),

                    el(BaseControl, { label: __('Override Border', 'modfarm') },
                      el(ColorPalette, {
                        value: btn.border_color || '',
                        onChange: (val) => updateButton(index, 'border_color', val || ''),
                        disableCustomColors: false,
                        clearable: true
                      })
                    ),

                    el(Button, {
                      variant: 'secondary',
                      onClick: () => clearOverrides(index),
                      style: { marginTop: '8px' }
                    }, __('Clear Overrides', 'modfarm'))
                  ),

                el(Button, {
                  isDestructive: true,
                  variant: 'secondary',
                  onClick: () => removeButton(index),
                  style: { marginTop: '10px' }
                }, __('Remove Button', 'modfarm'))
              )
            ),

            buttons.length < MAX_BUTTONS &&
              el(Button, {
                variant: 'primary',
                onClick: addButton,
                style: { marginTop: '15px' }
              }, __('Add Button', 'modfarm'))
          )
        ),

        // Preview
        (() => {
          const blockProps = useBlockProps();
          return el(
            'div',
            blockProps,
            el(ServerSideRender, {
              block: 'modfarm/book-page-buttons',
              attributes: attributes
            })
          );
        })()
      );
    },

    save: () => null
  });
})();