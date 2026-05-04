(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el, useState, useEffect, useRef } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls } = wp.blockEditor || {};
  const { PanelBody, TextControl, SelectControl, ToggleControl, RangeControl, Button, Notice, ColorPalette, BaseControl, ComboboxControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;
  const apiFetch = wp.apiFetch;
  const useSelect = wp.data && wp.data.useSelect;

  const IMAGE_ASPECT_OPTIONS = [
    { label: __('Square', 'modfarm'), value: '1 / 1' },
    { label: __('Portrait', 'modfarm'), value: '3 / 4' },
    { label: __('Landscape', 'modfarm'), value: '16 / 9' }
  ];

  function sanitizeIds(ids) {
    return Array.from(new Set((Array.isArray(ids) ? ids : []).map(function (id) {
      return parseInt(id, 10) || 0;
    }).filter(function (id) {
      return id > 0;
    })));
  }

  function OfferSearch({ label, help, onPick, disabled }) {
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [results, setResults] = useState([]);

    useEffect(function () {
      const term = (query || '').trim();
      if (term.length < 3 || disabled) {
        setResults([]);
        return;
      }

      const timer = setTimeout(function () {
        setLoading(true);
        apiFetch({ path: `/wp/v2/search?subtype=mf_offer&search=${encodeURIComponent(term)}&per_page=20` })
          .then(function (items) {
            setResults((items || []).map(function (item) {
              return { id: item.id, title: item.title || `#${item.id}` };
            }));
          })
          .catch(function () {
            setResults([]);
          })
          .finally(function () {
            setLoading(false);
          });
      }, 250);

      return function () {
        clearTimeout(timer);
      };
    }, [query, disabled]);

    return el(Fragment, null,
      el(TextControl, {
        label: label || __('Find Offer by Title', 'modfarm'),
        help: help || __('Type 3+ characters to search Store Offers.', 'modfarm'),
        value: query,
        disabled: !!disabled,
        onChange: function (next) {
          setQuery(next || '');
        }
      }),
      loading ? el(Notice, { status: 'info', isDismissible: false }, __('Searching...', 'modfarm')) : null,
      results.length ? el('div', { className: 'mfs-offer-search-results' },
        results.map(function (result) {
          return el(Button, {
            key: result.id,
            variant: 'secondary',
            disabled: !!disabled,
            onClick: function () {
              onPick(result.id);
              setQuery('');
              setResults([]);
            },
            style: { display: 'block', marginBottom: '6px', textAlign: 'left' }
          }, `#${result.id} - ${result.title}`);
        })
      ) : null
    );
  }

  function PickedOffers({ ids, onRemove, onClear, onMove }) {
    const [titles, setTitles] = useState({});
    const fetching = useRef({});

    useEffect(function () {
      const missing = ids.filter(function (id) {
        return !titles[id] && !fetching.current[id];
      });
      missing.slice(0, 8).forEach(function (id) {
        fetching.current[id] = true;
        apiFetch({ path: `/wp/v2/search?subtype=mf_offer&include=${id}&per_page=1` })
          .then(function (items) {
            setTitles(function (prev) {
              return Object.assign({}, prev, { [id]: items && items[0] ? items[0].title : `#${id}` });
            });
          })
          .catch(function () {
            setTitles(function (prev) {
              return Object.assign({}, prev, { [id]: `#${id}` });
            });
          })
          .finally(function () {
            delete fetching.current[id];
          });
      });
    }, [ids, titles]);

    if (!ids.length) {
      return null;
    }

    return el('div', { className: 'mfs-picked-offers' },
      el('div', { style: { marginBottom: '6px', fontWeight: 600 } }, __('Picked Offers', 'modfarm')),
      ids.map(function (id, index) {
        return el('div', {
          key: id,
          draggable: true,
          onDragStart: function (event) {
            event.dataTransfer.setData('text/plain', String(index));
          },
          onDragOver: function (event) {
            event.preventDefault();
          },
          onDrop: function (event) {
            event.preventDefault();
            const from = parseInt(event.dataTransfer.getData('text/plain'), 10);
            if (Number.isFinite(from)) {
              onMove(from, index);
            }
          },
          style: { display: 'flex', gap: '8px', alignItems: 'center', marginBottom: '6px' }
        },
          el('span', { style: { flex: '1 1 auto' } }, `#${id} - ${titles[id] || __('Loading...', 'modfarm')}`),
          el(Button, { isSmall: true, isDestructive: true, onClick: function () { onRemove(id); } }, __('Remove', 'modfarm'))
        );
      }),
      el(Button, { variant: 'secondary', isDestructive: true, onClick: onClear }, __('Clear picked Offers', 'modfarm'))
    );
  }

  function ColorField({ label, value, onChange, colors }) {
    return el(BaseControl, { label },
      el(ColorPalette, {
        value: value || '',
        colors: colors || [],
        disableCustomColors: false,
        clearable: true,
        onChange: function (next) {
          onChange(next || '');
        }
      })
    );
  }

  function ButtonControls({ attributes, setAttributes }) {
    const themeColors = useSelect
      ? useSelect(function (select) {
          const settings = select('core/block-editor')?.getSettings?.() || {};
          return settings.colors || [];
        }, [])
      : [];

    return el(Fragment, null,
      el(ToggleControl, {
        label: __('Show primary button', 'modfarm'),
        checked: attributes.showPrimaryButton !== false,
        onChange: function (value) { setAttributes({ showPrimaryButton: !!value }); }
      }),
      attributes.showPrimaryButton !== false && el(TextControl, {
        label: __('Primary label', 'modfarm'),
        value: attributes.primaryButtonLabel || '',
        onChange: function (value) { setAttributes({ primaryButtonLabel: value }); }
      }),
      el(ToggleControl, {
        label: __('Show secondary button', 'modfarm'),
        checked: attributes.showSecondaryButton !== false,
        onChange: function (value) { setAttributes({ showSecondaryButton: !!value }); }
      }),
      attributes.showSecondaryButton !== false && el(Fragment, null,
        el(TextControl, {
          label: __('Secondary label', 'modfarm'),
          value: attributes.secondaryButtonLabel || '',
          onChange: function (value) { setAttributes({ secondaryButtonLabel: value }); }
        }),
        el(SelectControl, {
          label: __('Secondary destination', 'modfarm'),
          value: attributes.secondaryButtonLink || 'permalink',
          options: [
            { label: __('Offer page', 'modfarm'), value: 'permalink' },
            { label: __('Checkout', 'modfarm'), value: 'checkout' }
          ],
          onChange: function (value) { setAttributes({ secondaryButtonLink: value || 'permalink' }); }
        })
      ),
      el(SelectControl, {
        label: __('Button layout', 'modfarm'),
        value: attributes.buttonLayout || 'joined',
        options: [
          { label: __('Joined', 'modfarm'), value: 'joined' },
          { label: __('Gap', 'modfarm'), value: 'gap' }
        ],
        onChange: function (value) { setAttributes({ buttonLayout: value || 'joined' }); }
      }),
      el(SelectControl, {
        label: __('Button corners', 'modfarm'),
        value: attributes.buttonCorners || 'square',
        options: [
          { label: __('Inherit', 'modfarm'), value: 'inherit' },
          { label: __('Square', 'modfarm'), value: 'square' },
          { label: __('Rounded', 'modfarm'), value: 'rounded' },
          { label: __('Pill', 'modfarm'), value: 'pill' }
        ],
        onChange: function (value) { setAttributes({ buttonCorners: value || 'square' }); }
      }),
      el(SelectControl, {
        label: __('Button color mode', 'modfarm'),
        value: attributes.buttonStyleMode || 'inherit',
        options: [
          { label: __('Inherit ModFarm Settings', 'modfarm'), value: 'inherit' },
          { label: __('Custom for this block', 'modfarm'), value: 'custom' }
        ],
        onChange: function (value) { setAttributes({ buttonStyleMode: value || 'inherit' }); }
      }),
      attributes.buttonStyleMode === 'custom' && el(Fragment, null,
        el('p', { className: 'components-base-control__label' }, __('Primary button', 'modfarm')),
        el(ColorField, { label: __('Background', 'modfarm'), value: attributes.primaryButtonBg, colors: themeColors, onChange: function (v) { setAttributes({ primaryButtonBg: v }); } }),
        el(ColorField, { label: __('Text', 'modfarm'), value: attributes.primaryButtonFg, colors: themeColors, onChange: function (v) { setAttributes({ primaryButtonFg: v }); } }),
        el(ColorField, { label: __('Border', 'modfarm'), value: attributes.primaryButtonBorder, colors: themeColors, onChange: function (v) { setAttributes({ primaryButtonBorder: v }); } }),
        el('p', { className: 'components-base-control__label' }, __('Secondary button', 'modfarm')),
        el(ColorField, { label: __('Background', 'modfarm'), value: attributes.secondaryButtonBg, colors: themeColors, onChange: function (v) { setAttributes({ secondaryButtonBg: v }); } }),
        el(ColorField, { label: __('Text', 'modfarm'), value: attributes.secondaryButtonFg, colors: themeColors, onChange: function (v) { setAttributes({ secondaryButtonFg: v }); } }),
        el(ColorField, { label: __('Border', 'modfarm'), value: attributes.secondaryButtonBorder, colors: themeColors, onChange: function (v) { setAttributes({ secondaryButtonBorder: v }); } })
      )
    );
  }

  registerBlockType('modfarm/related-products', {
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();
      const manualIds = sanitizeIds(attributes.manualIds);
      const [taxonomies, setTaxonomies] = useState([]);

      useEffect(function () {
        apiFetch({ path: '/wp/v2/taxonomies?type=mf_offer' })
          .then(function (records) {
            setTaxonomies(Object.keys(records || {}).map(function (key) {
              return records[key];
            }));
          })
          .catch(function () {
            setTaxonomies([]);
          });
      }, []);

      const taxonomyOptions = [{ label: __('Any published Offers', 'modfarm'), value: '' }].concat(
        (taxonomies || []).map(function (tax) {
          return { label: tax.name || tax.slug, value: tax.slug };
        })
      );

      function addOffer(id) {
        if (!id) return;
        setAttributes({ manualIds: sanitizeIds(manualIds.concat([id])) });
      }

      function moveOffer(from, to) {
        const next = manualIds.slice();
        const item = next.splice(from, 1)[0];
        next.splice(to, 0, item);
        setAttributes({ manualIds: next });
      }

      return el(Fragment, null,
        el(InspectorControls, null,
          el(PanelBody, { title: __('Products', 'modfarm'), initialOpen: true },
            el(OfferSearch, {
              label: __('Current Offer override', 'modfarm'),
              help: __('Optional. Leave empty to use the current Offer page context.', 'modfarm'),
              onPick: function (id) { setAttributes({ offerId: id }); }
            }),
            attributes.offerId ? el(Notice, { status: 'info', isDismissible: false }, __('Current Offer override selected: ', 'modfarm') + `#${attributes.offerId}`) : null,
            attributes.offerId ? el(Button, {
              variant: 'secondary',
              onClick: function () { setAttributes({ offerId: 0 }); }
            }, __('Use current Offer context', 'modfarm')) : null,
            el('hr', null),
            el(OfferSearch, {
              label: __('Add related Offer', 'modfarm'),
              help: __('Search and pick Offers to show manually. Drag picked Offers to reorder.', 'modfarm'),
              onPick: addOffer
            }),
            el(PickedOffers, {
              ids: manualIds,
              onRemove: function (id) { setAttributes({ manualIds: manualIds.filter(function (x) { return x !== id; }) }); },
              onClear: function () { setAttributes({ manualIds: [] }); },
              onMove: moveOffer
            }),
            el(ComboboxControl, {
              label: __('Related taxonomy', 'modfarm'),
              value: attributes.taxonomy || '',
              options: taxonomyOptions,
              onChange: function (value) { setAttributes({ taxonomy: value || '' }); }
            }),
            el(RangeControl, {
              label: __('Products to show', 'modfarm'),
              value: attributes.productsPerPage || 3,
              min: 1,
              max: 12,
              onChange: function (value) { setAttributes({ productsPerPage: value || 3 }); }
            }),
            el(RangeControl, {
              label: __('Columns', 'modfarm'),
              value: attributes.columns || 3,
              min: 1,
              max: 6,
              onChange: function (value) { setAttributes({ columns: value || 3 }); }
            })
          ),
          el(PanelBody, { title: __('Display', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Show heading', 'modfarm'),
              checked: attributes.showHeading !== false,
              onChange: function (value) { setAttributes({ showHeading: !!value }); }
            }),
            el(TextControl, {
              label: __('Heading', 'modfarm'),
              value: attributes.heading || '',
              onChange: function (value) { setAttributes({ heading: value }); }
            }),
            el(SelectControl, {
              label: __('Card layout', 'modfarm'),
              value: attributes.cardLayout || 'commerce',
              options: [
                { label: __('Commerce card', 'modfarm'), value: 'commerce' },
                { label: __('Vertical', 'modfarm'), value: 'vertical' },
                { label: __('Horizontal', 'modfarm'), value: 'horizontal' }
              ],
              onChange: function (value) { setAttributes({ cardLayout: value || 'commerce' }); }
            }),
            el(SelectControl, {
              label: __('Image aspect', 'modfarm'),
              value: attributes.imageAspect || '1 / 1',
              options: IMAGE_ASPECT_OPTIONS,
              onChange: function (value) { setAttributes({ imageAspect: value || '1 / 1' }); }
            }),
            el(ToggleControl, {
              label: __('Show title', 'modfarm'),
              checked: !!attributes.showTitle,
              onChange: function (value) { setAttributes({ showTitle: !!value }); }
            }),
            el(ToggleControl, {
              label: __('Show details', 'modfarm'),
              checked: attributes.showDetails !== false,
              onChange: function (value) { setAttributes({ showDetails: !!value }); }
            }),
            el(SelectControl, {
              label: __('Detail label source', 'modfarm'),
              value: attributes.detailMode || 'auto',
              options: [
                { label: __('Auto from each Offer', 'modfarm'), value: 'auto' },
                { label: __('Same custom label for all cards', 'modfarm'), value: 'custom' }
              ],
              onChange: function (value) { setAttributes({ detailMode: value || 'auto' }); }
            }),
            attributes.detailMode === 'custom' && el(TextControl, {
              label: __('Custom detail label', 'modfarm'),
              value: attributes.detailOverride || '',
              onChange: function (value) { setAttributes({ detailOverride: value }); }
            }),
            el(ToggleControl, {
              label: __('Show description', 'modfarm'),
              checked: !!attributes.showExcerpt,
              onChange: function (value) { setAttributes({ showExcerpt: !!value }); }
            })
          ),
          el(PanelBody, { title: __('Buttons', 'modfarm'), initialOpen: false },
            el(ButtonControls, { attributes, setAttributes })
          )
        ),
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/related-products',
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
