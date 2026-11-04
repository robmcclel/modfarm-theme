const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const path = require('node:path');

const code = fs.readFileSync(
    path.join(__dirname, '../modfarm-theme/modfarm-theme/assets/js/ppb-zones-panel.js'),
    'utf8'
);

function render(embedded, modernNamespace, exposePreview) {
    let plugin;
    let previewComponent;
    let parseCount = 0;
    let memoImplementation = factory => factory();
    const Sidebar = 'sidebar';
    const Document = 'document';
    const pattern = {
        value: 'modfarm/test-pattern',
        label: 'Test Pattern',
        content: '<!-- wp:paragraph --><p>Test</p><!-- /wp:paragraph -->'
    };
    const summary = {
        content_state: 'Zoned',
        layout_mode: 'Full PPB',
        zones: {
            header: { present: true, pattern: pattern.value },
            body: { present: true, pattern: '' },
            footer: { present: true, pattern: '' }
        },
        actions: {
            mode: 'zoned',
            convert: { enabled: false },
            zones: {
                header: { enabled: true, patterns: [pattern] },
                body: { enabled: false, patterns: [] },
                footer: { enabled: false, patterns: [] }
            }
        }
    };
    const createElement = (type, props, ...children) => {
        if (typeof type === 'function') {
            if (type.name === 'PatternPreview') {
                previewComponent = type;
                return { type: 'pattern-preview', props };
            }
            return type({ ...(props || {}), children });
        }
        return { type, props: props || {}, children };
    };
    const wp = {
        plugins: { registerPlugin: (name, definition) => { plugin = definition; } },
        editPost: { PluginSidebar: Sidebar, PluginDocumentSettingPanel: Document },
        editor: modernNamespace ? {} : undefined,
        element: {
            createElement,
            Fragment: 'fragment',
            useState: value => [value, () => {}],
            useMemo: (factory, deps) => memoImplementation(factory, deps),
            useEffect: () => {},
            useRef: value => ({ current: value })
        },
        components: {
            PanelRow: 'row',
            Notice: 'notice',
            Button: 'button',
            SelectControl: 'select',
            Modal: 'modal',
            TextControl: 'text'
        },
        data: { select: () => ({}), dispatch: () => ({}) },
        blocks: { parse: () => [{ clientId: 'preview-' + ++parseCount }], serialize: () => '' },
        blockEditor: exposePreview
            ? { BlockPreview: props => ({ type: 'block-preview', props }) }
            : {}
    };
    const window = {
        wp,
        ModFarmOSEmbeddedEditor: embedded,
        ModFarmPPBZonesPanel: { enabled: true, summary },
        setTimeout,
        clearTimeout
    };

    vm.runInNewContext(code, { window });
    assert(plugin, 'PPB plugin should be registered');

    const result = plugin.render();
    assert.equal(result.type, embedded ? Sidebar : Document);
    assert.equal(result.props.name, embedded ? 'modfarm-ppb' : 'modfarm-ppb-zones');

    if (embedded) {
        assert(previewComponent, 'embedded cards should render the shared preview component');
        // Simulate consecutive renders of one mounted thumbnail, retaining hook
        // state as React does when the surrounding selection/zone state changes.
        let memo;
        let dependencies;
        memoImplementation = (factory, deps) => {
            if (!dependencies || deps.some((value, index) => value !== dependencies[index])) {
                memo = factory();
                dependencies = deps;
            }
            return memo;
        };
        const first = previewComponent({ content: pattern.content });
        const second = previewComponent({ content: pattern.content });
        if (exposePreview) {
            assert.strictEqual(first.props.blocks, second.props.blocks,
                'selection updates must retain preview blocks and their client IDs');
            assert.equal(parseCount, 1, 'unchanged thumbnails must only be parsed once');
            const changed = previewComponent({ content: pattern.content + '\n' });
            assert.notStrictEqual(changed.props.blocks, first.props.blocks,
                'changed pattern markup must refresh its preview');
            assert.equal(parseCount, 2);
        } else {
            assert.equal(parseCount, 0, 'unavailable previews must not parse blocks');
        }
    }
}

render(true, true, true);
render(true, true, false);
render(false, true, false);
render(true, false, false);

console.log('PPB sidebar registration and pattern previews: 4 scenarios passed.');
