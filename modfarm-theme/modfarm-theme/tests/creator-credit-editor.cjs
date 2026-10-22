// Exercise editor controls without a running WordPress installation.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.join(__dirname, '..');
const schema = JSON.parse(fs.readFileSync(path.join(root, 'blocks/creator-credit/block.json')));
const defaults = Object.fromEntries(Object.entries(schema.attributes).map(([key, value]) => [key, value.default]));
let definition;
const componentNames = ['PanelBody', 'SelectControl', 'TextControl', 'RangeControl', 'ToggleControl', 'ColorPalette', 'Button', 'Spinner', 'Notice', 'ComboboxControl'];
const wp = {
  i18n: { __: text => text },
  blocks: { registerBlockType: (name, settings) => { definition = settings; } },
  blockEditor: { useBlockProps: () => ({}), InspectorControls: 'InspectorControls', RichText: 'RichText' },
  components: Object.fromEntries(componentNames.map(name => [name, name])),
  element: {
    Fragment: 'Fragment', createElement: (type, props, ...children) => ({ type, props: props || {}, children }),
    useEffect: () => {}, useState: value => [value, () => {}], useMemo: callback => callback()
  },
  data: { select: () => ({ getSettings: () => ({ colors: [] }) }), subscribe: () => () => {} },
  serverSideRender: 'ServerSideRender'
};
vm.runInNewContext(fs.readFileSync(path.join(root, 'blocks/creator-credit/index.js'), 'utf8'), { window: { wp } });
function render(attributes = defaults) {
  let update;
  const tree = definition.edit({ attributes, setAttributes: value => { update = value; } });
  const nodes = [];
  function visit(node) {
    if (!node || typeof node !== 'object') return;
    if (Array.isArray(node)) return node.forEach(visit);
    nodes.push(node);
    (node.children || []).forEach(visit);
  }
  visit(tree);
  return { nodes, getUpdate: () => update };
}
const original = render();
for (const label of ['Show portrait', 'Show name', 'Show role label', 'Show description']) {
  assert.equal(original.nodes.find(node => node.props.label === label).props.checked, true, label);
}
assert.equal(original.nodes.find(node => node.props.label === 'Show author social links').props.checked, false);
for (const [label, key] of [['Show portrait', 'showImage'], ['Show name', 'showName'], ['Show role label', 'showRole']]) {
  original.nodes.find(node => node.props.label === label).props.onChange(false);
  assert.equal(original.getUpdate()[key], false);
}
const custom = '<p>A <strong>short</strong> biography.</p>';
original.nodes.find(node => node.type === 'RichText').props.onChange(custom);
assert.equal(original.getUpdate().descriptionOverride, custom);
const editedAttributes = { ...defaults, descriptionOverride: custom, showImage: false, showName: false };
const edited = render(editedAttributes);
assert.equal(edited.nodes.find(node => node.type === 'ServerSideRender').props.attributes, editedAttributes);
assert.equal(edited.nodes.find(node => node.type === 'RichText').props.value, custom);
assert.equal(edited.nodes.some(node => node.props.label === 'Image size (px)'), false);
assert.equal(edited.nodes.some(node => node.props.label === 'Link name to archive'), false);
edited.nodes.find(node => node.type === 'Button' && node.children.includes('Use saved biography')).props.onClick();
assert.equal(edited.getUpdate().descriptionOverride, '');
console.log('PASS Creator Credit defaults, independent toggles, rich text override, SSR attributes, and reset');
