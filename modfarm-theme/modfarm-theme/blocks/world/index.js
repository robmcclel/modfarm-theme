(function (wp) {
    'use strict';
    const {createElement: el, Fragment, useState, useEffect, useRef} = wp.element;
    const {InspectorControls, useBlockProps} = wp.blockEditor;
    const {PanelBody, SelectControl, TextControl, ColorPalette, BaseControl, Notice} = wp.components;
    const ServerSideRender = wp.serverSideRender;
    const icon = el('svg', {viewBox: '0 0 24 24', width: 24, height: 24}, el('path', {fill: 'none', stroke: 'currentColor', strokeWidth: 1.5, d: 'M3 3h18v18H3zM3 8h18M9 3v5M15 3v5M6 12h12M6 16h5M14 16h4'}));
    wp.blocks.registerBlockType('modfarm/world', {
        title: 'World', category: 'modfarm-theme', icon,
        description: 'Display a Series world using its World Bible publishing and presentation controls.',
        edit({attributes, setAttributes}) {
            const previewRef = useRef(null);
            useEffect(() => {
                const root = previewRef.current; if (!root) return;
                const init = () => window.ModFarmWorldInit?.(root);
                const observer = new MutationObserver(init); observer.observe(root, {childList: true, subtree: true}); init();
                return () => observer.disconnect();
            }, []);
            const [search, setSearch] = useState(''); const [choices, setChoices] = useState([]); const [selected, setSelected] = useState(null); const [error, setError] = useState('');
            const context = wp.data.useSelect(select => { const editor = select('core/editor'); return {id: editor?.getCurrentPostId?.(), type: editor?.getCurrentPostType?.()}; }, []);
            useEffect(() => {
                let current = true;
                const timer = setTimeout(() => { wp.apiFetch({path: '/modfarm/v1/world-series?search=' + encodeURIComponent(search)}).then(results => { if (current) { setChoices(results); setError(''); } }).catch(() => { if (current) setError('Series choices are unavailable. Deploy the matching Core update and try again.'); }); }, 250);
                return () => { current = false; clearTimeout(timer); };
            }, [search]);
            useEffect(() => {
                let current = true; setSelected(null);
                if (attributes.seriesId) wp.apiFetch({path: '/modfarm/v1/world-series?id=' + attributes.seriesId}).then(results => { if (current) setSelected(results[0] || null); }).catch(() => {});
                return () => { current = false; };
            }, [attributes.seriesId]);
            const options = [{value: '0', label: 'Choose a Series…'}, ...choices.map(series => ({value: String(series.id), label: series.name}))];
            if (selected && !choices.some(series => series.id === selected.id)) options.push({value: String(selected.id), label: selected.name});
            const manual = attributes.placement === 'manual';
            const needsContext = !manual && context.type !== 'book';
            const colors = ['background', 'text', 'accent'].map(key => el(BaseControl, {key, label: key.charAt(0).toUpperCase() + key.slice(1), help: 'Blank inherits the Series setting, then ModFarm Settings.'}, el(ColorPalette, {value: attributes[key] || undefined, onChange: value => setAttributes({[key]: value || ''})})));
            return el(Fragment, {}, el(InspectorControls, {},
                el(PanelBody, {title: 'Series & placement'},
                    el(SelectControl, {label: 'Placement mode', value: attributes.placement, options: [{value: 'auto', label: 'Automatic · current Series or Book'}, {value: 'manual', label: 'Manual · choose a Series'}], onChange: placement => setAttributes({placement})}),
                    manual && el(Fragment, {}, el(TextControl, {label: 'Find a Series', value: search, onChange: setSearch}), el(SelectControl, {label: 'Series', value: String(attributes.seriesId || 0), options, onChange: value => setAttributes({seriesId: Number(value)})}), selected?.editUrl && el('a', {href: selected.editUrl, target: '_blank', rel: 'noopener'}, 'Open World Bible controls')),
                    el('p', {}, 'Automatic follows the current Series archive or a Book assigned to one Series. For Books in multiple Series, use Manual.')),
                el(PanelBody, {title: 'Placement overrides', initialOpen: false},
                    el(SelectControl, {label: 'Display', value: attributes.display, options: [{value: 'inherit', label: 'Use Series settings'}, {value: 'sections', label: 'Sections'}, {value: 'tabs', label: 'Tabs'}], onChange: display => setAttributes({display})}),
                    el(SelectControl, {label: 'Books placement', value: attributes.books, options: [{value: 'inherit', label: 'Use Series settings'}, {value: 'separate', label: 'Separate Books block'}, {value: 'included', label: 'Inside World block'}], onChange: books => setAttributes({books})}),
                    el('p', {}, 'Content, section order, publishing, descriptions, and spoiler behavior are controlled in the Series World Bible tab.'), ...colors)),
                el('div', useBlockProps({ref: previewRef}), error && el(Notice, {status: 'warning', isDismissible: false}, error),
                    needsContext ? el(Notice, {status: 'info', isDismissible: false}, 'World · Automatic placement. On a Series archive this follows the current Series. Switch to Manual to select and preview a Series here.') : manual && !attributes.seriesId ? el('p', {}, 'Choose a Series by name in the block settings.') : el(ServerSideRender, {block: 'modfarm/world', attributes, urlQueryArgs: context.id ? {post_id: context.id} : {}, EmptyResponsePlaceholder: () => el('p', {}, 'No published, eligible World content for this placement. Check the World Bible publishing status, entry status, section settings, or Series selection.')})));
        },
        save() { return null; }
    });
})(window.wp);
