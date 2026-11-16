const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { runInNewContext } = require('node:vm');

const events = Object.create(null);
const modalEvents = Object.create(null);
const heading = { focus: () => calls.push('heading.focus') };
const sample = {
    before: () => calls.push('placeholder'),
    getAttribute: () => 'sample-title',
    querySelector: selector => selector === 'h2' ? heading : null,
};
const calls = [];
const trigger = { dataset: { mfBookSample: 'book-sample' }, focus: options => calls.push(options.preventScroll ? 'trigger.focus' : 'trigger.scroll') };
const dialog = {
    close: () => { calls.push('close'); modalEvents.close(); },
    showModal: () => calls.push('showModal'),
    remove: () => calls.push('remove'),
    setAttribute: (name, value) => { assert.equal(name, 'aria-labelledby'); assert.equal(value, 'sample-title'); },
    addEventListener: (name, callback) => { modalEvents[name] = callback; },
    append: child => { assert.equal(child, sample); },
};
const position = { replaceWith: child => { assert.equal(child, sample); calls.push('restore'); } };
const document = {
    addEventListener: (name, callback) => { events[name] = callback; },
    getElementById: id => id === 'book-sample' ? sample : null,
    createComment: () => position,
    createElement: name => { assert.equal(name, 'dialog'); return dialog; },
    body: { append: child => { assert.equal(child, dialog); } },
};
runInNewContext(readFileSync(join(__dirname, '../modfarm-theme/modfarm-theme/blocks/book-page-buttons/view.js'), 'utf8'), { document });
const click = { button: 0, target: { closest: () => trigger }, preventDefault: () => calls.push('no navigation') };
events.click({ ...click, ctrlKey: true });
events.click({ ...click, target: { closest: () => null } });
assert.deepEqual(calls, []);
events.click(click);
assert.deepEqual(calls, ['placeholder', 'showModal', 'no navigation', 'heading.focus']);
modalEvents.click({ target: { closest: () => true } });
assert.deepEqual(calls.slice(4), ['close', 'restore', 'remove', 'trigger.focus']);
console.log('Sample click handler: passed');
