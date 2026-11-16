(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const trigger = event.target.closest('a[data-mf-book-sample]');
        if (!trigger) return;

        const sample = document.getElementById(trigger.dataset.mfBookSample);
        if (!sample) return;
        const dialog = document.createElement('dialog');
        if (typeof dialog.showModal !== 'function') return;

        const position = document.createComment('book sample');
        sample.before(position);
        dialog.className = 'mfs-book-sample-dialog';
        dialog.setAttribute('aria-labelledby', sample.getAttribute('aria-labelledby'));
        dialog.append(sample);
        document.body.append(dialog);
        dialog.showModal();
        event.preventDefault();

        sample.querySelector('h2').focus();
        dialog.addEventListener('click', function (click) {
            if (click.target === dialog || click.target.closest('.mfs-book-sample-close')) dialog.close();
        });
        dialog.addEventListener('close', function () {
            position.replaceWith(sample);
            dialog.remove();
            trigger.focus({ preventScroll: true });
        }, { once: true });
    });
}());
