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
})();