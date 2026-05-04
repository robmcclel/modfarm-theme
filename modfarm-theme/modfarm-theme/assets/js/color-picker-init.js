jQuery(document).ready(function($){
    function notifyPreview(input) {
        if (!input) return;
        input.dispatchEvent(new window.Event('input', { bubbles: true }));
        input.dispatchEvent(new window.Event('change', { bubbles: true }));
    }

    $('.modfarm-color-field').wpColorPicker({
        change: function(event, ui) {
            var input = event && event.target ? event.target : this;
            if (ui && ui.color && typeof ui.color.toString === 'function') {
                $(input).val(ui.color.toString());
            }
            window.setTimeout(function() {
                notifyPreview(input);
            }, 0);
        },
        clear: function(event) {
            var input = event && event.target ? event.target : this;
            window.setTimeout(function() {
                notifyPreview(input);
            }, 0);
        }
    });
});
