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
    restGetRatesUri: {
      'path': '',
      'params': {
        'act': 'getRates',
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
    restGetWalletAppsUri: {
      'path': '',
      'params': {
        'act': 'getWalletApps',
        'forumpay_nonce': nonce
      },
    },
    successResultUrl: forumPayData('forumpay-returnurl'),
    errorResultUrl: forumPayData('forumpay-cancelurl'),
    forumPayApiUrl: forumPayData('forumpay-forumpayapiurl'),
    invoiceAmount: forumPayData('forumpay-invoiceamount'),
    invoiceCurrency: forumPayData('forumpay-invoicecurrency'),
    payer: {
        'payer_type': '',
        'payer_first_name': forumPayData('forumpay-payerfirstname'),
        'payer_last_name': forumPayData('forumpay-payerlastname'),
        'payer_country_of_residence': forumPayData('forumpay-payercountry'),
        'payer_email': forumPayData('forumpay-payeremail'),
        'payer_date_of_birth': '',
        'payer_country_of_birth': '',
        'payer_company': forumPayData('forumpay-payercompany'),
        'payer_date_of_incorporation': '',
        'payer_country_of_incorporation': forumPayData('forumpay-payercountry'),
    },
    messageReceiver: function (name, data) {
    },
  }
  window.forumPayPaymentGatewayWidget = new ForumPayPaymentGatewayWidget(config);
  window.forumPayPaymentGatewayWidget.init();
}

jQuery(document).ready(function($) {
  initPlugin();
});
