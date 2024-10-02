<script>
import Container from './Container.vue';
import Copy from './Copy.vue';
import PaymentStatusIcon from './PaymentStatusIcon.vue';
import CurrencyIcon from './CurrencyIcon.vue';
import PaymentStatus from './PaymentStatus.vue';
import Loader from './Loader.vue';
import SvgIconQr from '../images/SvgIconQr.vue';
import SvgIconNotice from '../images/SvgIconNotice.vue';
import formatCurrencyName from '../utils/formatCurrency';
import cryptoPaymentStatsHandler from '../utils/cryptoPaymentStatsHandler';

const PaymentStateWaiting = {
  components: {
    Container,
    Copy,
    PaymentStatusIcon,
    CurrencyIcon,
    PaymentStatus,
    Loader,
    SvgIconQr,
    SvgIconNotice,
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
  },
  data() {
    return {
      isBackupQR: false,
      isPaymentStatusVisible: false,
      isTransactionInfoVisible: false,
    };
  },
  mounted() {
    if (!this.isBackupQR) {
      cryptoPaymentStatsHandler.event('QRCodeInit');
    }
  },
  methods: {
    formatCurrencyName,
    toggleQRCode() {
      this.isBackupQR = !this.isBackupQR;
      if (this.isBackupQR) {
        cryptoPaymentStatsHandler.event('QRAltCodeInit');
      }
    },
    togglePaymentStatus() {
      this.isPaymentStatusVisible = !this.isPaymentStatusVisible;
    },
    toggleTransactionInfo() {
      this.isTransactionInfoVisible = !this.isTransactionInfoVisible;
    },
    handleCancelPayment(event) {
      if (event) {
        event.preventDefault();
      }
      this.$emit('cancel-payment');
    },
    handleAddressCopy() {
      cryptoPaymentStatsHandler.event('addressCopy');
    },
    handleAmountCopy() {
      cryptoPaymentStatsHandler.event('amountCopy');
    },
    onQRCodeLoad(success) {
      cryptoPaymentStatsHandler.event('QRCodeLoad', success);
    },
    onQRAltCodeLoad(success) {
      cryptoPaymentStatsHandler.event('QRAltCodeLoad', success);
    },
  },
};

