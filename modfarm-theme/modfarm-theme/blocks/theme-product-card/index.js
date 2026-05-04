(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { Fragment, createElement: el, useState, useEffect } = wp.element;
  const { __ } = wp.i18n;
  const { useBlockProps, InspectorControls, RichText } = wp.blockEditor || {};
  const { PanelBody, TextControl, SelectControl, ToggleControl, RangeControl, Button, Notice, ColorPalette, BaseControl } = wp.components || {};
  const ServerSideRender = wp.serverSideRender;
  const apiFetch = wp.apiFetch;
  const useSelect = wp.data && wp.data.useSelect;

  const IMAGE_ASPECT_OPTIONS = [
    { label: __('Square', 'modfarm'), value: '1 / 1' },
    { label: __('Portrait', 'modfarm'), value: '3 / 4' },
    { label: __('Landscape', 'modfarm'), value: '16 / 9' }
  ];

  function OfferSearch({ value, onPick }) {
    const [query, setQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [results, setResults] = useState([]);
    const [selectedTitle, setSelectedTitle] = useState('');

    useEffect(function () {
      if (!value) {
        setSelectedTitle('');
        return;
      }
      apiFetch({ path: `/wp/v2/search?subtype=mf_offer&include=${value}&per_page=1` })
        .then(function (items) {
          setSelectedTitle(items && items[0] ? items[0].title : `#${value}`);
        })
        .catch(function () {
          setSelectedTitle(`#${value}`);
        });
    }, [value]);

    useEffect(function () {
      const term = (query || '').trim();
      if (term.length < 3) {
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
    }, [query]);

    return el(Fragment, null,
      selectedTitle ? el(Notice, { status: 'info', isDismissible: false }, __('Selected: ', 'modfarm') + selectedTitle) : null,
      el(TextControl, {
        label: __('Find Offer by Title', 'modfarm'),
        help: __('Type 3+ characters to search Store Offers.', 'modfarm'),
        value: query,
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
            onClick: function () {
              onPick(result.id);
              setQuery('');
              setResults([]);
            },
            style: { display: 'block', marginBottom: '6px', textAlign: 'left' }
          }, `#${result.id} - ${result.title}`);
        })
      ) : null,
      value ? el(Button, {
        variant: 'secondary',
        isDestructive: true,
        onClick: function () {
          onPick(0);
        }
      }, __('Use current Offer context', 'modfarm')) : null
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
        onChange: function (value) {
          setAttributes({ showPrimaryButton: !!value });
        }
      }),
      attributes.showPrimaryButton !== false && el(TextControl, {
        label: __('Primary label', 'modfarm'),
        value: attributes.primaryButtonLabel || '',
        onChange: function (value) {
          setAttributes({ primaryButtonLabel: value });
        }
      }),
      el(ToggleControl, {
        label: __('Show secondary button', 'modfarm'),
        checked: attributes.showSecondaryButton !== false,
        onChange: function (value) {
          setAttributes({ showSecondaryButton: !!value });
        }
      }),
      attributes.showSecondaryButton !== false && el(Fragment, null,
        el(TextControl, {
          label: __('Secondary label', 'modfarm'),
          value: attributes.secondaryButtonLabel || '',
          onChange: function (value) {
            setAttributes({ secondaryButtonLabel: value });
          }
        }),
        el(SelectControl, {
          label: __('Secondary destination', 'modfarm'),
          value: attributes.secondaryButtonLink || 'permalink',
          options: [
            { label: __('Offer page', 'modfarm'), value: 'permalink' },
            { label: __('Checkout', 'modfarm'), value: 'checkout' }
          ],
          onChange: function (value) {
            setAttributes({ secondaryButtonLink: value || 'permalink' });
          }
        })
      ),
      el(SelectControl, {
        label: __('Button layout', 'modfarm'),
        value: attributes.buttonLayout || 'joined',
        options: [
          { label: __('Joined', 'modfarm'), value: 'joined' },
          { label: __('Gap', 'modfarm'), value: 'gap' }
        ],
        onChange: function (value) {
          setAttributes({ buttonLayout: value || 'joined' });
        }
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
        onChange: function (value) {
          setAttributes({ buttonCorners: value || 'square' });
        }
      }),
      el(SelectControl, {
        label: __('Button color mode', 'modfarm'),
        value: attributes.buttonStyleMode || 'inherit',
        options: [
          { label: __('Inherit ModFarm Settings', 'modfarm'), value: 'inherit' },
          { label: __('Custom for this block', 'modfarm'), value: 'custom' }
        ],
        onChange: function (value) {
          setAttributes({ buttonStyleMode: value || 'inherit' });
        }
      }),
      attributes.buttonStyleMode === 'custom' && el(Fragment, null,
        el('p', { className: 'components-base-control__label' }, __('Primary button', 'modfarm')),
        el(ColorField, { label: __('Background', 'modfarm'), value: attributes.primaryButtonBg, colors: themeColors, onChange: function (v) { setAttributes({ primaryButtonBg: v }); } }),
        el(ColorField, { label: __('Text', 'modfarm'), value: attributes.primaryButtonFg, colors: themeColors, onChange: function (v) { setAttributes({ primaryButtonFg: v }); } }),
        el(ColorField, { label: __('Border', 'modfarm'), value: attributes.primaryButtonBorder, colors: themeColors, onChange: function (v) { setAttributes({ primaryButtonBorder: v }); } }),
        el('p', { className: 'components-base-control__label' }, __('Secondary button', 'modfarm')),
        el(ColorField, { label: __('Background', 'modfarm'), value: attributes.secondaryButtonBg, colors: themeColors, onChange: function (v) { setAttributes({ secondaryButtonBg: v }); } }),
        el(ColorField, { label: __('Text', 'modfarm'), value: attributes.secondaryButtonFg, colors: themeColors, onChange: function (v) { setAttributes({ secondaryButtonFg: v }); } }),
        el(ColorField, { label: __('Border', 'modfarm'), value: attributes.secondaryButtonBorder, colors: themeColors, onChange: function (v) { setAttributes({ secondaryButtonBorder: v }); } }),
        el(Button, {
          variant: 'secondary',
          onClick: function () {
            setAttributes({
              primaryButtonBg: '',
              primaryButtonFg: '',
              primaryButtonBorder: '',
              secondaryButtonBg: '',
              secondaryButtonFg: '',
              secondaryButtonBorder: ''
            });
          }
        }, __('Clear color overrides', 'modfarm'))
      )
    );
  }

  registerBlockType('modfarm/theme-product-card', {
    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      return el(Fragment, null,
        el(InspectorControls, null,
          el(PanelBody, { title: __('Product', 'modfarm'), initialOpen: true },
            el(OfferSearch, {
              value: attributes.offerId || 0,
              onPick: function (id) {
                setAttributes({ offerId: id });
              }
            })
          ),
          el(PanelBody, { title: __('Card Layout', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Layout', 'modfarm'),
              value: attributes.layout || 'commerce',
              options: [
                { label: __('Commerce card', 'modfarm'), value: 'commerce' },
                { label: __('Vertical', 'modfarm'), value: 'vertical' },
                { label: __('Horizontal', 'modfarm'), value: 'horizontal' }
              ],
              onChange: function (value) {
                setAttributes({ layout: value || 'commerce' });
              }
            }),
            el(SelectControl, {
              label: __('Image aspect', 'modfarm'),
              value: attributes.imageAspect || '1 / 1',
              options: IMAGE_ASPECT_OPTIONS,
              onChange: function (value) {
                setAttributes({ imageAspect: value || '1 / 1' });
              }
            })
          ),
          el(PanelBody, { title: __('Content', 'modfarm'), initialOpen: false },
            ['showImage', 'showTitle', 'showPrice', 'showDetails', 'showExcerpt'].map(function (key) {
              const labels = {
                showImage: __('Show image', 'modfarm'),
                showTitle: __('Show title', 'modfarm'),
                showPrice: __('Show price', 'modfarm'),
                showDetails: __('Show detail label', 'modfarm'),
                showExcerpt: __('Show description', 'modfarm')
              };
              const defaultOn = !['showTitle', 'showExcerpt'].includes(key);
              return el(ToggleControl, {
                key,
                label: labels[key],
                checked: attributes[key] !== false && (defaultOn || !!attributes[key]),
                onChange: function (value) {
                  setAttributes({ [key]: !!value });
                }
              });
            }),
            el(SelectControl, {
              label: __('Detail label source', 'modfarm'),
              value: attributes.detailMode || 'auto',
              options: [
                { label: __('Auto from Offer', 'modfarm'), value: 'auto' },
                { label: __('Custom', 'modfarm'), value: 'custom' }
              ],
              onChange: function (value) {
                setAttributes({ detailMode: value || 'auto' });
              }
            }),
            attributes.detailMode === 'custom' && el(TextControl, {
              label: __('Custom detail label', 'modfarm'),
              value: attributes.detailOverride || '',
              onChange: function (value) {
                setAttributes({ detailOverride: value });
              }
            }),
            el(SelectControl, {
              label: __('Description source', 'modfarm'),
              value: attributes.descriptionMode || 'auto',
              options: [
                { label: __('Auto from Offer', 'modfarm'), value: 'auto' },
                { label: __('Custom', 'modfarm'), value: 'custom' }
              ],
              onChange: function (value) {
                setAttributes({ descriptionMode: value || 'auto' });
              }
            }),
            attributes.descriptionMode === 'custom' && el(RichText, {
              tagName: 'div',
              value: attributes.descriptionOverride || '',
              onChange: function (value) {
                setAttributes({ descriptionOverride: value });
              },
              placeholder: __('Custom product-card description...', 'modfarm'),
              allowedFormats: ['core/bold', 'core/italic', 'core/link']
            }),
            el(RangeControl, {
              label: __('Auto description words', 'modfarm'),
              value: attributes.excerptWords || 24,
              min: 8,
              max: 60,
              onChange: function (value) {
                setAttributes({ excerptWords: value || 24 });
              }
            })
          ),
          el(PanelBody, { title: __('Buttons', 'modfarm'), initialOpen: false },
            el(ButtonControls, { attributes, setAttributes })
          )
        ),
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/theme-product-card',
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
