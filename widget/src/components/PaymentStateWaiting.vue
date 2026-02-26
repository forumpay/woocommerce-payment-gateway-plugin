<script>
import Container from './Container.vue';
import Copy from './Copy.vue';
import CurrencyIcon from './CurrencyIcon.vue';
import PaymentCountdown from './PaymentCountdown.vue';
import PageLogo from './PageLogo.vue';
import QrCodeAlternatives from './QrCodeAlternatives.vue';
import SvgInfo from '../images/SvgInfo.vue';
import SvgHelp from '../images/SvgHelp.vue';
import SvgClose from '../images/SvgClose.vue';
import SvgChevronDown from '../images/SvgChevronDown.vue';
import formatCurrencyName from '../utils/formatCurrency';
import getNetworkDisplayName from '../utils/getNetworkDisplayName';
import shortenAddress from '../utils/shortenAddress';

const PaymentStateWaiting = {
  components: {
    Container,
    Copy,
    CurrencyIcon,
    PaymentCountdown,
    QrCodeAlternatives,
    SvgInfo,
    SvgHelp,
    SvgClose,
    SvgChevronDown,
    PageLogo,
  },
  props: {
    loading: Boolean,
    refreshing: Boolean,
    currency: {
      type: Object,
      default() {
        return { currency: '' };
      },
    },
    status: {
      type: String,
      default: '',
    },
    inserted: {
      type: String,
      default: '',
    },
    state: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: '',
    },
    waitTime: {
      type: String,
      default: '',
    },
    amount: {
      type: String,
      default: '',
    },
    amountCurrency: {
      type: String,
      default: '',
    },
    payment: {
      type: String,
      default: '',
    },
    address: {
      type: String,
      default: '',
    },
    qr: {
      type: String,
      default: '',
    },
    qrImg: {
      type: String,
      default: '',
    },
    qrAlt: {
      type: String,
      default: '',
    },
    qrAltImg: {
      type: String,
      default: '',
    },
    notices: {
      type: Array,
      default() {
        return [];
      },
    },
    beneficiaryVaspDetails: {
      type: [Object, null],
      default: null,
    },
    minConfirmations: {
      type: Number,
      default: 0,
    },
    fastTransactionFee: {
      type: String,
      default: '',
    },
    fastTransactionFeeCurrency: {
      type: String,
      default: '',
    },
    invoiceNo: {
      type: String,
      default: '',
    },
    invoiceAmount: {
      type: String,
      default: '',
    },
    invoiceCurrency: {
      type: String,
      default: '',
    },
    rate: {
      type: String,
      default: '',
    },
    amountExchange: {
      type: String,
      default: '',
    },
    networkProcessingFee: {
      type: String,
      default: '',
    },
    itemName: {
      type: String,
      default: '',
    },
    invoiceSurchargeAmount: {
      type: String,
      default: '',
    },
    invoiceAmountWithSurcharge: {
      type: String,
      default: '',
    },
  },
  emits: ['cancel-payment', 'regenerate-qr'],
  data() {
    return {
      isBackupQR: false,
      isBeneficiaryDetailsVisible: false,
      isPaymentDetailsVisible: false,
      isQrAlternativesModalVisible: false,
    };
  },
  computed: {
    formattedCurrency() {
      // Use network display name format: "USDT on Tron" instead of "USDT (TRC-20)"
      return getNetworkDisplayName(this.currency);
    },
    shortenedAddress() {
      return shortenAddress(this.address);
    },
    gasFeeTooHigh() {
      return this.fastTransactionFee && parseFloat(this.fastTransactionFee) > 0;
    },
    exposedNotices() {
      if (!this.notices || this.notices.length === 0) {
        return [];
      }
      const exposedNoticeCodes = [
        'useEthErc20',
        'useTrxTrc20',
        'useEth',
        'usePolygonErc20',
        'usePolygonPol',
        'polygonUSDCvsUSDCE',
        'polygonUseCorrectUSDC',
        'useSolSPL',
      ];
      return this.notices.filter((notice) => exposedNoticeCodes.includes(notice.code));
    },
  },
  methods: {
    formatCurrencyName,
    toggleBackupQR() {
      this.isBackupQR = !this.isBackupQR;
    },
    toggleBeneficiaryDetails() {
      this.isBeneficiaryDetailsVisible = !this.isBeneficiaryDetailsVisible;
    },
    togglePaymentDetails() {
      this.isPaymentDetailsVisible = !this.isPaymentDetailsVisible;
    },
    handleCancelPayment(event) {
      if (event) {
        event.preventDefault();
      }
      this.$emit('cancel-payment');
    },
    openQrAlternativesModal() {
      this.isQrAlternativesModalVisible = true;
    },
    closeQrAlternativesModal() {
      this.isQrAlternativesModalVisible = false;
    },
    onSelectBasicQr() {
      this.isBackupQR = true;
      this.closeQrAlternativesModal();
    },
    onSelectWalletQr(wallet) {
      this.$emit('regenerate-qr', wallet);
      this.closeQrAlternativesModal();
    },
  },
};

