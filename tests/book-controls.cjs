// Run: node tests/book-controls.cjs
const fs = require('fs');
const vm = require('vm');
const assert = require('node:assert/strict');
const path = require('path');
const root = path.join(__dirname, '../modfarm-theme/modfarm-theme');
let changes;
const window = { wp: {
  element: { createElement: (type, props, ...children) => ({ type, props, children }), Fragment: 'fragment' },
  components: { ToggleControl: 'toggle', SelectControl: 'select' },
  i18n: { __: (s) => s },
  data: { useSelect: (fn) => fn(() => ({ getEntityRecords: () => [{ id: 7, name: 'Audiobook' }] })) }
} };
vm.runInNewContext(fs.readFileSync(path.join(root, 'assets/js/book-options.js'), 'utf8'), { window });
const controls = window.ModFarmBookOptions;
const format = controls.FormatControl({ attributes: {}, setAttributes: (v) => { changes = v; } });
assert.equal(format.props.options[0].label, 'All Formats');
assert.equal(controls.FormatControl({ attributes: { 'book-format': { id: 2 } }, setAttributes() {} }).props.value, 0);
assert.equal(controls.FormatControl({ attributes: { 'book-format': { id: 7 } }, setAttributes() {} }).props.value, 7);
format.props.onChange('7'); assert.equal(changes['book-format'].id, 7);
format.props.onChange('0'); assert.equal(changes['book-format'].id, 0);
const visibility = controls.VisibilityControls({ attributes: { cardUseGlobal: false, cardShowTitle: false }, setAttributes: (v) => { changes = v; } });
assert.equal(visibility.children.length, 4);
assert.equal(visibility.children[0].props.checked, false);
visibility.children[0].props.onChange(true); assert.equal(changes.cardShowTitle, true);
for (const block of ['multi-tax-format', 'book-page-tax', 'taxonomy-grid']) {
  const source = fs.readFileSync(path.join(root, 'blocks', block, 'index.js'), 'utf8');
  new vm.Script(source);
  assert(source.includes('ModFarmBookOptions.FormatControl'));
  assert(source.includes('ModFarmBookOptions.VisibilityControls'));
  const attrs = JSON.parse(fs.readFileSync(path.join(root, 'blocks', block, 'block.json'), 'utf8')).attributes;
  for (const key of ['cardShowTitle', 'cardShowSeries', 'cardShowPrimaryButton', 'cardShowSampleButton']) assert.equal(attrs[key].default, true);
}
console.log('Shared book controls: format selection/reset, visibility, and schemas passed across all three blocks.');
