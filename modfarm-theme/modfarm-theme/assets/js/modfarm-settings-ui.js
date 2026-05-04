(function() {
  const root = document.querySelector('.modfarm-settings-shell');
  if (!root) return;

  const tabs = root.querySelectorAll('.mf-tab');
  const panels = root.querySelectorAll('.mf-tab-panel');
  const storageKey = 'modfarm-settings-active-tab';

  function activate(tabId) {
    tabs.forEach(btn => {
      const isActive = btn.dataset.tab === tabId;
      btn.classList.toggle('is-active', isActive);
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    panels.forEach(panel => {
      const id = panel.id.replace('mf-tab-', '');
      panel.classList.toggle('is-active', id === tabId);
    });

    try {
      window.localStorage.setItem(storageKey, tabId);
    } catch (e) {}
  }

  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      activate(btn.dataset.tab);
    });
  });

  let initial = 'site-basics';
  try {
    const saved = window.localStorage.getItem(storageKey);
    if (saved) initial = saved;
  } catch (e) {}

  const hasTab = Array.prototype.some.call(tabs, t => t.dataset.tab === initial);
  activate(hasTab ? initial : 'site-basics');

  const config = typeof window.modfarmSettingsUi === 'object' ? window.modfarmSettingsUi : {};

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
      if (shape === 'rounded') return '14px';
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
      if (style === 'shadow-sm') return '0 2px 8px rgba(0,0,0,.12)';
      if (style === 'shadow-md') return '0 8px 22px rgba(0,0,0,.16)';
      if (style === 'shadow-lg') return '0 14px 36px rgba(0,0,0,.22)';
      if (style === 'emboss') return '0 8px 22px rgba(0,0,0,.14), inset 0 2px 8px rgba(255,255,255,.28)';
      return 'none';
    }

    function updateThemePreview() {
      if (!themePreview) return;

      const primary = settingValue('primary_color', '#94a3b8');
      const secondary = settingValue('secondary_color', '#475569');
      const background = settingValue('background_color', '#f3f4f5');
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

      setStyleVar(bookPreview, '--mf-preview-body', settingValue('body_text_color', '#1d2327'));
      setStyleVar(bookPreview, '--mf-preview-heading', settingValue('header_text_color', settingValue('body_text_color', '#1d2327')));
      setStyleVar(bookPreview, '--mf-preview-button-bg', buttonBg);
      setStyleVar(bookPreview, '--mf-preview-button-border', buttonBorder);
      setStyleVar(bookPreview, '--mf-preview-button-text', buttonText);
      setStyleVar(bookPreview, '--mf-preview-sample-bg', sampleBg);
      setStyleVar(bookPreview, '--mf-preview-sample-border', sampleBorder);
      setStyleVar(bookPreview, '--mf-preview-sample-text', sampleText);
      setStyleVar(bookPreview, '--mf-preview-cover-radius', settingValue('book_card_cover_shape') === 'rounded' ? '14px' : '0px');
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
    const viewportButtons = visualizer.querySelectorAll('.mf-ppb-visualizer__viewport');
    let activeType = 'book';
    let activeViewport = 'desktop';
    let activeViewportWidth = 1200;
    let refreshTimer = null;
    let requestId = 0;

    function fieldSelect(fieldId) {
      return document.querySelector(`select[name="modfarm_theme_settings[${fieldId}]"]`);
    }

    function activeFields() {
      return ((types[activeType] || {}).fields) || {};
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

    function updateViewport() {
      if (!frame || !frameWrap) return;

      const availableWidth = Math.max(frameWrap.clientWidth - 2, 1);
      const availableHeight = Math.max(frameWrap.clientHeight - 2, 1);
      const targetWidth = Math.max(activeViewportWidth, 1);
      const scale = Math.min(1, availableWidth / targetWidth);
      const scaledWidth = targetWidth * scale;

      frameWrap.setAttribute('data-viewport', activeViewport);
      frame.style.width = `${targetWidth}px`;
      frame.style.height = `${Math.ceil(availableHeight / scale)}px`;
      frame.style.transform = `scale(${scale})`;
      frame.style.marginLeft = scale === 1 && scaledWidth < availableWidth ? `${Math.floor((availableWidth - scaledWidth) / 2)}px` : '0';
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
      payload.set('contentType', activeType);
      payload.set('activeZone', 'body');
      payload.set('previewScope', 'ppb_layout');
      payload.set('sampleId', sampleSelect && !sampleSelect.disabled ? sampleSelect.value : '0');
      payload.set('patterns', JSON.stringify(currentPatterns()));

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

        if (!data || !data.success || !data.data || !data.data.html) {
          const message = data && data.data && data.data.message ? data.data.message : 'Preview could not be generated.';
          throw new Error(message);
        }

        frame.srcdoc = data.data.html;
        updateViewport();
        setFeedback('');
      } catch (error) {
        setFeedback(error && error.message ? error.message : 'Preview could not be generated.', true);
      }
    }

    layoutTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
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
          if (typeKey === activeType) {
            queueRefresh(150);
          }
        });
      });
    });

    if (sampleSelect) {
      sampleSelect.addEventListener('change', () => queueRefresh(0));
    }
    if (refreshButton) {
      refreshButton.addEventListener('click', () => queueRefresh(0));
    }
    viewportButtons.forEach((button) => {
      button.addEventListener('click', () => {
        activeViewport = button.getAttribute('data-viewport') || 'desktop';
        activeViewportWidth = parseInt(button.getAttribute('data-width') || '1200', 10) || 1200;
        viewportButtons.forEach((item) => {
          const isActive = item === button;
          item.classList.toggle('is-active', isActive);
          item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        updateViewport();
      });
    });
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
