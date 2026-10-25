(function() {
  const root = document.querySelector('.modfarm-settings-shell');
  if (!root) return;
  window.modfarmSettingsUiLoaded = true;

  // Mirrored layout controls must submit the same value, including hidden panels.
  function syncPatternControls(form, source) {
    if (!source || source.tagName !== 'SELECT' || !source.name ||
        !source.name.startsWith('modfarm_theme_settings[') || !source.name.includes('_pattern')) return;
    form.querySelectorAll('select[name]').forEach(peer => {
      if (peer !== source && peer.name === source.name) peer.value = source.value;
    });
  }

  const tabs = root.querySelectorAll('.mf-tab');
  const panels = root.querySelectorAll('.mf-tab-panel');
  const storageKey = 'modfarm-settings-active-tab';
  const studio = document.createElement('aside');
  studio.className = 'mf-settings-studio';
  studio.setAttribute('aria-label', 'Site visualizer');
  studio.innerHTML = '<header><div><span class="mf-studio-eyebrow">MODFARMOS / SITE CONFIGURATION</span><h2>Build your site</h2></div><a class="button" href="#" data-open-site>View site</a></header><p class="mf-studio-note" aria-live="polite"></p><div class="mf-studio-devices" role="group" aria-label="Preview device"><button type="button" data-size="1440" aria-pressed="true">Desktop</button><button type="button" data-size="768" aria-pressed="false">Tablet</button><button type="button" data-size="390" aria-pressed="false">Phone</button></div><div class="mf-studio-canvas"><div class="mf-studio-screen"></div></div>';
  const screen = studio.querySelector('.mf-studio-screen');
  studio.querySelector('[data-open-site]').href = root.dataset.siteUrl || '/';
  studio.querySelector('[data-open-site]').target = '_blank';
  studio.querySelector('[data-open-site]').rel = 'noopener';
  const canvas = studio.querySelector('.mf-studio-canvas');
  const previewStage = document.createElement('div');
  previewStage.className = 'mf-studio-stage';
  screen.before(previewStage); previewStage.appendChild(screen);
  const previewViewport = document.createElement('div');
  previewViewport.className = 'mf-studio-viewport';
  screen.appendChild(previewViewport);
  const sizeNote = document.createElement('p'); sizeNote.className = 'mf-studio-size';
  const themePreview = root.querySelector('#mf-theme-live-preview');
  const bookPreview = root.querySelector('#mf-book-live-preview');
  const layoutPreview = root.querySelector('#mf-ppb-visualizer');
  const deviceIcons = ['desktop', 'tablet', 'smartphone'];
  function decorateDevices(buttons) {
    buttons.forEach((button, index) => {
      const icon = document.createElement('span');
      icon.className = 'dashicons dashicons-' + deviceIcons[index];
      icon.setAttribute('aria-hidden', 'true');
      const label = document.createElement('span');
      label.textContent = ['Desktop', 'Tablet', 'Mobile'][index];
      button.replaceChildren(icon, label);
    });
  }
  decorateDevices(studio.querySelectorAll('[data-size]'));
  const toolbar = document.createElement('div');
  toolbar.className = 'mf-studio-toolbar';
  toolbar.append(studio.querySelector('.mf-studio-devices'), studio.querySelector('header'));
  studio.append(canvas, toolbar, sizeNote, studio.querySelector('.mf-studio-note'));
  if (layoutPreview) {
    const layoutDevices = layoutPreview.querySelector('.mf-ppb-visualizer__viewports');
    const layoutFrame = layoutPreview.querySelector('.mf-ppb-visualizer__frame-wrap');
    if (layoutDevices && layoutFrame) {
      layoutDevices.remove();
    }
  }
  const previews = [themePreview, bookPreview, layoutPreview].filter(Boolean);
  const form = root.closest('form');
  const saveContext = form && form.querySelector('.mf-settings-actions__context');
  let dirty = false;
  if (form) {
    form.addEventListener('change', event => syncPatternControls(form, event.target), true);
    form.addEventListener('input', event => {
      if (!event.target.name || !(/^(modfarm_theme_settings|modfarm_site_identity)\[/.test(event.target.name))) return;
      dirty = true;
      if (saveContext) saveContext.textContent = 'Unsaved changes · Apply to this site when saved.';
    });
    form.addEventListener('change', event => {
      if (!event.target.name || !(/^(modfarm_theme_settings|modfarm_site_identity)\[/.test(event.target.name))) return;
      dirty = true;
      if (saveContext) saveContext.textContent = 'Unsaved changes · Apply to this site when saved.';
    });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', event => {
      if (!dirty) return;
      event.preventDefault();
      event.returnValue = '';
    });
  }
  previews.forEach(preview => previewViewport.appendChild(preview));
  root.insertBefore(studio, root.querySelector('.mf-tab-panels'));
  const workspace = document.createElement('div');
  workspace.className = 'mf-settings-workspace';
  root.appendChild(workspace);
  workspace.append(studio, root.querySelector('.mf-tab-panels'));
  root.classList.add('is-studio');
  let deviceWidth = 1440;
  let layoutActive = false;
  function resizeStudio() {
    const device = deviceWidth === 390 ? 'mobile' : deviceWidth === 768 ? 'tablet' : 'desktop';
    if (window.ModFarmVisualizer) window.ModFarmVisualizer.fit({container:canvas, stage:previewStage, frame:screen, viewport:previewViewport, note:sizeNote, device});
    screen.style.height = 'auto';
    screen.dataset.device = String(deviceWidth);
  }
  studio.querySelectorAll('[data-size]').forEach(button => button.addEventListener('click', () => {
    deviceWidth = Number(button.dataset.size);
    studio.querySelectorAll('[data-size]').forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    resizeStudio();
  }));
  if (window.ResizeObserver) new ResizeObserver(resizeStudio).observe(canvas);
  window.addEventListener('resize', resizeStudio);
  studio.addEventListener('click', event => {
    if (event.target.closest('.mf-theme-visualizer a, .mf-book-visualizer a')) event.preventDefault();
  });

  function activate(tabId) {
	const control = document.getElementById(`mf-tab-control-${tabId}`);
	if (control) control.checked = true;

    tabs.forEach(btn => {
      const isActive = btn.dataset.tab === tabId;
      btn.classList.toggle('is-active', isActive);
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    panels.forEach(panel => {
      const id = panel.id.replace('mf-tab-', '');
      panel.classList.toggle('is-active', id === tabId);
    });
    layoutActive = tabId === 'page-layouts';
    studio.classList.toggle('is-layout', layoutActive);
    previews.forEach(preview => { preview.hidden = preview !== (layoutActive ? layoutPreview : tabId === 'book-presentation' ? bookPreview : themePreview); });
    studio.querySelector('.mf-studio-note').textContent = layoutActive
      ? 'Rendered patterns using your selected content. Save to keep these defaults.'
      : tabId === 'marketing' ? 'Measurement settings do not change the site appearance.'
      : tabId === 'navigation' ? 'Live navigation preview. Switch devices and open the menu to inspect mobile navigation.'
      : 'Live appearance sample. Changes are kept only when you save.';
    studio.querySelector('.mf-studio-devices').hidden = false;
    resizeStudio();
    window.dispatchEvent(new Event('resize'));

    try {
      window.localStorage.setItem(storageKey, tabId);
    } catch (e) {}
  }

  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      activate(btn.dataset.tab);
    });
  });
  root.querySelectorAll('.mf-tab-control').forEach(control => {
    control.addEventListener('change', () => { if (control.checked) activate(control.value); });
  });
  const navigationTab = root.querySelector('[data-tab="navigation"]');
  const appearanceTab = root.querySelector('[data-tab="theme-fonts"]');
  if (navigationTab && appearanceTab) appearanceTab.after(navigationTab);

  let initial = 'site-basics';
  try {
    const saved = window.localStorage.getItem(storageKey);
    if (saved) initial = saved;
  } catch (e) {}

  const hasTab = Array.prototype.some.call(tabs, t => t.dataset.tab === initial);
  activate(hasTab ? initial : 'site-basics');

  const config = typeof window.modfarmSettingsUi === 'object' ? window.modfarmSettingsUi : {};

  // Reuse the registered controls and their native forms; navigation only
  // changes which group is visible, never its enabled/submitted state.
  function initSettingsHierarchy() {
    const themeMain = root.querySelector('#mf-tab-theme-fonts .mf-settings-main');
    const bookMain = root.querySelector('#mf-tab-book-presentation .mf-settings-main');
    const siteMain = root.querySelector('#mf-tab-site-basics .mf-settings-main');
    const layoutMain = root.querySelector('#mf-tab-page-layouts .mf-settings-main');
    if (!themeMain || !bookMain || !siteMain || !layoutMain) return;
    const themeGroups = Array.from(themeMain.querySelectorAll(':scope > .mf-settings-group'));
    themeGroups.forEach(group => {
      if (!group.dataset.designSection) group.dataset.designSection = group.querySelector('.modfarm-color-field') ? 'colors' : group.querySelector('#mf-hero-default-image') ? 'branding' : 'fonts';
    });
    const makeGroup = (title, parent) => {
      const group = document.createElement('div');
      group.className = 'mf-settings-group';
      const heading = document.createElement('h3'); heading.className = 'mf-group-title'; heading.textContent = title;
      const table = document.createElement('table'); table.className = 'form-table mf-form-table';
      const body = document.createElement('tbody'); table.appendChild(body); group.append(heading, table); parent.appendChild(group);
      return {group, body};
    };
    const navColors = makeGroup('Navigation colors', themeMain);
    const bookColors = makeGroup('Book colors', themeMain);
    [navColors, bookColors].forEach(item => { item.group.dataset.designSection = 'colors'; });
    root.querySelectorAll('#mf-tab-navigation .modfarm-color-field').forEach(control => {
      const row = control.closest('tr'); if (row) navColors.body.appendChild(row);
    });
    bookMain.querySelectorAll('.modfarm-color-field').forEach(control => {
      const row = control.closest('tr'); if (row) bookColors.body.appendChild(row);
    });
    const navFont = settingControl('nav_font');
    if (navFont && navFont.closest('tr') && settingControl('heading_font').closest('.mf-settings-group')) settingControl('heading_font').closest('.mf-settings-group').querySelector('tbody').appendChild(navFont.closest('tr'));
    bookMain.querySelectorAll(':scope > .mf-settings-group').forEach(group => {
      if (!group.querySelector('input,select,textarea')) group.hidden = true;
    });
    const widthGroup = makeGroup('Content width', siteMain);
    const width = settingControl('content_width'); if (width && width.closest('tr')) widthGroup.body.appendChild(width.closest('tr'));
    const originalSiteGroups = Array.from(siteMain.querySelectorAll(':scope > .mf-settings-group')).filter(group => group !== widthGroup.group);
    originalSiteGroups.forEach((group,index) => {
      const archive = root.querySelector('.mf-ppb-layout-panel[data-ppb-layout-type="archive"]');
      if (index === 1 && archive) archive.appendChild(group);
      else layoutMain.appendChild(group);
    });
    organizeSettings(themeMain, navColors.group);
    const legacyNav = root.querySelector(':scope > .mf-tabs'); legacyNav.classList.add('mf-legacy-tabs');
    const primary = document.createElement('nav'); primary.className = 'mf-settings-categories'; primary.setAttribute('aria-label','Settings categories');
    const secondary = document.createElement('nav'); secondary.className = 'mf-settings-subnav'; secondary.setAttribute('aria-label','Settings sections');
    root.insertBefore(primary, workspace); root.insertBefore(secondary, workspace);
    const layoutTabs = root.querySelector('.mf-ppb-layout-tabs');
    const choices = {design:[['branding','Branding'],['colors','Colors'],['fonts','Fonts']],function:[['navigation','Navigation'],['book-presentation','Book cards'],['marketing','Marketing'],['site-basics','Site sizing']]};
    const remembered = {design:'colors',function:'navigation'};
    let category = 'design';
    const routeKey = 'modfarm-settings-section-v2';
    function selectSection(section) {
      remembered[category] = section;
      const design = category === 'design';
      const panelId = design ? 'theme-fonts' : section;
      activate(panelId);
      themeMain.querySelectorAll(':scope > .mf-settings-group').forEach(group => { group.hidden = design && group.dataset.designSection !== section; });
      secondary.querySelectorAll('[data-section]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.section === section)));
      const label = (choices[category] || []).find(item => item[0] === section);
      const panel = root.querySelector('#mf-tab-' + panelId);
      if (label && panel) panel.querySelector('.mf-panel-title').textContent = label[1];
      if (design) root.querySelector('#mf-tab-theme-fonts .mf-panel-intro').textContent = section === 'colors' ? 'Choose colors for the site, navigation, and books.' : section === 'fonts' ? 'Set typography and manage custom fonts.' : 'Manage the identity readers recognize.';
      try { window.localStorage.setItem(routeKey, JSON.stringify({category, section})); } catch (e) {}
    }
    function selectCategory(value) {
      category = value;
      primary.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed',String(button.dataset.category === value)));
      secondary.querySelectorAll('[data-section]').forEach(button => button.remove());
      layoutTabs.hidden = value !== 'layout';
      if (value === 'layout') { secondary.appendChild(layoutTabs); activate('page-layouts'); }
      else {
        choices[value].forEach(([id,label]) => {
          const button = document.createElement('button'); button.type = 'button'; button.dataset.section = id; button.textContent = label;
          button.addEventListener('click', () => selectSection(id)); secondary.appendChild(button);
        });
        selectSection(remembered[value]);
      }
    }
    [['design','Design'],['layout','Layout'],['function','Function']].forEach(([id,label]) => {
      const button = document.createElement('button'); button.type = 'button'; button.dataset.category = id; button.textContent = label;
      button.addEventListener('click', () => selectCategory(id)); primary.appendChild(button);
    });
    // Native details match Presentation and leave closed inputs enabled.
    root.querySelectorAll('.mf-settings-group').forEach(group => {
      const heading = group.querySelector(':scope > .mf-group-title');
      if (!heading || group.tagName === 'DETAILS') return;
      const details = document.createElement('details');
      Array.from(group.attributes).forEach(attribute => details.setAttribute(attribute.name, attribute.value));
      const summary = document.createElement('summary'); summary.textContent = heading.textContent; heading.remove();
      details.appendChild(summary); while (group.firstChild) details.appendChild(group.firstChild);
      group.replaceWith(details);
      if (!details.previousElementSibling || details.classList.contains('mf-ppb-layout-panel')) details.open = true;
    });
    try {
      const saved = JSON.parse(window.localStorage.getItem(routeKey) || 'null');
      if (saved && ['design','layout','function'].includes(saved.category)) {
        category = saved.category;
        if (choices[category] && choices[category].some(item => item[0] === saved.section)) remembered[category] = saved.section;
      }
    } catch (e) {}
    root.classList.add('has-hierarchy'); selectCategory(category);
  }
  function layoutMainCleanup() {
    root.querySelectorAll('#mf-tab-page-layouts .mf-settings-group').forEach(group => { if (!group.querySelector('input,select,textarea,button,details')) group.hidden = true; });
  }
  function organizeSettings(themeMain, navColors) {
    const subgroup = (parent, title, description, controls) => {
      const section = document.createElement('section'); section.className = 'mf-settings-subgroup';
      const heading = document.createElement('h4'); heading.textContent = title;
      const text = document.createElement('p'); text.className = 'description'; text.textContent = description;
      const table = document.createElement('table'); table.className = 'form-table mf-form-table';
      const body = document.createElement('tbody'); table.append(body); section.append(heading, text, table); parent.append(section);
      controls.forEach(id => { const input = settingControl(id); const row = input && input.closest('tr'); if (row) body.append(row); });
      return section;
    };
    const globals = settingControl('primary_color').closest('.mf-settings-group');
    subgroup(globals, 'Site Colors', 'The page surface and text colors used throughout the site.', ['background_color','header_text_color','body_text_color']);
    subgroup(globals, 'Accent Colors', 'Links and buttons guide readers to the next step. Primary and Secondary can also color hero gradients and section backgrounds to highlight content.', ['link_color','primary_color','secondary_color','button_color','button_text_color']);
    subgroup(navColors, 'Desktop Navigation', 'Colors for the main navigation and its dropdown menus.', ['nav_bg_color','nav_text_color','nav_hover_color','submenu_bg_color','submenu_text_color']);
    subgroup(navColors, 'Mobile Navigation', 'Colors for mobile menus. Switch to Tablet or Mobile and open the menu in the visualizer.', ['mobile_nav_bg_color','mobile_nav_text_color']);
    subgroup(navColors, 'Footer Navigation', 'Colors used when Footer Nav Style Mode is set to Manual Footer Colors.', ['footer_nav_bg_color','footer_nav_text_color','footer_nav_hover_color','footer_submenu_bg_color','footer_submenu_text_color']);
    const fonts = settingControl('heading_font').closest('.mf-settings-group');
    ['nav_font','nav_font_size'].forEach(id => { const input = settingControl(id); if (input) fonts.querySelector('tbody').append(input.closest('tr')); });
    const navigation = root.querySelector('#mf-tab-navigation .mf-panel-inner');
    const footer = navigation.querySelector('[data-nav-section="footer"]');
    ['footer_nav_mode','footer_nav_transparent'].forEach(id => { const input = settingControl(id); if (input && footer) footer.querySelector('tbody').append(input.closest('tr')); });
    if (footer) { const mode = settingControl('footer_nav_mode'); footer.querySelector('tbody').prepend(mode.closest('tr')); }
    const navGroup = navigation.querySelector('.mf-settings-group');
    navGroup.querySelector(':scope > .mf-group-title')?.remove();
    navigation.querySelectorAll('.mf-nav-section').forEach(section => { section.classList.add('mf-settings-group'); navigation.insertBefore(section, navGroup); });
    const mainNav = navigation.querySelector('[data-nav-section="main"] tbody');
    ['nav_padding','nav_logo_max_width','nav_logo_max_height','nav_icon_max_width','nav_icon_max_height','nav_brand_gap','nav_transparent'].forEach(id => { const input = settingControl(id); if(input) mainNav.append(input.closest('tr')); });
    // Preserve unused footer profile fields without leaving an empty section.
    navGroup.querySelectorAll('input[type="hidden"]').forEach(input => navigation.querySelector('[data-nav-section="shared"]').append(input));
    if (!navGroup.querySelector('input,select,textarea')) navGroup.remove();
    const postTemplate = settingControl('default_post_template');
    const postPanel = root.querySelector('.mf-ppb-layout-panel[data-ppb-layout-type="post"]');
    if (postTemplate && postPanel) { postPanel.append(postTemplate.closest('tr')); postTemplate.closest('tr').classList.add('mf-layout-mode-row'); }
    root.querySelectorAll('select[name^="mfc_collection_layout_mode["]').forEach(input => {
      const type = input.name.slice('mfc_collection_layout_mode['.length, -1);
      const panel = Array.from(root.querySelectorAll('.mf-ppb-layout-panel')).find(item => item.dataset.ppbLayoutType === 'mfc_collection_single__' + type);
      if (panel) panel.querySelector('tbody').prepend(input.closest('tr'));
    });
    layoutMainCleanup();
    const display = root.querySelector('.mf-archive-display-defaults');
    if (display) {
      const group = document.createElement('div'); group.className = 'mf-settings-group mf-ppb-layout-panel'; group.dataset.ppbLayoutType = 'book';
      display.parentElement.after(group); group.append(display);
      display.querySelectorAll('tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length !== 4) return;
        ['Page type','Books Per Row','Book Order','List Mode'].forEach((label,i) => cells[i].dataset.label = label);
      });
    }
    root.querySelectorAll('.mf-ppb-layout-panel').forEach(panel => {
      const heading = panel.querySelector(':scope > .mf-group-title');
      if (heading && !panel.querySelector('details')) {
        const note = document.createElement('p'); note.className = 'description';
        note.textContent = 'These patterns set the layout for new pages. Existing PPB pages keep their layouts until you use PPB Apply All or their individual PPB controls. Hybrid headers and footers follow saved defaults unless locally overridden.';
        heading.after(note);
      }
    });
    // Hybrid bodies are authored content, not centrally selected patterns. Keep
    // their saved default submitted, while presenting a disabled selector.
    ['post','update','event'].forEach(type => {
      const input = settingControl(type + '_body_pattern'); if (!input) return;
      const hybrid = type !== 'post' || !settingValue('default_post_template').includes('ppb');
      if (!hybrid) return;
      const mirror = document.createElement('input'); mirror.type = 'hidden'; mirror.name = input.name; mirror.value = input.value;
      input.after(mirror); input.disabled = true; input.classList.add('mf-hybrid-body');
      const note = document.createElement('p'); note.className = 'description'; note.textContent = 'Hybrid uses the content written in the editor. A Body pattern applies only to pages explicitly using PPB.'; input.after(note);
    });
    const help = (target, items) => {
      if (!target) return;
      const links = document.createElement('div'); links.className = 'mf-settings-help';
      items.forEach(([icon,title,text]) => {
        const details = document.createElement('details'); const summary = document.createElement('summary');
        const symbol = document.createElement('span'); symbol.className = 'dashicons dashicons-' + icon; symbol.setAttribute('aria-hidden','true');
        summary.append(symbol, document.createTextNode(' ' + title)); const p = document.createElement('p'); p.textContent = text; details.append(summary,p); links.append(details);
      }); target.append(links);
    };
    help(root.querySelector('.mf-ppb-layout-sample'), [['info','Overview','Choose named content, then select patterns to preview Header, Body and Footer. Previewing never changes the site.'],['book','Guide','Save defaults for future pages. Dynamic archives and Hybrid headers and footers use saved defaults unless overridden locally.'],['sos','Help','If a preview is empty, choose another sample. Individual pages and archive terms can have local settings that take precedence.']]);
    help(Array.from(root.querySelectorAll('.mf-major-title')).find(title => title.textContent === 'PPB Apply All')?.parentElement, [['info','Overview','Apply All replaces one selected zone across eligible existing pages. It is a deliberate redesign operation.'],['book','Guide','Choose content type, zone and pattern, then Preview Impact. Review the affected items and confirm before applying. Locks protect zones; matching content-slot IDs preserve portable content.'],['sos','Help','Back up before a redesign. Manual edits outside matching content slots can be removed. Review skipped items and failures in the report and Recent PPB Runs.']]);
    help(root.querySelector('#modfarm-font-family')?.closest('.mf-settings-group'), [['info','Overview','Upload a local font face to make it available in font selectors.'],['book','Guide','Use the same family name for each weight and italic face. WOFF2 is preferred for smaller downloads.'],['sos','Help','Use fonts you have permission to publish. If a face looks wrong, check its family, weight and style before uploading again.']]);
  }

  initSettingsHierarchy();
  root.querySelectorAll('.modfarm-color-field').forEach(control => {
    const row = control.closest('tr'); if (!row) return;
    row.classList.add('mf-color-option');
    const update = () => row.style.setProperty('--mf-swatch', /^#[0-9a-f]{3,8}$/i.test(control.value) ? control.value : 'transparent');
    control.addEventListener('input', update); control.addEventListener('change', update); update();
  });
  ['book_card_cover_shape','book_card_button_shape','book_card_sample_shape','book_card_cta_mode','book_card_shadow_style'].forEach(id => {
    const select = settingControl(id); if (!select || select.tagName !== 'SELECT') return;
    const choices = document.createElement('div'); choices.className = 'mf-choice-cards'; choices.setAttribute('role','group');
    choices.setAttribute('aria-label', select.closest('tr').querySelector('th').textContent.trim());
    Array.from(select.options).forEach(option => {
      const button = document.createElement('button'); button.type='button'; button.textContent=option.textContent; button.dataset.value=option.value;
      button.addEventListener('click', () => { select.value=option.value; select.dispatchEvent(new Event('change',{bubbles:true})); }); choices.appendChild(button);
    });
    const sync = () => choices.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed',String(button.dataset.value === select.value)));
    select.after(choices); select.classList.add('mf-choice-native'); select.addEventListener('change',sync); sync();
  });

  function settingControl(fieldId) {
    return document.querySelector(`[name="modfarm_theme_settings[${fieldId}]"]`);
  }

  function settingValue(fieldId, fallback) {
    const control = settingControl(fieldId);
    if (!control) {
      return fallback || '';
    }

    if (control.type === 'checkbox') {
      return control.checked ? '1' : '';
    }

    return control.value || fallback || '';
  }

  function setStyleVar(target, name, value) {
    if (!target) return;
    if (value === '' || value === null || typeof value === 'undefined') {
      target.style.removeProperty(name);
      return;
    }
    target.style.setProperty(name, value);
  }

  function bindSettingPreview(fields, callback) {
    fields.forEach((fieldId) => {
      const control = settingControl(fieldId);
      if (!control) return;
      ['change', 'input', 'keyup'].forEach((eventName) => {
        control.addEventListener(eventName, callback);
      });
    });
  }

  function initScopedPanelPreviews() {
    const themePreview = document.getElementById('mf-theme-live-preview');
    const bookPreview = document.getElementById('mf-book-live-preview');
    const bookSample = config.bookPreviewSample || {};
    const themeFields = [
      'content_width',
      'primary_color',
      'secondary_color',
      'background_color',
      'header_text_color',
      'body_text_color',
      'link_color',
      'button_color',
      'button_text_color',
      'heading_font',
      'body_font',
      'site_title_font',
      'site_title_font_size',
      'nav_font',
      'nav_bg_color',
      'nav_text_color',
      'nav_hover_color',
      'nav_font_size',
      'nav_padding',
      'nav_transparent',
      'footer_nav_mode',
      'footer_nav_bg_color',
      'footer_nav_text_color',
      'footer_nav_transparent'
    ];
    const bookFields = [
      'primary_color',
      'header_text_color',
      'body_text_color',
      'button_color',
      'button_text_color',
      'book_card_button_bg_color',
      'book_card_button_text_color',
      'book_card_button_border_color',
      'book_card_sample_bg_color',
      'book_card_sample_text_color',
      'book_card_sample_border_color',
      'book_card_pagination_accent_color',
      'book_card_cover_shape',
      'book_card_button_shape',
      'book_card_sample_shape',
      'book_card_cta_mode',
      'book_card_shadow_style',
      'book_card_hide_title',
      'book_card_hide_series',
      'book_card_hide_primary_button',
      'book_card_hide_sample_button',
      'book_page_primary_bg_color',
      'book_page_primary_text_color',
      'book_page_primary_border_color',
      'book_page_secondary_bg_color',
      'book_page_secondary_text_color',
      'book_page_secondary_border_color',
      'book_page_button_border_width',
      'book_page_button_radius'
    ];

    function buttonRadius(shape) {
      if (shape === 'pill') return '999px';
      if (shape === 'rounded') return '16px';
      return '0px';
    }

    function navPadding(value) {
      if (value === 'compact') return '10px 14px';
      if (value === 'spacious') return '20px 22px';
      return '14px 16px';
    }

    function pxValue(value, fallback) {
      const number = parseInt(value, 10);
      if (Number.isNaN(number) || number < 0) {
        return fallback;
      }
      return `${number}px`;
    }

    function shadowValue(style) {
      if (style === 'shadow-sm') return '0 1px 6px rgba(0,0,0,.08)';
      if (style === 'shadow-md') return '0 4px 16px rgba(0,0,0,.12)';
      if (style === 'shadow-lg') return '0 10px 30px rgba(0,0,0,.18)';
      if (style === 'emboss') return '0 4px 16px rgba(0,0,0,.12), inset 0 2px 6px rgba(255,255,255,.35), inset 0 -6px 12px rgba(0,0,0,.06)';
      return 'none';
    }

    function updateThemePreview() {
      if (!themePreview) return;
      const page = themePreview.querySelector('.mf-theme-visualizer__page');
      if (page) {
        const width = settingValue('content_width', '1200px');
        page.style.maxWidth = window.CSS && window.CSS.supports('max-width', width) ? width : '1200px';
        page.style.margin = '0 auto';
      }

      const primary = settingValue('primary_color', '#94a3b8');
      const secondary = settingValue('secondary_color', '#475569');
      const background = settingValue('background_color', '#ffffff');
      const body = settingValue('body_text_color', '#1d2327');
      const heading = settingValue('header_text_color', body);
      const link = settingValue('link_color', primary || '#2271b1');
      const buttonBg = settingValue('button_color', primary || '#2563eb');
      const buttonText = settingValue('button_text_color', '#ffffff');
      const navBg = settingValue('nav_transparent') === '1' ? 'transparent' : settingValue('nav_bg_color', '#111827');
      const navText = settingValue('nav_text_color', '#ffffff');
      const footerBg = settingValue('footer_nav_transparent') === '1' ? 'transparent' : settingValue('footer_nav_bg_color', navBg);
      const footerText = settingValue('footer_nav_text_color', navText);

      setStyleVar(themePreview, '--mf-preview-primary', primary);
      setStyleVar(themePreview, '--mf-preview-secondary', secondary);
      setStyleVar(themePreview, '--mf-preview-bg', background);
      setStyleVar(themePreview, '--mf-preview-body', body);
      setStyleVar(themePreview, '--mf-preview-heading', heading);
      setStyleVar(themePreview, '--mf-preview-link', link);
      setStyleVar(themePreview, '--mf-preview-button-bg', buttonBg);
      setStyleVar(themePreview, '--mf-preview-button-border', buttonBg);
      setStyleVar(themePreview, '--mf-preview-button-text', buttonText);
      setStyleVar(themePreview, '--mf-preview-heading-font', settingValue('heading_font', 'inherit'));
      setStyleVar(themePreview, '--mf-preview-body-font', settingValue('body_font', 'inherit'));
      setStyleVar(themePreview, '--mf-preview-site-title-font', settingValue('site_title_font', 'inherit'));
      setStyleVar(themePreview, '--mf-preview-site-title-size', pxValue(settingValue('site_title_font_size'), 'inherit'));
      setStyleVar(themePreview, '--mf-preview-nav-font', settingValue('nav_font', 'inherit'));
      setStyleVar(themePreview, '--mf-preview-nav-bg', navBg);
      setStyleVar(themePreview, '--mf-preview-nav-text', navText);
      setStyleVar(themePreview, '--mf-preview-nav-hover', settingValue('nav_hover_color', link));
      setStyleVar(themePreview, '--mf-preview-nav-size', pxValue(settingValue('nav_font_size'), '13px'));
      setStyleVar(themePreview, '--mf-preview-nav-pad', navPadding(settingValue('nav_padding')));
      setStyleVar(themePreview, '--mf-preview-footer-bg', footerBg);
      setStyleVar(themePreview, '--mf-preview-footer-text', footerText);
    }

    function updateBookPreview() {
      if (!bookPreview) return;

      const globalButtonBg = settingValue('button_color', settingValue('primary_color', '#f2b100'));
      const globalButtonText = settingValue('button_text_color', '#111111');
      const buttonBg = settingValue('book_card_button_bg_color', globalButtonBg);
      const buttonText = settingValue('book_card_button_text_color', globalButtonText);
      const buttonBorder = settingValue('book_card_button_border_color', buttonBg);
      const sampleBg = settingValue('book_card_sample_bg_color', 'transparent');
      const sampleText = settingValue('book_card_sample_text_color', settingValue('body_text_color', '#1d2327'));
      const sampleBorder = settingValue('book_card_sample_border_color', sampleText);
      const paginationAccent = settingValue('book_card_pagination_accent_color', settingValue('primary_color', '#111111'));
      const pagePrimaryBg = settingValue('book_page_primary_bg_color', globalButtonBg);
      const pagePrimaryText = settingValue('book_page_primary_text_color', globalButtonText);
      const pagePrimaryBorder = settingValue('book_page_primary_border_color', pagePrimaryBg);
      const pageSecondaryBg = settingValue('book_page_secondary_bg_color', 'transparent');
      const pageSecondaryText = settingValue('book_page_secondary_text_color', settingValue('body_text_color', '#1d2327'));
      const pageSecondaryBorder = settingValue('book_page_secondary_border_color', pageSecondaryText);
      const ctaMode = settingValue('book_card_cta_mode');
      bookPreview.classList.toggle('is-joined', ctaMode !== 'gap');
      bookPreview.dataset.shadow = settingValue('book_card_shadow_style');

      setStyleVar(bookPreview, '--mf-preview-body', settingValue('body_text_color', '#1d2327'));
      setStyleVar(bookPreview, '--mf-preview-heading', settingValue('header_text_color', settingValue('body_text_color', '#1d2327')));
      setStyleVar(bookPreview, '--mf-preview-button-bg', buttonBg);
      setStyleVar(bookPreview, '--mf-preview-button-border', buttonBorder);
      setStyleVar(bookPreview, '--mf-preview-button-text', buttonText);
      setStyleVar(bookPreview, '--mf-preview-sample-bg', sampleBg);
      setStyleVar(bookPreview, '--mf-preview-sample-border', sampleBorder);
      setStyleVar(bookPreview, '--mf-preview-sample-text', sampleText);
      setStyleVar(bookPreview, '--mf-preview-cover-radius', settingValue('book_card_cover_shape') === 'rounded' ? '16px' : '0px');
      setStyleVar(bookPreview, '--mf-preview-button-radius', buttonRadius(settingValue('book_card_button_shape')));
      setStyleVar(bookPreview, '--mf-preview-sample-radius', buttonRadius(settingValue('book_card_sample_shape')));
      setStyleVar(bookPreview, '--mf-preview-cta-gap', ctaMode === 'gap' ? '10px' : '0px');
      setStyleVar(bookPreview, '--mf-preview-shadow', shadowValue(settingValue('book_card_shadow_style')));
      setStyleVar(bookPreview, '--mf-preview-pagination-accent', paginationAccent);
      setStyleVar(bookPreview, '--mf-preview-pagination-on-accent', settingValue('button_text_color', '#ffffff'));
      setStyleVar(bookPreview, '--mf-preview-page-primary-bg', pagePrimaryBg);
      setStyleVar(bookPreview, '--mf-preview-page-primary-text', pagePrimaryText);
      setStyleVar(bookPreview, '--mf-preview-page-primary-border', pagePrimaryBorder);
      setStyleVar(bookPreview, '--mf-preview-page-secondary-bg', pageSecondaryBg);
      setStyleVar(bookPreview, '--mf-preview-page-secondary-text', pageSecondaryText);
      setStyleVar(bookPreview, '--mf-preview-page-secondary-border', pageSecondaryBorder);
      setStyleVar(bookPreview, '--mf-preview-page-button-border-width', pxValue(settingValue('book_page_button_border_width'), '1px'));
      setStyleVar(bookPreview, '--mf-preview-page-button-radius', pxValue(settingValue('book_page_button_radius'), '0px'));

      const cover = bookPreview.querySelector('.mf-book-visualizer__cover');
      const coverText = cover ? cover.querySelector('span') : null;
      const title = bookPreview.querySelector('.mf-book-visualizer__title');
      const author = bookPreview.querySelector('.mf-book-visualizer__series');
      const button = bookPreview.querySelector('.mf-book-visualizer__primary');
      const sampleButton = bookPreview.querySelector('.mf-book-visualizer__sample');
      if (cover) {
        if (bookSample.coverUrl) {
          cover.style.backgroundImage = `url("${String(bookSample.coverUrl).replace(/"/g, '%22')}")`;
          cover.classList.add('has-cover-image');
        } else {
          cover.style.removeProperty('background-image');
          cover.classList.remove('has-cover-image');
        }
      }
      if (coverText) coverText.hidden = !!bookSample.coverUrl;
      if (title) title.textContent = bookSample.title || 'Book Title';
      if (author) author.textContent = bookSample.series || 'Series Name';
      if (title) title.hidden = settingValue('book_card_hide_title') === '1';
      if (author) author.hidden = settingValue('book_card_hide_series') === '1';
      if (button) button.hidden = settingValue('book_card_hide_primary_button') === '1';
      if (sampleButton) sampleButton.hidden = settingValue('book_card_hide_sample_button') === '1';
    }

    bindSettingPreview(themeFields, updateThemePreview);
    bindSettingPreview(bookFields, updateBookPreview);
    updateThemePreview();
    updateBookPreview();
  }

  initScopedPanelPreviews();
  function initAppearancePreview() {
    if (!themePreview || !bookPreview || !config.appearanceNonce) return;
    const media = document.createElement('div'); media.className = 'mf-book-visualizer__media';
    const card = bookPreview.querySelector('.mf-book-visualizer__card');
    const cover = card.querySelector('.mf-book-visualizer__cover');
    cover.before(media); media.append(cover, card.querySelector('.mf-book-visualizer__primary'));
    const frame = document.createElement('iframe'); frame.className = 'mf-appearance-frame'; frame.title = 'Live site appearance and navigation';
    frame.setAttribute('sandbox','allow-scripts');
    themePreview.append(frame); themePreview.classList.add('has-native-preview');
    let timer, sequence = 0;
    const status = document.createElement('p'); status.className = 'mf-appearance-status'; status.setAttribute('role','status'); studio.append(status);
    function cloneSample() {
      const sample = themePreview.cloneNode(true); sample.querySelector('iframe').remove(); sample.classList.remove('has-native-preview'); sample.removeAttribute('id'); sample.hidden = false;
      const row = sample.querySelector('.mf-theme-visualizer__book-row'); row.replaceChildren();
      for (let i=0; i<3; i++) { const book = bookPreview.cloneNode(true); book.removeAttribute('id'); book.hidden = false; row.append(book); }
      const title = form.querySelector('[name="modfarm_site_identity[blogname]"]');
      if (title) sample.querySelector('.mf-theme-visualizer__site-title').textContent = title.value;
      return sample;
    }
    async function render() {
      const request = ++sequence;
      const settings = {};
      new FormData(form).forEach((value,name) => {
        const profile = name.match(/^modfarm_theme_settings\[navigation_profiles\]\[(header|footer)\]\[(\w+)\]$/);
        const field = name.match(/^modfarm_theme_settings\[(\w+)\]$/);
        if (profile) { settings.navigation_profiles ||= {}; settings.navigation_profiles[profile[1]] ||= {}; settings.navigation_profiles[profile[1]][profile[2]] = value; }
        else if (field) settings[field[1]] = value;
      });
      const identity = {};
      new FormData(form).forEach((value,name) => { const match = name.match(/^modfarm_site_identity\[(\w+)\]$/); if (match) identity[match[1]] = value; });
      const payload = new URLSearchParams({action:'modfarm_settings_appearance',nonce:config.appearanceNonce,settings:JSON.stringify(settings),identity:JSON.stringify(identity)});
      try {
        const response = await fetch(config.ajaxUrl,{method:'POST',credentials:'same-origin',body:payload});
        const result = await response.json(); if (request !== sequence) return;
        if (!result.success) throw new Error(result.data?.message || 'Preview unavailable');
        const sample = cloneSample();
        sample.querySelector('.mf-theme-visualizer__nav').outerHTML = result.data.header;
        sample.querySelector('.mf-theme-visualizer__footer').outerHTML = result.data.footer;
        const base = config.themeUrl.replace(/\/$/,'');
        frame.srcdoc = '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1">' +
          '<link rel="stylesheet" href="'+base+'/assets/css/modfarm-settings-ui.css"><link rel="stylesheet" href="'+base+'/blocks/navigation-menu/style.css">' + result.data.tokens +
          '<style>[hidden]{display:none!important}html,body{margin:0;padding:0}body{background:var(--mfs-background,#fff)}.mf-theme-visualizer{border:0;border-radius:0}.mf-theme-visualizer__hero{padding:60px}.mf-theme-visualizer__book-row .mf-book-visualizer{display:block}.mf-book-visualizer__page-buttons{margin-top:18px}@media(max-width:800px){.mf-theme-visualizer__book-row{grid-template-columns:1fr 1fr}.mf-theme-visualizer__hero{padding:30px 20px}}@media(max-width:480px){.mf-theme-visualizer__book-row{grid-template-columns:1fr}}</style></head><body>'+sample.outerHTML+
          '<script src="'+base+'/assets/js/navigation-toggle.js"><\/script><script>document.addEventListener("click",function(e){var a=e.target.closest("a");if(a)e.preventDefault()});<\/script></body></html>';
        status.textContent = '';
      } catch (error) { if (request === sequence) status.textContent = 'Appearance preview could not refresh. ' + error.message; }
    }
    form.addEventListener('input', () => { clearTimeout(timer); timer=setTimeout(render,250); });
    form.addEventListener('change', () => { clearTimeout(timer); timer=setTimeout(render,250); });
    document.addEventListener('click', event => {
      const select = event.target.closest('[data-identity-select]'); const clear = event.target.closest('[data-identity-clear]');
      if (!select && !clear) return;
      const key = (select || clear).dataset[select ? 'identitySelect' : 'identityClear'];
      const input = document.getElementById('mf-identity-'+key); const image = input.parentElement.querySelector('img');
      if (clear) { input.value='0'; image.hidden=true; input.dispatchEvent(new Event('change',{bubbles:true})); return; }
      const picker = window.wp.media({title:'Choose '+(key==='custom_logo'?'site logo':'site icon'),library:{type:'image'},multiple:false});
      picker.on('select',()=>{ const item=picker.state().get('selection').first().toJSON(); input.value=item.id; image.src=item.url; image.hidden=false; input.dispatchEvent(new Event('change',{bubbles:true})); }); picker.open();
    });
    render();
  }
  initAppearancePreview();


  function initPpbVisualizer() {
    const visualizer = document.getElementById('mf-ppb-visualizer');
    if (!visualizer || !config.ajaxUrl) return;

    const types = config.visualizerTypes || {};
    const samples = config.visualizerSamples || {};
    const layoutTabs = document.querySelectorAll('.mf-ppb-layout-tab');
    const layoutPanels = document.querySelectorAll('.mf-ppb-layout-panel');
    const sampleSelect = document.getElementById('mf-ppb-visualizer-sample');
    const frame = document.getElementById('mf-ppb-visualizer-frame');
    const frameWrap = visualizer.querySelector('.mf-ppb-visualizer__frame-wrap');
    const feedback = document.getElementById('mf-ppb-visualizer-feedback');
    const refreshButton = document.getElementById('mf-ppb-visualizer-refresh');
    const initialPatterns = new Map();
    Object.values(types).forEach(type => Object.values(type.fields || {}).forEach(field => {
      const select = fieldSelect(field);
      if (select && !initialPatterns.has(field)) initialPatterns.set(field, selectedValue(select));
    }));
    let activeType = 'book';
    let archivePreviewType = '';
    let refreshTimer = null;
    let requestId = 0;

    function fieldSelect(fieldId) {
      return document.querySelector(`select[name="modfarm_theme_settings[${fieldId}]"]`) ||
        document.querySelector(`select[data-mf-ppb-field-id="${fieldId}"]`);
    }

    function activeFields() {
      return ((types[archivePreviewType || activeType] || {}).fields) || {};
    }

    function selectedValue(selectEl) {
      if (!selectEl) {
        return 'default';
      }

      return selectEl.value || 'default';
    }

    function setFeedback(message, isError) {
      if (!feedback) return;
      feedback.textContent = message || '';
      feedback.classList.toggle('is-error', !!isError);
      feedback.classList.toggle('is-active', !!message);
    }

    function updateLayoutTabs() {
      layoutTabs.forEach((tab) => {
        const isActive = tab.getAttribute('data-ppb-layout-type') === activeType;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      layoutPanels.forEach((panel) => {
        panel.classList.toggle('is-active', panel.getAttribute('data-ppb-layout-type') === activeType);
      });
    }

    function updateSampleSelect() {
      if (!sampleSelect) return;

      const typeSamples = samples[activeType] || [];
      sampleSelect.innerHTML = '';

      if (!typeSamples.length) {
        const opt = document.createElement('option');
        opt.value = '0';
        opt.textContent = activeType === 'archive' ? 'Default archive context' : 'No sample content found';
        sampleSelect.appendChild(opt);
        sampleSelect.disabled = true;
        return;
      }

      typeSamples.forEach((item) => {
        const opt = document.createElement('option');
        opt.value = item.value;
        opt.textContent = item.label;
        sampleSelect.appendChild(opt);
      });
      sampleSelect.disabled = false;
    }

    function currentPatterns() {
      const fields = activeFields();
      return {
        header: selectedValue(fieldSelect(fields.header)),
        body: selectedValue(fieldSelect(fields.body)),
        footer: selectedValue(fieldSelect(fields.footer))
      };
    }

    function changedPatterns() {
      const changed = {};
      Object.entries(activeFields()).forEach(([zone, field]) => {
        const select = fieldSelect(field);
        if (select && selectedValue(select) !== initialPatterns.get(field)) changed[zone] = selectedValue(select);
      });
      return changed;
    }

    function updateViewport() {
      if (!frame || !frameWrap) return;
      frameWrap.setAttribute('data-viewport', deviceWidth === 390 ? 'mobile' : deviceWidth === 768 ? 'tablet' : 'desktop');
      frame.style.width = '100%'; frame.style.height = '100%';
      frame.style.transform = 'none'; frame.style.marginLeft = '0';
      resizeStudio();
    }

    function queueRefresh(delay) {
      window.clearTimeout(refreshTimer);
      refreshTimer = window.setTimeout(refreshPreview, delay || 150);
    }

    async function refreshPreview() {
      if (!frame) return;

      const currentRequest = ++requestId;
      setFeedback('Rendering preview...');

      const payload = new window.FormData();
      payload.set('action', 'modfarm_ppb_visualizer_preview');
      payload.set('nonce', config.visualizerNonce || '');
      payload.set('contentType', archivePreviewType || activeType);
      payload.set('activeZone', 'body');
      payload.set('previewScope', 'ppb_layout');
      payload.set('sampleId', !archivePreviewType && sampleSelect && !sampleSelect.disabled ? sampleSelect.value : '0');
      payload.set('patterns', JSON.stringify(currentPatterns()));
      payload.set('changedPatterns', JSON.stringify(changedPatterns()));

      try {
        const response = await window.fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: payload
        });
        const data = await response.json();

        if (currentRequest !== requestId) {
          return;
        }

        if (!data || !data.success || !data.data || (!data.data.html && !data.data.url)) {
          const message = data && data.data && data.data.message ? data.data.message : 'Preview could not be generated.';
          throw new Error(message);
        }

        if (data.data.url) {
          frame.removeAttribute('srcdoc');
          frame.src = data.data.url;
        } else {
          frame.removeAttribute('src');
          frame.srcdoc = data.data.html;
        }
        updateViewport();
        setFeedback(data.data.message || 'Pattern sample · no saved content selected.');
      } catch (error) {
        setFeedback(error && error.message ? error.message : 'Preview could not be generated.', true);
      }
    }

    frame.addEventListener('load', () => {
      const doc = frame.contentDocument;
      if (!doc) return;
      doc.addEventListener('click', event => { if (event.target.closest('a[href]')) event.preventDefault(); }, true);
      doc.addEventListener('submit', event => event.preventDefault(), true);
    });
    layoutTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        archivePreviewType = '';
        activeType = tab.getAttribute('data-ppb-layout-type') || 'book';
        const applyTypeSelect = document.getElementById('mf-ppb-preview-content-type');
        if (applyTypeSelect && applyTypeSelect.querySelector(`option[value="${activeType}"]`)) {
          applyTypeSelect.value = activeType;
          applyTypeSelect.dispatchEvent(new window.Event('change', { bubbles: true }));
        }
        updateLayoutTabs();
        updateSampleSelect();
        queueRefresh(0);
      });
    });

    Object.keys(types).forEach((typeKey) => {
      const fields = (types[typeKey] || {}).fields || {};
      ['header', 'body', 'footer'].forEach((zone) => {
        const selectEl = fieldSelect(fields[zone]);
        if (!selectEl) return;
        selectEl.addEventListener('change', () => {
          if (typeKey === (archivePreviewType || activeType)) {
            queueRefresh(150);
          }
        });
      });
    });

    root.querySelectorAll('[data-archive-layout-preview]').forEach(button => {
      button.addEventListener('click', () => {
        archivePreviewType = button.dataset.archiveLayoutPreview;
        queueRefresh(0);
      });
    });
    if (sampleSelect) {
      sampleSelect.addEventListener('change', () => { archivePreviewType = ''; queueRefresh(0); });
    }
    if (refreshButton) {
      refreshButton.addEventListener('click', () => queueRefresh(0));
    }
    window.addEventListener('resize', updateViewport);
    updateLayoutTabs();
    updateSampleSelect();
    updateViewport();
    queueRefresh(0);
  }

  initPpbVisualizer();

  const previewRoot = document.getElementById('mf-ppb-apply-all-preview');
  if (!previewRoot || !config.ajaxUrl) {
    return;
  }

  const patterns = config.applyAllPatterns || {};
  const contentTypeLabels = config.contentTypeLabels || {};
  const messages = config.messages || {};
  const contentTypeSelect = document.getElementById('mf-ppb-preview-content-type');
  const zoneSelect = document.getElementById('mf-ppb-preview-zone');
  const patternSelect = document.getElementById('mf-ppb-preview-pattern');
  const patternNote = document.getElementById('mf-ppb-preview-pattern-note');
  const runButton = document.getElementById('mf-ppb-preview-run');
  const feedback = document.getElementById('mf-ppb-preview-feedback');
  const results = document.getElementById('mf-ppb-preview-results');
  const executeWrap = document.getElementById('mf-ppb-preview-execute');
  const confirmInput = document.getElementById('mf-ppb-preview-confirm');
  const applyButton = document.getElementById('mf-ppb-preview-apply');
  const safeConvertContentTypeSelect = document.getElementById('mf-ppb-safe-convert-content-type');
  const safeConvertRunButton = document.getElementById('mf-ppb-safe-convert-run');
  const safeConvertFeedback = document.getElementById('mf-ppb-safe-convert-feedback');
  const safeConvertResults = document.getElementById('mf-ppb-safe-convert-results');
  const safeConvertExecuteWrap = document.getElementById('mf-ppb-safe-convert-execute');
  const safeConvertConfirmInput = document.getElementById('mf-ppb-safe-convert-confirm');
  const safeConvertApplyButton = document.getElementById('mf-ppb-safe-convert-apply');
  const runLog = document.getElementById('mf-ppb-run-log');
  let lastPreviewReport = null;
  let lastSafeConvertReport = null;
  let previewListState = {
    filter: 'will_update',
    visible: Number(config.previewPageSize || 50)
  };

  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function previewStatusMeta(item) {
    const action = String((item && item.action) || 'skip_legacy');
    if (action === 'will_update') {
      return { cls: 'is-update', label: 'Will update' };
    }
    if (action === 'skip_locked') {
      return { cls: 'is-locked', label: 'Skipped locked' };
    }
    return { cls: 'is-skip', label: 'Skipped non-zoned' };
  }

  function getPreviewFilterCount(report, filter) {
    const totals = (report && report.totals) || {};
    switch (filter) {
      case 'will_update':
        return Number(totals.will_update || 0);
      case 'skip_locked':
        return Number(totals.skipped_locked || 0);
      case 'skip_legacy':
        return Number(totals.skipped_legacy || 0);
      case 'conflicts':
        return Number(totals.potential_conflicts || 0);
      default:
        return Number(totals.items || 0);
    }
  }

  function matchesPreviewFilter(item, filter) {
    if (filter === 'all') {
      return true;
    }
    if (filter === 'conflicts') {
      return !!(((item || {}).zone || {}).duplicate_slot_ids || []).length;
    }
    return String((item && item.action) || '') === filter;
  }

  function buildPreviewItemsMarkup(report) {
    const items = Array.isArray(report && report.items) ? report.items : [];
    if (!items.length) {
      return '<p class="description">No matching items were found for this preview.</p>';
    }

    const filtered = items.filter((item) => matchesPreviewFilter(item, previewListState.filter));
    if (!filtered.length) {
      return '<p class="description">No items match the current filter.</p>';
    }

    const shown = filtered.slice(0, previewListState.visible);
    const summary = `<div class="mf-ppb-preview-list__summary">Showing ${shown.length} of ${filtered.length} matching items.</div>`;
    const list = shown.map((item) => {
      const status = previewStatusMeta(item);
      const title = escapeHtml(item.title || 'Untitled');
      const editLink = item.edit_link ? `<a href="${escapeHtml(item.edit_link)}">${title}</a>` : title;
      const meta = [
        escapeHtml(item.content_state || 'Unknown'),
        escapeHtml(item.layout_mode || 'Unknown layout'),
        `Status: ${escapeHtml(item.status || 'unknown')}`,
        (((item || {}).zone || {}).locked) ? 'Locked' : '',
        (((item || {}).zone || {}).contains_content_slot) ? 'Content-slot preserved' : ''
      ].filter(Boolean).map((part) => `<span>${part}</span>`).join('');
      const notes = Array.isArray(item.notes) && item.notes.length
        ? `<div class="mf-ppb-preview-item__notes">${escapeHtml(item.notes.join(' '))}</div>`
        : '';

      return `
        <li class="mf-ppb-preview-item">
          <div class="mf-ppb-preview-item__top">
            <strong>${editLink}</strong>
            <span class="mf-ppb-preview-pill ${status.cls}">${status.label}</span>
          </div>
          <div class="mf-ppb-preview-item__meta">${meta}</div>
          ${notes}
        </li>
      `;
    }).join('');

    const canLoadMore = shown.length < filtered.length;
    const loadMore = canLoadMore
      ? `<div class="mf-ppb-preview-list__actions"><button type="button" class="button button-secondary" id="mf-ppb-preview-load-more">Load more</button></div>`
      : '';

    return `${summary}<ul class="mf-ppb-preview-items">${list}</ul>${loadMore}`;
  }

  function renderPreviewReport(report) {
    if (!results) {
      return;
    }

    const totals = (report && report.totals) || {};
    const filterButtons = [
      { key: 'will_update', label: 'Will update' },
      { key: 'skip_locked', label: 'Skipped locked' },
      { key: 'skip_legacy', label: 'Skipped non-zoned' },
      { key: 'conflicts', label: 'Conflicts' },
      { key: 'all', label: 'All' }
    ].map((filter) => {
      const active = previewListState.filter === filter.key ? ' is-active' : '';
      return `<button type="button" class="mf-ppb-preview-filter${active}" data-filter="${filter.key}">${filter.label} (${getPreviewFilterCount(report, filter.key)})</button>`;
    }).join('');

    results.innerHTML = `
      <div class="mf-ppb-preview-report">
        <div class="mf-ppb-preview-header">
          <div>
            <h4>Apply All Preview</h4>
            <p>${escapeHtml(String(contentTypeLabels[report.content_type] || report.content_type || ''))} · ${escapeHtml(String(report.zone || ''))} Zone · ${escapeHtml(String(report.pattern || ''))}</p>
          </div>
        </div>
        <div class="mf-ppb-preview-stats">
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Total items</span><strong>${Number(totals.items || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Will update</span><strong>${Number(totals.will_update || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Skipped locked</span><strong>${Number(totals.skipped_locked || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Skipped non-zoned</span><strong>${Number(totals.skipped_legacy || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Slot content detected</span><strong>${Number(totals.slot_content_detected || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Potential conflicts</span><strong>${Number(totals.potential_conflicts || 0)}</strong></div>
        </div>
        <div class="mf-ppb-preview-list">
          <h5>Affected items</h5>
          <div class="mf-ppb-preview-filters">${filterButtons}</div>
          ${buildPreviewItemsMarkup(report)}
        </div>
      </div>
    `;

    const filterWrap = results.querySelector('.mf-ppb-preview-filters');
    if (filterWrap) {
      filterWrap.querySelectorAll('[data-filter]').forEach((button) => {
        button.addEventListener('click', () => {
          previewListState.filter = button.getAttribute('data-filter') || 'will_update';
          previewListState.visible = Number(config.previewPageSize || 50);
          renderPreviewReport(report);
        });
      });
    }

    const loadMoreButton = results.querySelector('#mf-ppb-preview-load-more');
    if (loadMoreButton) {
      loadMoreButton.addEventListener('click', () => {
        previewListState.visible += Number(config.previewPageSize || 50);
        renderPreviewReport(report);
      });
    }
  }

  function renderSafeConvertReport(report) {
    if (!safeConvertResults) {
      return;
    }

    const totals = (report && report.totals) || {};
    const items = Array.isArray(report && report.items) ? report.items.slice(0, Number(config.previewPageSize || 50)) : [];
    const contentTypeLabel = contentTypeLabels[report.content_type] || report.content_type || '';
    const itemsMarkup = items.length
      ? `<ul class="mf-ppb-preview-items">${items.map((item) => {
        const statusClass = item.action === 'will_convert' ? 'is-update' : 'is-skip';
        const statusLabel = item.action === 'will_convert' ? 'Will convert' : 'Skipped zoned';
        const title = escapeHtml(item.title || 'Untitled');
        const editLink = item.edit_link ? `<a href="${escapeHtml(item.edit_link)}">${title}</a>` : title;
        const meta = [
          escapeHtml(item.content_state || 'Unknown'),
          escapeHtml(item.layout_mode || 'Unknown layout'),
          `Status: ${escapeHtml(item.status || 'unknown')}`,
          item.has_slot_content ? 'Content-slot preserved' : ''
        ].filter(Boolean).map((part) => `<span>${part}</span>`).join('');
        const notes = Array.isArray(item.notes) && item.notes.length
          ? `<div class="mf-ppb-preview-item__notes">${escapeHtml(item.notes.join(' '))}</div>`
          : '';

        return `
          <li class="mf-ppb-preview-item">
            <div class="mf-ppb-preview-item__top">
              <strong>${editLink}</strong>
              <span class="mf-ppb-preview-pill ${statusClass}">${statusLabel}</span>
            </div>
            <div class="mf-ppb-preview-item__meta">${meta}</div>
            ${notes}
          </li>
        `;
      }).join('')}</ul>`
      : '<p class="description">No matching items were found for this preview.</p>';

    safeConvertResults.innerHTML = `
      <div class="mf-ppb-preview-report">
        <div class="mf-ppb-preview-header">
          <div>
            <h4>Safe Convert Preview</h4>
            <p>${escapeHtml(String(contentTypeLabel))} - Convert Legacy or Plain content into explicit Header, Body, and Footer zones.</p>
          </div>
        </div>
        <div class="mf-ppb-preview-stats">
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Total items</span><strong>${Number(totals.items || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Will convert</span><strong>${Number(totals.will_convert || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Skipped zoned</span><strong>${Number(totals.skipped_zoned || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Slot content detected</span><strong>${Number(totals.slot_content_detected || 0)}</strong></div>
        </div>
        <div class="mf-ppb-preview-list">
          <h5>Preview items</h5>
          <div class="mf-ppb-preview-list__summary">Showing ${items.length} of ${Number((report.items || []).length || 0)} matching items.</div>
          ${itemsMarkup}
        </div>
      </div>
    `;
  }

  function renderRunProgress(run, target) {
    if (!target || !run) {
      return;
    }

    const totals = run.totals || {};
    const contentTypeLabel = contentTypeLabels[run.content_type] || run.content_type || '';
    const percent = Number(run.percent || 0);
    const runTypeLabel = run.run_type_label || 'PPB Run';
    const primaryActionLabel = run.primary_action_label || 'Updated';
    const zoneLabel = run.zone ? ` - ${escapeHtml(String(run.zone))} Zone` : '';
    const patternLabel = run.pattern ? ` - ${escapeHtml(String(run.pattern))}` : '';
    target.innerHTML = `
      <div class="mf-ppb-preview-report mf-ppb-preview-report--progress">
        <div class="mf-ppb-preview-header">
          <div>
            <h4>${escapeHtml(String(runTypeLabel))} Progress</h4>
            <p>${escapeHtml(String(contentTypeLabel))} · ${escapeHtml(String(run.zone || ''))} Zone · ${escapeHtml(String(run.pattern || ''))}</p>
          </div>
        </div>
        <div class="mf-ppb-run-progress">
          <div class="mf-ppb-run-progress__bar">
            <span class="mf-ppb-run-progress__fill" style="width:${percent}%"></span>
          </div>
          <div class="mf-ppb-run-progress__meta">
            <strong>${Number(run.processed || 0)} / ${Number(run.eligible_total || 0)}</strong>
            <span>${percent}% complete</span>
          </div>
        </div>
        <div class="mf-ppb-preview-stats">
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Updated</span><strong>${Number(totals.updated || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Skipped locked</span><strong>${Number(totals.skipped_locked || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Skipped non-zoned</span><strong>${Number(totals.skipped_legacy || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Slot content preserved</span><strong>${Number(totals.slot_content_preserved || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Potential conflicts</span><strong>${Number(totals.potential_conflicts || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Failed</span><strong>${Number(totals.failed || 0)}</strong></div>
        </div>
      </div>
    `;
  }

  function renderBatchRunProgress(run, target) {
    if (!target || !run) {
      return;
    }

    const totals = run.totals || {};
    const contentTypeLabel = contentTypeLabels[run.content_type] || run.content_type || '';
    const percent = Number(run.percent || 0);
    const runTypeLabel = run.run_type_label || 'PPB Run';
    const primaryActionLabel = run.primary_action_label || 'Updated';
    const zoneLabel = run.zone ? ` - ${escapeHtml(String(run.zone))} Zone` : '';
    const patternLabel = run.pattern ? ` - ${escapeHtml(String(run.pattern))}` : '';
    const secondarySkipLabel = run.run_type === 'safe_convert' ? 'Skipped zoned' : 'Skipped locked';
    const secondarySkipValue = run.run_type === 'safe_convert' ? Number(totals.skipped_zoned || 0) : Number(totals.skipped_locked || 0);
    const thirdStatLabel = run.run_type === 'safe_convert' ? 'Eligible' : 'Skipped non-zoned';
    const thirdStatValue = run.run_type === 'safe_convert' ? Number(run.eligible_total || 0) : Number(totals.skipped_legacy || 0);
    const fourthStatLabel = run.run_type === 'safe_convert' ? 'Remaining' : 'Potential conflicts';
    const fourthStatValue = run.run_type === 'safe_convert' ? Number(run.remaining || 0) : Number(totals.potential_conflicts || 0);

    target.innerHTML = `
      <div class="mf-ppb-preview-report mf-ppb-preview-report--progress">
        <div class="mf-ppb-preview-header">
          <div>
            <h4>${escapeHtml(String(runTypeLabel))} Progress</h4>
            <p>${escapeHtml(String(contentTypeLabel))}${zoneLabel}${patternLabel}</p>
          </div>
        </div>
        <div class="mf-ppb-run-progress">
          <div class="mf-ppb-run-progress__bar">
            <span class="mf-ppb-run-progress__fill" style="width:${percent}%"></span>
          </div>
          <div class="mf-ppb-run-progress__meta">
            <strong>${Number(run.processed || 0)} / ${Number(run.eligible_total || 0)}</strong>
            <span>${percent}% complete</span>
          </div>
        </div>
        <div class="mf-ppb-preview-stats">
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">${escapeHtml(String(primaryActionLabel))}</span><strong>${Number(totals.updated || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">${secondarySkipLabel}</span><strong>${secondarySkipValue}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">${thirdStatLabel}</span><strong>${thirdStatValue}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Slot content preserved</span><strong>${Number(totals.slot_content_preserved || 0)}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">${fourthStatLabel}</span><strong>${fourthStatValue}</strong></div>
          <div class="mf-ppb-preview-stat"><span class="mf-ppb-preview-stat__label">Failed</span><strong>${Number(totals.failed || 0)}</strong></div>
        </div>
      </div>
    `;
  }

  function getPatternOptions() {
    const contentType = contentTypeSelect ? contentTypeSelect.value : '';
    const zone = zoneSelect ? zoneSelect.value : '';
    return (((patterns[contentType] || {})[zone]) || []);
  }

  function setFeedback(message, isError = false) {
    if (!feedback) return;
    feedback.textContent = message || '';
    feedback.classList.toggle('is-error', Boolean(isError));
    feedback.classList.toggle('is-active', Boolean(message));
  }

  function resetExecutionState() {
    lastPreviewReport = null;
    if (executeWrap) {
      executeWrap.hidden = true;
    }
    if (confirmInput) {
      confirmInput.checked = false;
    }
    if (applyButton) {
      applyButton.disabled = true;
    }
  }

  function setSafeConvertFeedback(message, isError = false) {
    if (!safeConvertFeedback) return;
    safeConvertFeedback.textContent = message || '';
    safeConvertFeedback.classList.toggle('is-error', Boolean(isError));
    safeConvertFeedback.classList.toggle('is-active', Boolean(message));
  }

  function resetSafeConvertExecutionState() {
    lastSafeConvertReport = null;
    if (safeConvertExecuteWrap) {
      safeConvertExecuteWrap.hidden = true;
    }
    if (safeConvertConfirmInput) {
      safeConvertConfirmInput.checked = false;
    }
    if (safeConvertApplyButton) {
      safeConvertApplyButton.disabled = true;
    }
  }

  function populatePatternSelect() {
    if (!patternSelect) return;
    const options = getPatternOptions();
    patternSelect.innerHTML = '';

    if (!options.length) {
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = 'No patterns available';
      patternSelect.appendChild(opt);
      patternSelect.disabled = true;
      if (patternNote) {
        patternNote.textContent = messages.noPatterns || '';
      }
      resetExecutionState();
      return;
    }

    options.forEach((option, index) => {
      const opt = document.createElement('option');
      opt.value = option.value;
      opt.textContent = option.label;
      if (index === 0) {
        opt.selected = true;
      }
      patternSelect.appendChild(opt);
    });

    patternSelect.disabled = false;
    if (patternNote) {
      patternNote.textContent = '';
    }
    resetExecutionState();
  }

  async function runPreview() {
    if (!patternSelect || patternSelect.disabled || !patternSelect.value) {
      setFeedback(messages.missingPattern || 'Select a valid pattern before running the preview.', true);
      if (results) {
        results.innerHTML = '';
      }
      resetExecutionState();
      return;
    }

    setFeedback(messages.loading || 'Scanning matching items...');
    runButton.disabled = true;

    const payload = new window.URLSearchParams();
    payload.set('action', 'modfarm_ppb_apply_all_preview');
    payload.set('nonce', config.previewNonce || '');
    payload.set('contentType', contentTypeSelect.value);
    payload.set('zone', zoneSelect.value);
    payload.set('pattern', patternSelect.value);

    try {
      const response = await window.fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: payload.toString(),
      });
      const data = await response.json();

      if (!data || !data.success) {
        const message = data && data.data && data.data.message ? data.data.message : (messages.error || 'Preview could not be generated.');
        throw new Error(message);
      }

      setFeedback('');
      lastPreviewReport = data.data && data.data.report ? data.data.report : null;
      previewListState = {
        filter: 'will_update',
        visible: Number(config.previewPageSize || 50)
      };
      if (results) {
        if (lastPreviewReport) {
          renderPreviewReport(lastPreviewReport);
        } else {
          results.innerHTML = data.data && data.data.html ? data.data.html : '';
        }
      }
      const canExecute = !!lastPreviewReport
        && ['header', 'body', 'footer'].includes(String(lastPreviewReport.zone || ''))
        && Number((lastPreviewReport.totals || {}).will_update || 0) > 0;
      if (executeWrap) {
        executeWrap.hidden = !canExecute;
      }
      if (confirmInput) {
        confirmInput.checked = false;
      }
      if (applyButton) {
        applyButton.disabled = true;
      }
    } catch (error) {
      setFeedback(error && error.message ? error.message : (messages.error || 'Preview could not be generated.'), true);
      if (results) {
        results.innerHTML = '';
      }
      resetExecutionState();
    } finally {
      runButton.disabled = false;
    }
  }

  async function runExecution() {
    if (!lastPreviewReport || !['header', 'body', 'footer'].includes(String(lastPreviewReport.zone || ''))) {
      setFeedback(messages.executionUnavailable || 'Apply All execution is currently available for Header, Body, and Footer zones only.', true);
      return;
    }

    if (!confirmInput || !confirmInput.checked) {
      setFeedback(messages.confirmRequired || 'Confirm the change before applying it.', true);
      return;
    }

    setFeedback(messages.executing || 'Applying the previewed change...');
    runButton.disabled = true;
    if (applyButton) {
      applyButton.disabled = true;
    }

    const payload = new window.URLSearchParams();
    payload.set('action', 'modfarm_ppb_apply_all_execute');
    payload.set('nonce', config.executeNonce || '');
    payload.set('contentType', String(lastPreviewReport.content_type || contentTypeSelect.value));
    payload.set('zone', String(lastPreviewReport.zone || zoneSelect.value));
    payload.set('pattern', String(lastPreviewReport.pattern || patternSelect.value));

    try {
      const response = await window.fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: payload.toString(),
      });
      const data = await response.json();

      if (!data || !data.success) {
        const message = data && data.data && data.data.message ? data.data.message : (messages.error || 'Preview could not be generated.');
        throw new Error(message);
      }

      const responsePayload = data.data || {};
      if (responsePayload.completed) {
        setFeedback('');
        if (results) {
          results.innerHTML = responsePayload.html || '';
        }
        if (runLog && responsePayload.runLogHtml) {
          runLog.innerHTML = responsePayload.runLogHtml;
        }
        resetExecutionState();
        return;
      }

      let runState = responsePayload.run || null;
      if (!runState || !responsePayload.runId) {
        throw new Error(messages.error || 'Preview could not be generated.');
      }

      renderBatchRunProgress(runState, results);

      while (runState && Number(runState.remaining || 0) > 0) {
        setFeedback(messages.processing || 'Processing the next Apply All batch...');

        const nextPayload = new window.URLSearchParams();
        nextPayload.set('action', 'modfarm_ppb_apply_all_process_run');
        nextPayload.set('nonce', config.processNonce || '');
        nextPayload.set('runId', String(responsePayload.runId || runState.run_id || ''));

        const nextResponse = await window.fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          },
          body: nextPayload.toString(),
        });
        const nextData = await nextResponse.json();

        if (!nextData || !nextData.success) {
          const message = nextData && nextData.data && nextData.data.message ? nextData.data.message : (messages.error || 'Preview could not be generated.');
          throw new Error(message);
        }

        const nextRunPayload = nextData.data || {};
        runState = nextRunPayload.run || null;

        if (nextRunPayload.completed) {
          setFeedback('');
          if (results) {
            results.innerHTML = nextRunPayload.html || '';
          }
          if (runLog && nextRunPayload.runLogHtml) {
            runLog.innerHTML = nextRunPayload.runLogHtml;
          }
          resetExecutionState();
          return;
        }

        if (runState) {
          renderBatchRunProgress(runState, results);
        }
      }
    } catch (error) {
      setFeedback(error && error.message ? error.message : (messages.error || 'Preview could not be generated.'), true);
      if (applyButton) {
        applyButton.disabled = false;
      }
    } finally {
      runButton.disabled = false;
    }
  }

  async function runSafeConvertPreview() {
    if (!safeConvertContentTypeSelect) {
      return;
    }

    setSafeConvertFeedback(messages.loading || 'Scanning matching items...');
    safeConvertRunButton.disabled = true;

    const payload = new window.URLSearchParams();
    payload.set('action', 'modfarm_ppb_safe_convert_preview');
    payload.set('nonce', config.safeConvertPreviewNonce || '');
    payload.set('contentType', safeConvertContentTypeSelect.value);

    try {
      const response = await window.fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: payload.toString(),
      });
      const data = await response.json();

      if (!data || !data.success) {
        const message = data && data.data && data.data.message ? data.data.message : (messages.error || 'Preview could not be generated.');
        throw new Error(message);
      }

      setSafeConvertFeedback('');
      lastSafeConvertReport = data.data && data.data.report ? data.data.report : null;
      if (safeConvertResults) {
        if (lastSafeConvertReport) {
          renderSafeConvertReport(lastSafeConvertReport);
        } else {
          safeConvertResults.innerHTML = data.data && data.data.html ? data.data.html : '';
        }
      }

      const canExecute = !!lastSafeConvertReport && Number((lastSafeConvertReport.totals || {}).will_convert || 0) > 0;
      if (safeConvertExecuteWrap) {
        safeConvertExecuteWrap.hidden = !canExecute;
      }
      if (safeConvertConfirmInput) {
        safeConvertConfirmInput.checked = false;
      }
      if (safeConvertApplyButton) {
        safeConvertApplyButton.disabled = true;
      }
    } catch (error) {
      setSafeConvertFeedback(error && error.message ? error.message : (messages.error || 'Preview could not be generated.'), true);
      if (safeConvertResults) {
        safeConvertResults.innerHTML = '';
      }
      resetSafeConvertExecutionState();
    } finally {
      safeConvertRunButton.disabled = false;
    }
  }

  async function runSafeConvertExecution() {
    if (!lastSafeConvertReport) {
      setSafeConvertFeedback(messages.error || 'Preview could not be generated.', true);
      return;
    }

    if (!safeConvertConfirmInput || !safeConvertConfirmInput.checked) {
      setSafeConvertFeedback(messages.convertConfirmRequired || 'Confirm the conversion before running Safe Convert.', true);
      return;
    }

    setSafeConvertFeedback(messages.executingConvert || 'Converting the previewed items to Zoned PPB...');
    if (safeConvertRunButton) {
      safeConvertRunButton.disabled = true;
    }
    if (safeConvertApplyButton) {
      safeConvertApplyButton.disabled = true;
    }

    const payload = new window.URLSearchParams();
    payload.set('action', 'modfarm_ppb_safe_convert_execute');
    payload.set('nonce', config.safeConvertExecuteNonce || '');
    payload.set('contentType', String(lastSafeConvertReport.content_type || safeConvertContentTypeSelect.value));

    try {
      const response = await window.fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: payload.toString(),
      });
      const data = await response.json();

      if (!data || !data.success) {
        const message = data && data.data && data.data.message ? data.data.message : (messages.error || 'Preview could not be generated.');
        throw new Error(message);
      }

      const responsePayload = data.data || {};
      if (responsePayload.completed) {
        setSafeConvertFeedback('');
        if (safeConvertResults) {
          safeConvertResults.innerHTML = responsePayload.html || '';
        }
        if (runLog && responsePayload.runLogHtml) {
          runLog.innerHTML = responsePayload.runLogHtml;
        }
        resetSafeConvertExecutionState();
        return;
      }

      let runState = responsePayload.run || null;
      if (!runState || !responsePayload.runId) {
        throw new Error(messages.error || 'Preview could not be generated.');
      }

      renderBatchRunProgress(runState, safeConvertResults);

      while (runState && Number(runState.remaining || 0) > 0) {
        setSafeConvertFeedback(messages.processingConvert || 'Processing the next Safe Convert batch...');

        const nextPayload = new window.URLSearchParams();
        nextPayload.set('action', 'modfarm_ppb_apply_all_process_run');
        nextPayload.set('nonce', config.processNonce || '');
        nextPayload.set('runId', String(responsePayload.runId || runState.run_id || ''));

        const nextResponse = await window.fetch(config.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          },
          body: nextPayload.toString(),
        });
        const nextData = await nextResponse.json();

        if (!nextData || !nextData.success) {
          const message = nextData && nextData.data && nextData.data.message ? nextData.data.message : (messages.error || 'Preview could not be generated.');
          throw new Error(message);
        }

        const nextRunPayload = nextData.data || {};
        runState = nextRunPayload.run || null;

        if (nextRunPayload.completed) {
          setSafeConvertFeedback('');
          if (safeConvertResults) {
            safeConvertResults.innerHTML = nextRunPayload.html || '';
          }
          if (runLog && nextRunPayload.runLogHtml) {
            runLog.innerHTML = nextRunPayload.runLogHtml;
          }
          resetSafeConvertExecutionState();
          return;
        }

        if (runState) {
          renderBatchRunProgress(runState, safeConvertResults);
        }
      }
    } catch (error) {
      setSafeConvertFeedback(error && error.message ? error.message : (messages.error || 'Preview could not be generated.'), true);
      if (safeConvertApplyButton) {
        safeConvertApplyButton.disabled = false;
      }
    } finally {
      if (safeConvertRunButton) {
        safeConvertRunButton.disabled = false;
      }
    }
  }

  if (contentTypeSelect) {
    contentTypeSelect.addEventListener('change', () => {
      populatePatternSelect();
      setFeedback('');
      if (results) results.innerHTML = '';
      resetExecutionState();
    });
  }

  if (zoneSelect) {
    zoneSelect.addEventListener('change', () => {
      populatePatternSelect();
      setFeedback('');
      if (results) results.innerHTML = '';
      resetExecutionState();
    });
  }

  if (runButton) {
    runButton.addEventListener('click', runPreview);
  }

  if (safeConvertContentTypeSelect) {
    safeConvertContentTypeSelect.addEventListener('change', () => {
      setSafeConvertFeedback('');
      if (safeConvertResults) safeConvertResults.innerHTML = '';
      resetSafeConvertExecutionState();
    });
  }

  if (safeConvertRunButton) {
    safeConvertRunButton.addEventListener('click', runSafeConvertPreview);
  }

  if (confirmInput && applyButton) {
    confirmInput.addEventListener('change', () => {
      applyButton.disabled = !confirmInput.checked;
    });
    applyButton.addEventListener('click', runExecution);
  }

  if (safeConvertConfirmInput && safeConvertApplyButton) {
    safeConvertConfirmInput.addEventListener('change', () => {
      safeConvertApplyButton.disabled = !safeConvertConfirmInput.checked;
    });
    safeConvertApplyButton.addEventListener('click', runSafeConvertExecution);
  }

  populatePatternSelect();
})();

