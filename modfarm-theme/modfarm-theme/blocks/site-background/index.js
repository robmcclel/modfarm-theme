(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps, ColorPalette } = wp.blockEditor || wp.editor;
  const {
    PanelBody, SelectControl, RangeControl, Button,
    __experimentalVStack: VStack, __experimentalHStack: HStack,
    ToolbarGroup, ToolbarButton
  } = wp.components;
  const { useCallback } = wp.element;
  const el = wp.element.createElement;

  /* -------------------- HELPERS -------------------- */

  // WP-style color field
  const WPColorField = ({ label, value, onChange, help }) =>
    el('div', { className:'mf-color-field' },
      el('label', { className:'mf-color-label' }, label),
      el(VStack, { spacing:'6px' },
        el(ColorPalette, {
          value: value || '',
          clearable: true,
          enableAlpha: false,
          onChange: (val)=>onChange(val || '')
        }),
        help ? el('p', { className:'mf-field-help' }, help) : null
      )
    );

  const POSITIONS = [
    'center center','top center','bottom center','center left','center right',
    'top left','top right','bottom left','bottom right'
  ].map(v => ({ label: v, value: v }));

  function hexToRgba(hex, alpha) {
    const h = (hex || '').replace('#','');
    const full = h.length === 3 ? h.split('').map(x=>x+x).join('') : h;
    if (!/^[0-9a-f]{6}$/i.test(full)) return '';
    const r = parseInt(full.slice(0,2),16);
    const g = parseInt(full.slice(2,4),16);
    const b = parseInt(full.slice(4,6),16);
    const a = Math.min(1, Math.max(0, Number(alpha ?? 1)));
    return `rgba(${r},${g},${b},${a})`;
  }

  function buildFullCss(a) {
    const mode = a.mode || 'color';
    const decl = [];

    if (mode === 'color' && a.color) {
      const base = (a.bgOpacity ?? 1) < 1 ? hexToRgba(a.color, a.bgOpacity) : a.color;
      decl.push(`background-color:${base};`);
    }

    if (mode === 'gradient' && a.gradientStartColor && a.gradientEndColor) {
      const angle = Number(a.gradientAngle ?? 180);
      const startPos = Math.min(100, Math.max(0, Number(a.gradientStartPos ?? 0)));
      const endPos   = Math.min(100, Math.max(0, Number(a.gradientEndPos ?? 100)));
      decl.push(`background-image:linear-gradient(${angle}deg, ${a.gradientStartColor} ${startPos}%, ${a.gradientEndColor} ${endPos}%);`);
      decl.push('background-size:cover;');
      decl.push('background-repeat:no-repeat;');
      decl.push(`background-attachment:${a.attachment || 'scroll'};`);
      decl.push(`background-position:${a.position || 'center center'};`);
    }

    if (mode === 'image' && a.image && a.image.url) {
      const layers = [];
      if (a.overlayColor && Number(a.overlayOpacity) > 0) {
        const rgba = hexToRgba(a.overlayColor, a.overlayOpacity);
        if (rgba) layers.push(`linear-gradient(${rgba}, ${rgba})`);
      }
      layers.push(`url("${a.image.url}")`);
      decl.push(`background-image:${layers.join(',')};`);
      decl.push(`background-size:${a.size || 'cover'};`);
      decl.push(`background-repeat:${a.repeat || 'no-repeat'};`);
      decl.push(`background-attachment:${a.attachment || 'scroll'};`);
      decl.push(`background-position:${a.position || 'center center'};`);
      if (a.color) {
        const base = (a.bgOpacity ?? 1) < 1 ? hexToRgba(a.color, a.bgOpacity) : a.color;
        decl.push(`background-color:${base};`);
      }
    }

    decl.push('background-origin:padding-box;');
    decl.push('background-clip:border-box;');
    decl.push('background-blend-mode:normal;');

    let css = `body{${decl.join('')}}`;
    if (a.textColor) {
  css += ` body{color:${a.textColor};}`;
  css += ` body :is(h1,h2,h3,h4,h5,h6,.wp-block-heading){color:${a.textColor};}`;
}

if (a.linkColor) {
  css += ` body a{color:${a.linkColor};}`;
  // Optional: heading links too (so a linked H2 doesn’t fall back to default link color)
  css += ` body :is(h1,h2,h3,h4,h5,h6,.wp-block-heading) a{color:${a.linkColor};}`;
}

    return css;
  }

  const DEFAULTS = Object.freeze({
    mode: 'color',
    color: '',
    bgOpacity: 1,
    gradientAngle: 180,
    gradientStartColor: '',
    gradientEndColor: '',
    gradientStartPos: 0,
    gradientEndPos: 100,
    image: { id:0, url:'' },
    size: 'cover',
    position: 'center center',
    repeat: 'no-repeat',
    attachment: 'scroll',
    overlayColor: '',
    overlayOpacity: 0,
    textColor: '',
    linkColor: ''
  });

  /* -------------------- PRESETS -------------------- */
  const PRESETS = [
    {
      label: 'Light Wash',
      apply: (set)=> set({
        mode:'color', color:'#ffffff', bgOpacity:0.85, textColor:'#111111', linkColor:'#0ea5e9'
      })
    },
    {
      label: 'Deep Fade',
      apply: (set)=> set({
        mode:'color', color:'#000000', bgOpacity:0.6, textColor:'#f5f5f5', linkColor:'#93c5fd'
      })
    },
    {
      label: 'Hero Photo Dim',
      apply: (set)=> set({
        mode:'image', overlayColor:'#000000', overlayOpacity:0.45, textColor:'#f3f4f6', linkColor:'#93c5fd'
      })
    },
    {
      label: 'Dark Glass',
      apply: (set)=> set({
        mode:'image', color:'#0f172a', bgOpacity:0.5, overlayColor:'#0f172a', overlayOpacity:0.25, textColor:'#e5e7eb', linkColor:'#60a5fa'
      })
    },
    {
      label: 'Soft Gradient',
      apply: (set)=> set({
        mode:'gradient', gradientStartColor:'#f1f5f9', gradientEndColor:'#e2e8f0', gradientStartPos:0, gradientEndPos:100, gradientAngle:180, textColor:'#0f172a', linkColor:'#0ea5e9'
      })
    },
    {
      label: 'Warm Gradient',
      apply: (set)=> set({
        mode:'gradient', gradientStartColor:'#fde68a', gradientEndColor:'#fca5a5', gradientStartPos:0, gradientEndPos:100, gradientAngle:135, textColor:'#111111', linkColor:'#7c3aed'
      })
    }
  ];

  const PresetsBar = ({ onApply }) =>
  el('div', { className:'mf-presets-grid' },
    PRESETS.map((p, i) =>
      el(Button, {
        key: i,
        size: 'small',
        variant: 'secondary',
        className: 'mf-preset',
        onClick: () => onApply(p)
      }, p.label)
    )
  );

  /* -------------------- BLOCK -------------------- */
  registerBlockType('modfarm/site-background', {
    apiVersion: 2,
    title: 'Page Background (Inline Style)',
    icon: 'art',
    category: 'design',
    supports: { html:false, align:false, multiple:false },

    attributes: Object.keys(DEFAULTS).reduce((acc, k)=>{
      acc[k] = { type: typeof DEFAULTS[k], default: DEFAULTS[k] };
      return acc;
    }, {}),

    edit: (props) => {
      const { attributes:a, setAttributes } = props;
      const css = buildFullCss(a);
      const doReset = useCallback(() => setAttributes({ ...DEFAULTS }), [setAttributes]);
      const applyPreset = (preset) => preset.apply((patch) => setAttributes({ ...a, ...patch }));

      const blockProps = useBlockProps({ className: 'mf-pb-shell', tabIndex: 0 });

      /* --- Color mode --- */
      const colorUI = el(VStack, { spacing:'8px' },
        el(WPColorField, { label:'Background Color', value:a.color||'', onChange:(v)=>setAttributes({ color: v || '' }) }),
        el(RangeControl, { label:'Background Opacity', min:0, max:1, step:0.05, value:a.bgOpacity, onChange:(v)=>setAttributes({ bgOpacity: v ?? 1 }) })
      );

      /* --- Gradient mode --- */
      const gradientUI = el(VStack, { spacing:'8px' },
        el(WPColorField, { label:'Start Color', value:a.gradientStartColor||'', onChange:(v)=>setAttributes({ gradientStartColor: v || '' }) }),
        el(RangeControl, { label:'Start Position (%)', min:0, max:100, step:1, value:a.gradientStartPos, onChange:(v)=>setAttributes({ gradientStartPos: v ?? 0 }) }),
        el(WPColorField, { label:'End Color', value:a.gradientEndColor||'', onChange:(v)=>setAttributes({ gradientEndColor: v || '' }) }),
        el(RangeControl, { label:'End Position (%)', min:0, max:100, step:1, value:a.gradientEndPos, onChange:(v)=>setAttributes({ gradientEndPos: v ?? 100 }) }),
        el(RangeControl, { label:'Angle (deg)', min:0, max:360, step:1, value:a.gradientAngle, onChange:(v)=>setAttributes({ gradientAngle: v ?? 180 }) }),
        el(SelectControl, { label:'Attachment', value:a.attachment, options:[{label:'scroll',value:'scroll'},{label:'fixed',value:'fixed'},{label:'local',value:'local'}], onChange:v=>setAttributes({attachment:v}) }),
        el(SelectControl, { label:'Position', value:a.position, options:POSITIONS, onChange:v=>setAttributes({position:v}) }),
        el('hr'),
        el(WPColorField, { label:'Fallback Color (optional)', value:a.color||'', onChange:(v)=>setAttributes({ color: v || '' }) })
      );

      /* --- Image mode --- */
      const imageUI = el(VStack, { spacing: '8px' },
        el(MediaUploadCheck, {},
          el(MediaUpload, {
            onSelect: (m)=>setAttributes({ image:{ id:m.id, url:m.url } }),
            allowedTypes: ['image'],
            value: a.image?.id || 0,
            render: ({ open }) => el(Button, { variant:'primary', onClick: open }, a.image?.url ? 'Change image' : 'Choose image')
          })
        ),
        a.image?.url ? el('div', { className:'mf-pb-thumb' }, el('img', { src:a.image.url, alt:'' })) : null,
        el(SelectControl, { label:'Size', value:a.size, options:['cover','contain','auto'].map(v=>({label:v,value:v})), onChange:v=>setAttributes({size:v}) }),
        el(SelectControl, { label:'Repeat', value:a.repeat, options:['no-repeat','repeat','repeat-x','repeat-y'].map(v=>({label:v,value:v})), onChange:v=>setAttributes({repeat:v}) }),
        el(SelectControl, { label:'Position', value:a.position, options:POSITIONS, onChange:v=>setAttributes({position:v}) }),
        el(SelectControl, { label:'Attachment', value:a.attachment, options:[{label:'scroll',value:'scroll'},{label:'fixed',value:'fixed'},{label:'local',value:'local'}], onChange:v=>setAttributes({attachment:v}) }),
        el('hr'),
        el(WPColorField, { label:'Overlay Color', value:a.overlayColor||'', onChange:(val)=>setAttributes({ overlayColor: val || '' }), help:'Use opacity below to dim the image.' }),
        el(RangeControl, { label:'Overlay Opacity', min:0, max:1, step:0.05, value:a.overlayOpacity, onChange:(v)=>setAttributes({ overlayOpacity: v ?? 0 }) }),
        el('hr'),
        el(WPColorField, { label:'Base Color (optional)', value:a.color||'', onChange:(v)=>setAttributes({ color: v || '' }) }),
        el(RangeControl, { label:'Base Color Opacity', min:0, max:1, step:0.05, value:a.bgOpacity, onChange:(v)=>setAttributes({ bgOpacity: v ?? 1 }) })
      );

      /* --- Text colors --- */
      const textUI = el(VStack, { spacing:'8px' },
        el(WPColorField, { label:'Text Color', value:a.textColor||'', onChange:(v)=>setAttributes({ textColor: v || '' }) }),
        el(WPColorField, { label:'Link Color', value:a.linkColor||'', onChange:(v)=>setAttributes({ linkColor: v || '' }) })
      );

      blockProps.toolbar = el(ToolbarGroup, {},
        el(ToolbarButton, { icon:'image-rotate', label:'Reset to Default', onClick: doReset })
      );

      return el('div', blockProps,
        el('style', { id:'mf-site-page-background' }, css),
        el(InspectorControls, {},
          el(PanelBody, { title:'Background Type', initialOpen:true },
            el(SelectControl, {
              value:a.mode,
              options:[{label:'Color',value:'color'},{label:'Image',value:'image'},{label:'Gradient',value:'gradient'}],
              onChange:(v)=>setAttributes({ mode:v })
            })
          ),
          el(PanelBody, { title:'Presets', initialOpen:true },
            el(PresetsBar, { onApply: applyPreset })
          ),
          a.mode==='color'    && el(PanelBody, { title:'Color Settings', initialOpen:true }, colorUI),
          a.mode==='image'    && el(PanelBody, { title:'Image Settings', initialOpen:true }, imageUI),
          a.mode==='gradient' && el(PanelBody, { title:'Gradient Settings', initialOpen:true }, gradientUI),
          el(PanelBody, { title:'Text Colors', initialOpen:false }, textUI),
          el(PanelBody, { title:'Utilities', initialOpen:false },
            el(Button, { variant:'secondary', isDestructive:true, onClick: doReset }, 'Reset to Default')
          )
        )
      );
    },

    save: (props) => {
      const a = props.attributes || {};
      const css = buildFullCss(a);
      return el('style', { id:'mf-site-page-background' }, css);
    }
  });
})(window.wp);