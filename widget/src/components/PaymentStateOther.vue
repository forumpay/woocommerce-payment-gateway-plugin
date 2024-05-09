<script>

import Container from './Container.vue';
import formatCurrencyName from '../utils/formatCurrency';

const PaymentStateOther = {
  components: {
    Container,
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
  },
  methods: {
    formatCurrencyName,
  },
};

export default PaymentStateOther;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center">
    <Container
      :alert="confirmed ? 'success' : 'danger'"
      :message="status"
      :message-class="'forumpay-pgw-container-state-' + state"
    />
    <Container>
      <ul class="forumpay-pgw-payment_status-list">
        <li>Total: <span>{{ amount }} {{ formatCurrencyName(amountCurrency) }}</span></li>
        <li>Payment: <span>{{ payment }} {{ formatCurrencyName(amountCurrency) }}</span></li>
        <li>State: <span>{{ state }}</span></li>
      </ul>
    </Container>
  </div>
</template>
