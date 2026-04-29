/* navigation-toggle.js (ES5-safe) */
document.addEventListener("DOMContentLoaded", function () {
  // Loop through every nav block individually
  var navBlocks = document.querySelectorAll(".mfs-nav");
  if (!navBlocks || !navBlocks.length) return;

  for (var i = 0; i < navBlocks.length; i++) {
    (function (nav) {
      if (!nav || !nav.classList) return;

      // Skip overlay wiring for "no-collapse" instances ONLY
      if (nav.classList.contains("mfs-nav--no-collapse")) return;

      var toggle = nav.querySelector(".mfs-nav-toggle");
      var overlay = nav.querySelector(".mfs-nav-overlay");
      var closeBtn = overlay ? overlay.querySelector(".mfs-nav-close") : null;

      if (!toggle || !overlay) return;

      function openOverlay() {
        overlay.classList.add("active");
        document.body.classList.add("overlay-open");
      }

      function closeOverlay() {
        overlay.classList.remove("active");
        document.body.classList.remove("overlay-open");

        // Reset any open submenus
        var openLis = overlay.querySelectorAll(".menu-item-has-children.open");
        for (var j = 0; j < openLis.length; j++) {
          openLis[j].classList.remove("open");
        }
      }

      // Open / Close
      toggle.addEventListener("click", openOverlay);
      if (closeBtn) closeBtn.addEventListener("click", closeOverlay);

      // ESC to close
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && overlay.classList.contains("active")) {
          closeOverlay();
        }
      });

      // Click backdrop to close (only if clicking the overlay itself)
      overlay.addEventListener("click", function (e) {
        if (e.target === overlay) closeOverlay();
      });

      // MOBILE: Tap-to-toggle submenus inside the overlay (event delegation)
      overlay.addEventListener("click", function (e) {
        var isMobile = window.innerWidth <= 768;

        // If a parent item (has children) was tapped
        var trigger = e.target.closest
          ? e.target.closest(".menu-item-has-children > a, .menu-item-has-children > button")
          : null;

        if (isMobile && trigger) {
          e.preventDefault(); // do not navigate; toggle instead
          var li = trigger.parentElement;
          if (!li || !li.parentElement) return;

          // Accordion: close siblings, open current
          var nowOpen = !li.classList.contains("open");
          var siblings = li.parentElement.children;

          for (var k = 0; k < siblings.length; k++) {
            if (siblings[k] !== li) siblings[k].classList.remove("open");
          }
          if (nowOpen) li.classList.add("open");
          else li.classList.remove("open");

          return; // keep overlay open
        }

        // If a leaf link (no submenu) was clicked, close overlay
        var leafLink = e.target.closest ? e.target.closest("a") : null;
        if (leafLink) {
          var inParent = leafLink.closest ? leafLink.closest(".menu-item-has-children") : null;
          if (!inParent) closeOverlay();
        }
      });

      // Clear any lingering open classes after transition hides overlay
      overlay.addEventListener("transitionend", function () {
        if (!overlay.classList.contains("active")) {
          var openLis2 = overlay.querySelectorAll(".menu-item-has-children.open");
          for (var m = 0; m < openLis2.length; m++) {
            openLis2[m].classList.remove("open");
          }
        }
      });

      // On resize to desktop, clear mobile-open states
      window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
          var openLis3 = overlay.querySelectorAll(".menu-item-has-children.open");
          for (var n = 0; n < openLis3.length; n++) {
            openLis3[n].classList.remove("open");
          }
        }
      });
    })(navBlocks[i]);
  }

  // Desktop overflow detection
  var desktopMenus = document.querySelectorAll(".mfs-nav-menu li.menu-item-has-children");
  for (var d = 0; d < desktopMenus.length; d++) {
    (function (item) {
      item.addEventListener("mouseenter", function () {
        var submenu = item.querySelector(".sub-menu");
        if (!submenu) return;

        submenu.classList.remove("submenu-align-right");
        var rect = submenu.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
          submenu.classList.add("submenu-align-right");
        }
      });
    })(desktopMenus[d]);
  }
});

// Tablet / crossover detection (ES5-safe)
(function () {
  if (typeof window === "undefined" || typeof document === "undefined") return;

  var hasTouch = ("ontouchstart" in window) || (navigator && navigator.maxTouchPoints > 0);
  var anyHover = window.matchMedia ? window.matchMedia("(any-hover: hover)").matches : false;
  var needsTapMenus = hasTouch && !anyHover;

  if (!needsTapMenus) return; // laptops/desktops keep pure hover
  if (window.__mfsTabletSubmenuBound) return;
  window.__mfsTabletSubmenuBound = true;

  function closeAll(nav) {
    var openLis = nav.querySelectorAll(".mfs-nav-menu li.open");
    for (var i = 0; i < openLis.length; i++) openLis[i].classList.remove("open");
  }

  document.addEventListener("click", function (e) {
    var navs = document.querySelectorAll(".mfs-nav");
    for (var i = 0; i < navs.length; i++) {
      if (!navs[i].contains(e.target)) closeAll(navs[i]);
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      var navs = document.querySelectorAll(".mfs-nav");
      for (var i = 0; i < navs.length; i++) closeAll(navs[i]);
    }
  });

  var navs2 = document.querySelectorAll(".mfs-nav");
  for (var n = 0; n < navs2.length; n++) {
    (function (nav) {
      var parents = nav.querySelectorAll(".mfs-nav-menu li.menu-item-has-children > a");
      for (var p = 0; p < parents.length; p++) {
        (function (anchor) {
          anchor.addEventListener("click", function (e) {
            // if overlay is active, let overlay logic handle it
            var overlayActive = document.querySelector(".mfs-nav-overlay.active");
            if (overlayActive) return;

            var li = anchor.parentElement;
            if (!li.classList.contains("open")) {
              e.preventDefault();

              // close siblings (no :scope for compatibility)
              var siblings = li.parentElement ? li.parentElement.children : [];
              for (var s = 0; s < siblings.length; s++) {
                if (siblings[s] !== li) siblings[s].classList.remove("open");
              }
              li.classList.add("open");
            }
            // else: already open → allow navigation
          }, false);
        })(parents[p]);
      }

      var leaves = nav.querySelectorAll(".mfs-nav-menu li:not(.menu-item-has-children) > a");
      for (var l = 0; l < leaves.length; l++) {
        (function (anchor2) {
          anchor2.addEventListener("click", function () {
            closeAll(nav);
          }, false);
        })(leaves[l]);
      }
    })(navs2[n]);
  }
})();