/* global window */
(function (wp) {
  if (!wp) return;

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useEffect = wp.element.useEffect;

  var __ = wp.i18n.__;

  var registerBlockType = wp.blocks.registerBlockType;

  var blockEditor = wp.blockEditor || wp.editor;
  var useBlockProps = blockEditor.useBlockProps;
  var InnerBlocks = blockEditor.InnerBlocks;
  var InspectorControls = blockEditor.InspectorControls;

  var components = wp.components;
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;

  registerBlockType('modfarm/tab-panel', {
    apiVersion: 2,
    title: __('Tab Panel', 'modfarm'),
    parent: ['modfarm/simple-tabs'],
    icon: 'excerpt-view',
    category: 'modfarm-theme',

    attributes: {
      title: { type: 'string', default: __('Tab', 'modfarm') },
      tabId: { type: 'string', default: '' }
    },

    edit: function (props) {
      var a = props.attributes;
      var setAttributes = props.setAttributes;

      // Ensure we have a stable tabId
      useEffect(function () {
        if (!a.tabId) {
          setAttributes({ tabId: 'tab-' + Date.now() });
        }
      }, []); // run once

      var blockProps = useBlockProps({
        className: 'mf-tab-panel is-active'
      });

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Tab Settings', 'modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Tab Title', 'modfarm'),
              value: a.title || '',
              onChange: function (v) { setAttributes({ title: v }); }
            })
          )
        ),
        el(
          'div',
          blockProps,
          el(
            'div',
            { className: 'mf-tab-panel__label' },
            el('strong', {}, __('Tab:', 'modfarm') + ' '),
            el('span', {}, a.title || __('Tab', 'modfarm'))
          ),
          el(
            'div',
            { className: 'mf-tab-panel__content' },
            el(InnerBlocks, {
              renderAppender: InnerBlocks.ButtonBlockAppender
            })
          )
        )
      );
    },

    save: function (props) {
      var a = props.attributes || {};
      var blockProps = (wp.blockEditor || wp.editor).useBlockProps.save({
        className: 'mf-tab-panel',
        'data-tab-id': a.tabId || ''
      });

      return el(
        'div',
        blockProps,
        el((wp.blockEditor || wp.editor).InnerBlocks.Content, null)
      );
    }
  });
})(window.wp);