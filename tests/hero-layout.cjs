// Run with NODE_PATH pointing to Playwright; optional MFS_CHROMIUM_PATH.
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const assert = require('assert/strict');
const root = path.join(__dirname, '../modfarm-theme/modfarm-theme');
const css = ['blocks/hero-cover/style.css', 'assets/css/hero-presentation.css', 'blocks/hero-cover/editor.css'].map(file => fs.readFileSync(path.join(root, file), 'utf8')).join('\n');
(async () => {
  const browser = await chromium.launch({ headless: true, ...(process.env.MFS_CHROMIUM_PATH ? { executablePath: process.env.MFS_CHROMIUM_PATH, args: ['--no-sandbox'] } : {}) });
  const page = await browser.newPage();
  await page.route('**/*', route => route.abort());
  for (const editor of [false, true]) {
    for (const width of [390, 768, 1440]) {
      for (const vertical of ['flex-start', 'center', 'flex-end']) {
        await page.setViewportSize({ width, height: 900 });
        let nav = '<div class="mf-hero-area mf-hero-area--navigation"><div class="mf-hero-area__inner"><nav style="height:52px">Top navigation</nav></div></div>';
        let content = '<div class="mf-hero-area mf-hero-area--content"><div class="mf-hero-area__inner"><h1 style="margin:0">A title that can wrap on mobile</h1><p>Book metadata and buttons</p></div></div>';
        if (editor) {
          nav = nav.replace('mf-hero-area__inner', 'block-editor-inner-blocks');
          content = content.replace('mf-hero-area__inner', 'block-editor-inner-blocks');
          content = '<div class="block-editor-inner-blocks"><div class="block-editor-block-list__layout">' + nav + content + '</div></div>';
        } else content = nav + content;
        await page.setContent('<style>body{margin:0}*{box-sizing:border-box}' + css + '</style><section class="wp-block-modfarm-hero-cover modfarm-hero-cover modfarm-hero-cover--structured' + (editor ? ' modfarm-hero-cover--editor' : '') + '" style="min-height:600px;--mf-hero-width:1200px;--mf-hero-align:left;--mf-hero-vertical:' + vertical + ';--mf-hero-mobile-align:center;--mf-hero-mobile-vertical:' + vertical + '"><span class="mf-hero-edge mf-hero-edge--bottom-deep" style="--mf-hero-edge-color:#123456"></span><div class="modfarm-hero-cover__content' + (editor ? ' modfarm-hero-cover__content--editor' : '') + '">' + content + '</div></section>');
        const navBox = await page.locator('nav').boundingBox();
        const title = await page.locator('h1').boundingBox();
        const hero = await page.locator('section').boundingBox();
        assert.equal(Math.round(navBox.y), 24, 'Navigation moved with content alignment');
        assert.ok(title.y >= navBox.y + navBox.height, 'Title overlapped navigation');
        if (vertical === 'flex-end') assert.ok(title.y > hero.height * 0.65, 'Bottom content did not reach bottom');
        if (vertical === 'center') assert.ok(title.y > hero.height * 0.3 && title.y < hero.height * 0.7, 'Centered content is misplaced');
        if (vertical === 'flex-start') assert.ok(title.y < 160, 'Top content is misplaced');
        assert.equal(await page.locator('.mf-hero-area--content').evaluate(el => getComputedStyle(el).textAlign), width < 782 ? 'center' : 'left');
        assert.equal(await page.locator('.mf-hero-edge').evaluate(el => getComputedStyle(el).pointerEvents), 'none');
        assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'Horizontal overflow');
        await page.locator('h1').evaluate(el => { el.textContent = 'A very long title '.repeat(80); });
        const expanded = await page.locator('section').boundingBox();
        const expandedTitle = await page.locator('h1').boundingBox();
        assert.ok(expandedTitle.y + expandedTitle.height <= expanded.y + expanded.height, 'Long mobile content overflowed Hero');
      }
    }
  }
  await browser.close();
  console.log('PASS 18 desktop/mobile frontend/editor layouts: pinned navigation, independent alignment, long titles, no overflow and nonblocking fades');
})().catch(error => { console.error(error); process.exit(1); });
