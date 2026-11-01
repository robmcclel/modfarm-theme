(function (wp) {
  const { __ } = wp.i18n;
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, InspectorControls } = wp.blockEditor || wp.editor;
  const {
    PanelBody,
    SelectControl,
    ComboboxControl,
    RangeControl,
    ToggleControl,
    ColorPalette,
    Button
  } = wp.components;
  const { Fragment, createElement: el, useEffect, useState } = wp.element;
  const ServerSideRender = wp.serverSideRender;

  const THEME_COLORS = wp.data.select('core/block-editor')?.getSettings()?.colors || [];

  registerBlockType('modfarm/author-social-links', {
    apiVersion: 2,
    title: 'Author Social Links',
    icon: 'share',
    category: 'modfarm-theme',

    edit: function (props) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();
      const identitySource = attributes.source === 'identity';
      const [identities, setIdentities] = useState([]);
      const [identityError, setIdentityError] = useState(false);
      useEffect(() => {
        if (!identitySource) return;
        let active = true;
        setIdentityError(false);
        wp.apiFetch({path: '/modfarm/v1/cir/entities?provider=modfarm-core'})
          .then(rows => { if (active) setIdentities(Array.isArray(rows) ? rows : []); })
          .catch(() => { if (active) setIdentityError(true); });
        return () => { active = false; };
      }, [identitySource]);
      const identityOptions = [
        {label: __('Site primary identity', 'modfarm'), value: ''},
        ...identities.map(row => ({label: row.label, value: row.reference.provider_id}))
      ];
      if (attributes.identityId && !identityOptions.some(option => option.value === attributes.identityId)) {
        identityOptions.push({label: __('Saved identity (unavailable)', 'modfarm'), value: attributes.identityId});
      }

      const authorId = parseInt(attributes.authorId, 10) || 0;

      const [authors, setAuthors] = useState([]);
      const [authorSearch, setAuthorSearch] = useState('');
      const [isSearchingAuthors, setIsSearchingAuthors] = useState(false);

      useEffect(() => {
        if (identitySource) return;
        let active = true;
        const search = authorSearch.trim();
        const searchArg = search ? `&search=${encodeURIComponent(search)}` : '';

        setIsSearchingAuthors(true);
        const timer = setTimeout(() => {
          const requests = [wp.apiFetch({
            path: `/wp/v2/book-author?per_page=20&orderby=name&order=asc&_fields=id,name${searchArg}`
          })];

          // Keep the saved author visible even when it is outside the current search page.
          if (authorId) {
            requests.push(wp.apiFetch({
              path: `/wp/v2/book-author?include=${authorId}&_fields=id,name`
            }));
          }

          Promise.all(requests)
            .then(results => {
              if (!active) return;
              const byId = new Map();
              results.forEach(terms => {
                (Array.isArray(terms) ? terms : []).forEach(term => byId.set(term.id, term));
              });
              setAuthors(Array.from(byId.values()));
            })
            .catch(() => {
              if (active) setAuthors([]);
            })
            .finally(() => {
              if (active) setIsSearchingAuthors(false);
            });
        }, 250);

        return () => {
          active = false;
          clearTimeout(timer);
        };
      }, [authorSearch, authorId, identitySource]);

      const authorOptions = [
        { label: __('Current author archive', 'modfarm'), value: '0' }
      ].concat((authors || []).map((term) => ({
        label: term.name,
        value: String(term.id)
      })));

      const clearMonoColor = () => setAttributes({ monotoneColor: '' });

      return el(
        Fragment,
        {},
        el('div', blockProps,
          el(ServerSideRender, {
            block: 'modfarm/author-social-links',
            attributes: Object.assign({}, attributes, { authorId })
          })
        ),
        el(InspectorControls, {},
          el(PanelBody, { title: __('Source', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Content source', 'modfarm'),
              value: attributes.source || 'author',
              options: [{label: __('Author taxonomy', 'modfarm'), value: 'author'}, {label: __('Site Identity', 'modfarm'), value: 'identity'}],
              onChange: source => setAttributes({source})
            }),
            identitySource && el(SelectControl, {
              label: __('Identity', 'modfarm'),
              value: attributes.identityId || '',
              options: identityOptions,
              help: identityError ? __('Site Identity could not be loaded. Check that ModFarm Core is available.', 'modfarm') : __('Choose a person or organization by name, or follow the site primary identity.', 'modfarm'),
              onChange: identityId => setAttributes({identityId})
            }),
            !identitySource && el(ComboboxControl, {
              label: __('Author', 'modfarm'),
              help: __('Search all authors, or use the current author archive.', 'modfarm'),
              value: String(authorId),
              options: authorOptions,
              onFilterValueChange: value => setAuthorSearch(value || ''),
              onChange: value => setAttributes({ authorId: parseInt(value, 10) || 0 }),
              isLoading: isSearchingAuthors
            }),
            !identitySource && el(ToggleControl, {
              label: __('Use current author archive when no author is selected', 'modfarm'),
              checked: attributes.useArchiveAuthor !== false,
              onChange: (value) => setAttributes({ useArchiveAuthor: !!value })
            })
          ),

          el(PanelBody, { title: __('Presentation', 'modfarm'), initialOpen: true },
            el(SelectControl, {
              label: __('Alignment', 'modfarm'),
              value: attributes.align || 'left',
              options: [
                { label: __('Left', 'modfarm'), value: 'left' },
                { label: __('Center', 'modfarm'), value: 'center' },
                { label: __('Right', 'modfarm'), value: 'right' }
              ],
              onChange: (value) => setAttributes({ align: value })
            }),
            el(RangeControl, {
              label: __('Icon size (px)', 'modfarm'),
              value: parseInt(attributes.iconSize, 10) || 36,
              min: 16,
              max: 96,
              step: 2,
              onChange: (value) => setAttributes({ iconSize: parseInt(value, 10) || 36 })
            }),
            el(RangeControl, {
              label: __('Icon gap (px)', 'modfarm'),
              value: parseInt(attributes.gap, 10) || 14,
              min: 0,
              max: 48,
              step: 1,
              onChange: (value) => setAttributes({ gap: parseInt(value, 10) || 0 })
            }),
            el(SelectControl, {
              label: __('Color mode', 'modfarm'),
              value: attributes.colorMode || 'native',
              options: [
                { label: __('Native colors', 'modfarm'), value: 'native' },
                { label: __('Monotone', 'modfarm'), value: 'monotone' }
              ],
              onChange: (value) => setAttributes({ colorMode: value })
            }),
            (attributes.colorMode || 'native') === 'monotone' && el('div', { className: 'mfas-color-control' },
              el('label', { className: 'components-base-control__label' }, __('Icon color', 'modfarm')),
              el(ColorPalette, {
                colors: THEME_COLORS,
                value: attributes.monotoneColor || undefined,
                onChange: (value) => setAttributes({ monotoneColor: value || '' })
              }),
              el(Button, {
                variant: 'secondary',
                onClick: clearMonoColor
              }, __('Use inherited color', 'modfarm'))
            )
          ),

          el(PanelBody, { title: __('Link Behavior', 'modfarm'), initialOpen: false },
            el(ToggleControl, {
              label: __('Open links in new tab', 'modfarm'),
              checked: attributes.openInNewTab !== false,
              onChange: (value) => setAttributes({ openInNewTab: !!value })
            }),
            el(ToggleControl, {
              label: __('Hide when no links are available', 'modfarm'),
              checked: !!attributes.hideIfEmpty,
              onChange: (value) => setAttributes({ hideIfEmpty: !!value })
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
