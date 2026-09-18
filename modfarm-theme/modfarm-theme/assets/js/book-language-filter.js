(function (wp, config) {
  'use strict';
  const el = wp.element.createElement;
  const __ = wp.i18n.__;
  wp.hooks.addFilter('blocks.registerBlockType', 'modfarm/book-language', function (settings, name) {
    if (!config.blocks.includes(name)) return settings;
    return Object.assign({}, settings, {
      attributes: Object.assign({}, settings.attributes, { bookLanguage: { type: 'string', default: '' } })
    });
  });
  wp.hooks.addFilter('editor.BlockEdit', 'modfarm/book-language', function (BlockEdit) {
    return function (props) {
      if (!config.blocks.includes(props.name)) return el(BlockEdit, props);
      const value = props.attributes.bookLanguage || '';
      const options = config.options.slice();
      if (!options.some(function (option) { return option.value === value; })) {
        options.push({ label: __('Saved language', 'modfarm') + ' (#' + value + ')', value: value });
      }
      return el(wp.element.Fragment, null,
        el(BlockEdit, props),
        props.isSelected && el(wp.blockEditor.InspectorControls, null,
          el(wp.components.PanelBody, { title: __('Book language', 'modfarm'), initialOpen: true },
            el(wp.components.SelectControl, {
              label: __('Filter books by language', 'modfarm'), value: value, options: options,
              help: props.name === 'modfarm/featured-book'
                ? __('Applies to automatic selection. Manually selected and pinned books take precedence.', 'modfarm')
                : __('English includes books with no Book Language assigned. Combines with the other book filters.', 'modfarm'),
              onChange: function (language) { props.setAttributes({ bookLanguage: language }); }
            })
          )
        )
      );
    };
  });
})(window.wp, window.modfarmBookLanguageFilter);