export default PaymentStateWaiting;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center" style="padding: 0 24px;">
    <Container>
      <!-- Top amount -->
      <div style="font-size: 16px; font-weight: 700; text-align: right; margin-bottom: 16px;">
        {{ invoiceAmount }} {{ invoiceCurrency }}
      </div>

      <!-- Payment ID -->
      <div class="payment-id-section">
        <span class="payment-id-label">Payment ID</span>
        <div class="payment-id-row">
          <span class="payment-id-value">{{ invoiceNo }}</span>
          <Copy style="width: 18px;height:18px;" :value="invoiceNo" />
        </div>
      </div>

      <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

      <!-- Currency header with timer -->
      <div style="width: 100%; display: inline-flex; justify-content: space-between; align-items: center; gap: 16px;">
        <div style="display: inline-flex; gap: 8px; align-items: center; width: 100%;">
          <CurrencyIcon :currency="currency" style="height: 25px;" />
          <span class="text-heading">Pay with {{ formattedCurrency }}</span>
        </div>
        <PaymentCountdown :status="status" />
      </div>

      <!-- Exposed notices (network warnings) -->
      <div v-for="notice in exposedNotices" :key="notice.code" class="notice-message" style="margin-top: 12px;">
        {{ notice.message }}
      </div>

      <div class="start-payment-details">
        <!-- Payment details group with info icon, amount, and copy -->
        <div v-click-outside="() => isPaymentDetailsVisible = false" class="payment-details-group">
          <div
            role="button"
            tabindex="0"
            style="cursor: pointer; width: 40px;"
            @click="togglePaymentDetails"
            @keyup.enter="togglePaymentDetails"
          >
            <SvgInfo style="width: 20px; height: 20px;" />
          </div>

          <!-- Transaction details popup -->
          <div v-if="isPaymentDetailsVisible" class="payment-details-overview">
            <div class="payment-details-header">
              <span class="payment-details-title">Transaction details</span>
              <SvgClose
                role="button"
                tabindex="0"
                style="cursor: pointer; width: 16px; height: 16px;"
                @click="isPaymentDetailsVisible = false"
                @keyup.enter="isPaymentDetailsVisible = false"
              />
            </div>
            <div v-if="itemName">
              <span>Item:</span>
              <span>{{ itemName }}</span>
            </div>
            <div v-if="invoiceSurchargeAmount">
              <span>Processing Fee:</span>
              <span>{{ invoiceSurchargeAmount }} {{ invoiceCurrency }}</span>
            </div>
            <div>
              <span>Total Amount:</span>
              <span>{{ invoiceAmountWithSurcharge || invoiceAmount }} {{ invoiceCurrency }}</span>
            </div>
            <div>
              <span>Exchange Rate:</span>
              <span>{{ rate }} {{ invoiceCurrency }}/{{ formatCurrencyName(amountCurrency) }}</span>
            </div>
            <div>
              <span>Exchange Amount:</span>
              <span>{{ amountExchange }} {{ formatCurrencyName(amountCurrency) }}</span>
            </div>
            <div>
              <span>Network Cost:</span>
              <span>{{ networkProcessingFee }} {{ formatCurrencyName(amountCurrency) }}</span>
            </div>
            <div>
              <span>Total to Send:</span>
              <span>{{ amount }} {{ formatCurrencyName(amountCurrency) }}</span>
            </div>
          </div>

          <span class="payment-amount-holder">{{ amount }} {{ formatCurrencyName(amountCurrency) }}</span>
          <div style="width: 40px;">
            <Copy :value="amount" />
          </div>
        </div>

        <!-- QR Code -->
        <div class="qr">
          <div>
            <img v-if="!isBackupQR" :src="qrImg" alt="QR Code">
            <img v-else :src="qrAltImg" alt="Alternative QR Code">
          </div>

          <span
            v-if="qrAlt"
            role="button"
            tabindex="0"
            class="qr-code-help"
            @click="openQrAlternativesModal"
            @keyup.enter="openQrAlternativesModal"
          >
            Try alternative QR code.
          </span>
        </div>

        <QrCodeAlternatives
          :visible="isQrAlternativesModalVisible"
          :qr-alt-img="qrAltImg"
          @close="closeQrAlternativesModal"
          @select-wallet-qr="onSelectWalletQr"
          @select-basic-qr="onSelectBasicQr"
        />

        <!-- Wallet Address -->
        <div v-if="address">
          <div class="payment-details-group">
            <span
              class="payment-amount-holder"
              style="border: none; outline:none; width: 100%;border-top-right-radius: 0;border-bottom-right-radius: 0;min-height:unset;"
              type="text"
              :value="address"
            >
              {{ shortenedAddress }}
            </span>
            <div style="width: 40px;">
              <Copy :value="address" />
            </div>
          </div>
        </div>
      </div>

      <!-- Gas fee / Wait time notice -->
      <div v-if="(fastTransactionFee && fastTransactionFee !== '' && fastTransactionFee !== '0') || waitTime" style="margin: 16px 0; display: flex; flex-direction: column;">
        <div v-if="fastTransactionFee && fastTransactionFee !== '' && fastTransactionFee !== '0'" class="payment-help-container">
          <span class="wlx-widget-help-text" style="text-align:center;">
            <span v-if="minConfirmations > 0">
              Set your wallet TX fee to at least:
            </span>
            <span v-else>For instant approval set tx fees to:</span>
            <span class="highlight">
              HIGH
            </span>
          </span>

          <div class="payment-help">
            <SvgHelp style="width: 20px; height: 20px;" class="help-icon" />
            <Container class="payment-help-tooltip">
              <p>
                For instant approval ensure you are using fast fees or manually set to
                {{ fastTransactionFee }} {{ fastTransactionFeeCurrency }}.
              </p>
            </Container>
          </div>
        </div>
        <span v-if="waitTime" class="wlx-widget-help-text" style="text-align: center;">
          Expected time to confirm: {{ waitTime }}.
        </span>
      </div>

      <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

      <!-- Beneficiary Details -->
      <div v-if="beneficiaryVaspDetails">
        <div
          role="button"
          tabindex="0"
          class="beneficiary-details-toggle"
          :class="{ open: isBeneficiaryDetailsVisible }"
          @click="toggleBeneficiaryDetails"
          @keyup.enter="toggleBeneficiaryDetails"
        >
          <span class="payment-details-label">Beneficiary Details</span>
          <SvgChevronDown class="chevron-icon" />
        </div>

        <div v-if="isBeneficiaryDetailsVisible">
          <p class="wlx-widget-help-text" style="margin-top: 5px;margin-bottom:10px;">
            When depositing from another service, they may ask you for the following
            information about us.
          </p>
          <div>
            <span class="payment-details-label">Beneficiary Name</span>
            <div class="payment-details-field">
              <span style="font-size: 10px;">{{ beneficiaryVaspDetails.beneficiary_name }}</span>
              <Copy :value="beneficiaryVaspDetails.beneficiary_name" />
            </div>
          </div>
          <div>
            <span class="payment-details-label">Beneficiary VASP</span>
            <div class="payment-details-field">
              <span style="font-size: 10px;">{{ beneficiaryVaspDetails.beneficiary_vasp }}</span>
              <Copy :value="beneficiaryVaspDetails.beneficiary_vasp" />
            </div>
          </div>
          <div v-if="beneficiaryVaspDetails.beneficiary_vasp_did">
            <span class="payment-details-label">Beneficiary VASP DID</span>
            <div class="payment-details-field">
              <span style="font-size: 10px;">{{ beneficiaryVaspDetails.beneficiary_vasp_did }}</span>
              <Copy :value="beneficiaryVaspDetails.beneficiary_vasp_did" />
            </div>
          </div>
        </div>
      </div>

      <PageLogo />
    </Container>

    <!-- Cancel Payment Button -->
    <div>
      <button
        type="button"
        class="forumpay-pgw-button forumpay-pgw-button--cancel"
        style="margin: 32px auto 0 auto; width: 100%;width:fit-content;"
        @click="handleCancelPayment"
      >
        Cancel Payment
      </button>
    </div>
  </div>
</template>
