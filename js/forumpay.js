const forumPayData = function (field) {
  return document.getElementById(field).getAttribute('data');
}

const initPlugin = function () {
  const config = {
    baseUrl: forumPayData('forumpay-apibase'),

    restGetCryptoCurrenciesUri: {
      'path': '',
      'params': {
        'act': 'currencies'
      },
    },
    restGetRateUri: {
      'path': '',
      'params': {
        'act': 'getRate'
      },
    },
    restStartPaymentUri: {
      'path': '',
      'params': {
        'act': 'startPayment'
      },
    },
    restCheckPaymentUri: {
      'path': '',
      'params': {
        'act': 'checkPayment'
      },
    },
    restCancelPaymentUri: {
      'path': '',
      'params': {
        'act': 'cancelPayment'
      },
    },
    restRestoreCart: {
      'path': '',
      'params': {
        'act': 'restoreCart'
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

initPlugin();
