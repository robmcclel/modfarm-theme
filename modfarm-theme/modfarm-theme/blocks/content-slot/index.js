(function (wp) {
  const { createElement: el, Fragment } = wp.element;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks, InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, TextControl, ToggleControl } = wp.components;

  const TEMPLATE = [];
  const ALLOWED = undefined; // allow any block

  registerBlockType('modfarm/content-slot', {
    title: 'Content Slot (ModFarm)',
    icon: 'align-left',
    category: 'modfarm-theme',
    description: 'A smart content slot for PPB templates. If empty, can auto-render post content or excerpt.',
    attributes: {
      slot: { type: 'string', default: 'main' },
      acceptImport: { type: 'boolean', default: true },
      autofillPostContent: { type: 'boolean', default: true },
      fallbackToExcerpt: { type: 'boolean', default: true },
      applyTheContentFilters: { type: 'boolean', default: true }
    },
    edit: (props) => {
      const { attributes, setAttributes } = props;
      const { slot, acceptImport, autofillPostContent, fallbackToExcerpt, applyTheContentFilters } = attributes;

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Content Slot Settings', initialOpen: true },
            el(TextControl, {
              label: 'Slot ID',
              help: 'Use the same ID across patterns so content keeps its place.',
              value: slot,
              onChange: (v) => setAttributes({ slot: v || 'main' })
            }),
            el(ToggleControl, {
              label: 'Accept Import / Autofill allowed',
              help: 'Importer and autofill can target this slot.',
              checked: !!acceptImport,
              onChange: (v) => setAttributes({ acceptImport: !!v })
            }),
            el(ToggleControl, {
              label: 'Autofill Post Content',
              help: 'If InnerBlocks are empty, render post content here.',
              checked: !!autofillPostContent,
              onChange: (v) => setAttributes({ autofillPostContent: !!v })
            }),
            el(ToggleControl, {
              label: 'Fallback to Excerpt',
              help: 'When no body is available, render the excerpt.',
              checked: !!fallbackToExcerpt,
              onChange: (v) => setAttributes({ fallbackToExcerpt: !!v })
            }),
            el(ToggleControl, {
              label: 'Apply the_content filters',
              help: 'Enable oEmbed, shortcodes, and block filters (recommended).',
              checked: !!applyTheContentFilters,
              onChange: (v) => setAttributes({ applyTheContentFilters: !!v })
            })
          )
        ),
        el('div', useBlockProps({ className: 'mf-content-slot__frame', 'data-slot': slot }),
          el('div', { className: 'mf-content-slot__label' }, `Content Slot: ${slot}`),
          el(InnerBlocks, {
            template: TEMPLATE,
            templateLock: false,
            allowedBlocks: ALLOWED,
            renderAppender: InnerBlocks.ButtonBlockAppender
          })
        )
      );
    },
    // Dynamic block: PHP render_callback handles frontend.
    save: () => el(InnerBlocks.Content, null)
  });
})(window.wp);