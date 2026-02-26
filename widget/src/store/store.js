/* global CryptoPaymentStats */
import { createStore } from 'vuex';

// View constants for navigation
export const VIEWS = {
  RATE_SELECTION: 'rate-selection',
  NETWORK_SELECTION: 'network-selection',
  PAYMENT: 'payment',
  KYC: 'kyc',
  PAYER: 'payer',
};

const formatRoute = (config) => {
  if (
    typeof config === 'object'
    && !Array.isArray(config)
    && config !== null
  ) {
    return {
      path: '',
      params: {},
      ...config,
    };
  }

  return {
    path: config,
    params: {},
  };
};

const getDefaultState = () => ({
  currentView: VIEWS.RATE_SELECTION,
  cryptoCurrencies: [],
  cryptoCurrency: null,
  currencyRates: {},
  ratesLoading: false,
  invoiceAmount: '',
  invoiceCurrency: '',
  networks: [],
  selectedNetwork: null,
  networkSelectionRequired: false,
  kycError: null,
  kycPin: '',
  kycRequired: false,
  rate: null,
  rateLoading: false,
  rateError: null,
  payment: null,
  paymentCheck: null,
  paymentCheckIgnoreResult: false,
  paymentCheckLoading: false,
  paymentCheckError: false,
  error: null,
  loading: null,
  showStartPaymentButton: false,
  forceShowStartPaymentButton: false,
  showInstructions: true,
  showCancel: false,
  userCanceled: false,
  payerRequired: false,
  payer: {},
});

