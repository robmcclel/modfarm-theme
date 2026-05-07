(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const {
    PanelBody,
    SelectControl,
    RangeControl,
    ToggleControl,
    ColorPalette,
    Button,
    Spinner
  } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const { useSelect } = wp.data;
  const ServerSideRender = wp.serverSideRender;

  const THEME_COLORS = wp.data.select('core/block-editor')?.getSettings()?.colors || [];

  registerBlockType('modfarm/author-social-links', {
    apiVersion: 2,
    title: 'Author Social Links',
    icon: 'share',
    category: 'modfarm-theme',

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();
      const authorId = parseInt(attributes.authorId, 10) || 0;

      const authors = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'book-author', {
          per_page: -1,
          orderby: 'name',
          order: 'asc',
          _fields: 'id,name'
        });
      }, []);

      const authorOptions = [
        { label: __('Current author archive', 'modfarm'), value: 0 }
      ].concat((authors || []).map((term) => ({
        label: term.name,
        value: term.id
      })));

      const clearMonoColor = () => setAttributes({ monotoneColor: '' });

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/author-social-links',
            attributes: Object.assign({}, attributes, { authorId })
          })
        ),
        el(InspectorControls, {},
          el(PanelBody, { title: __('Author Source', 'modfarm'), initialOpen: true },
            authors === null
              ? el('div', { className: 'mfas-editor-loading' },
                  el(Spinner, {}),
                  el('span', {}, __('Loading authors...', 'modfarm'))
                )
              : el(SelectControl, {
                  label: __('Author', 'modfarm'),
                  value: authorId,
                  options: authorOptions,
                  onChange: (value) => setAttributes({ authorId: parseInt(value, 10) || 0 })
                }),
            el(ToggleControl, {
              label: __('Use current author archive when no author is selected', 'modfarm'),
              checked: attributes.useArchiveAuthor !== false,
              onChange: (value) => setAttributes({ useArchiveAuthor: !!value })
            })
          ),

          el(PanelBody, { title: __('Presentation', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Alignment', 'modfarm'),
              value: attributes.align || 'left',
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: (value) => setAttributes({ align: value })
            }),
            el(RangeControl, {
              label: __('Icon size (px)', 'modfarm'),
              value: parseInt(attributes.iconSize, 10) || 36,
              min: 16,
              max: 96,
              step: 2,
              onChange: (value) => setAttributes({ iconSize: parseInt(value, 10) || 36 })
            }),
            el(RangeControl, {
              label: __('Icon gap (px)', 'modfarm'),
              value: parseInt(attributes.gap, 10) || 14,
              min: 0,
              max: 48,
              step: 1,
              onChange: (value) => setAttributes({ gap: parseInt(value, 10) || 0 })
            }),
            el(SelectControl, {
              label: __('Color mode', 'modfarm'),
              value: attributes.colorMode || 'native',
              options: [
                { label: __('Native colors', 'modfarm'), value: 'native' },
                { label: __('Monotone', 'modfarm'), value: 'monotone' }
              ],
              onChange: (value) => setAttributes({ colorMode: value })
            }),
            (attributes.colorMode || 'native') === 'monotone' && el('div', { className: 'mfas-color-control' },
              el('label', { className: 'components-base-control__label' }, __('Icon color', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.monotoneColor || undefined,
                onChange: (value) => setAttributes({ monotoneColor: value || '' })
              }),
              el(Button, {
                variant: 'secondary',
                onClick: clearMonoColor
              }, __('Use inherited color', 'modfarm'))
            )
          ),

          el(PanelBody, { title: __('Link Behavior', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Open links in new tab', 'modfarm'),
              checked: attributes.openInNewTab !== false,
              onChange: (value) => setAttributes({ openInNewTab: !!value })
            }),
            el(ToggleControl, {
              label: __('Hide when no links are available', 'modfarm'),
              checked: !!attributes.hideIfEmpty,
              onChange: (value) => setAttributes({ hideIfEmpty: !!value })
            })
          )
        )
      );
    },

    save: function () {
      return null;
    }
  });
})(window.wp);
