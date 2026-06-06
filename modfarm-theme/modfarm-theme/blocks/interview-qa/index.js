(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { InspectorControls, PanelColorSettings, RichText, useBlockProps } = wp.blockEditor || wp.editor;
  const {
    Button,
    PanelBody,
    SelectControl,
    Spinner,
    TextControl,
    ToggleControl
  } = wp.components;
  const { Fragment, createElement: el } = wp.element;
  const { useSelect } = wp.data;
  const ServerSideRender = wp.serverSideRender;

  function normalizeItems(items) {
    return Array.isArray(items) ? items : [];
  }

  function emptyItem() {
    return { question: '', answer: '' };
  }

  function move(items, from, to) {
    const next = items.slice();
    if (to < 0 || to >= next.length) return next;
    const item = next.splice(from, 1)[0];
    next.splice(to, 0, item);
    return next;
  }

  registerBlockType('modfarm/interview-qa', {
    apiVersion: 2,
    title: __('Interview Q&A', 'modfarm'),
    icon: 'format-chat',
    category: 'modfarm-theme',

    edit: function (props) {
      const { attributes, setAttributes, isSelected } = props;
      const blockProps = useBlockProps({ className: 'mf-interview-qa-editor' });
      const items = normalizeItems(attributes.items);

      const authors = useSelect((select) => {
        const core = select('core');
        const query = {
          per_page: 100,
          orderby: 'name',
          order: 'asc',
          _fields: 'id,name'
        };
        const singular = core.getEntityRecords('taxonomy', 'book-author', query);
        const plural = core.getEntityRecords('taxonomy', 'book-authors', query);

        if (singular === null && plural === null) {
          return null;
        }

        const seen = {};
        const singularItems = Array.isArray(singular)
          ? singular.map((term) => Object.assign({}, term, { taxonomy: 'book-author' }))
          : [];
        const pluralItems = Array.isArray(plural)
          ? plural.map((term) => Object.assign({}, term, { taxonomy: 'book-authors' }))
          : [];

        return singularItems.concat(pluralItems).filter((term) => {
          if (!term) return false;
          const key = `${term.taxonomy}:${term.id}`;
          if (seen[key]) return false;
          seen[key] = true;
          return true;
        });
      }, []);

      const selectedAuthorValue = `${attributes.authorTaxonomy || 'book-author'}:${parseInt(attributes.authorId, 10) || 0}`;
      const authorOptions = [{ label: __('No author profile', 'modfarm'), value: 'book-author:0' }].concat(
        (authors || []).map((term) => ({ label: term.name, value: `${term.taxonomy}:${term.id}` }))
      );

      const updateItem = (index, patch) => {
        const next = items.map((item, itemIndex) => (
          itemIndex === index ? Object.assign({}, item, patch) : item
        ));
        setAttributes({ items: next });
      };

      const addItem = () => setAttributes({ items: items.concat([emptyItem()]) });
      const removeItem = (index) => {
        const next = items.slice();
        next.splice(index, 1);
        setAttributes({ items: next });
      };

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Author Data', 'modfarm'), initialOpen: false },
            authors === null
              ? el('div', { className: 'mf-interview-qa-loading' }, el(Spinner, {}), __('Loading authors...', 'modfarm'))
                : el(SelectControl, {
                  label: __('Creator term', 'modfarm'),
                  value: selectedAuthorValue,
                  options: authorOptions,
                  onChange: (value) => {
                    const parts = String(value || 'book-author:0').split(':');
                    const taxonomy = parts[0] || 'book-author';
                    const authorId = parseInt(parts[1] || '0', 10) || 0;
                    setAttributes({
                      authorId,
                      authorTaxonomy: taxonomy,
                      showAuthorProfile: authorId > 0 ? true : !!attributes.showAuthorProfile
                    });
                  }
                }),
            el(ToggleControl, {
              label: __('Show author profile', 'modfarm'),
              checked: !!attributes.showAuthorProfile,
              onChange: (value) => setAttributes({ showAuthorProfile: !!value })
            }),
            !!attributes.showAuthorProfile && el(ToggleControl, {
              label: __('Show description (full bio)', 'modfarm'),
              checked: attributes.showAuthorBio !== false,
              onChange: (value) => setAttributes({ showAuthorBio: !!value })
            }),
            !!attributes.showAuthorProfile && el(ToggleControl, {
              label: __('Show author social links', 'modfarm'),
              checked: attributes.showAuthorSocials !== false,
              onChange: (value) => setAttributes({ showAuthorSocials: !!value })
            })
          ),
          el(
            PanelBody,
            { title: __('Structured Data', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Output JSON-LD', 'modfarm'),
              checked: attributes.emitStructuredData !== false,
              onChange: (value) => setAttributes({ emitStructuredData: !!value })
            })
          ),
          el(
            PanelBody,
            { title: __('Presentation', 'modfarm'), initialOpen: false },
            el(TextControl, {
              label: __('Heading', 'modfarm'),
              value: attributes.heading || '',
              onChange: (value) => setAttributes({ heading: value })
            }),
            el(SelectControl, {
              label: __('Question heading level', 'modfarm'),
              value: attributes.questionTag || 'h3',
              options: [
                { label: 'H2', value: 'h2' },
                { label: 'H3', value: 'h3' },
                { label: 'H4', value: 'h4' },
                { label: 'Paragraph', value: 'p' }
              ],
              onChange: (value) => setAttributes({ questionTag: value || 'h3' })
            }),
            el(ToggleControl, {
              label: __('Show question marker', 'modfarm'),
              checked: attributes.showQuestionMarker !== false,
              onChange: (value) => setAttributes({ showQuestionMarker: !!value })
            }),
            el(SelectControl, {
              label: __('Color source', 'modfarm'),
              value: attributes.colorMode || 'inherit',
              options: [
                { label: __('Use ModFarm settings', 'modfarm'), value: 'inherit' },
                { label: __('Custom colors', 'modfarm'), value: 'custom' }
              ],
              onChange: (value) => {
                const patch = { colorMode: value || 'inherit' };
                if (value !== 'custom') {
                  patch.accentColor = '';
                  patch.questionBgColor = '';
                  patch.markerTextColor = '';
                }
                setAttributes(patch);
              }
            })
          ),
          PanelColorSettings && (attributes.colorMode || 'inherit') === 'custom' && el(
            PanelColorSettings,
            {
              title: __('Interview Colors', 'modfarm'),
              initialOpen: false,
              colorSettings: [
                {
                  label: __('Accent / marker background', 'modfarm'),
                  value: attributes.accentColor || '',
                  onChange: (value) => setAttributes({ accentColor: value || '' })
                },
                {
                  label: __('Question background', 'modfarm'),
                  value: attributes.questionBgColor || '',
                  onChange: (value) => setAttributes({ questionBgColor: value || '' })
                },
                {
                  label: __('Marker text', 'modfarm'),
                  value: attributes.markerTextColor || '',
                  onChange: (value) => setAttributes({ markerTextColor: value || '' })
                }
              ]
            }
          )
        ),
        el(
          'div',
          blockProps,
          el('div', { className: 'mf-interview-qa-editor__preview' },
            el(ServerSideRender, {
              block: 'modfarm/interview-qa',
              attributes: attributes
            })
          ),
          isSelected && el('div', { className: 'mf-interview-qa-editor__controls' },
            items.length === 0 && el('p', {}, __('Add the first question and answer pair.', 'modfarm')),
            items.map((item, index) =>
              el('div', { className: 'mf-interview-qa-editor__item', key: index },
                el('div', { className: 'mf-interview-qa-editor__item-head' },
                  el('strong', {}, `${__('Q&A Pair', 'modfarm')} ${index + 1}`),
                  el('div', {},
                    el(Button, {
                      variant: 'secondary',
                      disabled: index === 0,
                      onClick: () => setAttributes({ items: move(items, index, index - 1) })
                    }, __('Up', 'modfarm')),
                    el(Button, {
                      variant: 'secondary',
                      disabled: index === items.length - 1,
                      onClick: () => setAttributes({ items: move(items, index, index + 1) })
                    }, __('Down', 'modfarm')),
                    el(Button, {
                      variant: 'secondary',
                      isDestructive: true,
                      onClick: () => removeItem(index)
                    }, __('Remove', 'modfarm'))
                  )
                ),
                el('label', { className: 'mf-interview-qa-editor__label' }, __('Question', 'modfarm')),
                el(RichText, {
                  tagName: 'div',
                  className: 'mf-interview-qa-editor__field',
                  value: item.question || '',
                  allowedFormats: ['core/bold', 'core/italic', 'core/link'],
                  onChange: (value) => updateItem(index, { question: value })
                }),
                el('label', { className: 'mf-interview-qa-editor__label' }, __('Answer', 'modfarm')),
                el(RichText, {
                  tagName: 'div',
                  className: 'mf-interview-qa-editor__field mf-interview-qa-editor__field--answer',
                  value: item.answer || '',
                  multiline: 'p',
                  allowedFormats: ['core/bold', 'core/italic', 'core/link'],
                  onChange: (value) => updateItem(index, { answer: value })
                })
              )
            ),
            el(Button, { variant: 'primary', onClick: addItem }, __('Add Q&A Pair', 'modfarm'))
          )
        )
      );
    },

    save: function () {
      return null;
    }
  });
})(window.wp);
