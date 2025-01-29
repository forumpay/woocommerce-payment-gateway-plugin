import { createApp } from 'vue';
import axios from 'axios';

import App from './App.vue';
import store from './store/store';

class ForumPayPaymentGatewayWidget {
  constructor(config) {
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
        customHeaders: {},
        forumPayApiUrl: '',
        payer: {},
      },
      ...config,
    };
    this.store = store;
  }

  init() {
    store.pluginConfig = this.config;

    if (this.config.payer) {
      store.commit('setPayer', this.config.payer);
    }

    if (this.config.forumPayApiUrl !== '') {
      const scriptLimitlex = document.createElement('script');
      scriptLimitlex.type = 'text/javascript';
      scriptLimitlex.src = `${this.config.forumPayApiUrl}/pay/events/payment.js`;
      document.head.appendChild(scriptLimitlex);
    }

    store.axios = axios.create({
      baseURL: this.config.baseUrl,
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...this.config.customHeaders,
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
      .directive('click-outside', {
        beforeMount: clickOutside.beforeMount,
        unmounted: clickOutside.unmounted,
      })
      .mount(this.config.widgetElementId);
  }

  startPayment() {
    this.store.dispatch('startPayment', this.store.state.cryptoCurrency);
  }

  validate() {
    return this.store.state.cryptoCurrency !== null
      && this.store.state.rate !== null
      && this.store.state.error === null;
  }
}

window.ForumPayPaymentGatewayWidget = ForumPayPaymentGatewayWidget;
