/* global wp, modfarmHeroPresentation */
(function () {
  'use strict';
  var el = wp.element.createElement;
  var config = modfarmHeroPresentation;
  var __ = wp.i18n.__;
  var components = wp.components;
  var previewRequests = new Map();
  var previewImages = new Map();
  var previewListeners = new Map();

  function publishPreview(clientId, url) {
    if (!clientId || previewImages.get(clientId) === url) return;
    if (url) previewImages.set(clientId, url);
    else previewImages.delete(clientId);
    (previewListeners.get(clientId) || []).forEach(function (listener) { listener(url); });
  }

  // BlockListBlock does not receive the inner block's post context. Subscribe to
  // its actual BlockEdit result instead of guessing from the open document.
  function useBlockPreview(clientId) {
    var state = wp.element.useState(previewImages.get(clientId) || '');
    wp.element.useEffect(function () {
      var listeners = previewListeners.get(clientId) || new Set();
      listeners.add(state[1]);
      previewListeners.set(clientId, listeners);
      state[1](previewImages.get(clientId) || '');
      return function () {
        listeners.delete(state[1]);
        if (!listeners.size) previewListeners.delete(clientId);
      };
    }, [clientId]);
    return state[0];
  }

  // Covers sharing a post context share a request. Never save the resolved URL.
  function useInheritedImage(props) {
    var a = props.attributes;
    var enabled = !!a.modfarmImageDefaults && (!a.backgroundType || a.backgroundType === 'image');
    var context = props.context || {};
    var postId = Number(context.postId) || 0;
    var needsLookup = enabled && !a.url;
    var key = String(postId);
    var state = wp.element.useState(null);
    var result = state[0];
    var setResult = state[1];
    wp.element.useEffect(function () {
      if (!needsLookup) return;
      var active = true;
      if (!previewRequests.has(key)) {
        var data = {
          context: 'edit',
          attributes: { __preview: true, mode: 'auto', fallbackFeatured: false, useFeaturedImage: false }
        };
        if (postId) data.post_id = postId;
        var request = wp.apiFetch({ path: '/wp/v2/block-renderer/modfarm/hero-cover', method: 'POST', data: data }).then(function (response) {
          var document = new DOMParser().parseFromString(response.rendered || '', 'text/html');
          var hero = document.querySelector('[data-mf-hero-image]');
          if (!hero) throw new Error('Missing Hero preview image metadata');
          return { key: key, url: hero.getAttribute('data-mf-hero-image'), error: false };
        }).catch(function () {
          previewRequests.delete(key);
          return { key: key, url: config.defaultImage || '', error: true };
        });
        previewRequests.set(key, request);
      }
      previewRequests.get(key).then(function (next) { if (active) setResult(next); });
      return function () { active = false; };
    }, [needsLookup, key, postId]);
    if (!enabled) return { url: '', error: false };
    if (a.url) return { url: a.url, error: false };
    return result && result.key === key ? result : { url: config.defaultImage || '', error: false };
  }

  function CoverEdit(props) {
    var preview = useInheritedImage(props);
    var a = props.attributes;
    wp.element.useEffect(function () { publishPreview(props.clientId, preview.url); }, [props.clientId, preview.url]);
    wp.element.useEffect(function () {
      return function () { publishPreview(props.clientId, ''); };
    }, [props.clientId]);
    // Native Cover handles live, including unsaved, Featured Image changes. For
    // ordinary inheritance, supply display props without calling setAttributes.
    var editProps = props;
    if (preview.url && !a.url && !a.useFeaturedImage) {
      editProps = Object.assign({}, props, { attributes: Object.assign({}, a, {
        url: preview.url, id: undefined, backgroundType: 'image'
      }) });
    }
    return el(wp.element.Fragment, {}, el(props.OriginalEdit, editProps),
      el(wp.blockEditor.InspectorControls, {}, el(Controls, props),
        el(components.PanelBody, { title: __('ModFarm image defaults', 'modfarm'), initialOpen: false },
          el(components.ToggleControl, {
            label: __('Use ModFarm image defaults', 'modfarm'), checked: !!a.modfarmImageDefaults,
            help: __('Uses the local hero or site default when no image is selected. The Cover featured-image option overrides it on singular pages.', 'modfarm'),
            onChange: function (value) { props.setAttributes({ modfarmImageDefaults: value }); }
          }),
          preview.error ? el(components.Notice, { status: 'warning', isDismissible: false },
            __('The local image preview could not be refreshed. Reload the editor to try again.', 'modfarm')
          ) : null
        )
      )
    );
  }

  function CoverListBlock(props) {
    var edge = resolved(props.attributes);
    var previewUrl = useBlockPreview(props.clientId);
    var wrapper = Object.assign({}, props.wrapperProps);
    var fallback = !!previewUrl && !!props.attributes.useFeaturedImage;
    var focal = props.attributes.focalPoint || { x: 0.5, y: 0.5 };
    wrapper.style = Object.assign({}, wrapper.style, {
      '--mf-hero-edge-color': edge.color,
      '--mf-cover-inherited-image': fallback ? 'url(' + JSON.stringify(previewUrl) + ')' : undefined,
      '--mf-cover-inherited-position': (focal.x * 100) + '% ' + (focal.y * 100) + '%',
      '--mf-cover-inherited-size': props.attributes.isRepeated ? 'auto' : 'cover',
      '--mf-cover-inherited-repeat': props.attributes.isRepeated ? 'repeat' : 'no-repeat'
    });
    return el(props.OriginalListBlock, Object.assign({}, props, {
      className: (props.className || '') + ' mf-cover-edge-preview mf-cover-edge-preview--' + edge.preset + (fallback ? ' mf-cover-inherited-preview' : ''),
      wrapperProps: wrapper
    }));
  }
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
      return el(CoverEdit, Object.assign({}, props, { OriginalEdit: BlockEdit }));
    };
  }, 'ModFarmCoverControls'));
  wp.hooks.addFilter('editor.BlockListBlock', 'modfarm/cover-preview', wp.compose.createHigherOrderComponent(function (BlockListBlock) {
    return function (props) {
      if (props.name !== 'core/cover') return el(BlockListBlock, props);
      return el(CoverListBlock, Object.assign({}, props, { OriginalListBlock: BlockListBlock }));
    };
  }, 'ModFarmCoverPreview'));
})();
