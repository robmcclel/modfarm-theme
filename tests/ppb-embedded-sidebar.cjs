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

// Exercise the pattern-card component, not just the plugin's outer container.
function patternPreviewScenario(previewAvailable) {
    let plugin;
    let states = [], cursor = 0;
    const Preview = function Preview() {};
    const pattern = { value: 'modfarm/page-clear', label: 'Clear page', content: '<!-- wp:paragraph --><p>Sample</p><!-- /wp:paragraph -->' };
    const parsed = [{ name: 'core/paragraph', attributes: { content: 'Sample' } }];
    const wp = {
        plugins: { registerPlugin: (name, definition) => { plugin = definition; } },
        editor: { PluginSidebar: 'sidebar', PluginDocumentSettingPanel: 'document' },
        blockEditor: previewAvailable ? { BlockPreview: Preview } : undefined,
        element: {
            createElement: (type, props, ...children) => ({ type, props: props || {}, children }),
            Fragment: 'fragment', useEffect: () => {}, useRef: value => ({ current: value }),
            useState: value => { const index = cursor++; if (!(index in states)) states[index] = value; return [states[index], next => { states[index] = typeof next === 'function' ? next(states[index]) : next; }]; }
        },
        components: { PanelRow: 'row', Notice: 'notice', Button: 'button', SelectControl: 'select', Modal: 'modal', TextControl: 'text' },
        data: { select: () => ({}), dispatch: () => ({}) },
        blocks: { parse: () => parsed, serialize: () => '' }
    };
    const window = { wp, ModFarmOSEmbeddedEditor: true, ModFarmPPBZonesPanel: { enabled: true, summary: { actions: { zones: { header: { patterns: [pattern] } } } } } };
    vm.runInNewContext(code, { window });
    function flatten(node) {
        if (Array.isArray(node)) return node.flatMap(flatten);
        if (!node || typeof node !== 'object') return [];
        return [node, ...flatten(node.children)];
    }
    const cards = flatten(plugin.render()).find(node => typeof node.type === 'function' && node.type.name === 'PatternCards');
    assert(cards, 'Embedded PPB should expose pattern cards');
    states = []; cursor = 0;
    let output = cards.type(cards.props);
    const nodes = flatten(output);
    if (previewAvailable) {
        const thumbnail = nodes.find(node => node.type === Preview);
        assert(thumbnail, 'Thumbnail should use WordPress BlockPreview');
        assert.equal(thumbnail.props.blocks, parsed);
    } else {
        assert(nodes.some(node => node.children.includes('Preview unavailable')), 'Missing preview API should have a fallback');
    }
    nodes.find(node => node.type === 'button' && node.children.includes('Preview')).props.onClick();
    cursor = 0; output = cards.type(cards.props);
    assert(flatten(output).some(node => node.type === 'modal' && node.props.title === pattern.label), 'Expanded preview opens without crashing');
    if (previewAvailable) assert.equal(flatten(output).filter(node => node.type === Preview).length, 2);
    else assert.equal(flatten(output).filter(node => node.children.includes('Preview unavailable')).length, 2);
    let selected;
    cursor = 0; output = cards.type({ ...cards.props, onChange: value => { selected = value; } });
    flatten(output).find(node => node.children.includes('Select pattern')).props.onClick();
    assert.equal(selected, pattern.value, 'Pattern selection works with or without previews');
}
patternPreviewScenario(true);
patternPreviewScenario(false);
console.log('PPB pattern thumbnails, expanded previews, fallback and selection: 2 scenarios passed.');

