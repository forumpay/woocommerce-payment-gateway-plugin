<script setup>

import Container from './Container.vue';
import formatCurrencyName from '../utils/formatCurrency';

defineProps({
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
  payment: {
    type: String,
    default: '',
  },
  amountCurrency: {
    type: String,
    default: '',
  },
});

</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center">
    <Container class="forumpay-pgw-payment_status">
      <div class="forumpay-pgw-payment_status-row">
        <span class="forumpay-pgw-payment_status-row-status">Transaction status</span>
        <small>
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
            Amount Requested: <span>{{ amount }} {{ formatCurrencyName(amountCurrency) }}</span>
          </li>
          <li>Amount Sent: <span>{{ payment }} {{ formatCurrencyName(amountCurrency) }}</span></li>
        </ul>
      </div>
      <div class="forumpay-pgw-payment_status-row">
        <p class="forumpay-pgw-payment_status-row-text">
          {{ status }}
        </p>
      </div>
    </Container>
  </div>
</template>
