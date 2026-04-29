(function (wp) {
  const { createElement: el } = wp.element;
  const { registerBlockType } = wp.blocks;
  const { InnerBlocks, useBlockProps } = wp.blockEditor || wp.editor;

  registerBlockType('modfarm/simple-tab', {
    title: 'Tab',
    icon: 'tag',
    category: 'layout',
    parent: ['modfarm/simple-tabs'],
    attributes: { label:{type:'string',default:'Tab'}, _active:{type:'boolean',default:false} },
    edit(props) {
      const { attributes } = props;
      const blockProps = useBlockProps({ className: attributes._active ? 'is-active-tab' : '' });
      return el('div', blockProps, el(InnerBlocks, { renderAppender: InnerBlocks.ButtonBlockAppender }));
    },
    save(){ return null; }
  });
})(window.wp);