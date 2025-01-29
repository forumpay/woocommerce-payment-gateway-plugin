<script>
import Container from '../components/Container.vue';
import Loader from '../components/Loader.vue';
import PageLogo from '../components/PageLogo.vue';
import PayerIndividualForm from '../components/PayerIndividualForm.vue';
import PayerCompanyForm from '../components/PayerCompanyForm.vue';

const PayerContainer = {
  components: {
    Container,
    Loader,
    PageLogo,
    PayerIndividualForm,
    PayerCompanyForm,
  },
  data() {
    return {
      store: this.$store,
    };
  },
  computed: {
    isLoading() {
      return !!this.store.state.loading;
    },
    payerType() {
      return this.store.state.payer.payer_type;
    },
    cryptoCurrency() {
      return this.store.state.cryptoCurrency;
    },
    payerRequired() {
      return this.store.state.payerRequired;
    },
    payer() {
      return this.store.state.payer;
    },
  },
  created() {
    if (this.payerType === '') {
      this.onPayerType('individual');
    }
  },
  methods: {
    onPayerSubmit() {
      const form = this.$refs.payerForm;

      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      this.store.commit('setLoading', true);
      this.store.dispatch('startPayment', this.cryptoCurrency);
    },
    onCancel() {
      this.store.commit('setLoading', true);
      this.store.dispatch(
        'cancelPayment',
        {
          forceRedirect: true,
          restoreCart: true,
          reason: 'other',
          description: 'Auto error: Customer decided to cancel during Payer additional information.',
        },
      );
    },
    onPayerType(type) {
      this.store.commit('setPayer', {
        ...this.store.state.payer,
        payer_type: type,
      });
    },
  },
};

export default PayerContainer;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center">
    <Container class="forumpay-pgw-container--gap">
      <div v-if="!isLoading">
        <form ref="payerForm">
          <div>
            <div class="forumpay-pgw-guide-text-padding-bottom">
              <b>Additional information needed</b>
            </div>
            <p class="forumpay-pgw-guide-text-align-start forumpay-pgw-guide-text-thin-font-weight">
              To comply with travel rule financial requirements for validation this transaction,
              we need to gather additional information.
            </p>
            <br>
            <div>
              <div class="forumpay-pgw-payer-type">
                <a
                  role="button"
                  :class="{ active: payerType === 'individual' || payerType === '' }"
                  tabindex="0"
                  @click="onPayerType('individual')"
                  @keydown.enter="onPayerType('individual')"
                >
                  Individual
                </a>
                <a
                  role="button"
                  :class="{ active: payerType === 'company' }"
                  tabindex="0"
                  @click="onPayerType('company')"
                  @keydown.enter="onPayerType('company')"
                >
                  Company
                </a>
              </div>
            </div>
            <div v-if="payerType === 'individual' || payerType === ''">
              <PayerIndividualForm />
            </div>
            <div v-if="payerType === 'company'">
              <PayerCompanyForm />
            </div>
          </div>
          <div class="forumpay-pgw-horizontal-list">
            <button
              class="forumpay-pgw-button forumpay-pgw-button--cancel"
              type="button"
              @click="onCancel"
            >
              Cancel
            </button>
            <button
              class="forumpay-pgw-button"
              type="button"
              @click="onPayerSubmit"
            >
              Continue
            </button>
          </div>
        </form>
      </div>
      <div v-else>
        <Loader :loading="true" :small="true" />
      </div>
    </Container>
  </div>
  <PageLogo />
</template>
