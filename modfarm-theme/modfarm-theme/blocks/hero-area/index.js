(function (wp) {
  var el = wp.element.createElement;
  var be = wp.blockEditor;
  wp.blocks.registerBlockType('modfarm/hero-area', {
    edit: function (props) {
      var navigation = props.attributes.area === 'navigation';
      return el('div', be.useBlockProps({ className: 'mf-hero-area mf-hero-area--' + (navigation ? 'navigation' : 'content') }),
        el(be.InnerBlocks, { templateLock: false, renderAppender: be.InnerBlocks.ButtonBlockAppender })
      );
    },
    save: function () { return el(be.InnerBlocks.Content); }
  });
})(window.wp);
