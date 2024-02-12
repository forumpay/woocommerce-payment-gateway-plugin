<script setup>

import { computed } from 'vue';

import { parseTransactionStatus } from '../utils/time';
import SvgIconClock from '../images/SvgIconClock.vue';
import formatCurrencyName from '../utils/formatCurrency';

const props = defineProps({
  waitTime: {
    type: String,
    default: '',
  },
  status: {
    type: String,
    default: '',
  },
  inserted: {
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
});

const paymentTimer = computed(() => parseTransactionStatus(props.status, props.inserted));

</script>

<template>
  <div class="forumpay-pgw-payment_status">
    <div class="forumpay-pgw-payment_status-row">
      <span class="forumpay-pgw-payment_status-row-tag">Transaction status</span>
      <small>Expected time to confirm: {{ waitTime }}</small>
    </div>
    <div class="forumpay-pgw-payment_status-row forumpay-pgw-payment_status-timer">
      <div class="forumpay-pgw-payment_status-timer-container">
        <span class="forumpay-pgw-payment_status-timer-container-icon">
          <SvgIconClock />
        </span>
        <span class="forumpay-pgw-payment_status-timer-container-remaining_time">{{ paymentTimer.time }}</span>
      </div>
      <small>Remaining Time</small>
    </div>
    <div class="forumpay-pgw-payment_status-row">
      <ul class="forumpay-pgw-payment_status-list">
        <li>
          Amount Requested: <span>{{ amount }} {{ formatCurrencyName(amountCurrency) }}</span>
        </li>
      </ul>
    </div>
    <small class="forumpay-pgw-payment_status-text">{{ paymentTimer.message }}</small>
  </div>
</template>
