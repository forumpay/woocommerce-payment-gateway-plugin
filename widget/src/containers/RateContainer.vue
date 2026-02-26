<script>
import formatCurrencyName from '../utils/formatCurrency';
import formatCurrencyDisplayName from '../utils/formatCurrencyDisplayName';
import extractBlockchainIdentifier from '../utils/extractBlockchainIdentifier';

import PageLogo from '../components/PageLogo.vue';
import Container from '../components/Container.vue';
import Loader from '../components/Loader.vue';
import CurrencyList from '../components/CurrencyList.vue';

const RateContainer = {
  components: {
    PageLogo,
    Container,
    Loader,
    CurrencyList,
  },

  data() {
    return {
      store: this.$store,
      selectedCurrency: null,
      getRateInterval: null,
    };
  },

  computed: {
    cryptoCurrencies() {
      return this.store.state.cryptoCurrencies;
    },
    groupedCurrencies() {
      // Step 1: Enrich each currency with its specific rate data
      const enrichedCurrencies = this.cryptoCurrencies.map((c) => {
        const rateData = this.currencyRates[c.currency];

        // Extract raw blockchain identifier (POLYGON, ETHEREUM, etc.) WITHOUT converting
        // This preserves the original blockchain for proper network name display
        // getNetworkName('POLYGON') → 'Polygon', getNetworkName('ETHEREUM') → 'Ethereum'
        const currencyTokenspec = extractBlockchainIdentifier(c.currency);

        // Normalize description for grouping
        // Remove network suffixes like " (ERC20)", " (TRC20)", " (POLYGON)" etc.
        let normalizedDescription = c.description;

        // Override for USDCE_POLYGON to group with USDC
        if (c.currency === 'USDCE_POLYGON') {
          normalizedDescription = 'USD Coin';
        } else {
          // Strip network suffix from description for grouping
          // e.g., "Tether USD (ERC20)" -> "Tether USD"
          normalizedDescription = normalizedDescription.replace(/\s*\([^)]+\)\s*$/, '').trim();
        }

        return {
          ...c,
          normalizedDescription,
          currencyWithoutNetwork: formatCurrencyDisplayName(c.currency, true),
          currencyTokenspec,
          err: rateData?.err || null,
          amount: rateData?.amount || null,
          waitTime: rateData?.wait_time || null,
          tokenbackReward: rateData?.tokenback_reward || null,
        };
      });

      // Step 2: Group by description (matching original widget implementation)
      // This ensures USDC and USDC.e are grouped together under "USD Coin"
      const grouped = enrichedCurrencies.reduce((acc, c) => {
        // Use normalizedDescription as grouping key
        const groupKey = c.normalizedDescription;

        if (!(groupKey in acc)) {
          acc[groupKey] = {
            description: c.normalizedDescription,
            currencyWithoutNetwork: c.currencyWithoutNetwork,
            amountMax: c.amount,
            amountMin: c.amount,
            waitTime: c.waitTime,
            err: c.err,
            icon_url: c.icon_url,
            blockchains: [],
          };
        }

        // Update min/max amounts across networks
        if (c.amount != null) {
          if (acc[groupKey].amountMax == null
            || parseFloat(c.amount) > parseFloat(acc[groupKey].amountMax)) {
            acc[groupKey].amountMax = c.amount;
          }
          if (acc[groupKey].amountMin == null
            || parseFloat(c.amount) < parseFloat(acc[groupKey].amountMin)) {
            acc[groupKey].amountMin = c.amount;
          }
        }

        // Merge tokenback reward if available
        if (c.tokenbackReward && !acc[groupKey].tokenbackReward) {
          acc[groupKey].tokenbackReward = c.tokenbackReward;
        }

        // Add enriched currency to blockchains array
        acc[groupKey].blockchains.push(c);

        return acc;
      }, {});

      // Convert object map → array
      return Object.values(grouped);
    },
    currencyRates() {
      return this.store.state.currencyRates;
    },
    ratesLoading() {
      return this.store.state.ratesLoading;
    },
    rate() {
      return this.store.state.rate;
    },
    cryptoCurrency() {
      return this.store.state.cryptoCurrency;
    },
    showStartPaymentButton() {
      return this.store.state.showStartPaymentButton
        || this.store.state.forceShowStartPaymentButton;
    },
    rateLoadingOld() {
      return this.store.state.rateLoading;
    },
    invoiceAmount() {
      return this.store.state.invoiceAmount || '';
    },
    invoiceCurrency() {
      return this.store.state.invoiceCurrency || '';
    },
  },

  async mounted() {
    await this.store.dispatch('setCryptoCurrencies');
    // After currencies are loaded, fetch rates for all
    await this.store.dispatch('fetchAllRates');
  },

  unmounted() {
    clearInterval(this.getRateInterval);
  },
  methods: {
    formatCurrencyName,
    async onCurrencySelect(currency) {
      // Direct click on currency in list
      this.selectedCurrency = currency;

      // Check if this currency has multiple networks
      const networks = this.getNetworksForCurrency(currency);

      if (networks.length === 0) {
        return;
      }

      // Set the first network as the selected currency for the store
      const selectedNetwork = networks[0];
      this.store.dispatch('setCryptoCurrency', selectedNetwork);

      if (networks.length > 1) {
        // Multiple networks available → navigate to network selection
        this.store.dispatch('setNetworks', networks);
      } else {
        // Single network → clear network selection state and auto start payment
        this.store.commit('setNetworks', []);
        this.store.commit('setNetworkSelectionRequired', false);
        await this.store.dispatch('setRate', selectedNetwork);
        // Only auto-start if manual mode is disabled
        if (!this.showStartPaymentButton) {
          this.onStartPayment();
        }
      }
    },
    getNetworksForCurrency(selectedCurrency) {
      // Selected currency from grouped list has blockchains array
      if (selectedCurrency.blockchains && selectedCurrency.blockchains.length > 0) {
        return selectedCurrency.blockchains;
      }
      // Fallback: this shouldn't happen with the new grouping logic,
      // but if it does, we need to return an empty array
      return [];
    },
    onStartPayment() {
      clearInterval(this.getRateInterval);
      this.store.dispatch('startPayment', this.cryptoCurrency);
    },
  },
};

export default RateContainer;

</script>

<template>
  <div>
    <div class="forumpay-pgw-content forumpay-pgw-center forumpay-pgw-rate">
      <Container>
        <div
          v-if="invoiceAmount"
          style="font-size: 16px; font-weight: 700; text-align: right; margin-bottom: 16px;"
        >
          {{ invoiceAmount }} {{ invoiceCurrency }}
        </div>

        <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

        <h3
          v-if="groupedCurrencies.length"
          class="forumpay-pgw-rate-title"
        >
          Pay with
        </h3>
        <CurrencyList
          v-if="groupedCurrencies.length"
          :currencies="groupedCurrencies"
          :selected-currency="selectedCurrency"
          :currency-rates="currencyRates"
          :rates-loading="ratesLoading"
          :show-selection="showStartPaymentButton"
          @select="onCurrencySelect"
        />
        <Loader
          v-if="!groupedCurrencies.length"
          :loading="true"
        />

        <PageLogo />
      </Container>
    </div>
  </div>
</template>
