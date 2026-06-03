(function (wp) {
	const { registerBlockType } = wp.blocks;
	const { useBlockProps, InspectorControls } = wp.blockEditor;
	const {
		PanelBody,
		SelectControl,
		TextControl
	} = wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { Fragment, createElement: el } = wp.element;

	const COVER_OPTIONS = (window.ModFarmBookOptions && window.ModFarmBookOptions.COVER_OPTIONS) || [
		{ label: 'eBook Cover (BMS)', value: 'cover_ebook' },
		{ label: 'Audiobook Cover (BMS)', value: 'cover_image_audio' },
		{ label: 'Paperback Cover', value: 'cover_paperback' },
		{ label: 'Hardcover Cover', value: 'cover_hardcover' },
		{ label: '3D eBook Cover', value: 'cover_ebook_3d' },
		{ label: '3D Paperback Cover', value: 'cover_paperback_3d' },
		{ label: '3D Hardcover Cover', value: 'cover_hardcover_3d' },
		{ label: '3D Audiobook Cover', value: 'cover_image_audio_3d' },
		{ label: 'Composite (BMS)', value: 'cover_image_composite' },
		{ label: '3D Mockup (BMS)', value: 'cover_image_3d' },
		{ label: 'Featured Image (Book Page)', value: 'featured_image' },
	];

	const normalizeCoverOption = (value) => ({
		featured: 'featured_image',
		hero_image: 'featured_image',
		cover_image_flat: 'cover_ebook',
		cover_audio: 'cover_image_audio',
	}[value] || value || 'cover_ebook');

	registerBlockType('modfarm/book-cover-art', {
		edit: function ({ attributes, setAttributes }) {
        	const {
        		coverType = 'cover_ebook',
        		alignment = 'center',
        		customAlt = ''
        	} = attributes;
        
        	const blockProps = useBlockProps({
        		className: 'modfarm-cover-art align-' + alignment,
        	});
        
        	return el(
        		Fragment,
        		null,
        		el(
        			InspectorControls,
        			null,
        			el(
        				PanelBody,
        				{ title: 'Cover Settings' },
        				el(SelectControl, {
        					label: 'Cover Type',
        					value: normalizeCoverOption(coverType),
        					options: COVER_OPTIONS,
        					onChange: (value) => setAttributes({ coverType: normalizeCoverOption(value) }),
        				}),
        				el(TextControl, {
        					label: 'Alt Text (optional)',
        					value: customAlt,
        					onChange: (value) => setAttributes({ customAlt: value }),
        				}),
        				el(SelectControl, {
        					label: 'Alignment',
        					value: alignment,
        					options: [
        						{ label: 'Left', value: 'left' },
        						{ label: 'Center', value: 'center' },
        						{ label: 'Right', value: 'right' },
        					],
        					onChange: (value) => setAttributes({ alignment: value }),
        				})
        			)
        		),
        		el(
        			'div',
        			blockProps,
        			el(ServerSideRender, {
        				block: 'modfarm/book-cover-art',
        				attributes: attributes
        			})
        		)
        	);
        },
		save: () => null,
	});
})(window.wp);
