const forumPayData = function (field) {
  return document.getElementById(field).getAttribute('data');
}

const initPlugin = function () {
  const nonce = forumPayData('forumpay-nonce');
  const config = {
    baseUrl: forumPayData('forumpay-apibase'),

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
        'forumpay_nonce': nonce
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
