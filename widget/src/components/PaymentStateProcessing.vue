<script>

import Container from './Container.vue';
import Copy from './Copy.vue';
import formatCurrencyName from '../utils/formatCurrency';
import PageLogo from './PageLogo.vue';

const PaymentStateProcessing = {
  components: {
    Container,
    Copy,
    PageLogo,
  },
  props: {
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
    originalAmount: {
      type: String,
      default: '',
    },
    payment: {
      type: String,
      default: '',
    },
    amountCurrency: {
      type: String,
      default: '',
    },
    paymentId: {
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
  methods: {
    formatCurrencyName,
  },
};

export default PaymentStateProcessing;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center" style="padding: 0 24px;">
    <Container class="forumpay-pgw-payment_status">
      <!-- Top amount -->
      <div v-if="invoiceAmount" style="margin-left:auto;font-size: 16px; font-weight: 700; text-align: right; margin-bottom: 16px;">
        {{ invoiceAmount }} {{ invoiceCurrency }}
      </div>

      <!-- Payment ID -->
      <div v-if="paymentId" class="payment-id-section">
        <span class="payment-id-label">Payment ID</span>
        <div class="payment-id-row">
          <span class="payment-id-value">{{ paymentId }}</span>
          <Copy :value="paymentId" />
        </div>
      </div>

      <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

      <div class="forumpay-pgw-payment_status-row">
        <span class="forumpay-pgw-payment_status-row-status">Transaction status</span>
        <small v-if="waitTime">
          Expected time to confirm: {{ waitTime }}
        </small>
      </div>
      <div class="forumpay-pgw-payment_status-row">
        <div class="forumpay-pgw-payment_status-row-processing-label">
          <span>Transaction processing!</span>
        </div>
      </div>
      <div class="forumpay-pgw-payment_status-row">
        <ul class="forumpay-pgw-payment_status-list">
          <li>
            Amount Requested:
            <span>
              {{ originalAmount || amount }} {{ formatCurrencyName(amountCurrency) }}
            </span>
          </li>
          <li>
            Amount Sent: <span>{{ payment && payment !== '0' ? payment + ' ' + formatCurrencyName(amountCurrency) : '/' }}</span>
          </li>
          <li v-if="originalAmount && originalAmount !== amount">
            Amount Accepted: <span>{{ amount }} {{ formatCurrencyName(amountCurrency) }}</span>
          </li>
        </ul>
      </div>
      <div class="forumpay-pgw-payment_status-row">
        <p class="forumpay-pgw-payment_status-row-text">
          {{ status }}
        </p>
      </div>
      <PageLogo />
    </Container>
  </div>
</template>
