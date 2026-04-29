document.addEventListener('DOMContentLoaded', function () {
  var roots = document.querySelectorAll('.mf-tabs');
  if (!roots || !roots.length) return;

  function clamp(n, min, max) {
    n = parseInt(n, 10);
    if (isNaN(n)) n = min;
    if (n < min) n = min;
    if (n > max) n = max;
    return n;
  }

  function getPanels(root) {
    return root.querySelectorAll('.mf-tab-panel');
  }

  function setActive(root, idx) {
    var buttons = root.querySelectorAll('.mf-tabs__btn');
    var panels = getPanels(root);
    var panelsWrap = root.querySelector('.mf-tabs__panels');
    var i;

    // Support "nothing active" if idx === -1 (rare, but safe)
    for (i = 0; i < buttons.length; i++) {
      var on = (i === idx);
      buttons[i].classList.toggle('is-active', on);
      buttons[i].setAttribute('aria-selected', on ? 'true' : 'false');
      buttons[i].setAttribute('tabindex', on ? '0' : '-1');
    }

    for (i = 0; i < panels.length; i++) {
      var show = (i === idx);
      panels[i].classList.toggle('is-active', show);
      panels[i].setAttribute('aria-hidden', show ? 'false' : 'true');
    }

    if (panelsWrap) {
      panelsWrap.setAttribute('data-active-index', String(idx));
    }
  }

  function setActiveByTabId(root, tabId) {
    if (!tabId) return false;

    var buttons = root.querySelectorAll('.mf-tabs__btn');
    var panels = getPanels(root);

    // Find index by matching panel data-tab-id first
    for (var i = 0; i < panels.length; i++) {
      var pid = panels[i].getAttribute('data-tab-id') || '';
      if (pid === tabId) {
        setActive(root, i);
        return true;
      }
    }

    // Fallback: match buttons
    for (var j = 0; j < buttons.length; j++) {
      var bid = buttons[j].getAttribute('data-tab') || '';
      if (bid === tabId) {
        setActive(root, j);
        return true;
      }
    }

    return false;
  }

  function wireTabs(root) {
    var buttons = root.querySelectorAll('.mf-tabs__btn');
    var panels = getPanels(root);
    if (!buttons.length || !panels.length) return;

    var panelsWrap = root.querySelector('.mf-tabs__panels');

    // Initial active: prefer data-active-index
    var initial = 0;
    if (panelsWrap) {
      initial = clamp(panelsWrap.getAttribute('data-active-index') || '0', 0, buttons.length - 1);
    }
    setActive(root, initial);

    // Click behavior
    for (var i = 0; i < buttons.length; i++) {
      (function (idx) {
        buttons[idx].addEventListener('click', function () {
          // Prefer matching by tab-id if present (more robust)
          var tabId = buttons[idx].getAttribute('data-tab');
          if (tabId && setActiveByTabId(root, tabId)) return;

          setActive(root, idx);
        });
      })(i);
    }

    // Keyboard: left/right to change tabs
    root.addEventListener('keydown', function (e) {
      if (!e || !e.key) return;

      var active = 0;
      if (panelsWrap) {
        active = parseInt(panelsWrap.getAttribute('data-active-index') || '0', 10);
        if (isNaN(active)) active = 0;
      }

      if (e.key === 'ArrowRight') {
        e.preventDefault();
        var next = (active + 1) % buttons.length;
        buttons[next].focus();
        buttons[next].click();
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        var prev = (active - 1 + buttons.length) % buttons.length;
        buttons[prev].focus();
        buttons[prev].click();
      }
    });
  }

  for (var r = 0; r < roots.length; r++) {
    wireTabs(roots[r]);
  }
});