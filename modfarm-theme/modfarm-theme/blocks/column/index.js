(function () {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, InnerBlocks } = wp.blockEditor;
	const { PanelBody, SelectControl, TextControl } = wp.components;
	const { createElement: el, Fragment } = wp.element;

	registerBlockType('modfarm/column', {
		title: 'ModFarm Column',
		icon: 'align-left',
		category: 'layout',
		parent: ['modfarm/columns'],
		supports: {
			html: false,
		},
		attributes: {
			verticalAlign: {
				type: 'string',
				default: 'top',
			},
			padding: {
				type: 'string',
				default: '',
			},
			width: {
				type: 'string',
				default: '100%',
			},
		},
		edit: function (props) {
			const { attributes, setAttributes } = props;
			const { verticalAlign, padding, width } = attributes;

			const classes = [
				'mf-column',
				verticalAlign ? `valign-${verticalAlign}` : '',
			].filter(Boolean).join(' ');

			const style = {
				padding: padding || undefined,
				width: width || undefined,
			};

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: 'Column Settings', initialOpen: true },
						el(SelectControl, {
							label: 'Vertical Alignment',
							value: verticalAlign,
							options: [
								{ label: 'Top', value: 'top' },
								{ label: 'Middle', value: 'middle' },
								{ label: 'Bottom', value: 'bottom' },
							],
							onChange: function (val) {
								setAttributes({ verticalAlign: val });
							},
						}),
						el(TextControl, {
							label: 'Padding (e.g. 20px or 10px 20px)',
							value: padding,
							onChange: function (val) {
								setAttributes({ padding: val });
							},
						}),
						el(SelectControl, {
							label: 'Width',
							value: width,
							options: [
								{ label: '66%', value: '65%' },
								{ label: '50%', value: '49%' },
								{ label: '33%', value: '32%' },
							],
							onChange: function (val) {
								setAttributes({ width: val });
							},
						})
					)
				),
				el(
                	'div',
                	{ className: classes, style: style },
                	el(InnerBlocks, {
                		template: [ ['core/paragraph'] ],
                		templateLock: false,
                		orientation: 'vertical',
                	})
                )
			);
		},
		save: function (props) {
			const { verticalAlign, padding, width } = props.attributes;

			const classes = [
				'mf-column',
				verticalAlign ? `valign-${verticalAlign}` : '',
			].filter(Boolean).join(' ');

			const style = {
				padding: padding || undefined,
				width: width || undefined,
			};

			return el(
				'div',
				{ className: classes, style: style },
				el(InnerBlocks.Content, null)
			);
		},
	});
})();