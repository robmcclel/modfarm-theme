(function (wp, config) {
  if (!wp || !config || !config.enabled) return;

  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editPost || {};
  const { createElement: el, Fragment } = wp.element;
  const { PanelRow, Notice } = wp.components;

  if (!registerPlugin || !PluginDocumentSettingPanel) return;

  const data = config.summary || {};
  const zones = data.zones || {};

  function zoneRow(label, zone, extra) {
    const value = zone && zone.present ? 'Present' : 'Not present';
    const pattern = zone && zone.pattern ? zone.pattern : 'None recorded';
    const locked = zone && zone.locked ? 'Locked' : 'Unlocked';
    const slotNote = extra || null;

    return el('div', { className: 'mf-ppb-zone-panel__zone-row' },
      el('div', { className: 'mf-ppb-zone-panel__zone-head' },
        el('strong', null, label),
        el('span', null, value)
      ),
      el('div', { className: 'mf-ppb-zone-panel__meta' },
        el('div', null, `Pattern: ${pattern}`),
        el('div', null, `Lock: ${locked}`),
        slotNote ? el('div', null, slotNote) : null
      )
    );
  }

  function Panel() {
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
          zoneRow('Header Zone', zones.header)
        ),
        el(PanelRow, {},
          zoneRow(
            'Body Zone',
            zones.body,
            `Contains content-slot: ${zones.body && zones.body.contains_content_slot ? 'Yes' : 'No'}`
          )
        ),
        el(PanelRow, {},
          zoneRow('Footer Zone', zones.footer)
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
        }, 'Read-only Phase 1 view. No zone replacement or migration actions are active here yet.')
      )
    );
  }

  registerPlugin('modfarm-ppb-zones-panel', {
    render: Panel
  });
})(window.wp, window.ModFarmPPBZonesPanel || {});
