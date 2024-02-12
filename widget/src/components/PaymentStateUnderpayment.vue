<script setup>

import { computed, ref } from 'vue';

import Container from './Container.vue';
import Copy from './Copy.vue';
import CurrencyIcon from './CurrencyIcon.vue';
import SvgIconExclamationTriangle from '../images/SvgIconExclamationTriangle.vue';
import formatCurrencyName from '../utils/formatCurrency';

const props = defineProps({
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
});

const isBackupQR = ref(false);
const qrShown = ref(true);

const qrImage = computed(() => (isBackupQR.value ? props.qrAltImg : props.qrImg));

</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center forumpay-pgw-payment">
    <Container class="forumpay-pgw-underpayment">
      <div class="forumpay-pgw-underpayment-section">
        <span class="forumpay-pgw-underpayment-name">Transaction status</span>
        <small>Expected time to confirm: {{ waitTime }}</small>
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
            Amount Requested: <span>{{ amount }} {{ formatCurrencyName(currency.currency) }}</span>
          </li>
          <li>
            Amount Sent: <span>{{ payment }} {{ formatCurrencyName(currency.currency) }}</span>
          </li>
          <li>Amount Balance: <span class="forumpay-pgw-underpayment-emphasized">{{ missingAmount }} {{ formatCurrencyName(missingCurrency) }}</span></li>
        </ul>
      </div>
      <div class="forumpay-pgw-underpayment-section">
        <small>{{ status }}</small>
        <div>
          <small
            class="forumpay-pgw-underpayment-toggle-qr-visibility-text"
            role="button"
            tabIndex="0"
            @keyup="qrShown = !qrShown"
            @click="qrShown = !qrShown"
          >
            {{ qrShown ? "Hide QR Code &#709;" : "Show QR Code &#708;" }}
          </small>
          <div v-show="qrShown">
            <img
              class="forumpay-pgw-underpayment-qr-image"
              :src="qrImage"
              alt="qr"
            >
            <div class="forumpay-pgw-underpayment-backup-qr">
              <div v-if="!isBackupQR">
                <CurrencyIcon :currency="currency" />
                <span>If this QR is not populating your wallet, please use this one.</span>
                <button
                  type="button"
                  @click="isBackupQR = true"
                >
                  Backup QR
                </button>
              </div>
              <span
                v-else
                class="forumpay-pgw-underpayment-show-original-qr"
                role="button"
                tabIndex="0"
                @keyup="isBackupQR = false"
                @click="isBackupQR = false"
              >&lt; Go back to original QR</span>
            </div>
          </div>
        </div>
        <div class="forumpay-pgw-underpayment-details">
          <div>
            <span class="forumpay-pgw-underpayment-details-name">Amount</span>
            <div class="forumpay-pgw-underpayment-details-field">
              <span>{{ missingAmount }} {{ formatCurrencyName(missingCurrency) }}</span>
              <Copy :value="missingAmount" />
            </div>
          </div>
          <div>
            <span class="forumpay-pgw-underpayment-details-name">Address</span>
            <div class="forumpay-pgw-underpayment-details-field forumpay-pgw-underpayment-details-field--small">
              <span>{{ address }}</span>
              <Copy :value="address" />
            </div>
          </div>
        </div>
      </div>
    </Container>
  </div>
</template>
