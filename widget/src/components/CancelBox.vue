<script>
const CancelBox = {
  data() {
    return {
      reason: null,
      description: '',
    };
  },
  computed: {
    isCancelVisible() {
      return this.$store.state.showCancel;
    },
    isReasonSelected() {
      return !!this.reason;
    },
  },
  methods: {
    onHide() {
      this.reason = null;
      this.description = '';
      this.$store.dispatch('hideCancel');
    },
    onConfirmCancel() {
      this.$emit('onCancelPaymentConfirm', this.reason, this.description);
    },
  },
  watch: {
    reason(newVal) {
      if (newVal) {
        this.description = '';
      }
    },
  },
};

export default CancelBox;
</script>

<template>
  <div v-if="isCancelVisible" class="forumpay-pgw-cancel">
    <div
      v-click-outside="onHide"
      class="forumpay-pgw-cancel-content"
    >
      <div class="forumpay-pgw-cancel-container">
        <a
          class="forumpay-pgw-cancel-close"
          role="button"
          tabindex="0"
          @click="onHide()"
          @keyup="onHide()"
        >
          &times;
        </a>

        <div>
          <div class="forumpay-pgw-cancel-title">
            Cancel payment
          </div>
          <div>
            Please select reason for cancellation
          </div>

          <ul class="forumpay-pgw-cancel-list">
            <li>
              <label for="cancellation_reason-qr_code_problem">
                <input
                  id="cancellation_reason-qr_code_problem"
                  v-model="reason"
                  type="radio"
                  name="cancellation_reason"
                  value="qr_code_problem"
                />
                <span>QR code does not work</span>
              </label>
            </li>
            <li v-if="reason === 'qr_code_problem'">
              <div>
                <label for="cancellation_reason_description_qr">Which wallet do you use?</label><br />
                <textarea id="cancellation_reason_description_qr" v-model="description" />
              </div>
            </li>
            <li>
              <label for="cancellation_reason-no_wallet">
                <input
                  id="cancellation_reason-no_wallet"
                  v-model="reason"
                  type="radio"
                  name="cancellation_reason"
                  value="no_wallet"
                />
                <span>I do not have a crypto wallet</span>
              </label>
            </li>
            <li>
              <label for="cancellation_reason-curious">
                <input
                  id="cancellation_reason-curious"
                  v-model="reason"
                  type="radio"
                  name="cancellation_reason"
                  value="curious"
                />
                <span>I was just curious</span>
              </label>
            </li>
            <li>
              <label for="cancellation_reason-bad_rate">
                <input
                  id="cancellation_reason-bad_rate"
                  v-model="reason"
                  type="radio"
                  name="cancellation_reason"
                  value="bad_rate"
                />
                <span>Bad exchange rate</span>
              </label>
            </li>
            <li>
              <label for="cancellation_reason_other">
                <input
                  id="cancellation_reason_other"
                  v-model="reason"
                  type="radio"
                  name="cancellation_reason"
                  value="other"
                />
                <span>Other</span>
              </label>
            </li>
            <li v-if="reason === 'other'">
              <div>
                <label for="cancellation_reason_description">Please explain</label><br />
                <textarea id="cancellation_reason_description" v-model="description" />
              </div>
            </li>
            <li class="button">
              <button
                :disabled="!isReasonSelected"
                type="button"
                class="forumpay-pgw-button forumpay-pgw-button--cancel"
                @click="onConfirmCancel()"
                @keyup="onConfirmCancel()"
              >
                Confirm cancel
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
