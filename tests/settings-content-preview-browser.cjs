const {chromium} = require('playwright');
const fs = require('fs'), path = require('path'), assert = require('assert/strict');
const source = fs.readFileSync(path.join(__dirname, '../modfarm-theme/modfarm-theme/assets/js/modfarm-settings-ui.js'), 'utf8');
const start = source.indexOf('  function initPpbVisualizer()');
const end = source.indexOf('\n  initPpbVisualizer();', start);
const initializer = source.slice(start, end);
(async () => {
  const browser = await chromium.launch({headless:true,args:['--no-sandbox']});
  const page = await browser.newPage();
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  const html = '<main id="root"><button class="mf-ppb-layout-tab" data-ppb-layout-type="page">Pages</button><section class="mf-ppb-layout-panel" data-ppb-layout-type="page"></section><select id="mf-ppb-visualizer-sample"></select>' +
    ['header','body','footer'].map(zone => '<select name="modfarm_theme_settings[page_'+zone+'_pattern]"><option value="default">Default</option><option value="new">New</option></select>').join('') +
    '<section id="mf-ppb-visualizer"><div class="mf-ppb-visualizer__frame-wrap"><iframe id="mf-ppb-visualizer-frame" sandbox="allow-same-origin"></iframe></div><div id="mf-ppb-visualizer-feedback"></div></section></main>';
  await page.route('https://preview.test/**', route => route.fulfill({contentType:'text/html',body:route.request().url().includes('/admin') ? html : '<h1>Saved page content</h1><a href="/logout">Logout</a>'}));
  await page.goto('https://preview.test/admin');
  await page.evaluate(() => {
    window.requests = [];
    window.fetch = async (url, options) => {
      const values = Object.fromEntries(options.body.entries()); window.requests.push(values);
      return {json:async () => ({success:true,data:{url:'https://preview.test/content?id='+values.sampleId+'&request='+window.requests.length,message:JSON.stringify(JSON.parse(values.changedPatterns)).length > 2 ? 'Previewing layout changes.' : 'Current page.'}})};
    };
  });
  await page.addScriptTag({content:'(function(){const root=document.getElementById("root"), deviceWidth=1440;function resizeStudio(){}const config={ajaxUrl:"/ajax",visualizerNonce:"test",visualizerTypes:{page:{fields:{header:"page_header_pattern",body:"page_body_pattern",footer:"page_footer_pattern"}}},visualizerSamples:{page:[{value:"1",label:"First"},{value:"2",label:"Second"}]}};'+initializer+'\ninitPpbVisualizer();})();'});
  await page.locator('[data-ppb-layout-type="page"]').first().click();
  await page.waitForFunction(() => window.requests.some(item => item.sampleId === '1'));
  let request = await page.evaluate(() => window.requests.at(-1));
  assert.deepEqual(JSON.parse(request.changedPatterns), {});
  await page.frameLocator('#mf-ppb-visualizer-frame').getByText('Saved page content').waitFor();
  assert.equal(await page.locator('#mf-ppb-visualizer-frame').getAttribute('srcdoc'), null);
  assert.equal(await page.locator('#mf-ppb-visualizer-feedback').textContent(), 'Current page.');
  const before = await page.locator('#mf-ppb-visualizer-frame').getAttribute('src');
  await page.frameLocator('#mf-ppb-visualizer-frame').getByRole('link', {name:'Logout'}).click();
  assert.equal(await page.locator('#mf-ppb-visualizer-frame').getAttribute('src'), before);
  await page.locator('[name="modfarm_theme_settings[page_body_pattern]"]').selectOption('new');
  await page.waitForFunction(() => window.requests.some(item => JSON.parse(item.changedPatterns).body === 'new'));
  request = await page.evaluate(() => window.requests.at(-1));
  assert.deepEqual(JSON.parse(request.changedPatterns), {body:'new'});
  await page.locator('#mf-ppb-visualizer-sample').selectOption('2');
  await page.waitForFunction(() => window.requests.at(-1).sampleId === '2');
  request = await page.evaluate(() => window.requests.at(-1));
  assert.deepEqual(JSON.parse(request.changedPatterns), {body:'new'});
  await page.locator('[name="modfarm_theme_settings[page_body_pattern]"]').selectOption('default');
  await page.waitForFunction(() => window.requests.at(-1).sampleId === '2' && window.requests.at(-1).changedPatterns === '{}');
  assert.deepEqual(errors, []);
  await browser.close();
  console.log('PASS real-content preview URL, explicit zone changes, reverting defaults, sample switching, and account link isolation');
})().catch(error => { console.error(error); process.exit(1); });
