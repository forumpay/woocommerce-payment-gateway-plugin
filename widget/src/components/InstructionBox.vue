<script setup>
import {
  computed,
} from 'vue';

import { useStore } from 'vuex';
import CurrencyIcon from './CurrencyIcon.vue';

defineProps({
  notices: {
    type: Array,
    default() {
      return [];
    },
  },
});

const store = useStore();
const isPaymentStatusVisible = computed(() => store.state.showInstructions);

const onHide = () => {
  store.dispatch('hideInstructions');
};

</script>

<template>
  <div v-if="isPaymentStatusVisible" class="forumpay-pgw-instruction">
    <div
      v-click-outside="onHide"
      class="forumpay-pgw-instruction-content"
    >
      <div class="forumpay-pgw-instruction-container">
        <a
          class="forumpay-pgw-instruction-close"
          role="button"
          tabindex="0"
          @click="onHide()"
          @keyup="onHide()"
        >
          &times;
        </a>
        <div class="forumpay-pgw-instruction-messages">
          <CurrencyIcon icon="hand" />
          <ul class="forumpay-pgw-instruction-list">
            <li>
              Please type the exact amount to be paid in crypto, as exchange rates can vary.
            </li>
            <li>
              Use "High" network fee when sending funds from
              your wallet to ensure instant confirmation.
            </li>
          </ul>
          <div class="forumpay-pgw-instruction-custom">
            <div
              v-for="notice in notices"
              :key="notice.code"
              class="forumpay-pgw-instruction-custom-message"
            >
              {{ notice.message }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
