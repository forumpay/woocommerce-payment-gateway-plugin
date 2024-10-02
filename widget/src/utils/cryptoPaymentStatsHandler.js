/* global CryptoPaymentStats */
import store from '../store/store';

const cryptoPaymentStatsHandler = {
  event(eventName, ...args) {
    const statsToken = store.state.payment.stats_token;

    if (!statsToken) {
      return;
    }

    if (typeof CryptoPaymentStats === 'undefined') {
      return;
    }

    const eventHandlers = {
      addressCopy: () => CryptoPaymentStats.onAddressCopy(),
      amountCopy: () => CryptoPaymentStats.onAmountCopy(),
      QRCodeInit: () => CryptoPaymentStats.onQRCodeInit(),
      QRCodeLoad: (success) => CryptoPaymentStats.onQRCodeLoad(success),
      QRAltCodeInit: () => CryptoPaymentStats.onQRAltCodeInit(),
      QRAltCodeLoad: (success) => CryptoPaymentStats.onQRAltCodeLoad(success),
      beforeClose: () => CryptoPaymentStats.beforeClose(),
    };

    if (eventHandlers[eventName]) {
      eventHandlers[eventName](...args);
    }
  },
};

export default cryptoPaymentStatsHandler;
