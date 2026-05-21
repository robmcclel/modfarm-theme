(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const {
    PanelBody, SelectControl, TextControl, RangeControl,
    ToggleControl, ColorPalette, Button, Spinner, Notice
  } = wp.components;
  const { Fragment, createElement: el, useEffect, useState, useMemo } = wp.element;
  const { select, subscribe } = wp.data;
  const ServerSideRender = wp.serverSideRender;

  // Helper: Build taxonomy list
  function makeTaxOptions(taxonomies) {
    const opts = [];
    (taxonomies || []).forEach(t => {
      const types = Array.isArray(t.types) ? t.types : [];
      if (types.includes('book')) {
        const label = (t.labels && (t.labels.singular_name || t.labels.name)) || t.name || t.slug;
        opts.push({ label, value: t.slug });
      }
    });
    opts.sort((a, b) => {
      if (a.value === 'book-author') return -1;
      if (b.value === 'book-author') return 1;
      return a.label.localeCompare(b.label);
    });
    opts.push({ label: '— Custom (enter below) —', value: '__custom__' });
    return opts;
  }

  // Theme color palette
  const THEME_COLORS = wp.data.select('core/block-editor')?.getSettings()?.colors || [];

  // Block registration
  registerBlockType('modfarm/creator-credit', {
    apiVersion: 2,
    title: 'Creator Credit',
    icon: 'groups',
    category: 'modfarm-theme',

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      /* ----------------------------- */
      /* Dynamic taxonomy list         */
      /* ----------------------------- */
      const [taxOpts, setTaxOpts] = useState(null);
      useEffect(() => {
        const refresh = () => {
          const list = select('core').getTaxonomies();
          if (list) setTaxOpts(makeTaxOptions(list));
        };
        refresh();
        const unsub = subscribe(refresh);
        return () => { if (unsub) unsub(); };
      }, []);

      /* ----------------------------- */
      /* Attached terms for this post  */
      /* ----------------------------- */
      const [attachedTerms, setAttachedTerms] = useState([]);
      const [allTerms, setAllTerms] = useState(null);
      useEffect(() => {
        if (!attributes.taxonomy || attributes.taxonomy === '__custom__') {
          setAttachedTerms([]);
          return;
        }
        const postId = select('core/editor').getCurrentPostId?.();
        if (!postId) return;

        wp.apiFetch({
          path: `/wp/v2/${attributes.taxonomy}?post=${postId}&_fields=id,name`,
        })
          .then(terms => setAttachedTerms(Array.isArray(terms) ? terms : []))
          .catch(() => setAttachedTerms([]));
      }, [attributes.taxonomy]);

      /* ----------------------------- */
      /* Derived values + helpers      */
      /* ----------------------------- */
      const effectiveTax = useMemo(() => {
        return attributes.taxonomy === '__custom__'
          ? (attributes.customTax || '').trim()
          : (attributes.taxonomy || '').trim();
      }, [attributes.taxonomy, attributes.customTax]);

      useEffect(() => {
        if (!effectiveTax) {
          setAllTerms([]);
          return;
        }

        setAllTerms(null);
        wp.apiFetch({
          path: `/wp/v2/${effectiveTax}?per_page=100&orderby=name&order=asc&_fields=id,name`,
        })
          .then(terms => setAllTerms(Array.isArray(terms) ? terms : []))
          .catch(() => setAllTerms([]));
      }, [effectiveTax]);

      const clearAccent = () => setAttributes({ accentColor: '' });
      const clearText = () => setAttributes({ textColor: '' });
      const clearSocialColor = () => setAttributes({ socialMonotoneColor: '' });

      /* ----------------------------- */
      /* Render                        */
      /* ----------------------------- */
      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/creator-credit',
            attributes: attributes
          })
        ),
        el(InspectorControls, {},
          /* === Content Panel === */
          el(PanelBody, { title: __('Content', 'modfarm'), initialOpen: true },
            el(TextControl, {
              label: __('Heading (optional)', 'modfarm'),
              value: attributes.heading || '',
              onChange: v => setAttributes({ heading: v })
            }),
            taxOpts === null
              ? el('div', { style: { display: 'flex', alignItems: 'center', gap: '8px' } },
                  el(Spinner, {}),
                  el('span', {}, __('Loading taxonomies…', 'modfarm'))
                )
              : el(SelectControl, {
                  label: __('Taxonomy', 'modfarm'),
                  value: attributes.taxonomy,
                  options: taxOpts.length
                    ? taxOpts
                    : [{ label: __('(No book taxonomies found)', 'modfarm'), value: '' }],
                  onChange: v => setAttributes({ taxonomy: v, termId: 0 })
                }),

            attributes.taxonomy === '__custom__' && el(TextControl, {
              label: __('Custom taxonomy slug', 'modfarm'),
              placeholder: 'e.g. artist',
              value: attributes.customTax || '',
              onChange: v => setAttributes({ customTax: v })
            }),

            allTerms === null
              ? el('div', { style: { display: 'flex', alignItems: 'center', gap: '8px' } },
                  el(Spinner, {}),
                  el('span', {}, __('Loading terms...', 'modfarm'))
                )
              : el(SelectControl, {
                  label: __('Creator term', 'modfarm'),
                  help: __('Use Auto on book pages; choose a term for about pages, home pages, or posts.', 'modfarm'),
                  value: parseInt(attributes.termId, 10) || 0,
                  options: [
                    { label: __('Auto: first term attached to current post', 'modfarm'), value: 0 },
                    ...(allTerms || []).map(t => ({ label: t.name, value: t.id }))
                  ],
                  onChange: v => setAttributes({ termId: parseInt(v, 10) })
                }),

            attachedTerms.length > 1 && parseInt(attributes.termId, 10) === 0 && el(Notice, { status: 'info', isDismissible: false },
              __('Auto mode will show the first attached term. Pick a creator term above for a specific person.', 'modfarm')
            ),

            !effectiveTax && el(Notice, { status: 'warning', isDismissible: false },
              __('Choose a taxonomy or enter a custom slug.', 'modfarm')
            )
          ),

          /* === Layout & Display Panel === */
          el(PanelBody, { title: __('Layout & Display', 'modfarm'), initialOpen: false },
            el(SelectControl, {
              label: __('Layout', 'modfarm'),
              value: attributes.layout,
              options: [
                { label: __('Auto', 'modfarm'), value: 'auto' },
                { label: __('Vertical', 'modfarm'), value: 'vertical' },
                { label: __('Horizontal', 'modfarm'), value: 'horizontal' }
              ],
              onChange: v => setAttributes({ layout: v })
            }),
            el(SelectControl, {
              label: __('Image Shape', 'modfarm'),
              value: attributes.imageShape,
              options: [
                { label: __('Circle', 'modfarm'), value: 'circle' },
                { label: __('Square', 'modfarm'), value: 'square' }
              ],
              onChange: v => setAttributes({ imageShape: v })
            }),
            el(RangeControl, {
              label: __('Image size (px)', 'modfarm'),
              value: attributes.imgSize,
              min: 120, max: 360, step: 10,
              onChange: v => setAttributes({ imgSize: v })
            }),
            el(ToggleControl, {
              label: __('Link name to archive', 'modfarm'),
              checked: !!attributes.linkToArchive,
              onChange: v => setAttributes({ linkToArchive: !!v })
            }),
            el(ToggleControl, {
              label: __('Show description (full bio)', 'modfarm'),
              checked: !!attributes.showDescription,
              onChange: v => setAttributes({ showDescription: !!v })
            }),
            el(ToggleControl, {
              label: __('Show author social links', 'modfarm'),
              checked: !!attributes.showSocialLinks,
              onChange: v => setAttributes({ showSocialLinks: !!v })
            })
          ),

          attributes.showSocialLinks && el(PanelBody, { title: __('Social Links', 'modfarm'), initialOpen: false },
            el(RangeControl, {
              label: __('Icon size (px)', 'modfarm'),
              value: parseInt(attributes.socialIconSize, 10) || 28,
              min: 16, max: 96, step: 2,
              onChange: v => setAttributes({ socialIconSize: parseInt(v, 10) || 28 })
            }),
            el(RangeControl, {
              label: __('Icon gap (px)', 'modfarm'),
              value: parseInt(attributes.socialGap, 10) || 10,
              min: 0, max: 48, step: 1,
              onChange: v => setAttributes({ socialGap: parseInt(v, 10) || 0 })
            }),
            el(SelectControl, {
              label: __('Color mode', 'modfarm'),
              value: attributes.socialColorMode || 'native',
              options: [
                { label: __('Native colors', 'modfarm'), value: 'native' },
                { label: __('Monotone', 'modfarm'), value: 'monotone' }
              ],
              onChange: v => setAttributes({ socialColorMode: v })
            }),
            (attributes.socialColorMode || 'native') === 'monotone' && el('div', { style: { marginTop: '14px' } },
              el('label', { style: { display: 'block', marginBottom: '6px' } }, __('Icon color', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.socialMonotoneColor || undefined,
                onChange: v => setAttributes({ socialMonotoneColor: v || '' })
              }),
              el(Button, { isSecondary: true, onClick: clearSocialColor, style: { marginTop: '6px' } },
                __('Use inherited color', 'modfarm'))
            ),
            el(ToggleControl, {
              label: __('Open links in new tab', 'modfarm'),
              checked: attributes.socialOpenInNewTab !== false,
              onChange: v => setAttributes({ socialOpenInNewTab: !!v })
            })
          ),

          /* === Colors Panel === */
          el(PanelBody, { title: __('Colors', 'modfarm'), initialOpen: false },
            el('div', {},
              el('label', { style: { display: 'block', marginBottom: '6px' } }, __('Accent color (role)', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.accentColor || undefined,
                onChange: v => setAttributes({ accentColor: v || '' })
              }),
              el(Button, { isSecondary: true, onClick: clearAccent, style: { marginTop: '6px' } },
                __('Use theme default', 'modfarm'))
            ),
            el('div', { style: { marginTop: '14px' } },
              el('label', { style: { display: 'block', marginBottom: '6px' } }, __('Text color', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.textColor || undefined,
                onChange: v => setAttributes({ textColor: v || '' })
              }),
              el(Button, { isSecondary: true, onClick: clearText, style: { marginTop: '6px' } },
                __('Use theme default', 'modfarm'))
            )
          )
        )
      );
    }
  });
})(window.wp);
