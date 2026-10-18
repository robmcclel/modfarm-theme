/* global wp, modfarmHeroPresentation */
(function () {
  'use strict';
  var el = wp.element.createElement;
  var config = modfarmHeroPresentation;
  var __ = wp.i18n.__;
  var components = wp.components;
  function choices(kind) {
    return [{ label: __('Use site default', 'modfarm'), value: 'site' }].concat(
      Object.keys(config.options[kind]).map(function (key) { return { label: config.options[kind][key], value: key }; })
    );
  }
  function colorValue(key) {
    var colors = {
      background: ['background_color', '#ffffff'], primary: ['primary_color', '#336699'],
      secondary: ['secondary_color', '#444444'], button: ['button_color', '#222222'],
      'button-text': ['button_text_color', '#ffffff'], heading: ['header_text_color', '#111111'],
      body: ['body_text_color', '#1d2327'], link: ['link_color', '#0066cc']
    };
    var pair = colors[key] || colors.background;
    return config.settings[pair[0]] || pair[1];
  }
  function resolved(attrs) {
    var preset = attrs.edgePreset && attrs.edgePreset !== 'site' ? attrs.edgePreset : config.preset;
    var color = attrs.edgeColor && attrs.edgeColor !== 'site' ? attrs.edgeColor : config.color;
    return { preset: preset, color: colorValue(color) };
  }
  function Controls(props) {
    var a = props.attributes;
    return el(components.PanelBody, { title: __('Edge treatment', 'modfarm'), initialOpen: false },
      el(components.SelectControl, {
        label: __('Gradation preset', 'modfarm'), value: a.edgePreset || 'site', options: choices('presets'),
        onChange: function (value) { props.setAttributes({ edgePreset: value }); }
      }),
      el(components.SelectControl, {
        label: __('Fade / border color', 'modfarm'), value: a.edgeColor || 'site', options: choices('colors'),
        help: __('Linked to ModFarm Settings; color changes carry through automatically.', 'modfarm'),
        onChange: function (value) { props.setAttributes({ edgeColor: value }); }
      })
    );
  }
  window.modfarmHeroControls = { Controls: Controls, resolved: resolved };
  wp.hooks.addFilter('blocks.registerBlockType', 'modfarm/cover-presentation', function (settings, name) {
    if (name !== 'core/cover') return settings;
    return Object.assign({}, settings, { attributes: Object.assign({}, settings.attributes, {
      edgePreset: { type: 'string', default: 'site' }, edgeColor: { type: 'string', default: 'site' },
      modfarmImageDefaults: { type: 'boolean', default: false }
    }) });
  });
  wp.hooks.addFilter('editor.BlockEdit', 'modfarm/cover-controls', wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      if (props.name !== 'core/cover') return el(BlockEdit, props);
      return el(wp.element.Fragment, {}, el(BlockEdit, props),
        el(wp.blockEditor.InspectorControls, {}, el(Controls, props),
          el(components.PanelBody, { title: __('ModFarm image defaults', 'modfarm'), initialOpen: false },
            el(components.ToggleControl, {
              label: __('Use ModFarm image defaults', 'modfarm'), checked: !!props.attributes.modfarmImageDefaults,
              help: __('Uses the local hero or site default when no image is selected. The Cover featured-image option overrides it on singular pages.', 'modfarm'),
              onChange: function (value) { props.setAttributes({ modfarmImageDefaults: value }); }
            })
          )
        )
      );
    };
  }, 'ModFarmCoverControls'));
  wp.hooks.addFilter('editor.BlockListBlock', 'modfarm/cover-preview', wp.compose.createHigherOrderComponent(function (BlockListBlock) {
    return function (props) {
      if (props.name !== 'core/cover') return el(BlockListBlock, props);
      var edge = resolved(props.attributes);
      var wrapper = Object.assign({}, props.wrapperProps);
      wrapper.style = Object.assign({}, wrapper.style, { '--mf-hero-edge-color': edge.color });
      return el(BlockListBlock, Object.assign({}, props, {
        className: (props.className || '') + ' mf-cover-edge-preview mf-cover-edge-preview--' + edge.preset,
        wrapperProps: wrapper
      }));
    };
  }, 'ModFarmCoverPreview'));
})();
