// Exercise the actual Hero save functions with WordPress's template and block APIs.
const assert = require('assert/strict');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const { JSDOM } = require('jsdom');
const dom = new JSDOM('');
Object.assign(global, { window: dom.window, document: dom.window.document, MutationObserver: dom.window.MutationObserver });
Object.defineProperty(global, 'navigator', { value: dom.window.navigator, configurable: true });
const blocks = require('@wordpress/blocks');
const element = require('@wordpress/element');
const root = path.join(__dirname, '../modfarm-theme/modfarm-theme');
const definitions = {};
let variation;
const Placeholder = () => null;
const InnerBlocks = () => null;
// This is the same inner-content serializer used by InnerBlocks.Content.
InnerBlocks.Content = () => blocks.__unstableGetInnerBlocksProps().children;
InnerBlocks.ButtonBlockAppender = Placeholder;
const blockEditor = {
  InnerBlocks, useBlockProps: value => value, InspectorControls: Placeholder,
  MediaUpload: Placeholder, MediaUploadCheck: Placeholder
};
window.modfarmHeroControls = { resolved: () => ({ preset: 'none', color: '#fff' }), Controls: Placeholder };
window.wp = {
  element, blockEditor, i18n: { __: text => text },
  components: new Proxy({}, { get: () => Placeholder }),
  blocks: Object.assign({}, blocks, {
    registerBlockType(name, definition) {
      const slug = name.split('/')[1];
      const metadata = JSON.parse(fs.readFileSync(path.join(root, 'blocks', slug, 'block.json'), 'utf8'));
      definitions[name] = definition;
      return blocks.registerBlockType(name, Object.assign({}, metadata, definition));
    },
    registerBlockVariation(name, settings) { variation = settings; }
  })
};
for (const slug of ['hero-area', 'hero-cover']) {
  vm.runInThisContext(fs.readFileSync(path.join(root, 'blocks', slug, 'index.js'), 'utf8'), { filename: slug + '/index.js' });
}
for (const name of ['modfarm/navigation-menu', 'core/post-title', 'modfarm/book-page-series', 'modfarm/book-author-credit', 'modfarm/format-icons']) {
  blocks.registerBlockType(name, { apiVersion: 3, title: name, category: 'text', attributes: { level: { type: 'number' } }, save: () => null });
}
blocks.registerBlockType('core/paragraph', {
  apiVersion: 3, title: 'Paragraph', category: 'text', attributes: { content: { type: 'string', source: 'html', selector: 'p' } },
  save: ({ attributes }) => element.createElement('p', null, attributes.content)
});
blocks.registerBlockType('core/group', {
  apiVersion: 3, title: 'Group', category: 'design',
  save: () => element.createElement('div', { className: 'wp-block-group' }, element.createElement(InnerBlocks.Content))
});
function find(node, type) {
  if (!node || typeof node !== 'object') return null;
  if (node.type === type) return node;
  for (const child of element.Children.toArray(node.props?.children)) {
    const result = find(child, type);
    if (result) return result;
  }
  return null;
}
function editableProps(structuredLayout) {
  return find(definitions['modfarm/hero-cover'].edit({ attributes: { structuredLayout }, setAttributes() {} }), InnerBlocks).props;
}
function outline(items) {
  return items.map(block => ({ name: block.name, content: block.attributes.content, children: outline(block.innerBlocks) }));
}
const areas = blocks.createBlocksFromInnerBlocksTemplate(variation.innerBlocks);
assert.equal(areas[0].attributes.area, 'navigation');
assert.equal(areas[1].innerBlocks[0].name, 'core/post-title');
areas[0].innerBlocks.push(blocks.createBlock('core/paragraph', { content: 'Additional navigation note' }));
areas[1].innerBlocks.push(
  blocks.createBlock('modfarm/book-page-series'),
  blocks.createBlock('modfarm/book-author-credit'),
  blocks.createBlock('modfarm/format-icons'),
  blocks.createBlock('core/group', {}, [blocks.createBlock('core/paragraph', { content: 'Custom book information' })])
);
// Reproduce the reported loss with the former live nested starter template.
assert.equal(blocks.doBlocksMatchTemplate(areas, variation.innerBlocks), false);
const reset = blocks.synchronizeBlocksWithTemplate(areas, variation.innerBlocks);
assert.equal(reset[1].innerBlocks.length, 1, 'Former template should reproduce title-only reset');
assert.equal(reset[0].innerBlocks.length, 1, 'Former template should reproduce navigation-only reset');

for (const structured of [true, false]) {
  const props = editableProps(structured);
  assert.equal(props.templateLock, false, 'Hero still enforces a structural lock');
  assert.equal(props.template, undefined, 'Starter template must only be applied at insertion');
  const children = areas.concat(blocks.createBlock('core/paragraph', { content: 'Extra Hero block' }));
  const expected = outline(children);
  let hero = blocks.createBlock('modfarm/hero-cover', { structuredLayout: structured }, children);
  for (let round = 0; round < 3; round++) {
    hero.innerBlocks = blocks.synchronizeBlocksWithTemplate(hero.innerBlocks, props.template);
    const saved = blocks.serialize([hero]);
    assert.ok(saved.includes('Custom book information'));
    hero = blocks.parse(saved)[0];
    assert.equal(hero.isValid, true, 'Saved Hero became invalid');
    assert.deepEqual(outline(hero.innerBlocks), expected, 'Added content was lost after save/reload');
  }
}
console.log('PASS real WordPress template regression and three save/reload cycles: series, author, formats, nested custom content, navigation additions and extra Hero blocks');
