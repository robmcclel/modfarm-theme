/* global window */
(function (wp) {
  if (!wp) return;

  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;

  var __ = wp.i18n.__;
  var registerBlockType = wp.blocks.registerBlockType;

  var be = wp.blockEditor || wp.editor;
  if (!be) return;

  var useBlockProps = be.useBlockProps;
  var InspectorControls = be.InspectorControls;
  var InnerBlocks = be.InnerBlocks;

  // Cover-like panel (name varies by WP version)
  var PanelColorGradientSettings =
    be.PanelColorGradientSettings ||
    be.__experimentalPanelColorGradientSettings ||
    null;

  var components = wp.components;
  var PanelBody = components.PanelBody;
  var SelectControl = components.SelectControl;
  var TextControl = components.TextControl;
  var RangeControl = components.RangeControl;
  var ToggleControl = components.ToggleControl;
  var ColorPalette = components.ColorPalette;
  var Button = components.Button;
  var MediaUpload = be.MediaUpload;
  var MediaUploadCheck = be.MediaUploadCheck;

  var ServerSideRender = wp.serverSideRender || null;

  registerBlockType('modfarm/hero-cover', {
    apiVersion: 3,

    edit: function (props) {
      var a = props.attributes || {};
      var setAttributes = props.setAttributes;

      var mode = a.mode || 'auto';
      var minHeight = typeof a.minHeight === 'number' ? a.minHeight : 420;
      var dimRatio = typeof a.dimRatio === 'number' ? a.dimRatio : 20;

      var overlayColor = a.overlayColor || '#000000';
      var overlayGradient = a.overlayGradient || '';

      var blockProps = useBlockProps({
        className: 'modfarm-hero-cover modfarm-hero-cover--editor' + (a.structuredLayout ? ' modfarm-hero-cover--structured' : ''),
        style: {
          minHeight: minHeight + 'px',
          '--mf-hero-width': a.contentMaxWidth || '1200px',
          '--mf-hero-align': a.contentAlign || 'center',
          '--mf-hero-vertical': { top: 'flex-start', center: 'center', bottom: 'flex-end' }[a.contentVerticalAlign || 'center'],
          '--mf-hero-mobile-align': a.mobileContentAlign === 'inherit' ? (a.contentAlign || 'center') : a.mobileContentAlign,
          '--mf-hero-mobile-vertical': { top: 'flex-start', center: 'center', bottom: 'flex-end' }[a.mobileVerticalAlign === 'inherit' ? (a.contentVerticalAlign || 'center') : (a.mobileVerticalAlign || 'center')]
        }
      });

      // Fallback background (manual only) if SSR not present
      var fallbackBgStyle = {};
      if (!ServerSideRender && mode === 'manual' && a.manualUrl) {
        fallbackBgStyle.backgroundImage = 'url(' + a.manualUrl + ')';
        fallbackBgStyle.backgroundSize = 'cover';
        fallbackBgStyle.backgroundPosition = '50% 0%';
      }

      var edge = window.modfarmHeroControls.resolved(a);

      return el(
        Fragment,
        {},
        el(
          'div',
          blockProps,

          // Background layer
          ServerSideRender
            ? el(
                'div',
                { className: 'modfarm-hero-cover__bg', style: { pointerEvents: 'none' } },
                el(ServerSideRender, {
                  block: 'modfarm/hero-cover',
                  attributes: Object.assign({}, a, { __preview: true }),
                  urlQueryArgs: { post_id: props.context && props.context.postId || undefined }
                })
              )
            : el('div', {
                className: 'modfarm-hero-cover__bg',
                style: Object.assign({ pointerEvents: 'none' }, fallbackBgStyle)
              }),

          // SSR supplies the readability overlay once. Preview the edge above it.
          el('span', {
            className: 'mf-hero-edge mf-hero-edge--' + edge.preset,
            style: { '--mf-hero-edge-color': edge.color }, 'aria-hidden': true
          }),

          // Inner content
          el(
            'div',
            {
              className: 'modfarm-hero-cover__content modfarm-hero-cover__content--editor',
              style: { maxWidth: a.contentMaxWidth || '1200px' }
            },
            el(InnerBlocks, {
              // The variation/pattern supplies the initial areas. A live locked
              // template recursively removes author-added blocks when synchronized.
              templateLock: false
            })
          )
        ),

        el(
          InspectorControls,
          {},
          // Source
          el(
            PanelBody,
            { title: __('Source', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Image Source', 'modfarm'),
              value: mode,
              options: [
                { label: __('Automatic (local hero / site default)', 'modfarm'), value: 'auto' },
                { label: __('Selected image', 'modfarm'), value: 'manual' }
              ],
              onChange: function (v) { setAttributes({ mode: v }); }
            }),
            mode === 'manual'
              ? el(Fragment, {}, el(MediaUploadCheck, {}, el(MediaUpload, {
                  allowedTypes: ['image'],
                  onSelect: function (image) { setAttributes({ manualUrl: image.url }); },
                  render: function (upload) { return el(Button, { variant: 'secondary', onClick: upload.open }, __('Select image', 'modfarm')); }
                })), el(TextControl, {
                  label: __('Manual Image URL', 'modfarm'),
                  value: a.manualUrl || '',
                  placeholder: 'https://…',
                  onChange: function (v) { setAttributes({ manualUrl: v }); }
                }))
              : null,

            el(TextControl, {
              label: __('Book Meta Key', 'modfarm'),
              value: a.bookMetaKey || 'hero_image',
              onChange: function (v) { setAttributes({ bookMetaKey: v || 'hero_image' }); }
            }),
            el(TextControl, {
              label: __('Term Meta Key', 'modfarm'),
              value: a.termMetaKey || 'archive_hero_image',
              onChange: function (v) { setAttributes({ termMetaKey: v || 'archive_hero_image' }); }
            }),
            el(ToggleControl, {
              label: __('Use Featured Image override', 'modfarm'),
              help: __('On pages, posts, and books, a featured image overrides the local hero when enabled. Archives use their explicit hero or the site default.', 'modfarm'),
              checked: !!a.useFeaturedImage,
              onChange: function (v) { setAttributes({ useFeaturedImage: !!v, fallbackFeatured: false }); }
            }),
            a.fallbackFeatured ? el(ToggleControl, {
              label: __('Legacy featured-image fallback', 'modfarm'), checked: true,
              onChange: function () { setAttributes({ fallbackFeatured: false }); }
            }) : null
          ),

          // Layout
          el(
            PanelBody,
            { title: __('Layout', 'modfarm'), initialOpen: false },
            el(components.Notice, { status: 'info', isDismissible: false },
              a.structuredLayout ? __('Navigation stays at the top; alignment controls affect the content area.', 'modfarm') : __('Use the Hero with top navigation variation for independent navigation and content alignment. Existing inner blocks remain unchanged.', 'modfarm')
            ),
            el(RangeControl, {
              label: __('Minimum Height (px)', 'modfarm'),
              value: minHeight,
              min: 200,
              max: 1000,
              onChange: function (v) { setAttributes({ minHeight: v }); }
            }),
            el(SelectControl, {
              label: __('Content Alignment', 'modfarm'),
              value: a.contentAlign || 'center',
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: function (v) { setAttributes({ contentAlign: v }); }
            }),
            el(TextControl, {
              label: __('Content Max Width', 'modfarm'),
              value: a.contentMaxWidth || '1200px',
              onChange: function (v) { setAttributes({ contentMaxWidth: v || '1200px' }); }
            }),
            el(SelectControl, {
              label: __('Vertical content position', 'modfarm'), value: a.contentVerticalAlign || 'center',
              options: [{ label: 'Top', value: 'top' }, { label: 'Middle', value: 'center' }, { label: 'Bottom', value: 'bottom' }],
              onChange: function (v) { setAttributes({ contentVerticalAlign: v }); }
            }),
            el(SelectControl, {
              label: __('Mobile horizontal alignment', 'modfarm'), value: a.mobileContentAlign || 'inherit',
              options: [{ label: 'Use desktop setting', value: 'inherit' }, { label: 'Left', value: 'left' }, { label: 'Center', value: 'center' }, { label: 'Right', value: 'right' }],
              onChange: function (v) { setAttributes({ mobileContentAlign: v }); }
            }),
            el(SelectControl, {
              label: __('Mobile vertical position', 'modfarm'), value: a.mobileVerticalAlign || 'inherit',
              options: [{ label: 'Use desktop setting', value: 'inherit' }, { label: 'Top', value: 'top' }, { label: 'Middle', value: 'center' }, { label: 'Bottom', value: 'bottom' }],
              onChange: function (v) { setAttributes({ mobileVerticalAlign: v }); }
            })
          ),
          el(window.modfarmHeroControls.Controls, props),

          // Overlay
          el(
            PanelBody,
            { title: __('Overlay', 'modfarm'), initialOpen: false },

            el(RangeControl, {
              label: __('Dim Ratio', 'modfarm'),
              value: dimRatio,
              min: 0,
              max: 100,
              onChange: function (v) { setAttributes({ dimRatio: v }); }
            }),

            // ✅ Native gradient UI WITHOUT custom origins (match core blocks)
            PanelColorGradientSettings
              ? el(PanelColorGradientSettings, {
                  title: __('Overlay', 'modfarm'),
                  settings: [
                    {
                      label: __('Overlay color', 'modfarm'),
                      colorValue: overlayColor,
                      onColorChange: function (v) {
                        setAttributes({
                          overlayColor: v || '#000000',
                          overlayGradient: ''
                        });
                      }
                    },
                    {
                      label: __('Overlay gradient', 'modfarm'),
                      gradientValue: overlayGradient || undefined,
                      onGradientChange: function (v) {
                        setAttributes({
                          overlayGradient: v || ''
                        });
                      }
                    }
                  ]
                })
              : el(
                  Fragment,
                  {},
                  el('div', { style: { marginTop: '8px' } },
                    el('div', { style: { marginBottom: '6px', fontSize: '12px', opacity: 0.8 } },
                      __('Overlay Color (fallback)', 'modfarm')
                    ),
                    el(ColorPalette, {
                      value: overlayColor || '#000000',
                      onChange: function (v) {
                        setAttributes({ overlayColor: v || '#000000', overlayGradient: '' });
                      }
                    })
                  ),
                  el(TextControl, {
                    label: __('Overlay Gradient CSS (fallback)', 'modfarm'),
                    value: overlayGradient || '',
                    placeholder: 'linear-gradient(180deg, rgba(0,0,0,.6), rgba(0,0,0,0))',
                    onChange: function (v) {
                      setAttributes({ overlayGradient: v || '' });
                    }
                  })
                )
          )
        )
      );
    },

    save: function () {
      return el(InnerBlocks.Content);
    }
  });

  wp.blocks.registerBlockVariation('modfarm/hero-cover', {
    name: 'top-navigation', title: __('Hero with top navigation', 'modfarm'),
    description: __('Navigation at the top with independently aligned title and content.', 'modfarm'),
    attributes: { structuredLayout: true, fallbackFeatured: false },
    innerBlocks: [
      ['modfarm/hero-area', { area: 'navigation', metadata: { name: 'Hero navigation' } }, [['modfarm/navigation-menu', {}]]],
      ['modfarm/hero-area', { area: 'content', metadata: { name: 'Hero content' } }, [['core/post-title', { level: 1 }]]]
    ],
    scope: ['inserter'], isActive: ['structuredLayout']
  });

})(window.wp);
