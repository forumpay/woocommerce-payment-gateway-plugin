<script setup>

import {
  computed,
  onMounted,
  onUnmounted,
  ref,
  watch,
} from 'vue';
import { useStore } from 'vuex';
import formatCurrencyName from '../utils/formatCurrency';

import PageLogo from '../components/PageLogo.vue';
import Container from '../components/Container.vue';
import Loader from '../components/Loader.vue';
import Dropdown from '../components/Dropdown.vue';
import CurrencyIcon from '../components/CurrencyIcon.vue';

const store = useStore();

const cryptoCurrencies = computed(() => store.state.cryptoCurrencies);
const rate = computed(() => store.state.rate);
const cryptoCurrency = computed(() => store.state.cryptoCurrency);
const showStartPaymentButton = computed(() => store.state.showStartPaymentButton);
const rateLoading = computed(() => store.state.rateLoading);

const selectedCurrency = ref(false);

let getRateInterval = null;

onMounted(() => {
  store.dispatch('setCryptoCurrencies');
});

onUnmounted(() => {
  clearInterval(getRateInterval);
});

watch(() => store.state.cryptoCurrencies, async (newCurrencies) => {
  selectedCurrency.value = newCurrencies.length ? newCurrencies[0] : false;
});

watch(() => store.state.rateError, async (newRateError) => {
  if (newRateError) {
    if (newRateError.code === 2001) {
      clearInterval(getRateInterval);
    }
  }
});

watch(selectedCurrency, async (newCurrency, oldCurrency) => {
  if (newCurrency && oldCurrency !== newCurrency) {
    store.dispatch('setCryptoCurrency', newCurrency);

    if (getRateInterval) {
      clearInterval(getRateInterval);
    }

    store.dispatch('clearRate');
    store.dispatch('setRate', newCurrency);
    getRateInterval = setInterval(
      () => store.dispatch('setRate', newCurrency),
      5000,
    );
  }
});

const onStartPayment = () => {
  clearInterval(getRateInterval);
  store.dispatch('startPayment', cryptoCurrency.value);
};

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
