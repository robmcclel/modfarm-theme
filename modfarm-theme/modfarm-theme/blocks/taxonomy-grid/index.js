(function (wp) {
  const el = wp.element.createElement;

  // Core
  const { registerBlockType } = wp.blocks;
  const { useMemo, useRef, useEffect } = wp.element;
  const { useSelect, useDispatch } = wp.data;

  // Editor + components
  const be = wp.blockEditor || wp.editor;
  const { InspectorControls, useBlockProps } = be;
  const {
    PanelBody,
    SelectControl,
    ToggleControl,
    RangeControl,
    TextControl,
    TreeSelect
  } = wp.components;

  // Server-side renderer (global)
  const ServerSideRender = wp.serverSideRender;

  // Helpers ---------------------------------------------------------------
  function useAllPublicTaxonomies() {
    return useSelect((select) => select('core').getTaxonomies() || [], []);
  }

  function useTermsForTaxonomy(taxonomy) {
    return useSelect((select) => {
      if (!taxonomy) return [];
      // Pull all terms (including empty) to build a friendly tree
      return select('core').getEntityRecords('taxonomy', taxonomy, {
        per_page: -1,
        hide_empty: false
      }) || [];
    }, [taxonomy]);
  }

  function buildTermTree(terms) {
    if (!Array.isArray(terms) || !terms.length) return [];
    const byParent = {};
    terms.forEach((t) => {
      const pid = t.parent || 0;
      (byParent[pid] = byParent[pid] || []).push(t);
    });
    const build = (pid) =>
      (byParent[pid] || [])
        .sort((a, b) => String(a.name).localeCompare(String(b.name)))
        .map((t) => ({
          id: t.id || t.term_id,
          name: t.name,
          children: build(t.id || t.term_id)
        }));
    return build(0);
  }

  // Block -----------------------------------------------------------------
  registerBlockType('modfarm/taxonomy-grid', {
    edit: function (props) {
      const { attributes, setAttributes, clientId } = props;

      // Make the SSR preview area properly selectable
      const blockProps = useBlockProps({ className: 'mfb-taxgrid-editor-host' });
      const hostRef = useRef(null);
      const { selectBlock } = useDispatch('core/block-editor');

      useEffect(() => {
        const host = hostRef.current;
        if (!host) return;

        // Capture early so Gutenberg selection doesn't swallow the event
        const onMouseDown = (e) => {
          // Ignore control popovers
          if (e.target.closest('.components-popover')) return;

          // Prevent anchor navigation inside editor preview
          const a = e.target.closest('a');
          if (a) e.preventDefault();

          // Force select this block so the side panel opens
          selectBlock(clientId);
        };

        host.addEventListener('mousedown', onMouseDown, true);
        return () => host.removeEventListener('mousedown', onMouseDown, true);
      }, [clientId, selectBlock]);

      // Data sources
      const taxonomies = useAllPublicTaxonomies();
      const terms = useTermsForTaxonomy(attributes.taxonomy);
      const termTree = useMemo(() => buildTermTree(terms), [terms]);

      // Options
      const taxonomyOptions = useMemo(() => {
        const allowed = ['book-series', 'book-author', 'book-genre', 'book-format', 'book-language', 'book-tags'];
        const labels = {
          'book-series': 'Book Series',
          'book-author': 'Book Authors',
          'book-genre': 'Book Genres',
          'book-format': 'Book Formats',
          'book-language': 'Book Languages',
          'book-tags': 'Book Tags'
        };
        const base = (taxonomies || [])
          .map((t) => Object.assign({}, t, { _slug: t.slug || t.name || t.rest_base }))
          .filter((t) => allowed.includes(t._slug))
          .map((t) => ({
            label: labels[t._slug] || t.labels?.singular_name || t.name,
            value: t._slug
          }));
        return allowed.map((s) => base.find((b) => b.value === s) || { label: labels[s], value: s });
      }, [taxonomies]);

      const imageSourceOptions = [
        { label: 'archive_default_image', value: 'archive_default_image' },
        { label: 'first_cover_in_series', value: 'first_cover_in_series' },
        { label: 'archive_hero_image', value: 'archive_hero_image' },
        { label: 'initials', value: 'initials' }
      ];

      const aspectOptions = [
        { label: '1 / 1', value: '1/1' },
        { label: '3 / 4', value: '3/4' },
        { label: '2 / 3', value: '2/3' },
        { label: '4 / 3', value: '4/3' },
        { label: '16 / 9', value: '16/9' },
        { label: '2 / 1', value: '2/1' },
        { label: 'Auto', value: 'auto' }
      ];

      const shapeOptions = [
        { label: 'Square', value: 'square' },
        { label: 'Rounded', value: 'rounded' },
        { label: 'Circle', value: 'circle' }
      ];

      // UI ----------------------------------------------------------------
      return el(
        wp.element.Fragment,
        {},
        // Inspector
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: 'Data', initialOpen: true },
            el(SelectControl, {
              label: 'Mode',
              value: attributes.groupMode || 'terms',
              options: [
                { label: 'Terms grid', value: 'terms' },
                { label: 'Series grouped by genre', value: 'series_by_genre' },
                { label: 'Books grouped by series', value: 'books_by_series' }
              ],
              onChange: (v) => setAttributes({ groupMode: v })
            }),
            el(SelectControl, {
              label: 'Taxonomy',
              value: attributes.taxonomy,
              options: taxonomyOptions,
              onChange: (v) => setAttributes({ taxonomy: v, parentId: 0 })
            }),
            (attributes.groupMode || 'terms') === 'terms' &&
              el(SelectControl, {
              label: 'Display',
              value: attributes.displayMode,
              options: [
                { label: 'All Terms', value: 'all' },
                { label: 'Top-level Only', value: 'top' },
                { label: 'Children of…', value: 'children' }
              ],
              onChange: (v) => setAttributes({ displayMode: v })
            }),
            (attributes.groupMode || 'terms') === 'terms' && attributes.displayMode === 'children' &&
              el(TreeSelect, {
                label: 'Parent term',
                noOptionLabel: '— Select a parent —',
                selectedId: attributes.parentId || 0,
                tree: termTree,
                onChange: (id) => setAttributes({ parentId: parseInt(id, 10) || 0 })
              }),
            (attributes.groupMode || 'terms') === 'terms' &&
              el(ToggleControl, {
                label: 'Hide Parent Terms (leaf only)',
                checked: !!attributes.hideParents,
                onChange: (v) => setAttributes({ hideParents: !!v })
              }),
            attributes.taxonomy === 'book-series' &&
              el(TextControl, {
                label: 'Series primary genre slug',
                help: 'Optional. Example: fantasy or litrpg.',
                value: attributes.seriesGenreSlug || '',
                onChange: (v) => setAttributes({ seriesGenreSlug: v })
              }),
            el(ToggleControl, {
              label: 'Hide Empty',
              checked: !!attributes.hideEmpty,
              onChange: (v) => setAttributes({ hideEmpty: !!v })
            }),
            el(ToggleControl, {
              label: 'Show Counts',
              checked: !!attributes.showCounts,
              onChange: (v) => setAttributes({ showCounts: !!v })
            })
          ),

          el(
            PanelBody,
            { title: 'Layout', initialOpen: false },
            el(RangeControl, {
              label: 'Columns',
              min: 2,
              max: 4,
              value: attributes.columns,
              onChange: (v) => setAttributes({ columns: v })
            }),
            el(RangeControl, {
              label: 'Items per Page',
              min: 4,
              max: 96,
              value: attributes.perPage,
              onChange: (v) => setAttributes({ perPage: v })
            }),
            el(ToggleControl, {
              label: 'Enable Pagination',
              checked: !!attributes.enablePagination,
              onChange: (v) => setAttributes({ enablePagination: !!v })
            }),
            el(SelectControl, {
              label: 'Aspect Ratio',
              value: attributes.aspectRatioOpt,
              options: aspectOptions,
              onChange: (v) => setAttributes({ aspectRatioOpt: v })
            }),
            el(SelectControl, {
              label: 'Shape',
              value: attributes.shape,
              options: shapeOptions,
              onChange: (v) => setAttributes({ shape: v })
            }),
            el(RangeControl, {
              label: 'Gutter (px)',
              min: 0,
              max: 40,
              value: attributes.gutter,
              onChange: (v) => setAttributes({ gutter: v })
            })
          ),

          el(
            PanelBody,
            { title: 'Sorting & TOC', initialOpen: false },
            el(SelectControl, {
              label: 'Order By',
              value: attributes.orderBy,
              options: [
                { label: 'Name (A→Z)', value: 'name_asc' },
                { label: 'Name (Z→A)', value: 'name_desc' },
                { label: 'Count (High→Low)', value: 'count_desc' }
              ],
              onChange: (v) => setAttributes({ orderBy: v })
            }),
            el(ToggleControl, {
              label: 'Show A–Z TOC (from all terms)',
              checked: !!attributes.showTOC,
              onChange: (v) => setAttributes({ showTOC: !!v })
            })
          ),

          el(
            PanelBody,
            { title: 'Image Sources', initialOpen: false },
            el(SelectControl, {
              label: 'Primary',
              value: attributes.primaryImageSource,
              options: imageSourceOptions,
              onChange: (v) => setAttributes({ primaryImageSource: v })
            }),
            el(SelectControl, {
              label: 'Fallback',
              value: attributes.fallbackImageSource,
              options: imageSourceOptions,
              onChange: (v) => setAttributes({ fallbackImageSource: v })
            })
          )
        ),

        // Clickable SSR preview (selects block on click)
        el(
          'div',
          Object.assign({}, blockProps, { ref: hostRef }),
          el(ServerSideRender, { block: 'modfarm/taxonomy-grid', attributes })
        )
      );
    },
    save: function () {
      return null;
    }
  });
})(window.wp);
