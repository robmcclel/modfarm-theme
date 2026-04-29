jQuery(document).ready(function ($) {
	$('.modfarm-media-upload').on('click', function (e) {
		e.preventDefault();

		const targetInputId = $(this).data('target');
		const inputField = $('#' + targetInputId);
		const previewContainer = inputField.closest('td').find('img');

		const mediaFrame = wp.media({
			title: 'Select Image',
			button: {
				text: 'Use This Image',
			},
			multiple: false,
		});

		mediaFrame.on('select', function () {
			const attachment = mediaFrame.state().get('selection').first().toJSON();
			inputField.val(attachment.id);

			if (previewContainer.length) {
				previewContainer.attr('src', attachment.sizes.medium.url);
			} else {
				inputField
					.closest('td')
					.prepend('<img src="' + attachment.sizes.medium.url + '" style="max-width:200px;" />');
			}
		});

		mediaFrame.open();
	});
});