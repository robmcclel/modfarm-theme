(function() {
  const el = wp.element.createElement;
  const { registerBlockType } = wp.blocks;
  const blockEditor = wp.blockEditor || wp.editor;
  const {
    InspectorControls,
    MediaUpload,
    MediaUploadCheck
  } = blockEditor;

  // Guard for older WP where useBlockProps may not exist on wp.editor
  const useBlockProps = (blockEditor && blockEditor.useBlockProps)
    ? blockEditor.useBlockProps
    : function(defaults){ return defaults || {}; };

  const { PanelBody, RangeControl, SelectControl, ToggleControl, Button, TextControl } = wp.components;
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/theme-icon', {
    apiVersion: 2,
    title: 'Theme Icon',
    icon: 'format-image',
    category: 'modfarm-theme',
    description: 'Display the Site Icon (favicon) or a custom override.',
    attributes: {
      size: { type: 'number', default: 48 },
      shape: { type: 'string', default: 'square' }, // square | rounded | circle
      linkHome: { type: 'boolean', default: true },
      overrideImageID: { type: 'number', default: 0 },
      overrideImageURL: { type: 'string', default: '' },
      alt: { type: 'string', default: '' },
      className: { type: 'string', default: '' },
      displayMode: { type: 'string', default: 'block' }, // inline | block
      align: { type: 'string', default: 'center' }          // left | center | right
    },
    edit: function(props) {
      const { attributes, setAttributes } = props;
      const { size, shape, linkHome, overrideImageID, overrideImageURL, alt } = attributes;

      // ✅ Wrap SSR preview with block props (your pattern)
      const blockProps = useBlockProps({ className: 'mf-theme-icon-editor' });

      return el(
        wp.element.Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: 'Icon Settings', initialOpen: true },
            el(RangeControl, {
              label: 'Size (px)',
              value: size,
              onChange: (v)=> setAttributes({ size: v }),
              min: 16, max: 256, step: 2
            }),
            el(SelectControl, {
              label: 'Shape',
              value: shape,
              options: [
                { label: 'Square', value: 'square' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Circle', value: 'circle' }
              ],
              onChange: (v)=> setAttributes({ shape: v })
            }),
            el(SelectControl, {
              label: 'Display Mode',
              value: attributes.displayMode,
              options: [
                { label: 'Inline', value: 'inline' },
                { label: 'Block (100% width)', value: 'block' }
              ],
              onChange: (v)=> setAttributes({ displayMode: v })
            }),
            el(SelectControl, {
              label: 'Alignment (block mode)',
              value: attributes.align,
              options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
              ],
              onChange: (v)=> setAttributes({ align: v })
            }),
            el(ToggleControl, {
              label: 'Link to Home',
              checked: linkHome,
              onChange: (v)=> setAttributes({ linkHome: v })
            }),
            el(TextControl, {
              label: 'Alt text',
              value: alt,
              onChange: (v)=> setAttributes({ alt: v }),
              placeholder: 'Site icon'
            })
          ),
          el(
            PanelBody,
            { title: 'Override Image (optional)', initialOpen: false },
            el('div', { className: 'mfti-media' },
              el(MediaUploadCheck, {},
                el(MediaUpload, {
                  onSelect: (media)=> setAttributes({
                    overrideImageID: media.id,
                    overrideImageURL: media.url || '',
                    alt: media.alt || alt
                  }),
                  allowedTypes: ['image'],
                  value: overrideImageID,
                  render: ({ open }) => el(Button, { variant: 'secondary', onClick: open }, overrideImageID ? 'Replace Image' : 'Select Image')
                })
              ),
              !!overrideImageID && el(Button, {
                variant: 'link',
                onClick: ()=> setAttributes({ overrideImageID: 0, overrideImageURL: '' })
              }, 'Remove override')
            )
          )
        ),
        // ✅ Server-side render preview
        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'modfarm/theme-icon',
            attributes: attributes
          })
        )
      );
    },
    save: function() { return null; }
  });
})();