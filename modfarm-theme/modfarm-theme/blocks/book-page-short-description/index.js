/* global window */
(function (wp) {
  if (!wp) return;

  const { createElement: el, Fragment } = wp.element;
  const { registerBlockType } = wp.blocks;

  const be = wp.blockEditor || wp.editor;
  if (!be) return;

  const { useBlockProps, InspectorControls } = be;

  const {
    PanelBody,
    TextareaControl,
    ToggleControl,
    SelectControl,
    RangeControl
  } = wp.components;

  const { useSelect } = wp.data;

  registerBlockType('modfarm/book-page-short-description', {
    edit: function (props) {
      const { attributes, setAttributes } = props;

      // Safe defaults in case block.json didn't update / older blocks exist
      const overrideText = attributes.overrideText || '';
      const showIfEmpty = !!attributes.showIfEmpty;
      const textAlign = attributes.textAlign || 'left';
      const fontSize = Number.isFinite(parseInt(attributes.fontSize, 10))
        ? parseInt(attributes.fontSize, 10)
        : 18;
      const fontWeight = attributes.fontWeight || '400';

      const blockProps = useBlockProps({
        className: 'mfb-short-description mfb-short-description--align-' + textAlign,
        style: {
          fontSize: fontSize + 'px',
          fontWeight: fontWeight
        }
      });

      const postId = wp.data.select('core/editor').getCurrentPostId();

      const shortDesc = useSelect(
        (select) => {
          const rec = select('core').getEntityRecord('postType', 'book', postId);
          return (rec && rec.meta && rec.meta.short_description) ? rec.meta.short_description : '';
        },
        [postId]
      );

      const displayText = overrideText || shortDesc;

      return el(
        Fragment,
        {},
        el(
          'div',
          blockProps,
          displayText
            ? el('p', {}, displayText)
            : el('p', { style: { opacity: 0.5 } }, 'No short description set.')
        ),
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: 'Short Description Settings', initialOpen: true },

            el(TextareaControl, {
              label: 'Override Text (optional)',
              value: overrideText,
              onChange: (val) => setAttributes({ overrideText: val })
            }),

            el(SelectControl, {
              label: 'Text Alignment',
              value: textAlign,
              options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
              ],
              onChange: (val) => setAttributes({ textAlign: val })
            }),

            el(RangeControl, {
              label: 'Font Size (px)',
              value: fontSize,
              onChange: (val) => setAttributes({ fontSize: val }),
              min: 12,
              max: 48
            }),

            el(SelectControl, {
              label: 'Font Weight',
              value: fontWeight,
              options: [
                { label: 'Light (300)', value: '300' },
                { label: 'Normal (400)', value: '400' },
                { label: 'Medium (500)', value: '500' },
                { label: 'Semi-Bold (600)', value: '600' },
                { label: 'Bold (700)', value: '700' },
                { label: 'Extra Bold (800)', value: '800' }
              ],
              onChange: (val) => setAttributes({ fontWeight: val })
            }),

            el(ToggleControl, {
              label: 'Show Even If Empty',
              checked: showIfEmpty,
              onChange: (val) => setAttributes({ showIfEmpty: !!val })
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