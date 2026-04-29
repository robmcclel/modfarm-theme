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

  var ServerSideRender = wp.serverSideRender || null;

  function clampInt(n, min, max, fallback) {
    var v = parseInt(n, 10);
    if (isNaN(v)) v = fallback;
    return Math.max(min, Math.min(max, v));
  }

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

      var hasGradient =
        typeof overlayGradient === 'string' && overlayGradient.trim().length > 0;

      var blockProps = useBlockProps({
        className: 'modfarm-hero-cover modfarm-hero-cover--editor',
        style: { minHeight: minHeight + 'px' }
      });

      // Fallback background (manual only) if SSR not present
      var fallbackBgStyle = {};
      if (!ServerSideRender && mode === 'manual' && a.manualUrl) {
        fallbackBgStyle.backgroundImage = 'url(' + a.manualUrl + ')';
        fallbackBgStyle.backgroundSize = 'cover';
        fallbackBgStyle.backgroundPosition = '50% 50%';
      }

      // Editor-only overlay preview so changes show immediately
      var editorOverlayBg = hasGradient ? overlayGradient : overlayColor;
      var editorOverlayOpacity = clampInt(dimRatio, 0, 100, 20) / 100;

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
                  attributes: Object.assign({}, a, { __preview: true })
                })
              )
            : el('div', {
                className: 'modfarm-hero-cover__bg',
                style: Object.assign({ pointerEvents: 'none' }, fallbackBgStyle)
              }),

          // Overlay layer preview (editor only)
          el('div', {
            className: 'modfarm-hero-cover__overlay modfarm-hero-cover__overlay--editor',
            style: { background: editorOverlayBg, opacity: editorOverlayOpacity, pointerEvents: 'none' }
          }),

          // Inner content
          el(
            'div',
            {
              className: 'modfarm-hero-cover__content modfarm-hero-cover__content--editor',
              style: {
                maxWidth: a.contentMaxWidth || '1200px',
                textAlign: a.contentAlign || 'center'
              }
            },
            el(InnerBlocks, { templateLock: false })
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
                { label: __('Auto (Book / Archive)', 'modfarm'), value: 'auto' },
                { label: __('Manual URL', 'modfarm'), value: 'manual' }
              ],
              onChange: function (v) { setAttributes({ mode: v }); }
            }),
            mode === 'manual'
              ? el(TextControl, {
                  label: __('Manual Image URL', 'modfarm'),
                  value: a.manualUrl || '',
                  placeholder: 'https://…',
                  onChange: function (v) { setAttributes({ manualUrl: v }); }
                })
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
              label: __('Fallback to Featured Image (books)', 'modfarm'),
              checked: !!a.fallbackFeatured,
              onChange: function (v) { setAttributes({ fallbackFeatured: !!v }); }
            })
          ),

          // Layout
          el(
            PanelBody,
            { title: __('Layout', 'modfarm'), initialOpen: false },
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
            })
          ),

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

})(window.wp);