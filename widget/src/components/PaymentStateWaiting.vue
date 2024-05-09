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
  methods: {
    formatCurrencyName,
    toggleQRCode() {
      this.isBackupQR = !this.isBackupQR;
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
  },
  computed: {
    qrImage() {
      return (this.isBackupQR ? this.qrAltImg : this.qrImg);
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
        class="forumpay-pgw-payment-qr"
        :src="qrImage"
        alt="qr"
      >

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
          <Copy :value="amount" />
        </div>
      </div>
      <div>
        <span class="forumpay-pgw-payment-amount-label">Address</span>
        <div class="forumpay-pgw-payment-amount-field forumpay-pgw-payment-amount-field--address">
          <span>{{ address }}</span>
          <Copy :value="address" />
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
        <ul v-if="isTransactionInfoVisible">
          <li>Payment ID: <span>{{ invoiceNo }}</span></li>
          <li>
            Order Amount:
            <span>{{ invoiceAmount }} {{ formatCurrencyName(invoiceCurrency) }}</span>
          </li>
          <li>
            Exchange Rate: <span>{{ rate }}/{{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li>
            Exchange Amount:
            <span>{{ amountExchange }} {{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li>
            Network Cost:
            <span>{{ networkProcessingFee }} {{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li>Total to Send: <span>{{ amount }}</span></li>
        </ul>
      </div>
    </Container>
  </div>
</template>
