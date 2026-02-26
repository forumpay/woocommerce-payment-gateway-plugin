<script>
import Container from './Container.vue';
import Copy from './Copy.vue';
import PageLogo from './PageLogo.vue';
import formatCurrencyName from '../utils/formatCurrency';

const PaymentStateOther = {
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
    state: {
      type: String,
      default: '',
    },
    confirmed: {
      type: Boolean,
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
  computed: {
    alertType() {
      if (this.confirmed) return 'success';
      if (this.state === 'cancelled' || this.state === 'blocked') return 'danger';
      return 'info';
    },
  },
  methods: {
    formatCurrencyName,
  },
};

export default PaymentStateOther;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center" style="padding: 0 24px;">
    <Container>
      <!-- Payment ID with copy button -->
      <div v-if="paymentId" class="payment-id-section">
        <span class="payment-id-label">Payment ID</span>
        <div class="payment-id-row">
          <span class="payment-id-value">{{ paymentId }}</span>
          <Copy :value="paymentId" />
        </div>
      </div>

      <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

      <!-- Status message with icon -->
      <div style="display: flex;box-sizing: border-box;">
        <Container :alert="alertType" :message="status" :message-class="'payment-state-' + state" />
      </div>

      <!-- Payment details -->
      <div class="payment-details-final">
        <div class="payment-detail-row">
          <span class="detail-label">Total:</span>
          <span class="detail-value">{{ invoiceAmount }} {{ formatCurrencyName(invoiceCurrency) }}</span>
        </div>
        <div class="payment-detail-row">
          <span class="detail-label">Payment received:</span>
          <span class="detail-value">
            {{ payment && payment !== '0' ? payment + ' ' + formatCurrencyName(amountCurrency) : '/' }}</span>
        </div>
      </div>

      <!-- Powered by ForumPay -->
      <PageLogo />
    </Container>
  </div>
</template>
