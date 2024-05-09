<script>
import CurrencyIcon from './CurrencyIcon.vue';

const InstructionBox = {
  components: {
    CurrencyIcon,
  },
  props: {
    notices: {
      type: Array,
      default() {
        return [];
      },
    },
  },
  methods: {
    onHide() {
      return this.$store.dispatch('hideInstructions');
    },
  },
  computed: {
    isPaymentStatusVisible() {
      return this.$store.state.showInstructions;
    },
  },
};

export default InstructionBox;
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
