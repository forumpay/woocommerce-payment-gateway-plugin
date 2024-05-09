<script>
import formatCurrencyName from '../utils/formatCurrency';

import PageLogo from '../components/PageLogo.vue';
import Container from '../components/Container.vue';
import Loader from '../components/Loader.vue';
import Dropdown from '../components/Dropdown.vue';
import CurrencyIcon from '../components/CurrencyIcon.vue';

const RateContainer = {
  components: {
    PageLogo,
    Container,
    Loader,
    Dropdown,
    CurrencyIcon,
  },

  data() {
    return {
      store: this.$store,
      selectedCurrency: false,
      getRateInterval: null,
    };
  },

  computed: {
    cryptoCurrencies() {
      return this.store.state.cryptoCurrencies;
    },
    rate() {
      return this.store.state.rate;
    },
    cryptoCurrency() {
      return this.store.state.cryptoCurrency;
    },
    showStartPaymentButton() {
      return this.store.state.showStartPaymentButton;
    },
    rateLoading() {
      return this.store.state.rateLoading;
    },
  },

  mounted() {
    this.store.dispatch('setCryptoCurrencies');
  },

  unmounted() {
    clearInterval(this.getRateInterval);
  },
  watch: {
    'store.state.cryptoCurrencies': {
      async handler(newCurrencies) {
        this.selectedCurrency = newCurrencies.length ? newCurrencies[0] : false;
      },
      deep: false,
    },
    'store.state.rateError': {
      async handler(newRateError) {
        if (newRateError && newRateError.code === 2001) {
          clearInterval(this.getRateInterval);
        }
      },
      deep: false,
    },
    selectedCurrency: {
      async handler(newCurrency, oldCurrency) {
        if (newCurrency && oldCurrency !== newCurrency) {
          this.store.dispatch('setCryptoCurrency', newCurrency);
          if (this.getRateInterval) {
            clearInterval(this.getRateInterval);
          }
          this.store.dispatch('clearRate');
          this.store.dispatch('setRate', newCurrency);
          this.getRateInterval = setInterval(() => {
            this.store.dispatch('setRate', newCurrency);
          }, 5000);
        }
      },
    },
  },
  methods: {
    formatCurrencyName,
    onStartPayment() {
      clearInterval(this.getRateInterval);
      this.store.dispatch('startPayment', this.cryptoCurrency);
    },
  },
};

export default RateContainer;

</script>

<template>
  <PageLogo />
  <div class="forumpay-pgw-content forumpay-pgw-center forumpay-pgw-rate">
    <Container>
      <Dropdown
        v-if="cryptoCurrencies.length"
        v-model="selectedCurrency"
        class="select-crypto"
        :options="cryptoCurrencies"
        label="Select Cryptocurrency"
        filter-property="description"
      >
        <template #selected="{ selected }">
          <CurrencyIcon :currency="selected" />
          <span>{{ selected?.description }}</span>
        </template>
        <template #option="{ option, markText }">
          <!-- eslint-disable vue/no-v-html -->
          <span v-html="markText(option?.description)" />
          <!--eslint-enable-->
          <CurrencyIcon :currency="option" />
        </template>
      </Dropdown>
      <Loader
        v-if="!cryptoCurrencies.length"
        :loading="true"
      />
    </Container>

    <Container>
      <div
        v-if="rate"
        class="forumpay-pgw-rate-details"
      >
        <ul class="forumpay-pgw-rate-details-list">
          <li>Total amount in FIAT: {{ rate.invoice_amount }} {{ rate.invoice_currency }}</li>
          <!-- eslint-disable-next-line -->
          <li>Total crypto amount: {{ rate.amount_exchange }} {{ formatCurrencyName(selectedCurrency.currency) }}</li>
        </ul>
        <span class="forumpay-pgw-rate-details-help-text">Expected time to confirm: {{ rate.wait_time }}</span>
      </div>

      <Loader
        v-if="!rate"
        :loading="true"
      />
      <Loader
        :loading="rate && rateLoading"
        :small="true"
      />
    </Container>

    <div
      v-if="rate && showStartPaymentButton"
      class="forumpay-pgw-content"
    >
      <button
        class="forumpay-pgw-button forumpay-pgw-button--warning"
        type="button"
        @click="onStartPayment"
      >
        Continue
      </button>
    </div>
  </div>
</template>
