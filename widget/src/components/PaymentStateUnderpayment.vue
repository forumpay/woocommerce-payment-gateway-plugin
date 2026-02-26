<script>
import Container from './Container.vue';
import Copy from './Copy.vue';
import CurrencyIcon from './CurrencyIcon.vue';
import PaymentCountdown from './PaymentCountdown.vue';
import SvgIconExclamationTriangle from '../images/SvgIconExclamationTriangle.vue';
import SvgChevronDown from '../images/SvgChevronDown.vue';
import cryptoPaymentStatsHandler from '../utils/cryptoPaymentStatsHandler';
import formatCurrencyName from '../utils/formatCurrency';
import getNetworkDisplayName from '../utils/getNetworkDisplayName';

const PaymentStateUnderpayment = {
  components: {
    Container,
    Copy,
    CurrencyIcon,
    PaymentCountdown,
    SvgIconExclamationTriangle,
    SvgChevronDown,
  },
  props: {
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
    state: {
      type: String,
      default: '',
    },
    confirmed: {
      type: Boolean,
      default: false,
    },
    invoiceNo: {
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
    missingAmount: {
      type: String,
      default: '',
    },
    missingCurrency: {
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
    invoiceAmount: {
      type: String,
      default: '',
    },
    invoiceCurrency: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      isBackupQR: false,
      qrShown: true,
    };
  },
  computed: {
    underpaymentAddress() {
      if (!this.address) return '';
      const [address] = this.address.split(':');
      return address;
    },
    destinationTag() {
      if (this.address && this.address.includes(':')) {
        const [, tag] = this.address.split(':');
        return tag;
      }
      return null;
    },
  },
  mounted() {
    if (!this.isBackupQR) {
      cryptoPaymentStatsHandler.event('QRCodeInit');
    }
  },
  methods: {
    formatCurrencyName,
    getNetworkDisplayName,
    toggleQRCode() {
      this.isBackupQR = !this.isBackupQR;
      if (this.isBackupQR) {
        cryptoPaymentStatsHandler.event('QRAltCodeInit');
      }
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

export default PaymentStateUnderpayment;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center forumpay-pgw-payment">
    <Container class="forumpay-pgw-underpayment">
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

      <div class="forumpay-pgw-underpayment-section">
        <div style="width: 100%; display: inline-flex; flex-direction: row; justify-content: space-between; align-items: center; gap: 1em; padding-top: 0;">
          <div style="display: inline-flex; gap: 0.5em; align-items: center; width: 100%;">
            <CurrencyIcon :currency="currency" style="height: 25px;" />
            <span class="text-heading">Pay with {{ formatCurrencyName(currency.currency) }}</span>
          </div>
          <PaymentCountdown v-if="waitTime" :status="status" />
        </div>
      </div>
      <div class="forumpay-pgw-underpayment-section">
        <div class="forumpay-pgw-underpayment-not-enough-crypto">
          <SvgIconExclamationTriangle class="forumpay-pgw-underpayment-fa" />
          <span>NOT ENOUGH CRYPTO SENT!</span>
        </div>
      </div>
      <div class="forumpay-pgw-underpayment-section">
        <ul class="forumpay-pgw-underpayment-amounts-list">
          <li>
            Amount Needed: <span>{{ amount }} {{ formatCurrencyName(currency.currency) }}</span>
          </li>
          <li>
            Amount Sent: <span>{{ payment }} {{ formatCurrencyName(currency.currency) }}</span>
          </li>
          <li>Amount Missing: <span class="emphasized">{{ missingAmount }} {{ formatCurrencyName(missingCurrency) }}</span></li>
        </ul>
      </div>
      <div class="forumpay-pgw-underpayment-section">
        <!-- <small>{{ status }}</small> -->
        <small>
          You have sent less {{ formatCurrencyName(currency.currency) }}
          than was required for this transaction.
          You have{{ waitTime && waitTime !== 'Unknown' ? ` ${waitTime}` : '' }} to send the remaining balance
          or your transaction will be cancelled.
        </small>
        <div>
          <small
            class="forumpay-pgw-underpayment-toggle-qr-visibility-text"
            role="button"
            tabIndex="0"
            @keyup="qrShown = !qrShown"
            @click="qrShown = !qrShown"
          >
            {{ qrShown ? "Hide QR Code" : "Show QR Code" }}
            <SvgChevronDown
              :style="{
                transform: qrShown ? 'rotate(180deg)' : 'rotate(0deg)', display: 'inline-block', width: '12px', height: '12px', marginLeft: '4px',
              }"
            />
          </small>
          <div v-show="qrShown">
            <img
              id="qr"
              class="forumpay-pgw-underpayment-qr-image"
              :src="isBackupQR ? qrAltImg : qrImg"
              :alt="isBackupQR ? 'alt-qr' : 'qr'"
              @load="isBackupQR ? onQRAltCodeLoad(true) : onQRCodeLoad(true)"
              @error="isBackupQR ? onQRAltCodeLoad(false) : onQRCodeLoad(false)"
            >
            <div class="forumpay-pgw-underpayment-backup-qr">
              <div v-if="!isBackupQR">
                <CurrencyIcon :currency="currency" style="width: 20px; height: 20px; margin-right: 10px;" />
                <span>If this QR is not populating your wallet, please use this one.</span>
                <button
                  type="button"
                  @click="toggleQRCode"
                >
                  Backup QR
                </button>
              </div>
              <span
                v-else
                class="forumpay-pgw-underpayment-show-original-qr"
                role="button"
                tabIndex="0"
                @keyup="toggleQRCode"
                @click="toggleQRCode"
              >
                <SvgChevronDown
                  :style="{
                    transform: 'rotate(90deg)', display: 'inline-block', width: '12px', height: '12px', marginRight: '4px',
                  }"
                />
                Go back to original QR
              </span>
            </div>
          </div>
        </div>
        <div class="forumpay-pgw-underpayment-details">
          <div>
            <span class="forumpay-pgw-underpayment-details-name">Amount</span>
            <div class="forumpay-pgw-underpayment-details-field">
              <span>{{ missingAmount }} {{ formatCurrencyName(missingCurrency) }}</span>
              <Copy :value="missingAmount" :on-copy="handleAmountCopy" />
            </div>
          </div>
          <div>
            <span class="forumpay-pgw-underpayment-details-name">Address</span>
            <div class="forumpay-pgw-underpayment-details-field forumpay-pgw-underpayment-details-field--small">
              <span>{{ underpaymentAddress }}</span>
              <Copy :value="address" :on-copy="handleAddressCopy" />
            </div>
          </div>
          <div v-if="destinationTag">
            <span class="forumpay-pgw-underpayment-details-name">Destination tag</span>
            <div class="forumpay-pgw-underpayment-details-field forumpay-pgw-underpayment-details-field--small">
              <span>{{ destinationTag }}</span>
              <Copy :value="destinationTag" />
            </div>
          </div>
        </div>
        <div v-if="waitTime && waitTime !== 'Unknown'" style="margin-top: 16px;">
          <small>Expected time to confirm: {{ waitTime }}.</small>
        </div>
      </div>
    </Container>
  </div>
</template>
