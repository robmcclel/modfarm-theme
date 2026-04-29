(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, CheckboxControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;

  const fieldOptions = [
    { label: 'Publication Date', key: 'show_publication_date' },
    { label: 'Page Count', key: 'show_pages' },
    { label: 'ISBN', key: 'show_isbn' },
    { label: 'ASIN', key: 'show_asin' },
    { label: 'Publisher', key: 'show_publisher' },
    { label: 'Edition', key: 'show_edition' },

    { label: 'Audiobook Publisher', key: 'show_audiobook_publisher' },
    { label: 'Audiobook Narrator', key: 'show_audiobook_narrator' },
    { label: 'Audiobook Duration', key: 'show_audiobook_duration' },
    { label: 'Audiobook Pub Date', key: 'show_audiobook_publication_date' },

    { label: 'Translator', key: 'show_translator' },
    { label: 'Editor', key: 'show_editor' },
    { label: 'Reading Order', key: 'show_reading_order' },
    { label: 'Series Position', key: 'show_series_position' },

    { label: 'Format (Taxonomy)', key: 'show_format' },
    { label: 'Genre (Taxonomy)', key: 'show_genre' },
    { label: 'Series Name (Taxonomy)', key: 'show_series_name' },
    { label: 'Universe (Taxonomy)', key: 'show_universe' }
  ];

  wp.blocks.registerBlockType('modfarm/book-details', {
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const toggleField = function (key) {
        setAttributes({ [key]: !attributes[key] });
      };

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Select Fields to Display', 'modfarm'), initialOpen: true },
            fieldOptions.map(function (field) {
              return el(CheckboxControl, {
                key: field.key,
                label: field.label,
                checked: !!attributes[field.key],
                onChange: function () {
                  toggleField(field.key);
                }
              });
            })
          )
        ),
        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'modfarm/book-details',
            attributes: attributes
          })
        )
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp);