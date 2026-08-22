/* global window */
(function (wp) {
  if (!wp) return;

  var el = wp.element.createElement;
  var __ = wp.i18n.__;
  var registerBlockType = wp.blocks.registerBlockType;
  var blockEditor = wp.blockEditor || wp.editor;
  var useBlockProps = blockEditor.useBlockProps;
  var InnerBlocks = blockEditor.InnerBlocks;
  var InspectorControls = blockEditor.InspectorControls;
  var useSelect = wp.data.useSelect;
  var PanelBody = wp.components.PanelBody;
  var ToggleControl = wp.components.ToggleControl;

  var TEMPLATE = [
    ['modfarm/smart-step', {
      stepNumber: 1,
      title: 'Log in',
      unlockCondition: 'always',
      completionCondition: 'logged_in'
    }],
    ['modfarm/smart-step', {
      stepNumber: 2,
      title: 'Select access',
      unlockCondition: 'logged_in',
      completionCondition: 'checkout_complete'
    }],
    ['modfarm/smart-step', {
      stepNumber: 3,
      title: 'Payment',
      unlockCondition: 'checkout_complete',
      completionCondition: 'never'
    }]
  ];

  registerBlockType('modfarm/smart-sequence', {
    apiVersion: 3,
    title: __('Smart Sequence', 'modfarm'),
    category: 'modfarm-theme',
    icon: 'editor-ol',
    supports: { html: false },
    attributes: {
      showProgress: { type: 'boolean', default: true }
    },

    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var clientId = props.clientId;
      var blockProps = useBlockProps({ className: 'mf-smart-sequence' });
      var steps = useSelect(function (select) {
        var store = select('core/block-editor');
        return store && store.getBlocks ? store.getBlocks(clientId) : [];
      }, [clientId]);

      var progress = attributes.showProgress ? el(
        'ol',
        { className: 'mf-smart-sequence__progress', 'aria-label': __('Sequence steps', 'modfarm') },
        steps.map(function (step, index) {
          var a = step.attributes || {};
          return el(
            'li',
            { key: step.clientId || index, className: index === 0 ? 'is-active' : 'is-locked' },
            el('span', {}, String(a.stepNumber || index + 1)),
            el('strong', {}, a.title || __('Step', 'modfarm'))
          );
        })
      ) : null;

      return el(
        wp.element.Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Sequence Display', 'modfarm'), initialOpen: true },
            el(ToggleControl, {
              label: __('Show step progress header', 'modfarm'),
              checked: attributes.showProgress !== false,
              onChange: function (value) { setAttributes({ showProgress: value }); }
            })
          )
        ),
        el(
          'div',
          blockProps,
          el(
            'div',
            { className: 'mf-smart-sequence__editor-note' },
            __('All steps remain editable here. The frontend opens and locks them according to their conditions.', 'modfarm')
          ),
          progress,
          el(InnerBlocks, {
            allowedBlocks: ['modfarm/smart-step'],
            template: TEMPLATE,
            templateLock: false,
            renderAppender: InnerBlocks.ButtonBlockAppender
          })
        )
      );
    },

    save: function () {
      var blockProps = blockEditor.useBlockProps.save({ className: 'mf-smart-sequence' });
      return el('div', blockProps, el(InnerBlocks.Content, null));
    }
  });
})(window.wp);
