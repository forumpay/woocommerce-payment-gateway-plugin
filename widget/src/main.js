import { createApp } from 'vue';
import axios from 'axios';

import App from './App.vue';
import store from './store/store';

function ForumPayPaymentGatewayWidget(config) {
  this.config = {
    ...{
      widgetElementId: '#ForumPayPaymentGatewayWidgetContainer',
      baseUrl: '/',
      restGetCryptoCurrenciesUri: '',
      restGetRateUri: '',
      restStartPaymentUri: '',
      restCheckPaymentUri: '',
      restCancelPaymentUri: '',
      successResultUrl: '',
      errorResultUrl: '',
      messageReceiver: function messageReceiver() {},
      showStartPaymentButton: false,
    },
    ...config,
  };
}

ForumPayPaymentGatewayWidget.prototype.init = function init() {
  store.pluginConfig = this.config;
  store.axios = axios.create({
    baseURL: this.config.baseUrl,
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });

  /* eslint-disable no-param-reassign */
  const clickOutside = {
    beforeMount: (el, binding) => {
      el.clickOutsideEvent = (event) => {
        if (!(el === event.target || el.contains(event.target))) {
          binding.value();
        }
      };
      document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted: (el) => {
      document.removeEventListener('click', el.clickOutsideEvent);
    },
  };

  createApp(App)
    .use(store)
    .directive('click-outside', clickOutside)
    .mount(this.config.widgetElementId);
};

ForumPayPaymentGatewayWidget.prototype.startPayment = function startPayment() {
  store.dispatch('startPayment', store.state.cryptoCurrency);
};

ForumPayPaymentGatewayWidget.prototype.validate = function validate() {
  return store.state.cryptoCurrency !== null
    && store.state.rate !== null
    && store.state.error === null;
};

window.ForumPayPaymentGatewayWidget = ForumPayPaymentGatewayWidget;
