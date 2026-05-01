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

  const previewRoot = document.getElementById('mf-ppb-apply-all-preview');
  if (!previewRoot || typeof window.modfarmSettingsUi !== 'object') {
    return;
  }

  const config = window.modfarmSettingsUi;
  const patterns = config.applyAllPatterns || {};
  const messages = config.messages || {};
  const contentTypeSelect = document.getElementById('mf-ppb-preview-content-type');
  const zoneSelect = document.getElementById('mf-ppb-preview-zone');
  const patternSelect = document.getElementById('mf-ppb-preview-pattern');
  const patternNote = document.getElementById('mf-ppb-preview-pattern-note');
  const runButton = document.getElementById('mf-ppb-preview-run');
  const feedback = document.getElementById('mf-ppb-preview-feedback');
  const results = document.getElementById('mf-ppb-preview-results');

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
  }

  async function runPreview() {
    if (!patternSelect || patternSelect.disabled || !patternSelect.value) {
      setFeedback(messages.missingPattern || 'Select a valid pattern before running the preview.', true);
      if (results) {
        results.innerHTML = '';
      }
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
      if (results) {
        results.innerHTML = data.data && data.data.html ? data.data.html : '';
      }
    } catch (error) {
      setFeedback(error && error.message ? error.message : (messages.error || 'Preview could not be generated.'), true);
      if (results) {
        results.innerHTML = '';
      }
    } finally {
      runButton.disabled = false;
    }
  }

  if (contentTypeSelect) {
    contentTypeSelect.addEventListener('change', () => {
      populatePatternSelect();
      setFeedback('');
      if (results) results.innerHTML = '';
    });
  }

  if (zoneSelect) {
    zoneSelect.addEventListener('change', () => {
      populatePatternSelect();
      setFeedback('');
      if (results) results.innerHTML = '';
    });
  }

  if (runButton) {
    runButton.addEventListener('click', runPreview);
  }

  populatePatternSelect();
})();