export default PaymentStateWaiting;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center forumpay-pgw-payment">
    <p class="forumpay-pgw-guide-text">
      Use your camera to scan the QR code or
      copy and paste the amount and address below to your wallet
    </p>

    <Container class="forumpay-pgw-container--gap">
      <div class="forumpay-pgw-payment-header">
        <span class="forumpay-pgw-payment-header-circle">
          <SvgIconQr class="forumpay-pgw-payment-header-icon-qr" />
        </span>
        <div class="forumpay-pgw-payment-header-amount">
          <div class="forumpay-pgw-payment-header-amount-total">
            <span>{{ amount }} {{ formatCurrencyName(currency.currency) }}</span>
            <CurrencyIcon
              class="forumpay-pgw-payment-header-amount-total-icon"
              :currency="currency"
            />
          </div>
        </div>
        <PaymentStatusIcon
          :status="status"
          :inserted="inserted"
          @click="togglePaymentStatus"
        />
      </div>

      <hr>
      <PaymentStatus
        v-if="isPaymentStatusVisible"
        :wait-time="waitTime"
        :status="status"
        :inserted="inserted"
        :amount="amount"
        :amount-currency="amountCurrency"
      />

      <img
        v-if="!isBackupQR"
        id="qr"
        class="forumpay-pgw-payment-qr"
        :src="qrImg"
        alt="qr"
        @load="onQRCodeLoad(true)"
        @error="onQRCodeLoad(false)"
      >
      <img
        v-else
        id="qralt"
        class="forumpay-pgw-payment-qr"
        :src="qrAltImg"
        alt="alt-qr"
        @load="onQRAltCodeLoad(true)"
        @error="onQRAltCodeLoad(false)"
      />

      <div
        v-if="!isBackupQR"
        class="forumpay-pgw-payment-backup-qr"
      >
        <CurrencyIcon :currency="currency" />
        <span class="forumpay-pgw-payment-backup-qr-help-text">If this QR is not populating your wallet, please use this one.</span>
        <button
          type="button"
          @click="toggleQRCode"
        >
          Backup QR
        </button>
      </div>
      <div
        v-else
        class="forumpay-pgw-payment-return-qr"
      >
        <CurrencyIcon :currency="currency" />
        <span
          class="forumpay-pgw-payment-backup-qr-help-text forumpay-pgw-payment-backup-qr-help-text--pointer"
          role="button"
          tabIndex="0"
          @keyup="toggleQRCode"
          @click="toggleQRCode"
        >
          <i
            class="fa fa-angle-left"
            aria-hidden="true"
          />
          Go back to original QR
        </span>
      </div>

      <div
        v-for="notice in notices"
        :key="notice.message"
        class="forumpay-pgw-payment-notice"
      >
        <SvgIconNotice />
        {{ notice.message }}
      </div>

      <div class="forumpay-pgw-payment-link-wrapper">
        <button
          class="forumpay-pgw-button forumpay-pgw-button--cancel"
          type="button"
          @click.stop="handleCancelPayment"
        >
          Cancel payment
        </button>
      </div>

      <Loader
        :loading="!loading && refreshing"
        :small="true"
      />
    </Container>

    <Container class="forumpay-pgw-payment-wrapper">
      <div v-if="fastTransactionFee" class="forumpay-pgw-payment-details">
        <span class="forumpay-pgw-payment-details-text">
          {{ minConfirmations > 0 ? "Set your wallet TX fee to at least:" : "For instant approval set tx fees to" }}
          {{ fastTransactionFee }}
          {{ fastTransactionFeeCurrency }}
        </span>

        <div class="forumpay-pgw-payment-details-info">
          <CurrencyIcon icon="help" />
          <Container class="forumpay-pgw-payment-details-info-tooltip">
            <p>
              For instant approval ensure you are using fast fees or manually set to this amount.
            </p>
          </Container>
        </div>
      </div>

      <div class="forumpay-pgw-payment-amount">
        <span class="forumpay-pgw-payment-amount-label">Amount</span>
        <div class="forumpay-pgw-payment-amount-field">
          <span>{{ amount }} {{ formatCurrencyName(currency.currency) }}</span>
          <Copy :value="amount" :on-copy="handleAmountCopy" />
        </div>
      </div>
      <div>
        <span class="forumpay-pgw-payment-amount-label">Address</span>
        <div class="forumpay-pgw-payment-amount-field forumpay-pgw-payment-amount-field--address">
          <span>{{ address }}</span>
          <Copy :value="address" :on-copy="handleAddressCopy" />
        </div>
      </div>

      <span class="forumpay-pgw-payment-confirm">Expected time to confirm: {{ waitTime }}</span>

      <div
        class="forumpay-pgw-payment-transaction"
        role="button"
        tabIndex="0"
        @keyup="toggleTransactionInfo"
        @click="toggleTransactionInfo"
      >
        <span>Transaction details</span>
      </div>

      <div class="forumpay-pgw-payment-transaction-content">
        <ul v-if="isTransactionInfoVisible" class="forumpay-pgw-payment-transaction-content-ul">
          <li class="forumpay-pgw-payment-transaction-content-li">
            <span class="forumpay-pgw-payment-transaction-content-li__title-font">Payment ID:</span>
            <span class="forumpay-pgw-payment-transaction-content-li__data-font">{{ invoiceNo }}</span>
          </li>
          <li class="forumpay-pgw-payment-transaction-content-li">
            <span class="forumpay-pgw-payment-transaction-content-li__title-font">Order Amount:</span>
            <span class="forumpay-pgw-payment-transaction-content-li__data-font">{{ invoiceAmount }} {{ formatCurrencyName(invoiceCurrency) }}</span>
          </li>
          <li class="forumpay-pgw-payment-transaction-content-li">
            <span class="forumpay-pgw-payment-transaction-content-li__title-font">Exchange Rate:</span>
            <span class="forumpay-pgw-payment-transaction-content-li__data-font">{{ rate }}/{{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li class="forumpay-pgw-payment-transaction-content-li">
            <span class="forumpay-pgw-payment-transaction-content-li__title-font">Exchange Amount:</span>
            <span class="forumpay-pgw-payment-transaction-content-li__data-font">{{ amountExchange }} {{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li class="forumpay-pgw-payment-transaction-content-li">
            <span class="forumpay-pgw-payment-transaction-content-li__title-font">Network Cost:</span>
            <span class="forumpay-pgw-payment-transaction-content-li__data-font">{{ networkProcessingFee }} {{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li class="forumpay-pgw-payment-transaction-content-li">
            <span class="forumpay-pgw-payment-transaction-content-li__title-font">Total to Send:</span>
            <span class="forumpay-pgw-payment-transaction-content-li__data-font">{{ amount }}</span>
          </li>
        </ul>
      </div>
    </Container>
  </div>
</template>
