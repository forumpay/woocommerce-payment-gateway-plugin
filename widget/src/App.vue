<script setup>

import { computed } from 'vue';
import { useStore } from 'vuex';

import KycContainer from './containers/KycContainer.vue';
import RateContainer from './containers/RateContainer.vue';
import PaymentContainer from './containers/PaymentContainer.vue';
import Container from './components/Container.vue';
import Loader from './components/Loader.vue';

const store = useStore();
const kycRequired = computed(() => store.state.kycRequired);
const payment = computed(() => store.state.payment);
const error = computed(() => store.state.error);
const loading = computed(() => store.state.loading);

const getErrorCode = ((errorMessage) => {
  let code = 'No code';

  if (errorMessage.code) {
    code = errorMessage.code;
  }

  if (errorMessage.response.status) {
    code = errorMessage.response.status;
  }

  if (errorMessage.response.data.code) {
    code = errorMessage.response.data.code;
  }

  return code;
});

const getErrorMessage = ((errorMessage) => {
  let message = 'Unexpected error occurred!';

  if (errorMessage.message) {
    message = errorMessage.message;
  }

  if (errorMessage.response.statusText) {
    message = errorMessage.response.statusText;
  }

  if (errorMessage.response.data.message) {
    message = errorMessage.response.data.message;
  }

  return message;
});

const resetWidget = (() => {
  store.dispatch('resetPlugin');
});

const cancelPaymentAndResetWidget = (() => {
  store.dispatch(
    'cancelPayment',
    {
      forceRedirect: true,
      restoreCart: true,
      reason: 'other',
      description: `Auto error: ${getErrorMessage(error.value)}`,
    },
  );
});

</script>

<template>
  <main class="forumpay-pgw">
    <div
      v-if="loading"
      class="forumpay-pgw-content forumpay-pgw-center"
    >
      <Loader :loading="true" />
    </div>

    <div
      v-else-if="error"
      class="forumpay-pgw-content forumpay-pgw-center"
    >
      <Container
        :alert="'danger'"
      >
        <p>
          [Code = {{ getErrorCode(error) }}]
          {{ getErrorMessage(error) }}<br />
          {{ payment?.payment_id }}
        </p>
      </Container>
      <Container
        :alert="'warning'"
      >
        <p>
          There was an error processing the payment. <br />
          If you think this might be a temporary problem,
          <a href="#" @click.prevent="resetWidget">try again</a>,
          otherwise please contact the merchant for support or
          <a href="#" @click.prevent="cancelPaymentAndResetWidget">cancel payment</a>
          and return to cart.
        </p>
      </Container>
    </div>

    <KycContainer v-else-if="!payment && kycRequired" />

    <RateContainer v-else-if="!payment" />

    <PaymentContainer
      v-else-if="payment"
      :payment="payment"
    />
  </main>
</template>
