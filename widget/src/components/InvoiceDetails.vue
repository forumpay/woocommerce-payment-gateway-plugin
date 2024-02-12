<script setup>

defineProps({
  invoiceAmount: {
    type: String,
    default: '',
  },
  invoiceCurrency: {
    type: String,
    default: '',
  },
  currency: {
    type: String,
    default: '',
  },
  exchangeAmount: {
    type: String,
    default: '',
  },
  rate: {
    type: String,
    default: '',
  },
  networkProcessingFee: {
    type: String,
    default: '',
  },
  amount: {
    type: String,
    default: '',
  },
  fastTransactionFeeCurrency: {
    type: String,
    default: '',
  },
  fastTransactionFee: {
    type: String,
    default: '',
  },
  waitTime: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  errorMessage: {
    type: String,
    default: '',
  },
});

</script>

<template>
  <div class="forumpay_get_rate_container">
    <div
      class="payment_status_container yl-pale yl-pale-yellow"
      data-role="payment-get-rate"
    >
      <div class="payment_info">
        <div class="item_group invoice_amount">
          <div class="form-label">
            Invoice amount
          </div>
          <div class="form-data">
            {{ invoiceAmount }} {{ invoiceCurrency }}
          </div>
        </div>
      </div>
      <details>
        <summary>Details</summary>
        <div class="item_group rate">
          <div class="form-label">
            Rate:
          </div>
          <div class="form-data">
            1 {{ currency }} = {{ rate }} {{ invoiceCurrency }}
          </div>
        </div>
        <div class="item_group exchange_amount">
          <div class="form-label">
            Exchange amount:
          </div>
          <div class="form-data">
            {{ exchangeAmount }} {{ currency }}
          </div>
        </div>
        <div class="item_group network_processing_fee">
          <div class="form-label">
            Network processing fee:
          </div>
          <div class="form-data">
            {{ networkProcessingFee }} {{ currency }}
          </div>
        </div>
      </details>
      <div class="payment_info">
        <div class="item_group total">
          <div class="form-label">
            Total:
          </div>
          <div class="form-data">
            {{ amount }} {{ currency }}
          </div>
        </div>
      </div>
      <div class="payment_info_bottom">
        <div class="item_group tx_fee">
          <div class="form-label">
            TX fee {{ fastTransactionFeeCurrency }} set to
          </div>
          <div class="form-data">
            {{ fastTransactionFee }} {{ fastTransactionFeeCurrency }}
          </div>
        </div>
        <div class="item_group expected_time_to_wait">
          <div class="form-label">
            Expected time to wait
          </div>
          <div class="form-data">
            {{ waitTime }}
          </div>
        </div>
      </div>
      <div v-if="error">
        <div
          role="alert"
          class="messages"
        >
          <div
            class="message-error error message"
            data-ui-id="message-success"
          >
            <div>{{ errorMessage }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
