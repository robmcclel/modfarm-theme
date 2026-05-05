/* global window */
(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const { PanelBody, ToggleControl, SelectControl, TextControl } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/table-of-contents', {
    apiVersion: 2,
    title: __('Table of Contents', 'modfarm'),
    icon: 'list-view',
    category: 'modfarm-theme',

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();
      const set = (k) => (v) => setAttributes({ [k]: v });

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/table-of-contents',
            attributes
          })
        ),

        el(InspectorControls, {},
          el(PanelBody, { title: __('Heading Levels', 'modfarm'), initialOpen: true },
            el(ToggleControl, {
              label: __('Include H2', 'modfarm'),
              checked: !!attributes.includeH2,
              onChange: (v) => setAttributes({ includeH2: !!v })
            }),
            el(ToggleControl, {
              label: __('Include H3', 'modfarm'),
              checked: !!attributes.includeH3,
              onChange: (v) => setAttributes({ includeH3: !!v })
            }),
            el(ToggleControl, {
              label: __('Include H4', 'modfarm'),
              checked: !!attributes.includeH4,
              onChange: (v) => setAttributes({ includeH4: !!v })
            }),
            el(SelectControl, {
              label: __('Anchor Slug Case', 'modfarm'),
              value: attributes.slugCase,
              options: [
                { label: __('Lowercase (recommended)', 'modfarm'), value: 'lower' },
                { label: __('Preserve Text Case', 'modfarm'),      value: 'preserve' }
              ],
              onChange: set('slugCase')
            }),
            el(TextControl, {
              label: __('Optional Heading', 'modfarm'),
              help: __('Leave blank for no heading', 'modfarm'),
              value: attributes.title || '',
              onChange: set('title')
            })
          ),

          el(PanelBody, { title: __('Layout', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Columns', 'modfarm'),
              value: attributes.columns,
              options: [{label:'1',value:1},{label:'2',value:2},{label:'3',value:3}],
              onChange: (v) => setAttributes({ columns: Math.max(1, Math.min(3, parseInt(v, 10) || 1)) })
            }),
            el(SelectControl, {
              label: __('Text Align', 'modfarm'),
              value: attributes.align,
              options: [
                { label: __('Left', 'modfarm'),   value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'),  value: 'right' }
              ],
              onChange: set('align')
            }),
            el(SelectControl, {
              label: __('List Style', 'modfarm'),
              value: attributes.listStyle,
              options: [
                { label: __('Plain', 'modfarm'),    value: 'plain' },
                { label: __('Bulleted', 'modfarm'), value: 'bulleted' },
                { label: __('Numbered', 'modfarm'), value: 'numbered' }
              ],
              onChange: set('listStyle')
            }),
            el(ToggleControl, {
              label: __('Collapse on mobile', 'modfarm'),
              checked: attributes.collapseOnMobile !== false,
              onChange: (v) => setAttributes({ collapseOnMobile: !!v })
            })
          )
        )
      );
    }
  });
})(window.wp);
