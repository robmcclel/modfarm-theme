(function(){
  const { createElement: el, Fragment } = wp.element;
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, TextControl, SelectControl, ToggleControl, ColorPalette, BaseControl } = wp.components;
  const ServerSideRender = wp.serverSideRender;
  const __ = (s)=>s;

  function setCanon(key, val, allowed, fallback, setAttributes){
    setAttributes({ [key]: allowed.includes(val) ? val : fallback });
  }

  function ColorRow(label, value, onChange){
    return el(BaseControl, { label: label },
      el(ColorPalette, {
        value: value || '',
        onChange: (v)=>onChange(v || ''),
        disableCustomColors: false,
        clearable: true
      })
    );
  }

  const SHAPE_OPTIONS_SAMPLE = [
    { label: __('Square','modfarm'),  value: 'square' },
    { label: __('Rounded','modfarm'), value: 'rounded' },
    { label: __('Pill','modfarm'),    value: 'pill' }
  ];
  const ALIGN_OPTIONS = [
    { label: __('Left','modfarm'),   value: 'left' },
    { label: __('Center','modfarm'), value: 'center' },
    { label: __('Right','modfarm'),  value: 'right' }
  ];

  registerBlockType('modfarm/book-page-audio', {
    apiVersion: 2,
    title: 'Book Page Audio Sample',
    icon: 'format-audio',
    category: 'modfarm-theme',
    attributes: {
      titleText:          { type:'string', default:'Listen To A Sample' },
      'samplebtn-text':   { type:'string', default:'Play Sample' },

      // empty = inherit global (recommended)
      'samplebtn-bg':     { type:'string', default:'' },
      'samplebtn-fg':     { type:'string', default:'' },
      'samplebtn-border': { type:'string', default:'' },

      'sample-shape':     { type:'string', default:'pill' },
      'button-align':     { type:'string', default:'center' },
      "label-size":       { type: "string", default: "16px" },
      "label-weight":     { type: "string", default: "600" },

      // NEW: keep UI clean unless needed
      showAdvanced:       { type:'boolean', default:false },

      className:          { type:'string', default:'' }
    },

    edit: (props)=>{
      const { attributes: A, setAttributes } = props;
      const blockProps = useBlockProps();

      return el('div', blockProps,
        el(InspectorControls, null,

          el(PanelBody, { title: __('Heading','modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Heading (erase to hide)','modfarm'),
              value: A.titleText || '',
              onChange: v => setAttributes({ titleText: v })
            }),
            el(TextControl, {
              label: __('Heading Font Size (e.g., 1rem, 18px)','modfarm'),
              value: A['label-size'] || '',
              onChange: v => setAttributes({ 'label-size': v })
            }),
            el(TextControl, {
              label: __('Heading Weight (e.g., 600, bold)','modfarm'),
              value: A['label-weight'] || '',
              onChange: v => setAttributes({ 'label-weight': v })
            })
          ),

          el(PanelBody, { title: __('Sample Button Style','modfarm'), initialOpen:false },
            el(TextControl, {
              label: __('Sample Button Text','modfarm'),
              value: A['samplebtn-text'],
              onChange: v => setAttributes({ 'samplebtn-text': v })
            }),
            el(SelectControl, {
              label: __('Button Alignment','modfarm'),
              value: A['button-align'],
              options: ALIGN_OPTIONS,
              onChange: v => setCanon('button-align', v, ['left','center','right'], 'center', setAttributes)
            }),
            el(SelectControl, {
              label: __('Sample Button Shape','modfarm'),
              value: A['sample-shape'],
              options: SHAPE_OPTIONS_SAMPLE,
              onChange: v => setCanon('sample-shape', v, ['square','rounded','pill'], 'pill', setAttributes)
            }),

            el(ToggleControl, {
              label: __('Advanced: override button colors', 'modfarm'),
              checked: !!A.showAdvanced,
              onChange: (v)=> setAttributes({ showAdvanced: !!v })
            }),

            A.showAdvanced && el(Fragment, null,
              ColorRow(__('Sample Background','modfarm'), A['samplebtn-bg'],     v => setAttributes({ 'samplebtn-bg': v })),
              ColorRow(__('Sample Text','modfarm'),       A['samplebtn-fg'],     v => setAttributes({ 'samplebtn-fg': v })),
              ColorRow(__('Sample Border','modfarm'),     A['samplebtn-border'], v => setAttributes({ 'samplebtn-border': v }))
            ),

            el('p', { className: 'description' },
              __('Leave colors empty to inherit global styles from ModFarm Settings.', 'modfarm')
            )
          )
        ),

        el(ServerSideRender, { block: 'modfarm/book-page-audio', attributes: A })
      );
    },

    save: ()=>null
  });
})();