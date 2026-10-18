jQuery(document).ready(function($){
    function notifyPreview(input) {
        input.dispatchEvent(new window.Event('input', { bubbles: true }));
        input.dispatchEvent(new window.Event('change', { bubbles: true }));
    }
    $('.modfarm-color-field').each(function() {
        // WordPress passes the Clear button as event.target; retain the field.
        var input = this;
        $(input).wpColorPicker({
            change: function(event, ui) {
                if (ui && ui.color) $(input).val(ui.color.toString());
                window.setTimeout(function() { notifyPreview(input); }, 0);
            },
            clear: function() {
                window.setTimeout(function() { input.value = ''; notifyPreview(input); }, 0);
            }
        });
        var reset = $('<button type="button" class="button mf-color-default">Default</button>');
        reset.on('click', function() {
            $(input).closest('.wp-picker-container').find('.wp-picker-clear').trigger('click');
            input.value = '';
            notifyPreview(input);
        });
        $(input).closest('.wp-picker-container').after(reset);
    });
});
