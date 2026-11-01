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
for (const label of ['Show image', 'Show name', 'Show role label', 'Show description']) {
  assert.equal(original.nodes.find(node => node.props.label === label).props.checked, true, label);
}
assert.equal(original.nodes.find(node => node.props.label === 'Show social links').props.checked, false);
for (const [label, key] of [['Show image', 'showImage'], ['Show name', 'showName'], ['Show role label', 'showRole']]) {
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
assert.equal(edited.nodes.some(node => node.props.label === 'Link name to profile page'), false);
edited.nodes.find(node => node.type === 'Button' && node.children.includes('Use saved biography')).props.onClick();
assert.equal(edited.getUpdate().descriptionOverride, '');
console.log('PASS Creator Credit defaults, independent toggles, rich text override, SSR attributes, and reset');

const identity = render({...defaults, source:'identity', identityId:'publisher-a'});
assert.equal(identity.nodes.some(node => node.props.label === 'Creator term'), false);
assert.equal(identity.nodes.find(node => node.props.label === 'Identity').props.value, 'publisher-a');
identity.nodes.find(node => node.props.label === 'Identity').props.onChange('publisher-b');
assert.equal(identity.getUpdate().identityId, 'publisher-b');
identity.nodes.find(node => node.props.label === 'Content source').props.onChange('taxonomy');
assert.equal(identity.getUpdate().source, 'taxonomy');
assert.equal(identity.nodes.find(node => node.type === 'RichText').props.value, '');
console.log('PASS Creator Credit Site Identity selector and source switching');
const socialSchema = JSON.parse(fs.readFileSync(path.join(root, 'blocks/author-social-links/block.json')));
const socialDefaults = Object.fromEntries(Object.entries(socialSchema.attributes).map(([key,value])=>[key,value.default]));
vm.runInNewContext(fs.readFileSync(path.join(root, 'blocks/author-social-links/index.js'), 'utf8'), {window:{wp}});
const social = render({...socialDefaults, source:'identity'});
assert.equal(social.nodes.some(node=>node.props.label==='Author'), false);
assert.equal(social.nodes.find(node=>node.props.label==='Identity').props.value, '');
social.nodes.find(node=>node.props.label==='Identity').props.onChange('sia');
assert.equal(social.getUpdate().identityId,'sia');
assert.equal(social.nodes.find(node=>node.props.label==='Icon size (px)').props.value,36);
assert.equal(render(socialDefaults).nodes.some(node=>node.props.label==='Author'),true);
console.log('PASS Author Social Links identity selection, existing author default and icon controls');
