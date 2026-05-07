/* global window */
(function (wp) {
  const { __ } = wp.i18n;

  const { registerBlockType } = wp.blocks;
  const {
    useBlockProps,
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
    PanelColorSettings
  } = wp.blockEditor || wp.editor;

  const {
    PanelBody,
    SelectControl,
    TextControl,
    Button
  } = wp.components;

  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  const EFFECT_OPTIONS = [
    { label: __('Flat', 'modfarm'), value: 'flat' },
    { label: __('Shadow – Small', 'modfarm'), value: 'shadow-sm' },
    { label: __('Shadow – Medium', 'modfarm'), value: 'shadow-md' },
    { label: __('Shadow – Large', 'modfarm'), value: 'shadow-lg' },
    { label: __('Emboss', 'modfarm'), value: 'emboss' }
  ];

  const SHAPE_OPTIONS_MAIN = [
    { label: __('Square', 'modfarm'), value: 'square' },
    { label: __('Rounded', 'modfarm'), value: 'rounded' },
    { label: __('Pill (gap only)', 'modfarm'), value: 'pill' },
    { label: __('Partial (bottom only)', 'modfarm'), value: 'partial' }
  ];

  const CTA_OPTIONS = [
    { label: __('Joined (no gap)', 'modfarm'), value: 'joined' },
    { label: __('Gap', 'modfarm'), value: 'gap' }
  ];

  function clampItems(items) {
    return Array.isArray(items) ? items : [];
  }

  function newItem() {
    return {
      imageId: 0,
      imageUrl: '',
      title: '',
      series: '',
      url: '',
      buttonText: ''
    };
  }

  function move(items, from, to) {
    const a = items.slice();
    if (to < 0 || to >= a.length) return a;
    const x = a.splice(from, 1)[0];
    a.splice(to, 0, x);
    return a;
  }

  registerBlockType('modfarm/generic-cards', {
    apiVersion: 2,
    title: 'Generic Book Cards',
    category: 'widgets',
    icon: 'book-alt',

    attributes: {
      items: { type: 'array', default: [] },

      'books-in-row': { type: 'string', default: '25%' },
      'display-layout': { type: 'string', default: 'grid' },
      'show-title': { type: 'string', default: 'none' },
      'show-series': { type: 'string', default: 'none' },
      'show-button': { type: 'string', default: 'block' },

      'button-text': { type: 'string', default: __('See The Book', 'modfarm') },
      'button-target': { type: 'string', default: '_self' },

      'use-global-style': { type: 'boolean', default: true },

      'buttonbg-color': { type: 'string', default: '' },
      'buttontx-color': { type: 'string', default: '' },

      effect: { type: 'string', default: 'flat' },
      'cover-shape': { type: 'string', default: 'square' },
      'button-shape': { type: 'string', default: 'square' },
      'cta-join': { type: 'string', default: 'joined' },

      'tracker-loc': { type: 'string', default: '' }
    },

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const items = clampItems(attributes.items);
      const useGlobal = !!attributes['use-global-style'];

      const setCanon = (key, val, allowed, fallback) => {
        const v = allowed.includes(val) ? val : fallback;
        setAttributes({ [key]: v });
      };

      const updateItem = (index, patch) => {
        const next = items.map((it, i) => (i === index ? { ...it, ...patch } : it));
        setAttributes({ items: next });
      };

      const addItem = () => setAttributes({ items: items.concat([newItem()]) });

      const removeItem = (index) => {
        const next = items.slice();
        next.splice(index, 1);
        setAttributes({ items: next });
      };

      const moveUp = (index) => setAttributes({ items: move(items, index, index - 1) });
      const moveDown = (index) => setAttributes({ items: move(items, index, index + 1) });

      // Multi-Tax style: if global is enabled, we keep local overrides empty
      const enableGlobal = (val) => {
        const on = !!val;
        const patch = { 'use-global-style': on };
        if (on) {
          patch['buttonbg-color'] = '';
          patch['buttontx-color'] = '';
        }
        setAttributes(patch);
      };

      return el(
        Fragment,
        {},
        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'modfarm/generic-cards',
            attributes
          })
        ),

        el(
          InspectorControls,
          {},

          // Cards (repeater)
          el(
            PanelBody,
            { title: __('Cards', 'modfarm'), initialOpen: true },
            el(
              'div',
              { style: { marginBottom: '10px' } },
              el(Button, { variant: 'primary', onClick: addItem }, __('Add Card', 'modfarm'))
            ),

            items.length === 0 &&
              el(
                'p',
                { style: { opacity: 0.8 } },
                __('Add one or more cards, then select cover image + link for each.', 'modfarm')
              ),

            items.map((item, idx) =>
              el(
                PanelBody,
                {
                  key: idx,
                  title: `${__('Card', 'modfarm')} ${idx + 1}`,
                  initialOpen: false
                },

                el(
                  'div',
                  { style: { marginBottom: '10px' } },
                  el(
                    MediaUploadCheck,
                    {},
                    el(MediaUpload, {
                      onSelect: (media) => {
                        const url = media?.url || media?.sizes?.large?.url || '';
                        updateItem(idx, { imageId: media?.id || 0, imageUrl: url });
                      },
                      allowedTypes: ['image'],
                      value: item.imageId || 0,
                      render: ({ open }) =>
                        el(
                          Fragment,
                          {},
                          item.imageUrl
                            ? el('img', {
                                src: item.imageUrl,
                                style: {
                                  width: '100%',
                                  height: 'auto',
                                  display: 'block',
                                  marginBottom: '8px'
                                }
                              })
                            : null,
                          el(
                            Button,
                            { variant: 'secondary', onClick: open },
                            item.imageUrl ? __('Replace Cover Image', 'modfarm') : __('Select Cover Image', 'modfarm')
                          ),
                          item.imageUrl &&
                            el(
                              Button,
                              {
                                variant: 'tertiary',
                                isDestructive: true,
                                onClick: () => updateItem(idx, { imageId: 0, imageUrl: '' }),
                                style: { marginLeft: '8px' }
                              },
                              __('Remove', 'modfarm')
                            )
                        )
                    })
                  )
                ),

                el(TextControl, {
                  label: __('Link URL', 'modfarm'),
                  value: item.url || '',
                  placeholder: 'https://…',
                  onChange: (v) => updateItem(idx, { url: v })
                }),

                el(TextControl, {
                  label: __('Title (optional)', 'modfarm'),
                  value: item.title || '',
                  onChange: (v) => updateItem(idx, { title: v })
                }),

                el(TextControl, {
                  label: __('Series / Subtitle (optional)', 'modfarm'),
                  value: item.series || '',
                  onChange: (v) => updateItem(idx, { series: v })
                }),

                el(TextControl, {
                  label: __('Button Text Override (optional)', 'modfarm'),
                  value: item.buttonText || '',
                  placeholder: attributes['button-text'] || __('See The Book', 'modfarm'),
                  onChange: (v) => updateItem(idx, { buttonText: v })
                }),

                el(
                  'div',
                  { style: { display: 'flex', gap: '6px', marginTop: '10px', flexWrap: 'wrap' } },
                  el(Button, { variant: 'secondary', onClick: () => moveUp(idx), disabled: idx === 0 }, __('Up', 'modfarm')),
                  el(
                    Button,
                    { variant: 'secondary', onClick: () => moveDown(idx), disabled: idx === items.length - 1 },
                    __('Down', 'modfarm')
                  ),
                  el(
                    Button,
                    { variant: 'secondary', isDestructive: true, onClick: () => removeItem(idx) },
                    __('Remove Card', 'modfarm')
                  )
                )
              )
            )
          ),

          // Display Settings (grid/visibility toggles)
          el(
            PanelBody,
            { title: __('Display Settings', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Presentation', 'modfarm'),
              value: attributes['display-layout'] || 'grid',
              options: [
                { label: __('Grid', 'modfarm'), value: 'grid' },
                { label: __('Horizontal Scroll', 'modfarm'), value: 'horizontal' }
              ],
              onChange: (val) => setAttributes({ 'display-layout': val || 'grid' })
            }),
            el(SelectControl, {
              label: __('Cards Per Row', 'modfarm'),
              value: attributes['books-in-row'],
              options: [
                { label: __('One', 'modfarm'), value: '100%' },
                { label: __('Two', 'modfarm'), value: '50%' },
                { label: __('Three', 'modfarm'), value: '33.33%' },
                { label: __('Four', 'modfarm'), value: '25%' }
              ],
              onChange: (val) => setAttributes({ 'books-in-row': val })
            }),

            el(SelectControl, {
              label: __('Show Title', 'modfarm'),
              value: attributes['show-title'],
              options: [
                { label: __('Yes', 'modfarm'), value: 'block' },
                { label: __('No', 'modfarm'), value: 'none' }
              ],
              onChange: (val) => setAttributes({ 'show-title': val })
            }),

            el(SelectControl, {
              label: __('Show Series', 'modfarm'),
              value: attributes['show-series'],
              options: [
                { label: __('Yes', 'modfarm'), value: 'block' },
                { label: __('No', 'modfarm'), value: 'none' }
              ],
              onChange: (val) => setAttributes({ 'show-series': val })
            }),

            el(SelectControl, {
              label: __('Show Button', 'modfarm'),
              value: attributes['show-button'],
              options: [
                { label: __('Yes', 'modfarm'), value: 'block' },
                { label: __('No', 'modfarm'), value: 'none' }
              ],
              onChange: (val) => setAttributes({ 'show-button': val })
            })
          ),

          // Global Settings (Option A)
          el(
            PanelBody,
            { title: __('Global Settings', 'modfarm'), initialOpen: false },
            el(wp.components.ToggleControl, {
              label: __('Use Global Styles', 'modfarm'),
              checked: useGlobal,
              onChange: enableGlobal,
              help: useGlobal
                ? __('This block will inherit the global ModFarm card/button styling.', 'modfarm')
                : __('Local style controls are enabled for this block.', 'modfarm')
            })
          ),

          // Design (only when NOT global)
          !useGlobal &&
            el(
              PanelBody,
              { title: __('Design', 'modfarm'), initialOpen: false },
              el(SelectControl, {
                label: __('Effect', 'modfarm'),
                value: attributes.effect,
                options: EFFECT_OPTIONS,
                onChange: (v) =>
                  setCanon(
                    'effect',
                    v,
                    EFFECT_OPTIONS.map((o) => o.value),
                    'flat'
                  )
              }),
              el(SelectControl, {
                label: __('Cover Shape', 'modfarm'),
                value: attributes['cover-shape'],
                options: [
                  { label: __('Square', 'modfarm'), value: 'square' },
                  { label: __('Rounded', 'modfarm'), value: 'rounded' }
                ],
                onChange: (v) => setCanon('cover-shape', v, ['square', 'rounded'], 'square')
              }),
              el(SelectControl, {
                label: __('Button Shape', 'modfarm'),
                value: attributes['button-shape'],
                options: SHAPE_OPTIONS_MAIN,
                onChange: (v) => setCanon('button-shape', v, ['square', 'rounded', 'pill', 'partial'], 'square')
              }),
              el(SelectControl, {
                label: __('CTA Spacing', 'modfarm'),
                value: attributes['cta-join'],
                options: CTA_OPTIONS,
                onChange: (v) => setCanon('cta-join', v, ['joined', 'gap'], 'joined')
              })
            ),

          // Button Style (content always; colors only when NOT global)
          el(
            PanelBody,
            { title: __('Button Style', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Default Button Text', 'modfarm'),
              value: attributes['button-text'],
              onChange: (val) => setAttributes({ 'button-text': val })
            }),
            el(SelectControl, {
              label: __('Button Target', 'modfarm'),
              value: attributes['button-target'],
              options: [
                { label: __('Same Tab', 'modfarm'), value: '_self' },
                { label: __('New Tab', 'modfarm'), value: '_blank' }
              ],
              onChange: (val) => setAttributes({ 'button-target': val })
            }),

            !useGlobal &&
              el(
                PanelColorSettings,
                {
                  title: __('Colors', 'modfarm'),
                  colorSettings: [
                    {
                      label: __('Button Background', 'modfarm'),
                      value: attributes['buttonbg-color'],
                      onChange: (color) => setAttributes({ 'buttonbg-color': color || '' })
                    },
                    {
                      label: __('Button Text', 'modfarm'),
                      value: attributes['buttontx-color'],
                      onChange: (color) => setAttributes({ 'buttontx-color': color || '' })
                    }
                  ]
                },
                null
              )
          ),

          // Misc (tracking lives here like Multi-Tax)
          el(
            PanelBody,
            { title: __('Misc', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Tracker Location', 'modfarm'),
              value: attributes['tracker-loc'],
              onChange: (val) => setAttributes({ 'tracker-loc': val })
            })
          )
        )
      );
    },

    save: function () {
      return null;
    }
  });
})(window.wp);
