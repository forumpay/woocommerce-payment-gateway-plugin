<script>

import { parseTransactionStatus } from '../utils/time';
import CountdownRing from './CountdownRing.vue';

const PaymentStatusIcon = {
  components: {
    CountdownRing,
  },
  props: {
    status: {
      type: String,
      default: '',
    },
    inserted: {
      type: String,
      default: '',
    },
  },
  computed: {
    paymentTimer() {
      return parseTransactionStatus(this.status, this.inserted);
    },
  },
};

export default PaymentStatusIcon;

</script>

<template>
  <div class="forumpay-pgw-info_timer">
    <CountdownRing
      radius="12.5"
      :countdown="paymentTimer.countdown"
      stroke="2"
    />
    <svg
      class="forumpay-pgw-info_timer-icon-i"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 192 512"
    ><!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M48 80a48 48 0 1 1 96 0A48 48 0 1 1 48 80zM0 224c0-17.7 14.3-32 32-32H96c17.7 0 32 14.3 32 32V448h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H64V256H32c-17.7 0-32-14.3-32-32z" /></svg>
  </div>
</template>
