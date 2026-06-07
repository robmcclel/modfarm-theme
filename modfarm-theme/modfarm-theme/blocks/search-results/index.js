(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
  const { PanelBody, RangeControl, SelectControl, ToggleControl } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  registerBlockType('modfarm/search-results', {
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/search-results',
            attributes: attributes
          })
        ),
        el(InspectorControls, {},
          el(PanelBody, { title: __('Sections', 'modfarm'), initialOpen: true },
            el(ToggleControl, {
              label: __('Show books', 'modfarm'),
              checked: attributes.showBooks !== false,
              onChange: (value) => setAttributes({ showBooks: !!value })
            }),
            el(ToggleControl, {
              label: __('Show posts', 'modfarm'),
              checked: attributes.showPosts !== false,
              onChange: (value) => setAttributes({ showPosts: !!value })
            }),
            el(ToggleControl, {
              label: __('Show authors', 'modfarm'),
              checked: attributes.showAuthors !== false,
              onChange: (value) => setAttributes({ showAuthors: !!value })
            }),
            el(ToggleControl, {
              label: __('Show series', 'modfarm'),
              checked: attributes.showSeries !== false,
              onChange: (value) => setAttributes({ showSeries: !!value })
            })
          ),
          el(PanelBody, { title: __('Limits', 'modfarm'), initialOpen: false },
            el(RangeControl, {
              label: __('Books', 'modfarm'),
              value: attributes.booksLimit || 8,
              min: 1,
              max: 24,
              onChange: (value) => setAttributes({ booksLimit: parseInt(value, 10) || 8 })
            }),
            el(RangeControl, {
              label: __('Posts', 'modfarm'),
              value: attributes.postsLimit || 8,
              min: 1,
              max: 24,
              onChange: (value) => setAttributes({ postsLimit: parseInt(value, 10) || 8 })
            }),
            el(RangeControl, {
              label: __('Authors / series', 'modfarm'),
              value: attributes.termsLimit || 6,
              min: 1,
              max: 24,
              onChange: (value) => setAttributes({ termsLimit: parseInt(value, 10) || 6 })
            })
          ),
          el(PanelBody, { title: __('Book Cards', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Cover source', 'modfarm'),
              value: attributes.bookCoverSource || 'cover_ebook',
              options: [
                { label: __('Ebook cover', 'modfarm'), value: 'cover_ebook' },
                { label: __('3D ebook cover', 'modfarm'), value: 'cover_ebook_3d' },
                { label: __('Paperback cover', 'modfarm'), value: 'cover_paperback' },
                { label: __('Audiobook cover', 'modfarm'), value: 'cover_image_audio' },
                { label: __('Featured image', 'modfarm'), value: 'featured_image' }
              ],
              onChange: (value) => setAttributes({ bookCoverSource: value || 'cover_ebook' })
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
