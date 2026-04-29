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

	const COVER_OPTIONS = [
		{ label: 'Flat', value: 'cover_image_flat' },
		{ label: '3D', value: 'cover_image_3d' },
		{ label: 'Composite', value: 'cover_image_composite' },
		{ label: 'Hero Image', value: 'hero_image' },
		{ label: 'Ebook (Kindle)', value: 'cover_ebook' },
		{ label: 'Paperback', value: 'cover_paperback' },
		{ label: 'Hardcover', value: 'cover_hardcover' },
		{ label: 'Audio (Legacy)', value: 'cover_image_audio' },
		{ label: 'Audio (New)', value: 'cover_audio' },
		{ label: 'Featured Image', value: 'featured' },
	];

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
        					value: coverType,
        					options: COVER_OPTIONS,
        					onChange: (value) => setAttributes({ coverType: value }),
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