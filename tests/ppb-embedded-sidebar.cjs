const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const path = require('node:path');
const code = fs.readFileSync(path.join(__dirname, '../modfarm-theme/modfarm-theme/assets/js/ppb-zones-panel.js'), 'utf8');
function render(embedded, modernNamespace) {
    let plugin;
    const Sidebar = function () {}, Document = function () {};
    const wp = {
        plugins: { registerPlugin: (name, definition) => { plugin = definition; } },
        editPost: { PluginSidebar: Sidebar, PluginDocumentSettingPanel: Document },
        editor: modernNamespace ? {} : undefined,
        element: { createElement: (type, props, ...children) => ({type, props, children}), Fragment: 'fragment', useState: value => [value, () => {}], useEffect: () => {}, useRef: value => ({current: value}) },
        components: {PanelRow: 'row', Notice: 'notice', Button: 'button', SelectControl: 'select'},
        data: {select: () => ({}), dispatch: () => ({})},
        blocks: {parse: () => [], serialize: () => ''}
    };
    const window = {wp, ModFarmOSEmbeddedEditor: embedded, ModFarmPPBZonesPanel: {enabled: true}, setTimeout, clearTimeout};
    vm.runInNewContext(code, {window});
    assert(plugin, 'PPB plugin should be registered');
    const result = plugin.render();
    assert.equal(result.type, embedded ? Sidebar : Document);
    assert.equal(result.props.name, embedded ? 'modfarm-ppb' : 'modfarm-ppb-zones');
}
render(true, true); render(false, true); render(true, false);
console.log('PPB sidebar registration: 3 scenarios passed.');
