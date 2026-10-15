(function () {
    'use strict';
    function init(scope) {
        const roots = Array.from(scope.querySelectorAll('.mfw--tabs[data-world-tabs]'));
        if (scope.matches?.('.mfw--tabs[data-world-tabs]')) roots.unshift(scope);
        roots.forEach(root => {
            if (root.dataset.worldReady) return;
            const list = Array.from(root.children).find(node => node.classList.contains('mfw-tablist'));
            if (!list) return;
            const buttons = Array.from(list.children);
            const panels = Array.from(root.children).filter(node => node.matches('[data-world-panel]'));
            if (!buttons.length || buttons.length !== panels.length) return;
            root.dataset.worldReady = 'true'; list.hidden = false; list.setAttribute('role', 'tablist');
            const activate = index => {
                buttons.forEach((button, i) => { button.setAttribute('aria-selected', String(i === index)); button.tabIndex = i === index ? 0 : -1; });
                panels.forEach((panel, i) => { panel.hidden = i !== index; });
            };
            buttons.forEach((button, i) => {
                button.setAttribute('role', 'tab'); panels[i].setAttribute('role', 'tabpanel'); panels[i].setAttribute('aria-labelledby', button.id); panels[i].tabIndex = 0;
                button.addEventListener('click', () => activate(i));
                button.addEventListener('keydown', event => {
                    let next;
                    const rtl = list.ownerDocument.defaultView.getComputedStyle(list).direction === 'rtl';
                    if (event.key === 'ArrowRight') next = (i + (rtl ? -1 : 1) + buttons.length) % buttons.length;
                    if (event.key === 'ArrowLeft') next = (i + (rtl ? 1 : -1) + buttons.length) % buttons.length;
                    if (event.key === 'Home') next = 0;
                    if (event.key === 'End') next = buttons.length - 1;
                    if (next !== undefined) { event.preventDefault(); activate(next); buttons[next].focus(); }
                });
            });
            const initial = buttons.findIndex(button => button.dataset.worldTab === root.dataset.worldDefault);
            activate(initial < 0 ? 0 : initial);
        });
    }
    window.ModFarmWorldInit = init;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init(document)); else init(document);
})();
