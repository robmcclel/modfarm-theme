(function () {
  'use strict';

  function updateCheckoutUrl(link) {
    var controls = link.closest('.mfs-offer-buy-button__checkout-controls');
    var discountInput = controls && controls.querySelector('[data-mf-offer-discount]');
    var buyUrl = link.getAttribute('data-mf-offer-buy-url');

    if (!buyUrl) {
      return;
    }

    var checkoutUrl = new URL(buyUrl, window.location.href);
    var discountCode = discountInput ? discountInput.value.trim() : '';

    if (discountCode) {
      checkoutUrl.searchParams.set('mf_discount', discountCode);
    } else {
      checkoutUrl.searchParams.delete('mf_discount');
    }

    link.href = checkoutUrl.toString();
  }

  document.addEventListener('input', function (event) {
    if (!event.target.matches('[data-mf-offer-discount]')) {
      return;
    }

    var controls = event.target.closest('.mfs-offer-buy-button__checkout-controls');
    var link = controls && controls.querySelector('[data-mf-offer-buy-url]');
    if (link) {
      updateCheckoutUrl(link);
    }
  });

  document.addEventListener('click', function (event) {
    var link = event.target.closest('[data-mf-offer-buy-url]');
    if (link) {
      updateCheckoutUrl(link);
    }
  });
})();
