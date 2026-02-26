<script>
import SvgChevronDown from '../images/SvgChevronDown.vue';
import Dropdown from './Dropdown.vue';

const QrCodeAlternatives = {
  components: {
    SvgChevronDown,
    Dropdown,
  },
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    qrAltImg: {
      type: String,
      default: '',
    },
  },
  emits: ['close', 'select-wallet-qr', 'select-basic-qr'],
  data() {
    return {
      enableClickOutside: false,
      isSelectWalletOpen: false,
      isBasicQrOpen: false,
      selectedWallet: null,
      walletOptions: [],
      isLoadingWallets: false,
    };
  },
  watch: {
    async visible(newVal) {
      if (newVal) {
        setTimeout(() => {
          this.enableClickOutside = true;
        }, 100);
        await this.fetchWalletApps();
      } else {
        this.enableClickOutside = false;
        this.isSelectWalletOpen = false;
        this.isBasicQrOpen = false;
      }
    },
  },
  computed: {
    selectedWalletLabel() {
      if (!this.selectedWallet) {
        return this.walletOptions.length > 0 ? this.walletOptions[0].name : 'Generic wallet';
      }
      const wallet = this.walletOptions.find((w) => w.id === this.selectedWallet);
      return wallet ? wallet.name : 'Generic wallet';
    },
  },
  methods: {
    async fetchWalletApps() {
      this.isLoadingWallets = true;
      try {
        const wallets = await this.$store.dispatch('getWalletApps');
        this.walletOptions = wallets;
        if (wallets.length > 0) {
          this.selectedWallet = wallets[0].id;
        }
      } catch (error) {
        console.error('Failed to fetch wallet apps:', error);
        this.walletOptions = [];
      }
      this.isLoadingWallets = false;
    },
    onClose() {
      this.$emit('close');
    },
    toggleSelectWallet() {
      this.isSelectWalletOpen = !this.isSelectWalletOpen;
      if (this.isSelectWalletOpen) {
        this.isBasicQrOpen = false;
      }
    },
    toggleBasicQr() {
      this.isBasicQrOpen = !this.isBasicQrOpen;
      if (this.isBasicQrOpen) {
        this.isSelectWalletOpen = false;
      }
    },
    onSwitchToWalletQr() {
      this.$emit('select-wallet-qr', this.selectedWallet);
      this.onClose();
    },
    onSelectBasicQr() {
      this.$emit('select-basic-qr');
      this.onClose();
    },
  },
};

export default QrCodeAlternatives;
</script>

<template>
  <div v-if="visible" class="qr-alternatives-overlay">
    <div
      v-click-outside="enableClickOutside ? onClose : () => {}"
      class="qr-alternatives-modal"
    >
      <div class="qr-alternatives-header">
        <h3 class="qr-alternatives-title">
          QR Code Alternatives
        </h3>
        <a
          class="qr-alternatives-close"
          role="button"
          tabindex="0"
          @click="onClose"
          @keyup.enter="onClose"
        >
          &times;
        </a>
      </div>

      <p class="qr-alternatives-description">
        A QR code usually contains a wallet address, amount, and currency.
        Whether it can read these details from the QR code
        depends on the crypto wallet you are using.
        Some crypto wallets use different QR code formats than others.
      </p>

      <div class="qr-alternatives-options-label">
        Options:
      </div>

      <!-- Select your wallet section -->
      <div class="qr-alternatives-section">
        <div
          role="button"
          tabindex="0"
          class="qr-alternatives-section-header"
          :class="{ open: isSelectWalletOpen }"
          @click="toggleSelectWallet"
          @keyup.enter="toggleSelectWallet"
        >
          <span>Select your wallet</span>
          <SvgChevronDown class="chevron-icon" />
        </div>

        <div v-if="isSelectWalletOpen" class="qr-alternatives-section-content">
          <p class="qr-alternatives-section-description">
            In case of problems reading generic QR code with your app
            you can choose QR code designed for specific app.
          </p>

          <div v-if="isLoadingWallets" class="qr-alternatives-loading">
            Loading wallets...
          </div>

          <Dropdown
            v-else-if="walletOptions.length > 0"
            v-model="selectedWallet"
            :options="walletOptions"
            filter-property="name"
            option-key="id"
            class="qr-alternatives-dropdown"
          >
            <template #selected="{ selected }">
              <img
                v-if="selected.image"
                :src="selected.image"
                :alt="selected.name"
                class="wallet-icon"
              >
              <span>{{ selected.name }}</span>
            </template>
            <template #option="{ option, markText }">
              <img
                v-if="option.image"
                :src="option.image"
                :alt="option.name"
                class="wallet-icon"
              >
              <span v-html="markText(option.name)" />
            </template>
          </Dropdown>

          <div v-else class="qr-alternatives-no-wallets">
            No wallet apps available
          </div>

          <button
            type="button"
            class="qr-alternatives-button"
            :disabled="!selectedWallet || isLoadingWallets"
            @click="onSwitchToWalletQr"
          >
            Switch to QR code for selected wallet
          </button>
        </div>
      </div>

      <!-- Use basic QR code section -->
      <div class="qr-alternatives-section">
        <div
          role="button"
          tabindex="0"
          class="qr-alternatives-section-header"
          :class="{ open: isBasicQrOpen }"
          @click="toggleBasicQr"
          @keyup.enter="toggleBasicQr"
        >
          <span>Use basic QR code with address only</span>
          <SvgChevronDown class="chevron-icon" />
        </div>

        <div v-if="isBasicQrOpen" class="qr-alternatives-section-content">
          <p class="qr-alternatives-section-description">
            If you also have problems using a QR code designed for your app,
            or if your wallet app is not on the list,
            you can switch to a basic QR code that provides
            only the wallet address. Then enter the currency and amount manually.
          </p>

          <button
            type="button"
            class="qr-alternatives-button"
            @click="onSelectBasicQr"
          >
            Switch to basic QR code
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
