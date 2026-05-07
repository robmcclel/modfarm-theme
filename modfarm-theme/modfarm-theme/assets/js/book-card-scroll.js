(function () {
  function getWrapFromButton(button) {
    return button && button.closest ? button.closest('[data-mf-card-scroll-wrap]') : null;
  }

  function getRailFromButton(button) {
    var target = button ? button.getAttribute('data-mf-card-scroll-target') : '';
    if (target) {
      var rail = document.getElementById(target);
      if (rail) return rail;
    }

    var wrap = getWrapFromButton(button);
    return wrap ? wrap.querySelector('[data-mf-card-scroll-rail]') : null;
  }

  function scrollRail(rail, direction) {
    var distance = Math.max(280, Math.floor(rail.clientWidth * 0.86));
    var left = direction * distance;

    if (typeof rail.scrollBy === 'function') {
      try {
        rail.scrollBy({ left: left, behavior: 'smooth' });
        return;
      } catch (error) {
        rail.scrollBy(left, 0);
        return;
      }
    }

    rail.scrollLeft += left;
  }

  function updateButtons(wrap) {
    var rail = wrap.querySelector('[data-mf-card-scroll-rail]');
    if (!rail) return;

    var buttons = Array.prototype.slice.call(wrap.querySelectorAll('[data-mf-card-scroll-direction]'));
    var canScroll = rail.scrollWidth > rail.clientWidth + 2;
    var atStart = rail.scrollLeft <= 2;
    var atEnd = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 2;

    buttons.forEach(function (button) {
      var direction = parseInt(button.getAttribute('data-mf-card-scroll-direction') || '0', 10);
      button.disabled = !canScroll || (direction < 0 && atStart) || (direction > 0 && atEnd);
    });
  }

  function initWrap(wrap) {
    var rail = wrap.querySelector('[data-mf-card-scroll-rail]');
    if (!rail || rail.dataset.mfCardScrollReady === '1') return;

    rail.dataset.mfCardScrollReady = '1';
    rail.addEventListener('scroll', function () { updateButtons(wrap); }, { passive: true });
    updateButtons(wrap);
    window.setTimeout(function () { updateButtons(wrap); }, 250);
    window.setTimeout(function () { updateButtons(wrap); }, 1000);
  }

  function init() {
    Array.prototype.slice.call(document.querySelectorAll('[data-mf-card-scroll-wrap]')).forEach(initWrap);
  }

  document.addEventListener('click', function (event) {
    var button = event.target && event.target.closest ? event.target.closest('[data-mf-card-scroll-direction]') : null;
    if (!button) return;

    var rail = getRailFromButton(button);
    if (!rail) return;

    event.preventDefault();
    scrollRail(rail, parseInt(button.getAttribute('data-mf-card-scroll-direction') || '1', 10));

    var wrap = getWrapFromButton(button);
    if (wrap) {
      window.setTimeout(function () { updateButtons(wrap); }, 260);
    }
  });

  window.addEventListener('resize', init);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
