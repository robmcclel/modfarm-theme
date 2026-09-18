const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const filters = {};
const wp = {
  hooks: { addFilter: (hook, namespace, callback) => { filters[hook] = callback; } },
  element: { createElement: (type, props, ...children) => ({ type, props, children }), Fragment: 'Fragment' },
  i18n: { __: value => value },
  blockEditor: { InspectorControls: 'InspectorControls' },
  components: { PanelBody: 'PanelBody', SelectControl: 'SelectControl' }
};
const config = { blocks: ['modfarm/taxonomy-grid', 'modfarm/featured-book'], options: [
  { label: 'All languages', value: '' }, { label: 'English (including unassigned)', value: 'english' },
  { label: 'French', value: '20' }
] };
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../assets/js/book-language-filter.js'), 'utf8'), {
  window: { wp, modfarmBookLanguageFilter: config }
});
const settings = { attributes: { taxonomy: { type: 'string' } } };
const registered = filters['blocks.registerBlockType'](settings, 'modfarm/taxonomy-grid');
assert.equal(registered.attributes.bookLanguage.default, '');
assert.equal(registered.attributes.taxonomy, settings.attributes.taxonomy);
assert.equal(filters['blocks.registerBlockType'](settings, 'core/paragraph'), settings);
let updated;
const props = { name: 'modfarm/taxonomy-grid', isSelected: true, attributes: { bookLanguage: '20' }, setAttributes: value => { updated = value; } };
const tree = filters['editor.BlockEdit']('OriginalEditor')(props);
assert.equal(tree.children[0].props.attributes.bookLanguage, '20', 'Original editor receives selection for server preview');
const select = tree.children[1].children[0].children[0];
assert.equal(select.props.value, '20');
select.props.onChange('english');
assert.equal(updated.bookLanguage, 'english');
props.attributes.bookLanguage = '999';
const deleted = filters['editor.BlockEdit']('OriginalEditor')(props).children[1].children[0].children[0];
assert.equal(deleted.props.options.at(-1).value, '999', 'Deleted selections remain visible instead of silently showing All');
console.log('Book language editor checks passed.');
