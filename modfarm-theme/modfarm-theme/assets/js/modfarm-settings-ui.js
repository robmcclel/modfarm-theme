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

  function initPpbVisualizer() {
    const visualizer = document.getElementById('mf-ppb-visualizer');
    if (!visualizer || !config.ajaxUrl) return;

    const types = config.visualizerTypes || {};
    const samples = config.visualizerSamples || {};
    const layoutTabs = document.querySelectorAll('.mf-ppb-layout-tab');
    const layoutPanels = document.querySelectorAll('.mf-ppb-layout-panel');
    const sampleSelect = document.getElementById('mf-ppb-visualizer-sample');
    const frame = document.getElementById('mf-ppb-visualizer-frame');
    const feedback = document.getElementById('mf-ppb-visualizer-feedback');
    const refreshButton = document.getElementById('mf-ppb-visualizer-refresh');
    let activeType = 'book';
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
    updateLayoutTabs();
    updateSampleSelect();
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
