const fs = require('fs');
const {chromium} = require('playwright');
(async () => {
    const browser = await chromium.launch({headless: true, executablePath: process.env.CHROME_PATH || undefined});
    const page = await browser.newPage();
    const html = fs.readFileSync(process.argv[2], 'utf8');
    // Keep test artwork deterministic and offline; production uses attachment images.
    await page.route('https://dev.test/portrait.jpg', route => route.fulfill({contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><defs><linearGradient id="g" x2="1" y2="1"><stop stop-color="#102d3d"/><stop offset="1" stop-color="#5a9bad"/></linearGradient></defs><path fill="url(#g)" d="M0 0h800v600H0z"/><path fill="#18313b" d="M0 460 190 230 450 450 620 160 800 430v170H0z"/></svg>'}));
    const metrics = [];
    for (const width of [320,390,768,1440]) {
        await page.setViewportSize({width, height: 1000}); await page.setContent(html); await page.addScriptTag({path: 'modfarm-theme/modfarm-theme/blocks/world/view.js'});
        await page.locator('img').first().waitFor();
        const result = await page.evaluate(() => {
            const stacked = document.querySelector('.mfw--sections');
            const tabs = document.querySelector('.mfw--tabs');
            const columns = stacked.querySelector('.mfw-region--illustrated .mfw-entries');
            const features = stacked.querySelectorAll('.mfw-region--alternating .mfw-entry');
            const buttons = tabs.querySelectorAll('[role=tab]');
            const invalidImages = [...document.querySelectorAll('.mfw-entry-image')].some(image => getComputedStyle(image).objectFit === 'fill');
            return {width: innerWidth, scrollWidth: document.documentElement.scrollWidth, columns: getComputedStyle(columns).gridTemplateColumns.split(' ').length, alternatingRight: features[1].classList.contains('mfw-image-right'), tabs: buttons.length, initial: tabs.querySelector('[aria-selected=true]').dataset.worldTab, invalidImages};
        });
        if (result.scrollWidth > width + 1 || result.invalidImages || result.tabs !== 3 || result.initial !== 'region-features' || !result.alternatingRight) throw new Error(JSON.stringify(result));
        if (width === 1440 && result.columns !== 3) throw new Error('Desktop illustrated columns not three across');
        if (width <= 390 && result.columns !== 1) throw new Error('Phone columns did not stack');
        await page.locator('.mfw--tabs [role=tab]').first().click();
        if (!(await page.locator('.mfw--tabs .mfw-region--illustrated').isVisible())) throw new Error('Changing tab lost illustrated layout');
        metrics.push(result);
        if ([390,1440].includes(width)) await page.screenshot({path: `/tmp/world-regions-${width}.png`, fullPage: true});
    }
    console.log(JSON.stringify(metrics)); await browser.close(); console.log('World Region layouts in Chromium: passed');
})().catch(error => { console.error(error); process.exit(1); });
