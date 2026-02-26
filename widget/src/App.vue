<script>
import KycContainer from './containers/KycContainer.vue';
import RateContainer from './containers/RateContainer.vue';
import NetworkContainer from './containers/NetworkContainer.vue';
import PaymentContainer from './containers/PaymentContainer.vue';
import Container from './components/Container.vue';
import Loader from './components/Loader.vue';
import PayerContainer from './containers/PayerContainer.vue';
import { VIEWS } from './store/store';

const App = {
  components: {
    KycContainer,
    RateContainer,
    NetworkContainer,
    PaymentContainer,
    Container,
    Loader,
    PayerContainer,
  },
  data() {
    return {
      store: this.$store,
      VIEWS, // Make VIEWS available in template
    };
  },
  computed: {
    currentView() {
      return this.store.state.currentView;
    },
    kycRequired() {
      return this.store.state.kycRequired;
    },
    payment() {
      return this.store.state.payment;
    },
    error() {
      return this.store.state.error;
    },
    loading() {
      return this.store.state.loading;
    },
    payerRequired() {
      return this.store.state.payerRequired;
    },
    paymentRetry() {
      const forbiddenErrorCodes = [
        3052,
      ];

      return !forbiddenErrorCodes.includes(this.getErrorCode(this.error));
    },
    networkSelectionRequired() {
      return this.store.state.networkSelectionRequired;
    },
  },
  methods: {
    getErrorCode(errorMessage) {
      let code = 'No code';

      if (!errorMessage) {
        return code;
      }

      if (errorMessage.code) {
        code = errorMessage.code;
      }

      if (errorMessage.response?.status) {
        code = errorMessage.response.status;
      }

      if (errorMessage.response?.data?.code) {
        code = errorMessage.response.data.code;
      }

      return code;
    },
    getErrorMessage(errorMessage) {
      let message = 'Unexpected error occurred!';

      if (!errorMessage) {
        return message;
      }

      if (errorMessage.message) {
        message = errorMessage.message;
      }

      if (errorMessage.response?.statusText) {
        message = errorMessage.response.statusText;
      }

      if (errorMessage.response?.data?.message) {
        message = errorMessage.response.data.message;
      }

      return message;
    },
    resetWidget() {
      this.store.dispatch('resetPlugin');
    },
    cancelPaymentAndResetWidget() {
      this.store.dispatch(
        'cancelPayment',
        {
          forceRedirect: true,
          restoreCart: true,
          reason: 'other',
          description: `Auto error: ${this.getErrorMessage(this.error.value)}`,
        },
      );
    },
  },
};

export default App;
</script>

<template>
  <main class="forumpay-pgw">
    <div v-if="loading" class="forumpay-pgw-content forumpay-pgw-center">
      <Loader :loading="true" />
    </div>

    <div v-else-if="error" class="forumpay-pgw-content forumpay-pgw-center">
      <Container :alert="'danger'">
        <p>
          [Code = {{ getErrorCode(error) }}]
          {{ getErrorMessage(error) }}<br />
          {{ payment?.payment_id }}
        </p>
      </Container>
      <Container :alert="'warning'">
        <p v-if="paymentRetry">
          There was an error processing the payment. <br />
          If you think this might be a temporary problem,
          <a href="#" @click.prevent="resetWidget">try again</a>,
          otherwise please contact the merchant for support or
          <a href="#" @click.prevent="cancelPaymentAndResetWidget">cancel payment</a>
          and return to cart.
        </p>
        <p v-else>
          There was an error processing the payment.<br />
          Please contact the merchant for support or <a href="#" @click.prevent="cancelPaymentAndResetWidget">return to cart</a>.
        </p>
      </Container>
    </div>

    <!-- View-based navigation with error and loading taking priority -->
    <KycContainer v-else-if="currentView === VIEWS.KYC" />
    <PayerContainer v-else-if="currentView === VIEWS.PAYER" />
    <NetworkContainer v-else-if="currentView === VIEWS.NETWORK_SELECTION" />
    <RateContainer v-else-if="currentView === VIEWS.RATE_SELECTION" />
    <PaymentContainer
      v-else-if="currentView === VIEWS.PAYMENT && payment"
      :payment="payment"
    />
    <Container v-else :alert="'warning'">
      <p>
        Something went wrong.<br />
        Please contact the merchant for support or <a href="#" @click.prevent="cancelPaymentAndResetWidget">return to cart</a>.
      </p>
    </Container>
  </main>
</template>
