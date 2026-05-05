/* ModFarm Taxonomy Grid frontend helpers. */
(function(){
  function syncMobileCollapse(){
    var shouldCollapse = false;
    if (window.matchMedia) {
      shouldCollapse = window.matchMedia('(max-width: 640px)').matches;
    }

    document.querySelectorAll('.mfb-taxgrid-section-toc.mftoc--collapse-mobile details.mftoc-details').forEach(function(details){
      details.open = !shouldCollapse;
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncMobileCollapse);
  } else {
    syncMobileCollapse();
  }

  if (window.matchMedia) {
    var collapseQuery = window.matchMedia('(max-width: 640px)');
    if (collapseQuery.addEventListener) {
      collapseQuery.addEventListener('change', syncMobileCollapse);
    } else if (collapseQuery.addListener) {
      collapseQuery.addListener(syncMobileCollapse);
    }
  }
})();
