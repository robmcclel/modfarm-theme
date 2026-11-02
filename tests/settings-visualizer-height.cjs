const {chromium} = require('playwright');
const fs = require('fs'), path = require('path'), assert = require('assert/strict');
const root = path.join(__dirname, '../modfarm-theme/modfarm-theme');
const source = fs.readFileSync(root + '/assets/js/modfarm-settings-ui.js', 'utf8');
(async () => {
  const browser = await chromium.launch({headless:true,args:['--no-sandbox']});
  try {
    const page = await browser.newPage();
    await page.setContent('<div class="modfarm-settings-shell"><div id="mf-ppb-visualizer" class="mf-ppb-visualizer"><div class="mf-ppb-visualizer__head">Preview</div><div class="mf-ppb-visualizer__viewports"></div><div class="mf-ppb-visualizer__frame-wrap"><iframe class="mf-ppb-visualizer__frame" srcdoc="<p>Saved page</p>"></iframe></div><div id="mf-ppb-visualizer-feedback" class="mf-ppb-visualizer__feedback is-active">Current page.</div><div class="mf-preview-card"></div></div></div>');
    await page.addStyleTag({path:root + '/assets/css/modfarm-settings-ui.css'});
    await page.addScriptTag({path:root + '/assets/js/modfarm-visualizer.js'});
    // Exercise the production studio assembly, including relocation of feedback.
    await page.addScriptTag({content:source.slice(0, source.indexOf('  const form = root.closest')) + '\npreviews.forEach(preview => previewViewport.appendChild(preview));root.appendChild(studio);})();'});
    for (const device of ['desktop', 'tablet', 'mobile']) {
      const sizes = await page.evaluate(device => {
        const studio = document.querySelector('.mf-settings-studio');
        window.ModFarmVisualizer.fit({container:studio.querySelector('.mf-studio-canvas'),stage:studio.querySelector('.mf-studio-stage'),frame:studio.querySelector('.mf-studio-screen'),viewport:studio.querySelector('.mf-studio-viewport'),device});
        const height = selector => studio.querySelector(selector).offsetHeight;
        return {viewport:height('.mf-studio-viewport'),frame:height('iframe'),wrap:height('.mf-ppb-visualizer__frame-wrap'),feedbackOutside:!studio.querySelector('.mf-studio-viewport').contains(document.getElementById('mf-ppb-visualizer-feedback'))};
      }, device);
      assert.equal(sizes.frame, sizes.viewport, device + ': iframe fills screen');
      assert.equal(sizes.wrap, sizes.viewport, device + ': wrapper fills screen');
      assert.equal(sizes.feedbackOutside, true);
    }
    console.log('PASS Settings iframe fills desktop/tablet/mobile viewports; feedback stays outside device');
  } finally { await browser.close(); }
})().catch(error => {console.error(error);process.exit(1);});
