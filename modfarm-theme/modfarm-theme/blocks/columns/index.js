(function () {
	const { registerBlockType } = wp.blocks;
	const { InnerBlocks, InspectorControls, useBlockProps } = wp.blockEditor;
	const { useSelect, useDispatch } = wp.data;
	const { Fragment, useEffect } = wp.element;
	const { PanelBody, ToggleControl } = wp.components;
	const { createElement: el } = wp.element;

	registerBlockType('modfarm/columns', {
		title: 'ModFarm Columns',
		icon: 'columns',
		category: 'layout',
		supports: {
			html: false,
		},
		attributes: {
			reverseMobile: {
				type: 'boolean',
				default: false,
			},
			stackMobile: {
				type: 'boolean',
				default: true,
			},
		},

		edit: function (props) {
        	const { attributes, setAttributes, clientId } = props;
        	const { reverseMobile, stackMobile } = attributes;
        
        	// Count how many columns exist
        	const columnCount = useSelect(
        		(select) => select('core/block-editor').getBlockOrder(clientId).length,
        		[clientId]
        	);
        
        	const disableInserter = columnCount >= 2;
        
        	const classes = [
        		'mf-columns',
        		stackMobile ? 'stack-mobile' : '',
        		reverseMobile ? 'reverse-mobile' : '',
        	].filter(Boolean).join(' ');
        
        	const blockProps = useBlockProps({ className: classes });
        
        	return el(
        		Fragment,
        		null,
        		el(
        			InspectorControls,
        			null,
        			el(
        				PanelBody,
        				{ title: 'Mobile Behavior' },
        				el(ToggleControl, {
        					label: 'Stack on Mobile',
        					checked: stackMobile,
        					onChange: (val) => setAttributes({ stackMobile: val }),
        				}),
        				el(ToggleControl, {
        					label: 'Reverse on Mobile',
        					checked: reverseMobile,
        					onChange: (val) => setAttributes({ reverseMobile: val }),
        				})
        			)
        		),
        		el(
        			'div',
        			blockProps,
        			el(InnerBlocks, {
        				allowedBlocks: ['modfarm/column'],
        				orientation: 'horizontal',
        				template: [
        					['modfarm/column'],
        					['modfarm/column'],
        				],
        				templateLock: false,
        				renderAppender: disableInserter ? false : undefined,
        			})
        		)
        	);
        },



		save: function (props) {
			const { reverseMobile, stackMobile } = props.attributes;

			const classes = [
				'mf-columns',
				stackMobile ? 'stack-mobile' : '',
				reverseMobile ? 'reverse-mobile' : '',
			]
				.filter(Boolean)
				.join(' ');

			const blockProps = useBlockProps.save({ className: classes });

			return el('div', blockProps, el(InnerBlocks.Content, null));
		},
	});
})();