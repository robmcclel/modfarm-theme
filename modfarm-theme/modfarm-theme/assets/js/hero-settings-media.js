(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var select = event.target.closest('.mf-hero-image-select');
    var clear = event.target.closest('.mf-hero-image-clear');
    if (!select && !clear) return;
    var input = document.getElementById('mf-hero-default-image');
    var preview = document.getElementById('mf-hero-default-preview');
    if (!input || !preview) return;
    if (clear) {
      input.value = '';
      preview.removeAttribute('src');
      preview.style.display = 'none';
      input.dispatchEvent(new Event('change', { bubbles: true }));
      return;
    }
    var frame = window.wp.media({ title: 'Default Hero / Cover image', library: { type: 'image' }, multiple: false });
    frame.on('select', function () {
      var image = frame.state().get('selection').first().toJSON();
      input.value = image.id;
      preview.src = image.url;
      preview.style.display = '';
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    frame.open();
  });
})();
