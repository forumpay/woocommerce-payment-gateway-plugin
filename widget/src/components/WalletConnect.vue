<script>
import loadWalletConnectSdk from '../utils/loadWalletConnectSdk';
import SvgWalletConnect from '../images/SvgWalletConnect.vue';

const SDK_STATES = {
  Loading: 'Loading',
  Disconnected: 'Disconnected',
  Connecting: 'Connecting',
  Connected: 'Connected',
  PreparingTransaction: 'PreparingTransaction',
  AwaitingSignature: 'AwaitingSignature',
  TxSuccess: 'TxSuccess',
};

const ERROR_MESSAGES = {
  UserRejected: 'Transaction rejected in wallet.',
  NotEnoughFunds: 'Not enough funds on the connected wallet address.',
  Unknown: 'The transaction could not be processed by your wallet.',
};

const INIT_TIMEOUT_MS = 20000;

const WalletConnect = {
  components: {
    SvgWalletConnect,
  },
  props: {
    wcToken: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      hasToken: false,
      supported: false,
      sdkState: SDK_STATES.Loading,
      walletName: null,
      errorMessage: null,
      sdkLoadError: null,
      isInitializing: false,
      unsubscribeFns: [],
      initializedToken: null,
    };
  },
  computed: {
    showButton() {
      return this.hasToken && this.supported && this.sdkState !== SDK_STATES.TxSuccess;
    },
    isButtonDisabled() {
      return this.isInitializing
        || this.sdkState === SDK_STATES.AwaitingSignature
        || this.sdkState === SDK_STATES.PreparingTransaction
        || this.sdkState === SDK_STATES.Connecting;
    },
    showConnectedWallet() {
      return this.walletName
        && this.sdkState !== SDK_STATES.Disconnected
        && this.sdkState !== SDK_STATES.TxSuccess
        && this.sdkState !== SDK_STATES.Loading;
    },
    showAwaitingSignature() {
      return this.sdkState === SDK_STATES.AwaitingSignature
        || this.sdkState === SDK_STATES.PreparingTransaction;
    },
    showTxSuccess() {
      return this.sdkState === SDK_STATES.TxSuccess;
    },
  },
  watch: {
    wcToken(newToken) {
      if (newToken && newToken !== this.initializedToken) {
        this.initializeSdk(newToken);
      }
    },
  },
  mounted() {
    this.initializeSdk(this.wcToken);
  },
  unmounted() {
    this.cleanup(false);
  },
  methods: {
    getSdk() {
      return window.PaymentWalletConnect || null;
    },

    wait(ms) {
      return new Promise((resolve) => {
        setTimeout(resolve, ms);
      });
    },

    setupListeners() {
      const sdk = this.getSdk();
      if (!sdk) {
        return;
      }

      this.unsubscribeFns.push(
        sdk.onStateChange((state, walletInfo) => {
          this.sdkState = state;
          if (walletInfo) {
            this.walletName = walletInfo.walletName;
          }
          if (state === SDK_STATES.Disconnected) {
            this.walletName = null;
          }
        }),
        sdk.onTxSignFailed(() => {
          if (!this.errorMessage) {
            this.errorMessage = ERROR_MESSAGES.Unknown;
          }
        }),
      );
    },

    cleanup(resetSdk) {
      this.unsubscribeFns.forEach((unsubscribe) => unsubscribe());
      this.unsubscribeFns = [];

      if (resetSdk) {
        const sdk = this.getSdk();
        if (sdk) {
          sdk.reset();
        }
      }

      this.hasToken = false;
      this.supported = false;
      this.sdkState = SDK_STATES.Loading;
      this.walletName = null;
      this.errorMessage = null;
      this.sdkLoadError = null;
      this.isInitializing = false;
      this.initializedToken = null;
    },

    resolveErrorMessage(error) {
      if (error && error.reason && ERROR_MESSAGES[error.reason]) {
        return ERROR_MESSAGES[error.reason];
      }
      if (error && error.message) {
        return error.message;
      }
      return ERROR_MESSAGES.Unknown;
    },

    async initializeSdk(token) {
      this.cleanup(true);

      if (!token) {
        return;
      }

      this.hasToken = true;
      this.isInitializing = true;
      this.sdkLoadError = null;

      let sdk = null;

      try {
        sdk = await loadWalletConnectSdk();

        const initConfig = { jwt: token, theme: 'light' };

        const initResult = await Promise.race([
          sdk.init(initConfig),
          this.wait(INIT_TIMEOUT_MS).then(() => {
            throw new Error('WalletConnect initialization timed out.');
          }),
        ]);

        const { supported, connectedWallet } = initResult;
        this.supported = supported;
        this.initializedToken = token;
        this.sdkState = connectedWallet ? SDK_STATES.Connected : SDK_STATES.Disconnected;
        if (connectedWallet) {
          this.walletName = connectedWallet.walletName;
        }
        this.setupListeners();
      } catch (error) {
        if (sdk) {
          sdk.reset();
        }
        this.supported = false;
        this.sdkLoadError = error.message || 'WalletConnect is unavailable for this payment.';
        console.error('WalletConnect init failed:', error);
      } finally {
        this.isInitializing = false;
      }
    },

    async useWallet() {
      const sdk = this.getSdk();
      if (!sdk || !this.supported) {
        return;
      }

      this.errorMessage = null;

      try {
        if (this.sdkState === SDK_STATES.Disconnected || this.sdkState === SDK_STATES.Connected) {
          const wallet = await sdk.connect();
          if (!wallet) {
            return;
          }
        }

        await sdk.sendTransaction();
      } catch (error) {
        this.errorMessage = this.resolveErrorMessage(error);
      }
    },

    async disconnectWallet() {
      const sdk = this.getSdk();
      if (!sdk) {
        return;
      }

      await sdk.disconnect();
      this.walletName = null;
      this.errorMessage = null;
    },
  },
};

export default WalletConnect;
</script>

<template>
  <div v-if="hasToken && (supported || isInitializing)" class="wallet-connect">
    <button
      v-if="showButton"
      type="button"
      class="wallet-action-btn"
      :disabled="isButtonDisabled"
      @click="useWallet"
    >
      <SvgWalletConnect />
      <span>Open in Wallet</span>
    </button>

    <span v-if="isInitializing" class="wallet-status">Loading wallet connection...</span>

    <div v-if="showConnectedWallet" class="connected-wallet">
      <small>Connected: {{ walletName }}</small>
      <button
        type="button"
        class="disconnect-btn"
        title="Disconnect"
        @click="disconnectWallet"
      >
        ×
      </button>
    </div>

    <span v-if="errorMessage" class="wallet-status error">{{ errorMessage }}</span>
    <span v-if="showAwaitingSignature" class="wallet-status">Please sign transaction with your wallet.</span>
    <span v-if="showTxSuccess" class="wallet-status">Transaction sent successfully. Waiting for confirmation.</span>
  </div>
</template>
