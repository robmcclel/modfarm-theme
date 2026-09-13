// Run with NODE_PATH pointing to a Playwright installation; uses installed Edge on Windows.
const { chromium } = require('playwright');
const { execFileSync } = require('child_process');
const path = require('path');
const assert = require('assert/strict');
(async () => {
 const browser = await chromium.launch(process.platform === 'win32' ? {channel:'msedge',headless:true} : {headless:true});
 const page = await browser.newPage({reducedMotion:'reduce'});
 const errors=[];page.on('pageerror', e=>errors.push(e.message));
 async function fixture(mode,width=412,side='right',search=false) {
   await page.setViewportSize({width,height:924});
   await page.setContent(execFileSync('php',[path.join(__dirname,'navigation.php'),'--fixture',mode,side,search?'search':''],{encoding:'utf8'}));
 }
 for(const [width,expected] of [[412,412],[768,384],[1000,350]]) {
   await fixture('drawer',width);
   await page.locator('.mfs-nav-toggle').click();
   assert.equal(Math.round((await page.locator('.mfs-nav-panel').boundingBox()).width),expected);
   assert.equal(await page.locator('header').evaluate(el=>el.inert),true);
   await page.locator('.mfs-nav-overlay .mfs-submenu-toggle').first().click();
   assert.equal(await page.locator('.mfs-nav-overlay .menu-cover img').first().isVisible(),false);
   assert.equal(await page.locator('.mfs-nav-overlay .menu-icon img').isVisible(),true);
   await page.locator('.mfs-nav-overlay .sub-menu .mfs-submenu-toggle').first().click();
   const book16=page.locator('.mfs-nav-overlay a').filter({hasText:'Book 16'});
   await book16.scrollIntoViewIfNeeded();assert.equal(await book16.isVisible(),true);
   await page.locator('.mfs-nav-close').click();
   assert.equal(await page.locator('header').evaluate(el=>el.inert),false);
   assert.equal(await page.locator('.mfs-nav-toggle').getAttribute('aria-expanded'),'false');
   console.log('PASS drawer width, nested scrolling, cover/icon visibility and close at '+width);
 }
 await fixture('drawer',768,'left');await page.locator('.mfs-nav-toggle').click();
 assert.equal((await page.locator('.mfs-nav-panel').boundingBox()).x,0);
 await page.mouse.click(700,200);assert.equal(await page.locator('.mfs-nav-overlay').isVisible(),false);
 console.log('PASS left drawer and backdrop dismiss');
 await fixture('overlay');await page.locator('.mfs-nav-toggle').click();
 await page.keyboard.press('Shift+Tab');assert.equal(await page.locator('.mfs-nav-overlay a').last().evaluate(el=>el===document.activeElement),true);
 await page.keyboard.press('Tab');assert.equal(await page.locator('.mfs-nav-close').evaluate(el=>el===document.activeElement),true);
 await page.keyboard.press('Escape');assert.equal(await page.locator('.mfs-nav-toggle').evaluate(el=>el===document.activeElement),true);
 await page.locator('.mfs-nav-toggle').click();await page.setViewportSize({width:1300,height:900});
 await page.locator('.mfs-nav-overlay').waitFor({state:'hidden'});
 assert.equal(await page.locator('.mfs-nav-overlay').isVisible(),false);assert.notEqual(await page.locator('body').evaluate(el=>el.style.overflow),'hidden');
 console.log('PASS modal focus loop, Escape, focus return and desktop resize cleanup');
 await fixture('below');const before=(await page.locator('main').boundingBox()).y;await page.locator('.mfs-nav-toggle').click();
 assert.ok((await page.locator('main').boundingBox()).y>before);
 assert.equal(await page.locator('main').evaluate(el=>el.inert),false);
 await page.locator('.mfs-nav-overlay .mfs-submenu-toggle').first().click();
 const parent=page.locator('.mfs-nav-overlay a').filter({has:page.locator('.mfs-menu-label', {hasText:/^Books$/})});
 await parent.click();assert.equal(await page.locator('.mfs-nav-overlay').isVisible(),false);
 console.log('PASS below-header flow and parent links navigate independently');
 await fixture('overlay',1440);await page.locator('.mfs-nav-menu > li').nth(1).hover();
 assert.equal(await page.locator('.mfs-nav-menu > li > .sub-menu').isVisible(),true);
 await page.locator('.mfs-nav-menu .sub-menu > li').first().hover();
 const flyout=await page.locator('.mfs-nav-menu .sub-menu .sub-menu').boundingBox();assert.ok(flyout.x>=0 && flyout.x+flyout.width<=1440);
 assert.equal(await page.locator('.mfs-nav-menu .menu-cover img').first().isVisible(),true);
 console.log('PASS desktop cover images and nested flyouts');
 await fixture('no-collapse',412);assert.equal(await page.locator('.mfs-nav-menu').isVisible(),true);assert.equal(await page.locator('.mfs-nav-toggle').count(),0);
 console.log('PASS no-collapse menu');
 for (const mode of ['drawer','overlay','below']) {
   await fixture(mode);
   await page.locator('.mfs-nav').evaluate(el => {
     el.style.setProperty('--mfs-mobile-bg','#123456');
     el.style.setProperty('--mfs-mobile-color','#fefefe');
   });
   await page.locator('.mfs-nav-toggle').click();
   const colors=await page.locator('.mfs-nav-panel').evaluate(el=>[getComputedStyle(el).backgroundColor,getComputedStyle(el).color]);
   assert.deepEqual(colors,['rgb(18, 52, 86)','rgb(254, 254, 254)']);
   const iconLink=page.locator('.mfs-nav-overlay .menu-icon-only > a');
   assert.equal(await iconLink.locator('.mfs-menu-copy').evaluate(el=>getComputedStyle(el).width),'1px');
   assert.equal(await iconLink.locator('.mfs-menu-label').textContent(),'News');
 }
 console.log('PASS mobile colors across all presentations and accessible icon-only labels');
 await fixture('overlay',1440);
 assert.equal(await page.locator('.mfs-nav-menu .mfs-submenu-toggle span').first().evaluate(el=>getComputedStyle(el).display),'none');
 await page.locator('.mfs-nav-menu .mfs-submenu-toggle').first().focus();
 await page.keyboard.press('Enter');
 assert.equal(await page.locator('.mfs-nav-menu > li > .sub-menu').isVisible(),true);
 console.log('PASS desktop chevrons hidden with keyboard submenu access retained');
 for (const width of [412,768,1440]) {
   await fixture('drawer',width,'right',true);
   const summary=page.locator('.mfs-nav-search summary');
   await summary.click();
   const input=page.locator('.mfs-nav-search input');
   await page.waitForFunction(()=>document.activeElement?.matches('.mfs-nav-search input'));
   const box=await page.locator('.mfs-nav-search-popover').boundingBox();
   assert.ok(box.x>=0&&box.x+box.width<=width);
   await input.fill('Greatcoats');
   await page.keyboard.press('Escape');
   assert.equal(await summary.evaluate(el=>el===document.activeElement),true);
   assert.equal(await page.locator('.mfs-nav-search').getAttribute('open'),null);
   await summary.click();await page.locator('main h1').click();
   assert.equal(await page.locator('.mfs-nav-search').getAttribute('open'),null);
   if(width<1025){
     await page.locator('.mfs-nav-toggle').click();
     await page.keyboard.press('Tab');
     assert.equal(await page.locator('.mfs-nav-overlay input').evaluate(el=>el===document.activeElement),true);
     await page.locator('.mfs-nav-overlay input').fill('Book 1');
     await page.route('https://example.test/**',route=>route.fulfill({body:'Search results'}));
     await Promise.all([page.waitForURL('https://example.test/?s=Book+1'),page.locator('.mfs-nav-overlay button[type=submit]').click()]);
   }
 }
 console.log('PASS search popup bounds, focus, Escape, outside dismissal and native mobile submission');
 assert.deepEqual(errors,[]);
 if(process.env.MFS_NAV_SCREENSHOT) {await fixture('drawer',768);await page.locator('.mfs-nav-toggle').click();await page.locator('.mfs-nav-overlay .mfs-submenu-toggle').first().click();await page.screenshot({path:process.env.MFS_NAV_SCREENSHOT});}
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
