(function (wp, config) {
  if (!wp || !config || !config.enabled) return;

  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editPost || {};
  const { createElement: el, Fragment, useState } = wp.element;
  const { PanelRow, Notice, Button, SelectControl } = wp.components;
  const { select, dispatch } = wp.data;
  const { parse, serialize } = wp.blocks;

  if (!registerPlugin || !PluginDocumentSettingPanel || !select || !dispatch || !parse || !serialize) return;

  const initialData = config.summary || {};

  function zoneLabel(slot) {
    return `${slot.charAt(0).toUpperCase()}${slot.slice(1)} Zone`;
  }

  function replaceZoneInBlocks(blocks, slot, pattern) {
    let replaced = false;

    const nextBlocks = blocks.map(function (block) {
      if (block.blockName === 'modfarm/zone' && block.attrs && block.attrs.slot === slot && !replaced) {
        replaced = true;
        return {
          ...block,
          attrs: {
            ...block.attrs,
            pattern: pattern.value
          },
          innerBlocks: parse(pattern.content)
        };
      }

      if (Array.isArray(block.innerBlocks) && block.innerBlocks.length) {
        const child = replaceZoneInBlocks(block.innerBlocks, slot, pattern);
        if (child.replaced) {
          replaced = true;
          return {
            ...block,
            innerBlocks: child.blocks
          };
        }
      }

      return block;
    });

    return {
      blocks: nextBlocks,
      replaced: replaced
    };
  }

  function zoneRow(slot, data, setData, editors, selections, setSelections, notices, setNotices) {
    const zones = data.zones || {};
    const actions = (data.actions && data.actions.zones) || {};
    const zone = zones[slot] || {};
    const action = actions[slot] || {};
    const selectState = selections[slot] || { open: false, value: '' };
    const notice = notices[slot] || '';
    const canReplace = !!action.enabled;
    const mode = data.actions && data.actions.mode ? data.actions.mode : 'disabled';
    const hasPatterns = Array.isArray(action.patterns) && action.patterns.length > 0;

    function openSelector() {
      setSelections((prev) => ({
        ...prev,
        [slot]: {
          open: true,
          value: zone.pattern || (hasPatterns ? action.patterns[0].value : '')
        }
      }));
      setNotices((prev) => ({ ...prev, [slot]: '' }));
    }

    function closeSelector() {
      setSelections((prev) => ({
        ...prev,
        [slot]: {
          open: false,
          value: ''
        }
      }));
    }

    function applyReplacement() {
      const selectedPattern = (action.patterns || []).find((item) => item.value === selectState.value);
      if (!selectedPattern) {
        setNotices((prev) => ({ ...prev, [slot]: 'Choose a pattern first.' }));
        return;
      }

      if (mode === 'zoned') {
        const currentContent = editors.selectEditor().getEditedPostContent();
        const parsedBlocks = parse(currentContent);
        const replaced = replaceZoneInBlocks(parsedBlocks, slot, selectedPattern);

        if (!replaced.replaced) {
          setNotices((prev) => ({ ...prev, [slot]: `${zoneLabel(slot)} was not found in the current content.` }));
          return;
        }

        editors.dispatchEditor().editPost({
          content: serialize(replaced.blocks)
        });

        setData((prev) => ({
          ...prev,
          zones: {
            ...prev.zones,
            [slot]: {
              ...prev.zones[slot],
              present: true,
              pattern: selectedPattern.value
            }
          }
        }));
      } else if (mode === 'hybrid') {
        const metaKey = action.meta_key || '';
        if (!metaKey) {
          setNotices((prev) => ({ ...prev, [slot]: 'No Hybrid override key is registered for this zone.' }));
          return;
        }

        const currentMeta = editors.selectEditor().getEditedPostAttribute('meta') || {};
        editors.dispatchEditor().editPost({
          meta: {
            ...currentMeta,
            [metaKey]: selectedPattern.value
          }
        });

        setData((prev) => ({
          ...prev,
          zones: {
            ...prev.zones,
            [slot]: {
              ...prev.zones[slot],
              pattern: selectedPattern.value
            }
          }
        }));
      }

      setNotices((prev) => ({ ...prev, [slot]: '' }));
      closeSelector();
    }

    const notes = [];
    if (slot === 'body') {
      notes.push(`Contains content-slot: ${zone.contains_content_slot ? 'Yes' : 'No'}`);
    }
    if (mode === 'hybrid' && (slot === 'header' || slot === 'footer')) {
      notes.push('Hybrid dynamic path');
    }

    return el('div', { className: 'mf-ppb-zone-panel__zone-row' },
      el('div', { className: 'mf-ppb-zone-panel__zone-head' },
        el('strong', null, zoneLabel(slot)),
        el('span', null, zone.present ? 'Present' : 'Not present')
      ),
      el('div', { className: 'mf-ppb-zone-panel__meta' },
        el('div', null, `Pattern: ${zone.pattern || 'None recorded'}`),
        el('div', null, `Lock: ${zone.locked ? 'Locked' : 'Unlocked'}`),
        notes.map((note, index) => el('div', { key: `${slot}-note-${index}` }, note))
      ),
      (slot === 'header' || slot === 'footer') ? el('div', { className: 'mf-ppb-zone-panel__actions' },
        el(Button, {
          variant: 'secondary',
          onClick: openSelector,
          disabled: !canReplace || !hasPatterns
        }, 'Replace')
      ) : null,
      notice ? el('p', { className: 'mf-ppb-zone-panel__status' }, notice) : null,
      selectState.open ? el('div', { className: 'mf-ppb-zone-panel__selector' },
        el(SelectControl, {
          label: `Replace ${zoneLabel(slot)} With`,
          value: selectState.value,
          options: (action.patterns || []).map((pattern) => ({
            value: pattern.value,
            label: pattern.label
          })),
          onChange: function (value) {
            setSelections((prev) => ({
              ...prev,
              [slot]: {
                ...prev[slot],
                value: value
              }
            }));
          }
        }),
        el('div', { className: 'mf-ppb-zone-panel__selector-buttons' },
          el(Button, {
            variant: 'primary',
            onClick: applyReplacement
          }, 'Apply'),
          el(Button, {
            variant: 'tertiary',
            onClick: closeSelector
          }, 'Cancel')
        )
      ) : null
    );
  }

  function Panel() {
    const [data, setData] = useState(initialData);
    const [selections, setSelections] = useState({
      header: { open: false, value: '' },
      footer: { open: false, value: '' }
    });
    const [notices, setNotices] = useState({
      header: '',
      footer: ''
    });
    const editors = {
      selectEditor: () => select('core/editor'),
      dispatchEditor: () => dispatch('core/editor')
    };

    return el(PluginDocumentSettingPanel, {
      name: 'modfarm-ppb-zones',
      title: 'PPB Zones',
      className: 'mf-ppb-zone-panel'
    },
      el(Fragment, {},
        el(PanelRow, {},
          el('div', { className: 'mf-ppb-zone-panel__summary' },
            el('div', null, `Content State: ${data.content_state || 'Unknown'}`),
            el('div', null, `Layout Mode: ${data.layout_mode || 'Unknown'}`)
          )
        ),
        el(PanelRow, {},
          zoneRow('header', data, setData, editors, selections, setSelections, notices, setNotices)
        ),
        el(PanelRow, {},
          zoneRow('body', data, setData, editors, selections, setSelections, notices, setNotices)
        ),
        el(PanelRow, {},
          zoneRow('footer', data, setData, editors, selections, setSelections, notices, setNotices)
        ),
        el(PanelRow, {},
          el('div', { className: 'mf-ppb-zone-panel__zone-row' },
            el('div', { className: 'mf-ppb-zone-panel__zone-head' },
              el('strong', null, 'Data Zone'),
              el('span', null, 'Future / not active')
            )
          )
        ),
        el(Notice, {
          status: 'info',
          isDismissible: false
        }, 'Local PPB actions are limited to Header and Footer. Body replacement, migration, and Apply All are not active here yet.')
      )
    );
  }

  registerPlugin('modfarm-ppb-zones-panel', {
    render: Panel
  });
})(window.wp, window.ModFarmPPBZonesPanel || {});
