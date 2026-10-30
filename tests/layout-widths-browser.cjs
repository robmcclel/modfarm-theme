const {chromium} = require('playwright');
const fs = require('fs'), path = require('path'), assert = require('assert/strict'), vm = require('vm');
const root = path.join(__dirname, '../modfarm-theme/modfarm-theme');
const css = fs.readFileSync(root+'/style.css', 'utf8') + fs.readFileSync(root+'/assets/css/layout-widths.css', 'utf8');
// Exercise the editor's Group wrapper using the same props supplied by Gutenberg.
let enhance;
vm.runInNewContext(fs.readFileSync(root+'/assets/js/layout-widths.js','utf8'), {window:{wp:{element:{createElement:(component,props)=>props},hooks:{addFilter:(name,ns,callback)=>enhance=callback}}}});
const groupProps = enhance(null)({name:'core/group',attributes:{layout:{type:'constrained',contentSize:'800px',wideSize:'1200px'}},wrapperProps:{style:{color:'red'}}});
assert.equal(groupProps.wrapperProps.style['--mf-layout-content-width'],'800px');
assert.equal(groupProps.wrapperProps.style['--mf-layout-wide-width'],'1200px');
assert.equal(groupProps.wrapperProps.style.color,'red');
(async()=>{
 const browser = await chromium.launch({headless:true,args:['--no-sandbox']});
 const page = await browser.newPage();
 for (const viewport of [1600,1024,390]) {
  await page.setViewportSize({width:viewport,height:900});
  for (const localWide of [null,1200]) {
   const groupStyle = `--mf-layout-content-width:800px;${localWide ? '--mf-layout-wide-width:'+localWide+'px;' : ''}`;
   const children = '<p class="prose">Article text</p><figure class="alignwide">Wide image</figure><figure class="alignfull">Full image</figure>';
   await page.setContent(`<style>${css}
    :root{--mf-content-width:1100px;--mf-wide-width:1300px}
    .test-group{width:min(1068px,calc(100% - 32px));margin:auto}
    .test-group>:where(:not(.alignfull)){max-width:800px;margin-left:auto!important;margin-right:auto!important}
    .test-group>.alignwide{max-width:${localWide || 1300}px}
    .direct-prose{max-width:800px;margin:auto}
   </style><div class="test-group is-layout-constrained" style="${groupStyle}">
    <p class="direct-prose">Outside prose</p><figure class="alignwide direct">Outside wide</figure>
    <div class="mf-content-slot">${children}<div class="mf-content-slot">${children}</div></div>
   </div>`);
   const rects = await page.locator('.test-group, .prose, .alignwide, .alignfull').evaluateAll(es=>es.map(e=>{const r=e.getBoundingClientRect();return {cls:e.className,x:r.x,width:r.width,center:r.x+r.width/2}}));
   const center = rects[0].center;
   for (const r of rects.slice(1)) {
    assert.ok(Math.abs(r.center-center)<1,`Not centered at ${viewport}: ${JSON.stringify(r)}`);
    if (r.cls.includes('alignwide')) assert.ok(Math.abs(r.width-Math.min(localWide||1300,viewport-32))<1,`Wrong wide width: ${JSON.stringify(r)}`);
    if (r.cls.includes('prose')) assert.ok(r.width<=800 && r.width<=viewport-32,'Text stays narrow and responsive');
    if (r.cls.includes('alignfull')) assert.ok(Math.abs(r.width-viewport)<1,'Full stays viewport-wide');
   }
  }
 }
 // Editor InnerBlocks wrappers must preserve the same geometry.
 await page.setViewportSize({width:1600,height:900});
 await page.setContent(`<style>${css}</style><div class="editor-styles-wrapper" style="width:1068px;margin:auto;--mf-layout-content-width:800px;--mf-wide-width:1300px"><div class="wp-block-modfarm-content-slot"><div class="block-editor-inner-blocks"><div class="block-editor-block-list__layout"><p>Text</p><figure class="alignwide">Image</figure></div></div></div></div>`);
 const editor = await page.locator('p,figure').evaluateAll(es=>es.map(e=>{const r=e.getBoundingClientRect();return {width:r.width,center:r.x+r.width/2}}));
 assert.equal(editor[0].width,800); assert.equal(editor[1].width,1300); assert.equal(editor[0].center,editor[1].center);
 console.log('PASS site/local widths, nested slots, direct wide blocks, full alignment, editor wrappers and 1600/1024/390px viewports');
 await browser.close();
})().catch(error=>{console.error(error);process.exit(1)});
