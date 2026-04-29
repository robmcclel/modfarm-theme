(function(wp){
  const el = wp.element.createElement;
  const Fragment = wp.element.Fragment;
  const registerBlockType = wp.blocks.registerBlockType;
  const InspectorControls = wp.blockEditor ? wp.blockEditor.InspectorControls : wp.editor.InspectorControls;

  const PanelBody = wp.components.PanelBody;
  const SelectControl = wp.components.SelectControl;
  const TextControl = wp.components.TextControl;
  const ToggleControl = wp.components.ToggleControl;
  const RangeControl = wp.components.RangeControl;

  const PanelColorSettings = wp.blockEditor ? wp.blockEditor.PanelColorSettings : null;

  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/series-nav', {
    title: 'Series Previous/Next',
    icon: 'controls-forward',
    category: 'modfarm-theme',
    attributes: {
      mode: { type: 'string', default: 'position' }, // position|pubdate|reading
      prevLabel: { type: 'string', default: '« Previous' },
      nextLabel: { type: 'string', default: 'Next »' },

      showSeriesLabel: { type: 'boolean', default: true },

      fontSizePx: { type: 'number', default: 0 },

      textColor: { type: 'string', default: '' },
      linkColor: { type: 'string', default: '' },

      context: { type: 'object', default: {} }
    },

    edit: function(props){
      const atts = props.attributes;

      // Pass postId via context for SSR preview
      const ssrAtts = Object.assign({}, atts, {
        context: Object.assign({}, atts.context, {
          postId: (props.context && props.context.postId) ? props.context.postId : (atts.context ? atts.context.postId : 0)
        })
      });

      const colorPanel = PanelColorSettings
        ? el(PanelColorSettings, {
            title: 'Colors',
            colorSettings: [
              {
                label: 'Text Color',
                value: atts.textColor,
                onChange: function(v){ props.setAttributes({ textColor: v || '' }); }
              },
              {
                label: 'Link Color',
                value: atts.linkColor,
                onChange: function(v){ props.setAttributes({ linkColor: v || '' }); }
              }
            ]
          })
        : null;

      return el(Fragment, {},
        el(InspectorControls, {},

          el(PanelBody, { title: 'Navigation Settings', initialOpen: true },
            el(SelectControl, {
              label: 'Order Mode',
              value: atts.mode,
              options: [
                { label: 'Series Position (1,2,3...)', value: 'position' },
                { label: 'Publication Date', value: 'pubdate' },
                { label: 'Reading Order (custom)', value: 'reading' }
              ],
              onChange: function(v){ props.setAttributes({ mode: v }); }
            }),
            el(TextControl, {
              label: 'Previous Label',
              value: atts.prevLabel,
              onChange: function(v){ props.setAttributes({ prevLabel: v }); }
            }),
            el(TextControl, {
              label: 'Next Label',
              value: atts.nextLabel,
              onChange: function(v){ props.setAttributes({ nextLabel: v }); }
            }),
            el(ToggleControl, {
              label: 'Show Series Label',
              checked: !!atts.showSeriesLabel,
              onChange: function(v){ props.setAttributes({ showSeriesLabel: !!v }); }
            })
          ),

          el(PanelBody, { title: 'Typography', initialOpen: false },
            el(RangeControl, {
              label: 'Font Size (px)',
              value: atts.fontSizePx || 0,
              min: 0,
              max: 40,
              help: (atts.fontSizePx && atts.fontSizePx > 0) ? ('Using ' + atts.fontSizePx + 'px') : '0 = inherit theme font size',
              onChange: function(v){ props.setAttributes({ fontSizePx: parseInt(v || 0, 10) }); }
            })
          ),

          colorPanel
        ),

        el(ServerSideRender, {
          block: 'modfarm/series-nav',
          attributes: ssrAtts
        })
      );
    },

    save: function(){ return null; }
  });
})(window.wp);