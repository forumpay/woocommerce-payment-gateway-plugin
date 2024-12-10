const forumPayData = function (field) {
  const forumPayElement = document.getElementById(field);
  return forumPayElement ? forumPayElement.getAttribute('data') : null;
}

const initPlugin = function () {
  const apiBase = forumPayData('forumpay-apibase');

  if (apiBase === null) {
    return;
  }

  const nonce = forumPayData('forumpay-nonce');
  const orderId = forumPayData('forumpay-orderid');
  const config = {
    baseUrl: apiBase,
    customHeaders: {
      'X-WP-Nonce': nonce
    },

    restGetCryptoCurrenciesUri: {
      'path': '',
      'params': {
        'act': 'currencies',
        'forumpay_nonce': nonce
      },
    },
    restGetRateUri: {
      'path': '',
      'params': {
        'act': 'getRate',
        'forumpay_nonce': nonce
      },
    },
    restStartPaymentUri: {
      'path': '',
      'params': {
        'act': 'startPayment',
        'forumpay_nonce': nonce
      },
    },
    restCheckPaymentUri: {
      'path': '',
      'params': {
        'act': 'checkPayment',
        'forumpay_nonce': nonce,
        'forumpay_order_id': orderId
      },
    },
    restCancelPaymentUri: {
      'path': '',
      'params': {
        'act': 'cancelPayment',
        'forumpay_nonce': nonce
      },
    },
    restRestoreCart: {
      'path': '',
      'params': {
        'act': 'restoreCart',
        'forumpay_nonce': nonce
      },
    },
    successResultUrl: forumPayData('forumpay-returnurl'),
    errorResultUrl: forumPayData('forumpay-cancelurl'),
    forumPayApiUrl: forumPayData('forumpay-forumpayapiurl'),
    messageReceiver: function (name, data) {
    },
    showStartPaymentButton: true,
  }
  window.forumPayPaymentGatewayWidget = new ForumPayPaymentGatewayWidget(config);
  window.forumPayPaymentGatewayWidget.init();
}

jQuery(document).ready(function($) {
  initPlugin();
});
