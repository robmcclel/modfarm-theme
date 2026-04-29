/* ModFarm TOC view logic — runs in frontend and block editor */
(function(){
  // Global de-dupe set per document (handles multiple TOCs)
  var USED = new Set();
  var TMR  = null;

  function norm(s){ return (s||'').replace(/\s+/g,' ').trim(); }
  function toSlug(s, caseMode){
    s = String(s || '');
    if (caseMode === 'lower') s = s.toLowerCase();
    try { s = s.normalize('NFKD').replace(/[\u0300-\u036f]/g,''); } catch(e){}
    s = s.replace(/[^A-Za-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');
    if (caseMode === 'lower') s = s.toLowerCase();
    return s || 'section';
  }
  function ensureUnique(base){
    var id = base, i = 2;
    while (document.getElementById(id) || USED.has(id)) id = base + '-' + (i++);
    USED.add(id);
    return id;
  }

  function processOneTOC(nav){
    if (!nav || !nav.getAttribute) return;

    var caseMode = nav.getAttribute('data-mftoc-case') || 'lower';
    var levels   = (nav.getAttribute('data-mftoc-levels') || '2,3')
                    .split(',').map(function(n){ return 'h' + String(n).trim(); });
    var selector = levels.join(',');
    if (!selector) return;

    // 1) Collect/assign heading IDs in DOM order
    var heads = Array.prototype.slice.call(document.querySelectorAll(selector));
    if (!heads.length) return;

    var textToId = [];
    heads.forEach(function(h){
      var id = h.getAttribute('id');
      if (!id) {
        var base = toSlug(norm(h.textContent||''), caseMode);
        id = ensureUnique(base);
        h.setAttribute('id', id);
      } else {
        USED.add(id);
      }
      textToId.push({ text: norm(h.textContent||''), id: id });
    });

    // 2) Update this TOC's links to the final IDs
    var links = nav.querySelectorAll('a[data-mftoc-text]');
    if (!links.length) return;

    // fast index by normalized text
    var byText = {};
    textToId.forEach(function(r){ if (r.text && !byText[r.text]) byText[r.text] = r.id; });

    links.forEach(function(a, i){
      var t = norm(a.getAttribute('data-mftoc-text') || a.textContent || '');
      var target = byText[t] || (textToId[i] && textToId[i].id) || null;
      if (target) a.setAttribute('href', '#'+target);
    });
  }

  function runAll(){
    // Ensure USED reflects any pre-existing IDs
    document.querySelectorAll('[id]').forEach(function(el){ USED.add(el.id); });
    document.querySelectorAll('nav.mftoc').forEach(processOneTOC);
  }

  function debouncedRun(){
    if (TMR) clearTimeout(TMR);
    TMR = setTimeout(runAll, 50);
  }

  // Initial
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runAll);
  } else {
    runAll();
  }

  // Observe changes (editor canvas & dynamic pages)
  var mo = new MutationObserver(debouncedRun);
  mo.observe(document.documentElement, { childList: true, subtree: true });
})();