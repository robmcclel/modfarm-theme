(function() {
	const el = wp.element.createElement;
	const ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType('modfarm/book-page-description', {
		title: wp.i18n.__('Book Page Description', 'modfarm'),
		category: 'modfarm-book',
		icon: 'editor-paragraph',
		supports: {
			html: false
		},
		attributes: {
			align: { type: 'string' }
		},
		edit: function (props) {
			const { attributes } = props;
			const blockProps = wp.blockEditor.useBlockProps();
			
			console.log('ATTRIBUTES', attributes);

			return el(
				'div',
				blockProps,
				el(ServerSideRender, {
					block: 'modfarm/book-page-description',
					attributes: attributes
				})
			);
		},
		save: function () {
			return null;
		}
	});
})();