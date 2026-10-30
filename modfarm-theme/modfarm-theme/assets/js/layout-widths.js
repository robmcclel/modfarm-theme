(function (wp) {
  const el = wp.element.createElement;
  wp.hooks.addFilter('editor.BlockListBlock', 'modfarm/group-layout-widths', function (BlockListBlock) {
    return function (props) {
      const layout = (props.attributes || {}).layout || {};
      if (props.name !== 'core/group' || (layout.type && layout.type !== 'constrained')) return el(BlockListBlock, props);
      const style = Object.assign({}, (props.wrapperProps || {}).style);
      const widths = {contentSize: '--mf-layout-content-width', wideSize: '--mf-layout-wide-width'};
      Object.keys(widths).forEach(function (key) {
        const value = String(layout[key] || '').trim().toLowerCase();
        if (/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|rem|em|%|vw|vh|vmin|vmax)$/.test(value) && parseFloat(value) > 0) style[widths[key]] = value;
      });
      return el(BlockListBlock, Object.assign({}, props, {
        wrapperProps: Object.assign({}, props.wrapperProps, {style})
      }));
    };
  });
})(window.wp);
