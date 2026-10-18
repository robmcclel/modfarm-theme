// Hook integration in real React/Chromium, with native Cover's edit contract doubled.
// NODE_PATH must include Playwright, React 18, and ReactDOM 18.
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const assert = require('assert/strict');
const root = path.join(__dirname, '../modfarm-theme/modfarm-theme');
(async () => {
  const browser = await chromium.launch({ headless: true, ...(process.env.MFS_CHROMIUM_PATH ? { executablePath: process.env.MFS_CHROMIUM_PATH, args: ['--no-sandbox'] } : {}) });
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.route('https://example.test/**', route => route.fulfill({ contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"><rect width="20" height="20" fill="blue"/></svg>' }));
  await page.setContent('<div id="root"></div>');
  await page.addScriptTag({ path: path.join(path.dirname(require.resolve('react/package.json')), 'umd/react.development.js') });
  await page.addScriptTag({ path: path.join(path.dirname(require.resolve('react-dom/package.json')), 'umd/react-dom.development.js') });
  await page.addStyleTag({ content: fs.readFileSync(path.join(root, 'assets/css/hero-presentation.css'), 'utf8') + '\n.wp-block-cover{position:relative;min-height:300px}.wp-block-cover__image-background{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0}.wp-block-cover__background{position:absolute;inset:0;background:#0003;z-index:1}' });
  await page.evaluate(() => {
    const el = React.createElement;
    window.modfarmHeroPresentation = { defaultImage: 'https://example.test/default.jpg', settings: {}, options: { presets: { none: 'Clean' }, colors: { background: 'Background' } }, preset: 'none', color: 'background' };
    window.filters = {};
    window.requests = [];
    window.pending = {};
    window.mutations = [];
    const Panel = props => el('aside', null, props.children);
    window.wp = {
      element: React, i18n: { __: value => value },
      components: { PanelBody: Panel, SelectControl: () => null, ToggleControl: () => null, Notice: props => el('p', { role: 'alert' }, props.children) },
      blockEditor: { InspectorControls: Panel },
      compose: { createHigherOrderComponent: callback => callback },
      hooks: { addFilter: (hook, name, callback) => { filters[hook] = callback; } },
      apiFetch: options => {
        requests.push(options);
        return new Promise((resolve, reject) => { pending[options.data.post_id || 0] = { resolve, reject }; });
      }
    };
    window.resolvePreview = (postId, url) => pending[postId].resolve({ rendered: '<section data-mf-hero-image="' + url + '"></section>' });
  });
  await page.addScriptTag({ content: fs.readFileSync(path.join(root, 'assets/js/hero-presentation.js'), 'utf8') });
  await page.evaluate(() => {
    const el = React.createElement;
    function NativeEdit(props) {
      const a = props.attributes;
      const url = a.useFeaturedImage ? window.featuredUrl : a.url;
      return el(React.Fragment, null,
        el('input', { 'aria-label': 'Native Featured Image', type: 'checkbox', style: { position: 'relative', zIndex: 10 }, checked: !!a.useFeaturedImage, onChange: e => props.setAttributes({ useFeaturedImage: e.target.checked }) }),
        url ? el('img', { className: 'wp-block-cover__image-background', src: url }) : el('span', { className: 'wp-block-cover__image--placeholder-image' }, 'Placeholder'),
        el('span', { className: 'wp-block-cover__background' }),
        el('div', { className: 'wp-block-cover__inner-container' }, el('button', { onClick: () => { window.contentClicked = true; } }, 'Editable content'))
      );
    }
    const Edit = filters['editor.BlockEdit'](NativeEdit);
    const Wrapper = filters['editor.BlockListBlock'](props => el('div', Object.assign({}, props.wrapperProps, { className: 'wp-block-cover ' + props.className }), props.children));
    const reactRoot = ReactDOM.createRoot(document.getElementById('root'));
    window.show = (attributes, postId = 10, clientId = 'cover') => {
      window.savedAttributes = attributes;
      const props = { name: 'core/cover', attributes, context: { postId, postType: 'book' }, clientId, setAttributes: values => { mutations.push(values); show(Object.assign({}, attributes, values), postId, clientId); } };
      // Match WordPress: the outer wrapper has no post context.
      reactRoot.render(el(Wrapper, { name: props.name, attributes, clientId }, el(Edit, props)));
    };
    window.show({ modfarmImageDefaults: true });
  });
  await page.waitForFunction(() => requests.length === 1);
  await page.waitForFunction(() => document.querySelector('img')?.src.endsWith('/default.jpg'));
  await page.evaluate(() => resolvePreview(10, 'https://example.test/local-hero.jpg'));
  await page.waitForFunction(() => document.querySelector('img')?.src.endsWith('/local-hero.jpg'));
  assert.deepEqual(await page.evaluate(() => savedAttributes), { modfarmImageDefaults: true });
  assert.deepEqual(await page.evaluate(() => mutations), []);
  assert.equal(await page.evaluate(() => requests[0].data.post_id), 10);
  assert.equal(await page.evaluate(() => requests[0].data.attributes.useFeaturedImage), false);
  await page.getByRole('button', { name: 'Editable content' }).click();
  assert.equal(await page.evaluate(() => contentClicked), true);

  await page.evaluate(() => show({ modfarmImageDefaults: true, useFeaturedImage: true }));
  await page.waitForFunction(() => document.querySelector('.mf-cover-inherited-preview'));
  assert.equal(await page.getByRole('checkbox').isChecked(), true);
  assert.equal(await page.locator('.wp-block-cover__image--placeholder-image').isVisible(), false);
  assert.ok((await page.locator('.wp-block-cover').evaluate(el => getComputedStyle(el, '::before').backgroundImage)).includes('local-hero.jpg'));
  await page.evaluate(() => { featuredUrl = 'https://example.test/unsaved-featured.jpg'; show({ modfarmImageDefaults: true, useFeaturedImage: true }); });
  await page.waitForFunction(() => document.querySelector('img')?.src.endsWith('/unsaved-featured.jpg'));
  await page.getByRole('checkbox').uncheck();
  assert.deepEqual(await page.evaluate(() => mutations), [{ useFeaturedImage: false }]);
  await page.evaluate(() => { featuredUrl = ''; show({ modfarmImageDefaults: true, useFeaturedImage: true }); });
  await page.waitForFunction(() => !document.querySelector('img'));
  assert.ok((await page.locator('.wp-block-cover').evaluate(el => getComputedStyle(el, '::before').backgroundImage)).includes('local-hero.jpg'));
  assert.equal(await page.evaluate(() => requests.length), 1, 'Shared context requested twice');

  await page.evaluate(() => show({ modfarmImageDefaults: true }, 20));
  await page.waitForFunction(() => requests.length === 2);
  await page.evaluate(() => show({ modfarmImageDefaults: true }, 30));
  await page.waitForFunction(() => requests.length === 3);
  await page.evaluate(() => resolvePreview(20, 'https://example.test/stale.jpg'));
  await page.evaluate(() => resolvePreview(30, 'https://example.test/right-context.jpg'));
  await page.waitForFunction(() => document.querySelector('img')?.src.endsWith('/right-context.jpg'));
  assert.deepEqual(await page.evaluate(() => savedAttributes), { modfarmImageDefaults: true });

  await page.evaluate(() => show({ modfarmImageDefaults: true }, 40));
  await page.waitForFunction(() => requests.length === 4);
  await page.evaluate(() => pending[40].reject(new Error('Permission denied')));
  await page.getByRole('alert').waitFor();
  assert.ok((await page.locator('img').getAttribute('src')).endsWith('/default.jpg'));
  await page.evaluate(() => show({ modfarmImageDefaults: true, url: 'https://example.test/manual.jpg' }));
  await page.waitForFunction(() => document.querySelector('img')?.src.endsWith('/manual.jpg'));
  await page.evaluate(() => show({ modfarmImageDefaults: true, backgroundType: 'video', url: 'https://example.test/video.mp4' }));
  await page.waitForFunction(() => !document.querySelector('.mf-cover-inherited-preview'));
  assert.equal(await page.evaluate(() => requests.length), 4, 'Explicit image or video caused a lookup');
  await page.evaluate(() => show({ modfarmImageDefaults: false }));
  await page.locator('.wp-block-cover__image--placeholder-image').waitFor({ state: 'visible' });
  assert.deepEqual(errors, []);
  await browser.close();
  console.log('PASS Cover editor inheritance, saved attribute preservation, editable content, native Featured Image controls, unsaved featured changes, context races, failures, explicit media and opt-out');
})().catch(error => { console.error(error); process.exit(1); });
