(function (wp) {
  const { createElement: el, Fragment } = wp.element;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks, InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, SelectControl, TextControl, ToggleControl, Notice } = wp.components;

  const SLOT_LABELS = {
    header: 'Header Zone',
    body: 'Body Zone',
    footer: 'Footer Zone',
    data: 'Data Zone'
  };

  const SLOT_OPTIONS = [
    { label: 'Header Zone', value: 'header' },
    { label: 'Body Zone', value: 'body' },
    { label: 'Footer Zone', value: 'footer' },
    { label: 'Data Zone', value: 'data' }
  ];

  registerBlockType('modfarm/zone', {
    title: 'ModFarm Zone',
    icon: 'screenoptions',
    category: 'modfarm-theme',
    description: 'Explicit PPB and Skate zone wrapper. Frontend renders only inner blocks.',
    attributes: {
      slot: { type: 'string', default: 'body' },
      origin: { type: 'string', default: 'ppb' },
      pattern: { type: 'string', default: '' },
      locked: { type: 'boolean', default: false },
      version: { type: 'number', default: 1 }
    },
    edit: (props) => {
      const { attributes, setAttributes } = props;
      const { slot, origin, pattern, locked, version } = attributes;
      const zoneLabel = SLOT_LABELS[slot] || 'Zone';
      const replacementState = locked ? 'Locked / exempt from PPB replacement' : 'Eligible for PPB replacement';

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Zone Settings', initialOpen: true },
            el(SelectControl, {
              label: 'Zone Slot',
              value: slot,
              options: SLOT_OPTIONS,
              onChange: (value) => setAttributes({ slot: value || 'body' })
            }),
            el(TextControl, {
              label: 'Origin',
              help: 'Records how this zone was created or managed.',
              value: origin || '',
              onChange: (value) => setAttributes({ origin: value || '' })
            }),
            el(TextControl, {
              label: 'Pattern Source',
              help: 'Optional source pattern slug used to populate this zone.',
              value: pattern || '',
              onChange: (value) => setAttributes({ pattern: value || '' })
            }),
            el(ToggleControl, {
              label: 'Locked / exempt',
              help: 'Marks this zone as protected from future PPB replacement actions.',
              checked: !!locked,
              onChange: (value) => setAttributes({ locked: !!value })
            }),
            el(TextControl, {
              label: 'Schema Version',
              type: 'number',
              value: String(version || 1),
              onChange: (value) => {
                const parsed = parseInt(value, 10);
                setAttributes({ version: Number.isFinite(parsed) && parsed > 0 ? parsed : 1 });
              }
            })
          )
        ),
        el('div', useBlockProps({ className: `mf-zone-block mf-zone-block--${slot}` }),
          el('div', { className: 'mf-zone-block__header' },
            el('strong', { className: 'mf-zone-block__title' }, zoneLabel),
            el('span', { className: 'mf-zone-block__state' }, replacementState)
          ),
          pattern
            ? el(Notice, {
                status: 'info',
                isDismissible: false,
                className: 'mf-zone-block__notice'
              }, `Pattern source: ${pattern}`)
            : el(Notice, {
                status: 'warning',
                isDismissible: false,
                className: 'mf-zone-block__notice'
              }, 'Pattern source not recorded for this zone.'),
          el('div', { className: 'mf-zone-block__meta' },
            el('span', null, `Origin: ${origin || 'unspecified'}`),
            el('span', null, `Version: ${version || 1}`)
          ),
          el('div', { className: 'mf-zone-block__content' },
            el(InnerBlocks, {
              templateLock: false,
              renderAppender: InnerBlocks.ButtonBlockAppender
            })
          )
        )
      );
    },
    save: () => el(InnerBlocks.Content, null)
  });
})(window.wp);
