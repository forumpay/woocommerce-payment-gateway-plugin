<script>
import Container from '../components/Container.vue';
import Loader from '../components/Loader.vue';
import PageLogo from '../components/PageLogo.vue';
import PayerIndividualForm from '../components/PayerIndividualForm.vue';
import PayerCompanyForm from '../components/PayerCompanyForm.vue';
import Dropdown from '../components/Dropdown.vue';
import { VIEWS } from '../store/store';

const PayerContainer = {
  components: {
    Container,
    Loader,
    PageLogo,
    PayerIndividualForm,
    PayerCompanyForm,
    Dropdown,
  },
  data() {
    return {
      store: this.$store,
      VIEWS,
      payerTypeOptions: ['individual', 'company'],
    };
  },
  computed: {
    isLoading() {
      return !!this.store.state.loading;
    },
    payerType: {
      get() {
        return this.store.state.payer.payer_type || 'individual';
      },
      set(value) {
        this.onPayerType(value);
      },
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
    networkSelectionRequired() {
      return this.store.state.networkSelectionRequired;
    },
    showStartPaymentButton() {
      return this.store.state.showStartPaymentButton;
    },
    invoiceAmount() {
      return this.store.state.invoiceAmount || '';
    },
    invoiceCurrency() {
      return this.store.state.invoiceCurrency || '';
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
    onBack() {
      // Clear payer required flag and payment data
      this.store.commit('setPayerRequired', false);
      this.store.commit('setPayment', null);
      this.store.commit('setKycRequired', false);

      // Navigate back to the previous view based on whether we have networks
      // If networks exist (length > 1), we came from network selection
      if (this.store.state.networks && this.store.state.networks.length > 1) {
        // Go back to network selection
        this.store.commit('setNetworkSelectionRequired', true);
        this.store.commit('setCurrentView', this.VIEWS.NETWORK_SELECTION);
      } else {
        // Go back to rate selection
        this.store.commit('setCurrentView', this.VIEWS.RATE_SELECTION);
      }
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
        <div
          v-if="invoiceAmount"
          style="font-size: 16px; font-weight: 700; text-align: right; margin-bottom: 16px;"
        >
          {{ invoiceAmount }} {{ invoiceCurrency }}
        </div>

        <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

        <form ref="payerForm">
          <div>
            <div class="forumpay-pgw-payer-header">
              Additional information needed
            </div>
            <p class="forumpay-pgw-payer-description">
              To comply with travel rule financial requirements for validating this transaction,
              we need to gather additional information.
            </p>
            <Dropdown
              v-model="payerType"
              label="Payer Type"
              :options="payerTypeOptions"
              class="forumpay-pgw-payer-type-dropdown"
            >
              <template #selected="{ selected }">
                <span style="text-transform: capitalize;">{{ selected }}</span>
              </template>
              <template #option="{ option, markText }">
                <span style="text-transform: capitalize;" v-html="markText(option)" />
              </template>
            </Dropdown>
            <div v-if="payerType === 'individual' || payerType === ''">
              <PayerIndividualForm />
            </div>
            <div v-if="payerType === 'company'">
              <PayerCompanyForm />
            </div>
          </div>
          <div class="forumpay-pgw-payer-buttons">
            <button
              v-if="!showStartPaymentButton"
              class="w3-button btn-secondary"
              type="button"
              @click="onBack"
            >
              Back
            </button>
            <button
              class="w3-button btn-primary"
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
      <PageLogo />
    </Container>
  </div>
</template>

<style lang="scss" scoped>
.forumpay-pgw-payer-header {
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 4px;
  color: var(--pgw-color-text-primary);
}

.forumpay-pgw-payer-description {
  font-size: 11px;
  font-weight: 400;
  color: var(--pgw-color-gray-2);
  margin-top: 4px;
  margin-bottom: 12px;
  line-height: 1.4;
}

.forumpay-pgw-payer-buttons {
  display: flex;
  gap: 8px;
  margin-top: 20px;
  justify-content: center;
  align-items: center;

  .w3-button {
    padding: 10px 16px;
    border: none;
    border-radius: var(--pgw-default-radius-1);
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    outline: none;
    width: fit-content;

    &.btn-secondary {
      color: var(--pgw-color-black) !important;
      background-color: var(--pgw-color-white) !important;
      border: 1px solid var(--pgw-color-gray-4);

      &:hover {
        color: var(--pgw-color-black) !important;
        background-color: var(--pgw-color-white) !important;
        border: 1px solid var(--pgw-color-gray-4);
      }

      &:focus, &:active {
        outline: none;
        box-shadow: none;
      }
    }

    &.btn-primary {
      color: var(--pgw-color-black) !important;
      background-color: var(--pgw-color-primary) !important;
      border: none;

      &:hover {
        background-color: var(--pgw-color-primary-light) !important;
      }

      &:focus, &:active {
        outline: none;
        box-shadow: none;
      }
    }
  }
}
</style>
