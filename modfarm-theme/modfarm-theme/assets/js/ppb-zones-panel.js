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

  function resolveZoneSlot(attributes) {
    if (attributes && typeof attributes.slot === 'string' && attributes.slot.trim()) {
      return attributes.slot.trim();
    }

    return 'body';
  }

  function findZoneBlock(blocks, slot) {
    for (let i = 0; i < blocks.length; i += 1) {
      const block = blocks[i];

      if (block.name === 'modfarm/zone' && resolveZoneSlot(block.attributes || {}) === slot) {
        return block;
      }

      if (Array.isArray(block.innerBlocks) && block.innerBlocks.length) {
        const nested = findZoneBlock(block.innerBlocks, slot);
        if (nested) {
          return nested;
        }
      }
    }

    return null;
  }

  function cloneBlocks(blocks) {
    if (!Array.isArray(blocks) || !blocks.length) {
      return [];
    }

    return parse(serialize(blocks));
  }

  function getSlotId(attributes) {
    if (attributes && typeof attributes.slot === 'string' && attributes.slot.trim()) {
      return attributes.slot.trim();
    }

    return 'main';
  }

  function collectSlotPayloads(blocks, payloads = {}) {
    if (!Array.isArray(blocks)) {
      return payloads;
    }

    blocks.forEach((block) => {
      if (!block || typeof block !== 'object') {
        return;
      }

      if (block.name === 'modfarm/content-slot') {
        const slotId = getSlotId(block.attributes || {});
        if (Array.isArray(block.innerBlocks) && block.innerBlocks.length) {
          payloads[slotId] = cloneBlocks(block.innerBlocks);
        }
      }

      if (Array.isArray(block.innerBlocks) && block.innerBlocks.length) {
        collectSlotPayloads(block.innerBlocks, payloads);
      }
    });

    return payloads;
  }

  function zoneBlocksContainContentSlot(blocks) {
    if (!Array.isArray(blocks)) {
      return false;
    }

    for (let i = 0; i < blocks.length; i += 1) {
      const block = blocks[i];
      if (!block || typeof block !== 'object') {
        continue;
      }

      if (block.name === 'modfarm/content-slot') {
        return true;
      }

      if (Array.isArray(block.innerBlocks) && block.innerBlocks.length && zoneBlocksContainContentSlot(block.innerBlocks)) {
        return true;
      }
    }

    return false;
  }

  function hydrateIncomingContentSlots(blocks, slotPayloads) {
    if (!Array.isArray(blocks) || !blocks.length) {
      return [];
    }

    return blocks.map((block) => {
      if (!block || typeof block !== 'object') {
        return block;
      }

      const nextBlock = {
        ...block,
        attributes: block.attributes ? { ...block.attributes } : {},
        innerBlocks: Array.isArray(block.innerBlocks) ? cloneBlocks(block.innerBlocks) : []
      };

      if (nextBlock.name === 'modfarm/content-slot') {
        const slotId = getSlotId(nextBlock.attributes || {});
        const payload = slotPayloads[slotId];
        const isEmpty = !Array.isArray(nextBlock.innerBlocks) || nextBlock.innerBlocks.length === 0;
        if (isEmpty && Array.isArray(payload) && payload.length) {
          nextBlock.innerBlocks = cloneBlocks(payload);
        }
        return nextBlock;
      }

      if (Array.isArray(nextBlock.innerBlocks) && nextBlock.innerBlocks.length) {
        nextBlock.innerBlocks = hydrateIncomingContentSlots(nextBlock.innerBlocks, slotPayloads);
      }

      return nextBlock;
    });
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
    const canToggleLock = mode === 'zoned' && ['header', 'body', 'footer'].includes(slot) && !!zone.present;

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
        const blockTree = editors.selectBlocks().getBlocks();
        const zoneBlock = findZoneBlock(blockTree, slot);

        if (!zoneBlock || !zoneBlock.clientId) {
          setNotices((prev) => ({ ...prev, [slot]: `${zoneLabel(slot)} was not found in the current content.` }));
          return;
        }

        const outgoingSlotPayloads = collectSlotPayloads(zoneBlock.innerBlocks || []);
        const incomingBlocks = hydrateIncomingContentSlots(parse(selectedPattern.content), outgoingSlotPayloads);

        editors.dispatchBlocks().replaceInnerBlocks(zoneBlock.clientId, incomingBlocks, false);
        editors.dispatchBlocks().updateBlockAttributes(zoneBlock.clientId, {
          pattern: selectedPattern.value
        });

        setData((prev) => ({
          ...prev,
          zones: {
            ...prev.zones,
            [slot]: {
              ...prev.zones[slot],
              present: true,
              pattern: selectedPattern.value,
              contains_content_slot: zoneBlocksContainContentSlot(incomingBlocks)
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

    function toggleLock() {
      if (!canToggleLock) {
        return;
      }

      const blockTree = editors.selectBlocks().getBlocks();
      const zoneBlock = findZoneBlock(blockTree, slot);

      if (!zoneBlock || !zoneBlock.clientId) {
        setNotices((prev) => ({ ...prev, [slot]: `${zoneLabel(slot)} was not found in the current content.` }));
        return;
      }

      const nextLocked = !zone.locked;
      editors.dispatchBlocks().updateBlockAttributes(zoneBlock.clientId, {
        locked: nextLocked
      });

      setData((prev) => ({
        ...prev,
        zones: {
          ...prev.zones,
          [slot]: {
            ...prev.zones[slot],
            locked: nextLocked
          }
        },
        actions: {
          ...prev.actions,
          zones: {
            ...prev.actions.zones,
            [slot]: {
              ...prev.actions.zones[slot],
              enabled: nextLocked ? false : (!!hasPatterns)
            }
          }
        }
      }));

      setNotices((prev) => ({ ...prev, [slot]: '' }));
      if (nextLocked) {
        closeSelector();
      }
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
        el('div', null,
          'Lock: ',
          zone.locked
            ? el('span', { className: 'mf-ppb-zone-panel__badge' }, 'Locked')
            : 'Unlocked'
        ),
        notes.map((note, index) => el('div', { key: `${slot}-note-${index}` }, note))
      ),
      (slot === 'header' || slot === 'body' || slot === 'footer') ? el('div', { className: 'mf-ppb-zone-panel__actions' },
        el(Button, {
          variant: 'secondary',
          onClick: openSelector,
          disabled: !canReplace || !hasPatterns
        }, 'Replace'),
        canToggleLock ? el(Button, {
          variant: zone.locked ? 'primary' : 'tertiary',
          onClick: toggleLock
        }, zone.locked ? 'Unlock' : 'Lock') : null
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
      body: { open: false, value: '' },
      footer: { open: false, value: '' }
    });
    const [notices, setNotices] = useState({
      header: '',
      body: '',
      footer: ''
    });
    const editors = {
      selectEditor: () => select('core/editor'),
      dispatchEditor: () => dispatch('core/editor'),
      selectBlocks: () => select('core/block-editor'),
      dispatchBlocks: () => dispatch('core/block-editor')
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
        }, 'Local PPB actions can now replace and lock zoned Header, Body, and Footer regions. Hybrid still exposes local PPB control for Header and Footer only.')
      )
    );
  }

  registerPlugin('modfarm-ppb-zones-panel', {
    render: Panel
  });
})(window.wp, window.ModFarmPPBZonesPanel || {});
