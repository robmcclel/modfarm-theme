/* global window */
(function (wp) {
  if (!wp) return;

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useEffect = wp.element.useEffect;
  var useMemo = wp.element.useMemo;

  var __ = wp.i18n.__;

  var registerBlockType = wp.blocks.registerBlockType;

  var blockEditor = wp.blockEditor || wp.editor;
  var useBlockProps = blockEditor.useBlockProps;
  var InnerBlocks = blockEditor.InnerBlocks;
  var InspectorControls = blockEditor.InspectorControls;

  var components = wp.components;
  var PanelBody = components.PanelBody;
  var SelectControl = components.SelectControl;
  var ToggleControl = components.ToggleControl;
  var Button = components.Button;
  var ButtonGroup = components.ButtonGroup;

  // WP style color picker
  var ColorPalette = components.ColorPalette;

  var data = wp.data;
  var useSelect = data.useSelect;

  var ALLOWED = ['modfarm/tab-panel'];

  function shallowTabsEqual(a, b) {
    if (!a || !b) return false;
    if (a.length !== b.length) return false;
    for (var i = 0; i < a.length; i++) {
      if ((a[i].id || '') !== (b[i].id || '')) return false;
      if ((a[i].title || '') !== (b[i].title || '')) return false;
    }
    return true;
  }

  function clampInt(n, min, max) {
    n = parseInt(n, 10);
    if (isNaN(n)) n = min;
    if (n < min) n = min;
    if (n > max) n = max;
    return n;
  }

  function buildWrapperClass(a) {
    var variant = a.variant || 'underline';
    var navAlign = a.navAlign || 'left';
    var shape = a.tabShape || 'rounded';

    return (
      'mf-tabs mf-tabs--' +
      variant +
      ' mf-tabs--align-' +
      navAlign +
      ' mf-tabs--shape-' +
      shape +
      (a.equalWidth ? ' mf-tabs--equal' : '')
    );
  }

  function buildWrapperStyle(a) {
    // CSS vars so we can style in CSS cleanly
    var s = {};
    if (a.tabBg) s['--mf-tab-bg'] = a.tabBg;
    if (a.tabText) s['--mf-tab-text'] = a.tabText;
    if (a.tabBorder) s['--mf-tab-border'] = a.tabBorder;

    if (a.tabBgActive) s['--mf-tab-bg-active'] = a.tabBgActive;
    if (a.tabTextActive) s['--mf-tab-text-active'] = a.tabTextActive;
    if (a.tabBorderActive) s['--mf-tab-border-active'] = a.tabBorderActive;

    return s;
  }

  registerBlockType('modfarm/simple-tabs', {
    apiVersion: 2,
    title: __('Simple Tabs', 'modfarm'),
    category: 'modfarm-theme',
    icon: 'index-card',
    supports: { html: false },

    attributes: {
      tabs: { type: 'array', default: [] },

      // Runtime open tab
      activeIndex: { type: 'number', default: 0 },

      // Initial/default open tab (native open)
      defaultActiveIndex: { type: 'number', default: 0 },

      navAlign: { type: 'string', default: 'left' },
      variant: { type: 'string', default: 'underline' },
      equalWidth: { type: 'boolean', default: false },

      // NEW: Label/button styling
      tabShape: { type: 'string', default: 'rounded' }, // square | rounded | pill
      tabBg: { type: 'string', default: '' },
      tabText: { type: 'string', default: '' },
      tabBorder: { type: 'string', default: '' },

      tabBgActive: { type: 'string', default: '' },
      tabTextActive: { type: 'string', default: '' },
      tabBorderActive: { type: 'string', default: '' },

      // Optional: show chevron (useful for “Editions” vibe)
      showChevron: { type: 'boolean', default: false }
    },

    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var clientId = props.clientId;

      var blockProps = useBlockProps({
        className: buildWrapperClass(attributes),
        style: buildWrapperStyle(attributes)
      });

      // Get child tab blocks
      var innerBlocks = useSelect(
        function (select) {
          var editorStore = select('core/block-editor');
          if (!editorStore || !editorStore.getBlocks) return [];
          return editorStore.getBlocks(clientId) || [];
        },
        [clientId]
      );

      // Build tabs array from child blocks (id + title)
      var derivedTabs = useMemo(function () {
        var out = [];
        for (var i = 0; i < innerBlocks.length; i++) {
          var b = innerBlocks[i];
          var attrs = (b && b.attributes) ? b.attributes : {};
          var id = attrs.tabId || ('tab-' + (b.clientId || i));
          var title = attrs.title || __('Tab', 'modfarm');
          out.push({ id: id, title: title });
        }
        return out;
      }, [innerBlocks]);

      // Keep parent tabs attribute synced + clamp indices
      useEffect(
        function () {
          if (!shallowTabsEqual(attributes.tabs || [], derivedTabs)) {
            setAttributes({ tabs: derivedTabs });
          }

          var max = Math.max(0, (derivedTabs || []).length - 1);

          var nextActive = clampInt(attributes.activeIndex, 0, max);
          if (nextActive !== attributes.activeIndex) {
            setAttributes({ activeIndex: nextActive });
          }

          var nextDefault = clampInt(attributes.defaultActiveIndex, 0, max);
          if (nextDefault !== attributes.defaultActiveIndex) {
            setAttributes({ defaultActiveIndex: nextDefault });
          }
        },
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [derivedTabs]
      );

      function setActive(idx) {
        setAttributes({ activeIndex: idx });
      }

      function addTab() {
        var createBlock = wp.blocks.createBlock;
        var dispatch = wp.data.dispatch('core/block-editor');

        var count = (derivedTabs || []).length + 1;
        var newId = 'tab-' + Date.now();
        var newTitle = __('Tab', 'modfarm') + ' ' + count;

        var newBlock = createBlock('modfarm/tab-panel', {
          title: newTitle,
          tabId: newId
        });

        dispatch.insertBlocks(newBlock, (innerBlocks || []).length, clientId);
        setAttributes({ activeIndex: (derivedTabs || []).length });
      }

      function removeActiveTab() {
        var idx = attributes.activeIndex || 0;
        if (!innerBlocks || !innerBlocks.length) return;
        if (idx < 0 || idx >= innerBlocks.length) return;

        var dispatch = wp.data.dispatch('core/block-editor');
        dispatch.removeBlock(innerBlocks[idx].clientId);
      }

      // Editor nav buttons
      var navButtons = (attributes.tabs || []).map(function (t, idx) {
        var isActive = idx === (attributes.activeIndex || 0);
        return el(
          'button',
          {
            key: t.id || idx,
            className: 'mf-tabs__btn' + (isActive ? ' is-active' : ''),
            type: 'button',
            onClick: function () { setActive(idx); },
            'data-tab': t.id || ('tab-' + idx),
            role: 'tab',
            'aria-selected': isActive ? 'true' : 'false'
          },
          t.title || __('Tab', 'modfarm'),
          attributes.showChevron ? el('span', { className: 'mf-tabs__chev', 'aria-hidden': 'true' }, '▾') : null
        );
      });

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Tabs', 'modfarm'), initialOpen: true },
            el('p', { style: { marginTop: 0 } }, __('Add “Tab Panel” blocks inside this container.', 'modfarm')),
            el(
              ButtonGroup,
              {},
              el(Button, { variant: 'primary', onClick: addTab }, __('Add Tab', 'modfarm')),
              el(
                Button,
                { variant: 'secondary', onClick: removeActiveTab, disabled: !(innerBlocks && innerBlocks.length) },
                __('Remove Active', 'modfarm')
              )
            ),
            el(SelectControl, {
              label: __('Default open tab', 'modfarm'),
              value: String(attributes.defaultActiveIndex || 0),
              options: (attributes.tabs || []).map(function (t, idx) {
                return { label: t.title || ('Tab ' + (idx + 1)), value: String(idx) };
              }),
              onChange: function (v) {
                var n = parseInt(v || '0', 10);
                if (isNaN(n)) n = 0;
                setAttributes({ defaultActiveIndex: n, activeIndex: n });
              }
            })
          ),
          el(
            PanelBody,
            { title: __('Nav Appearance', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Tabs Nav Style', 'modfarm'),
              value: attributes.variant || 'underline',
              options: [
                { label: __('Underline', 'modfarm'), value: 'underline' },
                { label: __('Pills', 'modfarm'), value: 'pills' },
                { label: __('Box', 'modfarm'), value: 'box' }
              ],
              onChange: function (v) { setAttributes({ variant: v }); }
            }),
            el(SelectControl, {
              label: __('Shape', 'modfarm'),
              value: attributes.tabShape || 'rounded',
              options: [
                { label: __('Square', 'modfarm'), value: 'square' },
                { label: __('Rounded', 'modfarm'), value: 'rounded' },
                { label: __('Pill', 'modfarm'), value: 'pill' }
              ],
              onChange: function (v) { setAttributes({ tabShape: v }); }
            }),
            el(SelectControl, {
              label: __('Nav Alignment', 'modfarm'),
              value: attributes.navAlign || 'left',
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: function (v) { setAttributes({ navAlign: v }); }
            }),
            el(ToggleControl, {
              label: __('Equal-width tabs', 'modfarm'),
              checked: !!attributes.equalWidth,
              onChange: function (v) { setAttributes({ equalWidth: !!v }); }
            }),
            el(ToggleControl, {
              label: __('Show chevron indicator', 'modfarm'),
              checked: !!attributes.showChevron,
              onChange: function (v) { setAttributes({ showChevron: !!v }); }
            })
          ),
          el(
            PanelBody,
            { title: __('Tab Label Colors', 'modfarm'), initialOpen: false },

            el('p', { style: { marginBottom: '6px' } }, __('Default', 'modfarm')),
            el('div', { className: 'mf-color-row' },
              el('div', { className: 'mf-color-col' },
                el('div', { className: 'mf-color-label' }, __('Background', 'modfarm')),
                el(ColorPalette, { value: attributes.tabBg || '', onChange: function (v) { setAttributes({ tabBg: v || '' }); } })
              ),
              el('div', { className: 'mf-color-col' },
                el('div', { className: 'mf-color-label' }, __('Text', 'modfarm')),
                el(ColorPalette, { value: attributes.tabText || '', onChange: function (v) { setAttributes({ tabText: v || '' }); } })
              ),
              el('div', { className: 'mf-color-col' },
                el('div', { className: 'mf-color-label' }, __('Border', 'modfarm')),
                el(ColorPalette, { value: attributes.tabBorder || '', onChange: function (v) { setAttributes({ tabBorder: v || '' }); } })
              )
            ),

            el('p', { style: { marginTop: '14px', marginBottom: '6px' } }, __('Active', 'modfarm')),
            el('div', { className: 'mf-color-row' },
              el('div', { className: 'mf-color-col' },
                el('div', { className: 'mf-color-label' }, __('Background', 'modfarm')),
                el(ColorPalette, { value: attributes.tabBgActive || '', onChange: function (v) { setAttributes({ tabBgActive: v || '' }); } })
              ),
              el('div', { className: 'mf-color-col' },
                el('div', { className: 'mf-color-label' }, __('Text', 'modfarm')),
                el(ColorPalette, { value: attributes.tabTextActive || '', onChange: function (v) { setAttributes({ tabTextActive: v || '' }); } })
              ),
              el('div', { className: 'mf-color-col' },
                el('div', { className: 'mf-color-label' }, __('Border', 'modfarm')),
                el(ColorPalette, { value: attributes.tabBorderActive || '', onChange: function (v) { setAttributes({ tabBorderActive: v || '' }); } })
              )
            )
          )
        ),

        el(
          'div',
          blockProps,
          el('div', { className: 'mf-tabs__nav', role: 'tablist' }, navButtons),
          el(
            'div',
            { className: 'mf-tabs__panels', 'data-active-index': String(attributes.activeIndex || 0) },
            el(InnerBlocks, { allowedBlocks: ALLOWED, renderAppender: false })
          ),
          (!innerBlocks || !innerBlocks.length) &&
            el(
              'div',
              { className: 'mf-tabs__empty' },
              el('p', {}, __('No tabs yet. Click “Add Tab” in the sidebar (or use the button below).', 'modfarm')),
              el(Button, { variant: 'primary', onClick: addTab }, __('Add Your First Tab', 'modfarm'))
            )
        )
      );
    },

    // keep whatever you’re doing now (pass-through render in PHP)
    save: function (props) {
      var a = props.attributes || {};
      var blockProps = (wp.blockEditor || wp.editor).useBlockProps.save({
        className: buildWrapperClass(a),
        style: buildWrapperStyle(a)
      });

      var tabs = a.tabs || [];
      var navButtons = tabs.map(function (t, idx) {
        var isActive = idx === (a.activeIndex || 0);
        return el(
          'button',
          {
            key: t.id || idx,
            className: 'mf-tabs__btn' + (isActive ? ' is-active' : ''),
            type: 'button',
            'data-tab': t.id || ('tab-' + idx),
            role: 'tab',
            'aria-selected': isActive ? 'true' : 'false'
          },
          t.title || ('Tab ' + (idx + 1)),
          a.showChevron ? el('span', { className: 'mf-tabs__chev', 'aria-hidden': 'true' }, '▾') : null
        );
      });

      return el(
        'div',
        blockProps,
        el('div', { className: 'mf-tabs__nav', role: 'tablist' }, navButtons),
        el(
          'div',
          {
            className: 'mf-tabs__panels',
            'data-active-index': String(a.defaultActiveIndex || 0) // use “native open”
          },
          el((wp.blockEditor || wp.editor).InnerBlocks.Content, null)
        )
      );
    }
  });
})(window.wp);