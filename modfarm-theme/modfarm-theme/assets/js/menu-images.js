(function ($, wp) {
  'use strict';
  function bindField(field, read, write) {
    if (!field.length || field.data('mfs-bound')) return;
    field.data('mfs-bound', true);
    var currentId = -1;
    function refresh() {
      var value = read();
      if (!value) return;
      var id = parseInt(value.mfs_image_id, 10) || 0;
      field.find('.mfs-menu-image-id').val(id);
      field.find('.mfs-menu-image-style').val(/(^|\s)menu-icon-only(\s|$)/.test(value.classes || '') ? 'menu-icon-only' : /(^|\s)menu-icon(\s|$)/.test(value.classes || '') ? 'menu-icon' : 'menu-cover');
      field.find('.mfs-menu-image-remove').toggle(!!id);
      if (id === currentId) return;
      currentId = id;
      var preview = field.find('.mfs-menu-image-preview').empty();
      if (!id) return;
      var attachment = wp.media.attachment(id);
      attachment.fetch().then(function () {
        if (currentId !== id) return;
        var data = attachment.toJSON(), sizes = data.sizes || {};
        $('<img>', { src: sizes.thumbnail ? sizes.thumbnail.url : data.url, alt: '' }).appendTo(preview);
      }).fail(function () { if (currentId === id) preview.text('Image unavailable. Select a replacement.'); });
    }
    function update(id, style) {
      var value = $.extend({}, read());
      value.mfs_image_id = id;
      var classes = (value.classes || '').split(/\s+/).filter(function (c) { return c && c !== 'menu-cover' && c !== 'menu-icon' && c !== 'menu-icon-only'; });
      if (id) classes.push(style);
      value.classes = classes.join(' ');
      write(value);
      refresh();
    }
    field.on('click', '.mfs-menu-image-select', function () {
      var frame = wp.media({ title: 'Menu image', library: { type: 'image' }, button: { text: 'Use image' }, multiple: false });
      frame.on('select', function () { update(frame.state().get('selection').first().id, field.find('select').val()); });
      frame.open();
    });
    field.on('click', '.mfs-menu-image-remove', function () { update(0, 'menu-cover'); });
    field.on('change', '.mfs-menu-image-style', function () { update(parseInt(field.find('input.mfs-menu-image-id').val(), 10) || 0, this.value); });
    refresh();
    return refresh;
  }
  if (wp.customize && wp.customize.Menus) {
    var api = wp.customize;
    function setup(control) {
      if (control.params.type !== 'nav_menu_item') return;
      control.deferred.embedded.done(function () {
        var refresh = bindField(control.container.find('.mfs-menu-image-field'),
          function () { return control.setting(); }, function (v) { control.setting.set(v); });
        if (refresh) control.setting.bind(refresh);
      });
    }
    api.bind('ready', function () {
      api.control.each(setup);
      api.control.bind('add', setup);
    });
  } else {
    function setupAdmin() {
      $('.mfs-menu-image-field').each(function () {
        var field = $(this), item = field.closest('.menu-item');
        bindField(field, function () {
          return { mfs_image_id: field.find('.mfs-menu-image-id').val(), classes: item.find('.edit-menu-item-classes').val() || '' };
        }, function (v) {
          field.find('.mfs-menu-image-id').val(v.mfs_image_id).trigger('change');
          item.find('.edit-menu-item-classes').val(v.classes).trigger('change');
          if (window.wpNavMenu && window.wpNavMenu.registerChange) window.wpNavMenu.registerChange();
        });
      });
    }
    $(setupAdmin);
    $(document).ajaxComplete(setupAdmin);
  }
})(jQuery, window.wp);
