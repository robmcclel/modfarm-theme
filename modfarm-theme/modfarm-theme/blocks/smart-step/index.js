/* global window */
(function (wp) {
  if (!wp) return;

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var __ = wp.i18n.__;
  var registerBlockType = wp.blocks.registerBlockType;
  var blockEditor = wp.blockEditor || wp.editor;
  var useBlockProps = blockEditor.useBlockProps;
  var InnerBlocks = blockEditor.InnerBlocks;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var SelectControl = wp.components.SelectControl;

  var CONDITION_OPTIONS = [
    { label: __('Always', 'modfarm'), value: 'always' },
    { label: __('User is logged in', 'modfarm'), value: 'logged_in' },
    { label: __('Checkout completed', 'modfarm'), value: 'checkout_complete' },
    { label: __('Never', 'modfarm'), value: 'never' }
  ];

  registerBlockType('modfarm/smart-step', {
    apiVersion: 3,
    title: __('Smart Step', 'modfarm'),
    parent: ['modfarm/smart-sequence'],
    category: 'modfarm-theme',
    icon: 'excerpt-view',

    attributes: {
      stepNumber: { type: 'number', default: 1 },
      title: { type: 'string', default: __('Step', 'modfarm') },
      unlockCondition: { type: 'string', default: 'always' },
      completionCondition: { type: 'string', default: 'never' },
      lockedMessage: { type: 'string', default: __('Complete the previous step to continue.', 'modfarm') }
    },

    edit: function (props) {
      var a = props.attributes;
      var setAttributes = props.setAttributes;
      var blockProps = useBlockProps({ className: 'mf-smart-step mf-smart-step--editor' });

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Step Settings', 'modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Step number', 'modfarm'),
              type: 'number',
              min: 1,
              value: a.stepNumber || 1,
              onChange: function (value) { setAttributes({ stepNumber: Math.max(1, parseInt(value, 10) || 1) }); }
            }),
            el(TextControl, {
              label: __('Step title', 'modfarm'),
              value: a.title || '',
              onChange: function (value) { setAttributes({ title: value }); }
            }),
            el(SelectControl, {
              label: __('Unlock when', 'modfarm'),
              value: a.unlockCondition || 'always',
              options: CONDITION_OPTIONS.filter(function (option) { return option.value !== 'never' || a.unlockCondition === 'never'; }),
              onChange: function (value) { setAttributes({ unlockCondition: value }); }
            }),
            el(SelectControl, {
              label: __('Mark complete when', 'modfarm'),
              value: a.completionCondition || 'never',
              options: CONDITION_OPTIONS,
              onChange: function (value) { setAttributes({ completionCondition: value }); }
            }),
            el(TextControl, {
              label: __('Locked message', 'modfarm'),
              value: a.lockedMessage || '',
              onChange: function (value) { setAttributes({ lockedMessage: value }); }
            })
          )
        ),
        el(
          'section',
          blockProps,
          el(
            'div',
            { className: 'mf-smart-step__summary' },
            el('span', { className: 'mf-smart-step__number' }, String(a.stepNumber || 1)),
            el(
              'span',
              { className: 'mf-smart-step__heading' },
              el('small', {}, __('Editable sequence step', 'modfarm')),
              el('strong', {}, a.title || __('Step', 'modfarm'))
            ),
            el('span', { className: 'mf-smart-step__editor-condition' }, (a.unlockCondition || 'always').replace('_', ' '))
          ),
          el('div', { className: 'mf-smart-step__content' }, el(InnerBlocks, { renderAppender: InnerBlocks.ButtonBlockAppender }))
        )
      );
    },

    save: function () {
      return el(InnerBlocks.Content, null);
    }
  });
})(window.wp);
