(function () {
  'use strict';
  var serial = 0;
  var controllers = new Set();
  function init(root) {
    root.querySelectorAll('.mfs-nav').forEach(function (nav) {
      if (nav.dataset.mfsReady) { if (nav.mfsEnhance) nav.mfsEnhance(); return; }
      nav.dataset.mfsReady = 'true';
      var toggle = nav.querySelector('.mfs-nav-toggle');
      var overlay = nav.querySelector('.mfs-nav-overlay');
      var panel = nav.querySelector('.mfs-nav-panel');
      var below = nav.classList.contains('mfs-nav--below');
      var placeholder = overlay ? document.createComment('mobile navigation') : null;
      var isOpen = false, inertNodes = [], oldOverflow = '';
      function direct(item, selector) {
        return Array.from(item.children).find(function (child) { return child.matches(selector); });
      }
      function setExpanded(item, expanded) {
        item.classList.toggle('open', expanded);
        var button = direct(item, '.mfs-submenu-toggle');
        if (button) button.setAttribute('aria-expanded', String(expanded));
        if (!expanded) item.querySelectorAll('li.open').forEach(function (child) { setExpanded(child, false); });
      }
      function reset() {
        nav.querySelectorAll('li.open').forEach(function (item) { setExpanded(item, false); });
        if (overlay) overlay.querySelectorAll('li.open').forEach(function (item) { setExpanded(item, false); });
      }
      function close(restoreFocus) {
        if (!isOpen) return;
        isOpen = false;
        overlay.hidden = true;
        overlay.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Open menu');
        if (!below) {
          inertNodes.forEach(function (entry) { entry[0].inert = entry[1]; });
          inertNodes = [];
          document.body.style.overflow = oldOverflow;
          if (placeholder.parentNode) placeholder.replaceWith(overlay);
        }
        reset();
        if (restoreFocus !== false && toggle.isConnected) toggle.focus();
      }
      function open() {
        controllers.forEach(function (controller) { if (controller !== close) controller(false); });
        if (!below) {
          // Escape transformed/clipped theme containers while preserving resolved styles.
          var computed = getComputedStyle(nav);
          overlay.style.fontFamily = computed.fontFamily;
          overlay.style.fontSize = computed.fontSize;
          ['--submenu-bg','--submenu-color','--mf-nav-hover-color','--mf-nav-font','--mf-nav-font-size','--mfs-menu-icon-size','--mfs-menu-image-gap'].forEach(function (key) {
            overlay.style.setProperty(key, computed.getPropertyValue(key));
          });
          overlay.classList.toggle('mfs-mobile-drawer', nav.classList.contains('mfs-nav--drawer'));
          overlay.classList.toggle('mfs-mobile-left', nav.classList.contains('mfs-nav--drawer-left'));
          overlay.replaceWith(placeholder);
          document.body.appendChild(overlay);
          oldOverflow = document.body.style.overflow;
          document.body.style.overflow = 'hidden';
          Array.from(document.body.children).forEach(function (element) {
            if (element !== overlay && !['SCRIPT','STYLE','LINK'].includes(element.tagName)) {
              inertNodes.push([element, element.inert]); element.inert = true;
            }
          });
        }
        isOpen = true;
        overlay.hidden = false;
        overlay.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Close menu');
        if (!below) panel.querySelector('.mfs-nav-close').focus();
      }
      if (toggle && overlay && panel) {
        controllers.add(close);
        toggle.addEventListener('click', function () { if (isOpen) close(); else open(); });
        overlay.querySelector('.mfs-nav-close').addEventListener('click', function () { close(); });
        overlay.addEventListener('click', function (event) {
          if (event.target === overlay || event.target.closest('a')) close();
        });
        overlay.addEventListener('keydown', function (event) {
          if (!isOpen || below || event.key !== 'Tab') return;
          var focusable = Array.from(panel.querySelectorAll('a[href],button,[tabindex="0"]')).filter(function (el) { return !el.disabled && el.getClientRects().length; });
          var first = focusable[0], last = focusable[focusable.length - 1];
          if (event.shiftKey && (document.activeElement === first || document.activeElement === panel)) { event.preventDefault(); last.focus(); }
          else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
      }
      nav.mfsEnhance = function () {
      var items = Array.from(nav.querySelectorAll('li.menu-item-has-children'));
      if (overlay && !nav.contains(overlay)) items = items.concat(Array.from(overlay.querySelectorAll('li.menu-item-has-children')));
      items.forEach(function (item) {
        if (item.dataset.mfsSubmenuReady) return;
        item.dataset.mfsSubmenuReady = 'true';
        var submenu = direct(item, '.sub-menu'), anchor = direct(item, 'a');
        if (!submenu || !anchor) return;
        var mobile = !!item.closest('.mfs-nav-overlay');
        // wp_nav_menu is rendered twice: give every generated submenu a unique ID.
        submenu.id = 'mfs-submenu-' + (++serial);
        var button = document.createElement('button');
        button.type = 'button'; button.className = 'mfs-submenu-toggle';
        button.setAttribute('aria-expanded', 'false');
        button.setAttribute('aria-controls', submenu.id);
        button.setAttribute('aria-label', 'Toggle submenu: ' + (anchor.querySelector('.mfs-menu-label') || anchor).textContent.trim());
        button.innerHTML = '<span aria-hidden="true"></span>';
        anchor.after(button);
        function position() {
          if (mobile) return;
          item.classList.remove('submenu-align-right');
          if (submenu.getBoundingClientRect().right > window.innerWidth - 8) item.classList.add('submenu-align-right');
        }
        button.addEventListener('click', function () {
          var next = !item.classList.contains('open');
          Array.from(item.parentElement.children).forEach(function (sibling) { if (sibling !== item) setExpanded(sibling, false); });
          setExpanded(item, next); position();
        });
        if (!mobile) {
          item.addEventListener('mouseenter', function () {
            if (window.matchMedia('(hover: hover)').matches) { setExpanded(item, true); position(); }
          });
          item.addEventListener('mouseleave', function () {
            if (!item.contains(document.activeElement)) setExpanded(item, false);
          });
          item.addEventListener('focusout', function (event) {
            if (!item.contains(event.relatedTarget)) setExpanded(item, false);
          });
        }
        submenu.addEventListener('keydown', function (event) {
          if (event.key === 'Escape') { event.stopPropagation(); setExpanded(item, false); button.focus(); }
        });
      });
      };
      nav.mfsEnhance();
      document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (isOpen) close(); else reset();
      });
      document.addEventListener('click', function (event) {
        if (!nav.contains(event.target) && !(overlay && overlay.contains(event.target))) {
          if (below && isOpen) close(false);
          reset();
        }
      });
      window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) close(false);
      });
    });
  }
  function boot() { init(document); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
  // Customizer selective refresh replaces block markup without a page load.
  if (window.wp && wp.customize && wp.customize.selectiveRefresh) {
    wp.customize.selectiveRefresh.bind('partial-content-rendered', boot);
  }
})();