export default createStore({
  state() {
    return getDefaultState();
  },
  mutations: {
    resetPlugin(state) {
      Object.assign(state, {
        ...getDefaultState(),
        payer: state.payer,
      });
    },
    setCurrentView(state, view) {
      state.currentView = view;
    },
    setCryptoCurrencies(state, cryptoCurrencies) {
      state.cryptoCurrencies = cryptoCurrencies;
    },
    setCryptoCurrency(state, cryptoCurrency) {
      state.cryptoCurrency = cryptoCurrency;
    },
    setNetworks(state, networks) {
      state.networks = networks;
    },
    setSelectedNetwork(state, network) {
      state.selectedNetwork = network;
    },
    setNetworkSelectionRequired(state, required) {
      state.networkSelectionRequired = required;
    },
    setCurrencyRates(state, rates) {
      state.currencyRates = rates;
    },
    setCurrencyRate(state, { currency, rateData }) {
      state.currencyRates[currency] = rateData;
    },
    setInvoiceAmount(state, { amount, currency }) {
      state.invoiceAmount = amount;
      state.invoiceCurrency = currency;
    },
    setRatesLoading(state, loading) {
      state.ratesLoading = loading;
    },
    setKycError(state, kycError) {
      state.kycError = kycError;
    },
    setKycPin(state, kycPin) {
      state.kycPin = kycPin;
    },
    setKycRequired(state, kycRequired) {
      state.kycRequired = kycRequired;
    },
    setRate(state, rate) {
      state.rate = rate;
      state.rateLoading = false;
      state.showStartPaymentButton = this.pluginConfig.showStartPaymentButton;
      if (this.pluginConfig.messageReceiver) {
        this.pluginConfig.messageReceiver('RATE_SET', { rate });
      }
    },
    setRateLoading(state, loading) {
      state.rateLoading = loading;
    },
    setPayment(state, payment) {
      state.payment = payment;
    },
    setPaymentCheck(state, paymentCheck) {
      state.paymentCheck = paymentCheck;
      state.paymentCheckLoading = false;

      if (paymentCheck.status === 'Cancelled') {
        this.pluginConfig.messageReceiver('PAYMENT_CANCELED', {});
        if (
          this.state.userCanceled
          || paymentCheck.reason !== null
        ) {
          setTimeout(
            () => {
              window.location.href = this.pluginConfig.errorResultUrl;
            },
            2000,
          );
        } else {
          state.showCancel = true;
        }
      }

      if (paymentCheck.status === 'Confirmed') {
        setTimeout(
          () => { window.location.href = this.pluginConfig.successResultUrl; },
          2000,
        );
      }
    },
    setCheckPaymentLoading(state, loading) {
      state.paymentCheckLoading = loading;
    },
    setCheckPaymentIgnoreResult(state, ignoreResult) {
      state.paymentCheckIgnoreResult = ignoreResult;
    },
    setCheckPaymentError(state, error) {
      state.paymentCheckError = error;
    },
    setError(state, error) {
      state.error = error;
    },
    setRateError(state, error) {
      state.rateError = error;
    },
    setLoading(state, loading) {
      state.loading = loading;
    },
    setShowInstructions(state, showInstructions) {
      state.showInstructions = showInstructions;
    },
    setShowCancel(state, showCancel) {
      state.showCancel = showCancel;
      state.showInstructions = false;
    },
    setUserCanceled(state, userCanceled) {
      state.userCanceled = userCanceled;
    },
    setPayerRequired(state, payerRequired) {
      state.payerRequired = payerRequired;
    },
    setPayer(state, payer) {
      state.payer = payer;
    },
    setForceShowStartPaymentButton(state, forceShowStartPaymentButton) {
      state.forceShowStartPaymentButton = forceShowStartPaymentButton;
    },
  },
  actions: {
    async resetPlugin({ commit }) {
      if (this.state.payment !== null) {
        const paymentId = this.state.payment.payment_id;
        const route = formatRoute(this.pluginConfig.restCancelPaymentUri);

        await this.axios.post(route.path, { payment_id: paymentId, ...route.params });
        this.pluginConfig.messageReceiver('PAYMENT_RETRY', {});
      }

      commit('resetPlugin');
    },
    async setCryptoCurrencies({ commit }) {
      try {
        const route = formatRoute(this.pluginConfig.restGetCryptoCurrenciesUri);

        const response = await this.axios.post(route.path, route.params);

        commit(
          'setCryptoCurrencies',
          response.data.currencies,
        );

        commit(
          'setCryptoCurrency',
          response.data.currencies[0],
        );

        if (response.data.currencies.length === 0) {
          commit('setError', {
            code: 1001,
            message: 'No currencies are available.',
          });
        }
      } catch (error) {
        commit('setError', error);
      }
    },
    setCryptoCurrency({ commit }, cryptoCurrency) {
      commit(
        'setCryptoCurrency',
        cryptoCurrency,
      );
    },
    setNetworks({ commit }, networks) {
      commit('setNetworks', networks);
      commit('setNetworkSelectionRequired', networks.length > 1);
      // Navigate to network selection view
      commit('setCurrentView', VIEWS.NETWORK_SELECTION);
    },
    setSelectedNetwork({ commit }, network) {
      commit('setSelectedNetwork', network);
      commit('setNetworkSelectionRequired', false);
    },
    navigateToRateSelection({ commit }) {
      commit('setNetworkSelectionRequired', false);
      commit('setCryptoCurrency', null);
      commit('setRate', null);
      commit('setCurrentView', VIEWS.RATE_SELECTION);
    },
    clearRate({ commit }) {
      commit('setRate', null);
    },
    async fetchAllRates({ commit, state }) {
      commit('setRatesLoading', true);
      const rates = {};

      try {
        // Build comma-separated list of currency codes
        const currencyCodes = state.cryptoCurrencies
          .map((c) => c.currency)
          .join(',');

        if (!currencyCodes) {
          commit('setRatesLoading', false);
          return;
        }

        const route = formatRoute(this.pluginConfig.restGetRatesUri);

        const result = await this.axios.post(
          route.path,
          { currencies: currencyCodes, ...route.params },
        );

        // Save invoice amount from response
        if (result.data.invoice_amount && result.data.invoice_currency) {
          commit('setInvoiceAmount', {
            amount: result.data.invoice_amount,
            currency: result.data.invoice_currency,
          });
        }

        // Parse the response - currencies are in result.data.currencies
        const currenciesData = result.data.currencies || {};

        // Map the response to our rates object
        Object.entries(currenciesData).forEach(([currencyCode, rateData]) => {
          if (rateData.err || rateData.err_code) {
            // Currency has an error
            rates[currencyCode] = { error: true, message: rateData.err };
          } else {
            // Currency has valid rate data
            rates[currencyCode] = rateData;
          }
        });

        commit('setCurrencyRates', rates);
      } catch (error) {
        // Failed to fetch rates - set error flag for all currencies
        state.cryptoCurrencies.forEach((currency) => {
          rates[currency.currency] = { error: true };
        });
        commit('setCurrencyRates', rates);
      }

      commit('setRatesLoading', false);
    },
    async setRate({ commit, state }, cryptoCurrency) {
      if (state.rateLoading) {
        return;
      }

      commit('setRateLoading', true);

      try {
        const route = formatRoute(this.pluginConfig.restGetRateUri);

        const result = await this.axios.post(
          route.path,
          { currency: cryptoCurrency.currency, ...route.params },
        );

        commit('setRate', result.data);
      } catch (error) {
        if (error.response) {
          commit(
            'setRateError',
            error.response.data,
          );

          if (error.response.data.code === 2001) {
            // expected to fail once when order is first created.
            return;
          }
        }

        commit('setError', error);
      }
    },
    async startPayment({ commit, state }, cryptoCurrency) {
      try {
        commit('setLoading', true);
        commit('setKycError', null);
        commit('setPayerRequired', false);

        const route = formatRoute(this.pluginConfig.restStartPaymentUri);

        const data = {
          currency: cryptoCurrency.currency,
          payer: state.payer,
          ...(state.kycRequired && state.kycPin && { kycPin: state.kycPin }),
          ...route.params,
        };

        const result = await this.axios.post(route.path, data);

        commit('setLoading', false);
        commit('setPayment', result.data);
        commit('setCurrentView', VIEWS.PAYMENT);

        if (result.data && result.data.stats_token) {
          CryptoPaymentStats.setToken(result.data.stats_token);
        }
      } catch (error) {
        commit('setLoading', false);
        switch (error.response.data.code) {
          case 3051:
            commit('setKycRequired', true);
            commit('setCurrentView', VIEWS.KYC);
            break;
          case 3056:
            commit('setPayerRequired', true);
            commit('setCurrentView', VIEWS.PAYER);
            break;
          case 3053:
          case 3054:
          case 3055: {
            const errorMsg = error.response.data.message;
            const i = errorMsg.lastIndexOf(']');
            commit('setKycError', i !== -1 ? errorMsg.slice(i + 2) : errorMsg);
            commit('setCurrentView', VIEWS.KYC);
            break;
          }
          case 3052:
          default:
            commit('setError', error);
            commit('setForceShowStartPaymentButton', true);
            break;
        }
      }
    },
    async checkPayment({ commit, state }, paymentId) {
      if (state.paymentCheckLoading) {
        return;
      }

      commit('setCheckPaymentLoading', true);

      try {
        const route = formatRoute(this.pluginConfig.restCheckPaymentUri);

        const result = await this.axios.post(
          route.path,
          { payment_id: paymentId, ...route.params },
        );

        if (!this.state.paymentCheckIgnoreResult) {
          commit('setPaymentCheck', result.data);
          commit('setLoading', false);
        }
      } catch (error) {
        commit('setLoading', false);
        if (error.response && error.response.data.code === 4001) {
          // expected to fail once when order is fulfilled.
          commit(
            'setCheckPaymentError',
            error.response.data,
          );

          // If user cancelled, restore cart and redirect to error page
          // since order is no longer active
          if (this.state.userCanceled) {
            try {
              const restoreCartRoute = formatRoute(this.pluginConfig.restRestoreCart);
              await this.axios.post(restoreCartRoute.path, { ...restoreCartRoute.params });
            } catch (restoreError) {
              // Continue with redirect even if restore fails
            }
            this.pluginConfig.messageReceiver('PAYMENT_CANCELED', {});
            window.location.href = this.pluginConfig.errorResultUrl;
          }
          return;
        }

        commit('setError', error);
      }
    },
    async cancelPayment({ commit }, payload) {
      if (this.state.payment === null) {
        if (payload.restoreCart) {
          const restoreCartRoute = formatRoute(this.pluginConfig.restRestoreCart);
          await this.axios.post(restoreCartRoute.path, { ...restoreCartRoute.params });
        }
        window.location.href = this.pluginConfig.errorResultUrl;
        return;
      }

      commit('setLoading', true);
      commit('setCheckPaymentIgnoreResult', true);

      try {
        const route = formatRoute(this.pluginConfig.restCancelPaymentUri);
        const paymentId = this.state.payment.payment_id;

        await this.axios.post(
          route.path,
          {
            payment_id: paymentId,
            ...payload,
            ...route.params,
          },
        );

        if (payload.forceRedirect) {
          this.pluginConfig.messageReceiver('PAYMENT_CANCELED', {});
          window.location.href = this.pluginConfig.errorResultUrl;
          return;
        }

        commit('setUserCanceled', true);
        commit('setCheckPaymentIgnoreResult', false);
        commit('setShowCancel', false);
        this.dispatch('checkPayment', paymentId);
      } catch (error) {
        commit('setLoading', false);
        commit('setError', error);
      }
    },
    async hideInstructions({ commit }) {
      commit('setShowInstructions', false);
    },
    async hideCancel({ commit }) {
      commit('setShowCancel', false);
    },
    async showCancel({ commit }) {
      commit('setShowCancel', true);
    },
    async getWalletApps() {
      try {
        const route = formatRoute(this.pluginConfig.restGetWalletAppsUri);
        const response = await this.axios.post(route.path, route.params);
        return response.data.walletApps || [];
      } catch (error) {
        console.error('Failed to fetch wallet apps:', error);
        return [];
      }
    },
    async regeneratePaymentQr({ commit, state }, walletAppId) {
      try {
        commit('setLoading', true);

        const route = formatRoute(this.pluginConfig.restStartPaymentUri);

        const data = {
          currency: state.cryptoCurrency.currency,
          payer: state.payer,
          walletAppId,
          ...route.params,
        };

        const result = await this.axios.post(route.path, data);

        commit('setLoading', false);
        commit('setPayment', result.data);
      } catch (error) {
        commit('setLoading', false);
        commit('setError', error);
      }
    },
  },
});
