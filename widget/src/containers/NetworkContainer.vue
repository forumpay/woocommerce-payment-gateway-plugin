<script>
import PageLogo from '../components/PageLogo.vue';
import Container from '../components/Container.vue';
import CurrencyIcon from '../components/CurrencyIcon.vue';
import getNetworkDisplayName from '../utils/getNetworkDisplayName';
import getNetworkShortname from '../utils/getNetworkShortname';

const NetworkContainer = {
  components: {
    PageLogo,
    Container,
    CurrencyIcon,
  },

  data() {
    return {
      store: this.$store,
      selectedNetwork: null,
    };
  },

  computed: {
    cryptoCurrency() {
      return this.store.state.cryptoCurrency;
    },
    networks() {
      return this.store.state.networks || [];
    },
    showStartPaymentButton() {
      return this.store.state.showStartPaymentButton;
    },
    invoiceAmount() {
      return this.store.state.invoiceAmount || this.store.state.rate?.invoice_amount || '';
    },
    invoiceCurrency() {
      return this.store.state.invoiceCurrency || this.store.state.rate?.invoice_currency || '';
    },
  },

  mounted() {
    // Auto-select first network if only one available (unless in manual mode)
    if (this.networks.length === 1 && !this.showStartPaymentButton) {
      this.onNetworkSelect(this.networks[0]);
    }
  },

  methods: {
    goBack() {
      // Navigate back to rate selection view
      this.store.dispatch('navigateToRateSelection');
    },
    async onNetworkSelect(network) {
      this.selectedNetwork = network;
      this.store.dispatch('setSelectedNetwork', network);
      // Update the selected currency to be the network
      this.store.dispatch('setCryptoCurrency', network);

      // Set the rate before starting payment
      await this.store.dispatch('setRate', network);

      // Only auto-start if manual mode is disabled
      if (!this.showStartPaymentButton) {
        this.store.dispatch('startPayment', network);
      }
    },
    getNetworkDisplayName(network) {
      // Use the utility function for consistent formatting
      return getNetworkDisplayName(network);
    },
    getNetworkShortname(network) {
      // Use the utility function for consistent token standard display
      return getNetworkShortname(network);
    },
    getNetworkAmount(network) {
      // Display the exchange amount for this specific network
      if (network.amount) {
        return `≈ ${network.amount} ${network.currencyWithoutNetwork}`;
      }
      return '';
    },
    getNetworkWaitTime(network) {
      return network.waitTime || '';
    },
    getNetworkFee(network) {
      return network.networkProcessingFee || 'N/A';
    },
  },
};

export default NetworkContainer;
</script>

<template>
  <div class="forumpay-pgw-content forumpay-pgw-center forumpay-pgw-network">
    <Container>
      <div
        v-if="invoiceAmount"
        style="font-size: 16px; font-weight: 700; text-align: right; margin-bottom: 16px; width: 100%;"
      >
        {{ invoiceAmount }} {{ invoiceCurrency }}
      </div>

      <hr style="width: calc(100% + 40px);margin: 0 -20px 20px -20px;" />

      <div style="display:flex; flex-wrap:wrap;margin-right: auto;">
        <div style="display:flex;align-items: center; gap: 8px;">
          <div class="forumpay-pgw-currency-icon-wrapper" style="max-width:30px;">
            <CurrencyIcon :currency="cryptoCurrency" />
          </div>
          <h3 class="forumpay-pgw-network-title" style="margin:0;">
            Select {{ cryptoCurrency?.currencyWithoutNetwork }} Network
          </h3>
        </div>
      </div>

      <div style="width:100%;margin: 16px 0;">
        <p class="forumpay-pgw-network-subtitle" style="text-align: start;margin-bottom:12px;">
          Use the network you select or your payment will be lost.
        </p>
        <div class="forumpay-pgw-network-list">
          <div
            v-for="network in networks"
            :key="network.currency"
            role="button"
            tabindex="0"
            class="forumpay-pgw-network-item"
            :class="{ selected: selectedNetwork === network }"
            @click="onNetworkSelect(network)"
            @keyup.enter="onNetworkSelect(network)"
          >
            <div class="forumpay-pgw-network-item-icon">
              <CurrencyIcon :currency="network" />
            </div>

            <div class="forumpay-pgw-network-item-details">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div class="forumpay-pgw-network-item-name">
                  {{ getNetworkDisplayName(network) }}
                </div>
                <div class="forumpay-pgw-network-tag">
                  {{ getNetworkShortname(network) }}
                </div>
              </div>

              <div class="forumpay-pgw-network-item-info">
                <div
                  v-if="getNetworkAmount(network)"
                  class="forumpay-pgw-network-item-amount"
                >
                  {{ getNetworkAmount(network) }}
                </div>
                <div
                  v-if="getNetworkWaitTime(network)"
                  class="forumpay-pgw-network-item-wait-time"
                >
                  {{ getNetworkWaitTime(network) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div style="display: flex;justify-content: center;flex-direction: column;gap: 32px;">
        <p class="forumpay-pgw-network-subtitle" style="text-align:center;">
          The actual amount of crypto required to complete your payment may vary slightly after
          you make your choice.
        </p>

        <button
          type="button"
          class="forumpay-pgw-button forumpay-pgw-button--outline"
          @click="goBack()"
          @keyup="goBack()"
        >
          Back
        </button>
      </div>
      <div style="display: flex;justify-content:start;width: 100%;">
        <PageLogo />
      </div>
    </Container>
  </div>
</template>

<style scoped lang="scss">
.forumpay-pgw-network {
  &-tag {
    white-space: nowrap;
    background-color: black;
    border-radius: 9999px;
    color: white;
    padding: 3px 13px;
    font-size: 11px;
    font-weight: 600;
  }

  &-subtitle {
    font-size: 11px;
    color: var(--pgw-color-gray-2);
    margin: 0;
    text-align: center;
    font-weight: 500;
  }

  &-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  &-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 11px;
    background-color: var(--pgw-color-background-secondary);
    border-radius: 11px;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      background-color: var(--pgw-color-background-tertiary);
      &-name {
        font-weight: bold;
      }
    }

    &.selected {
      background-color: var(--pgw-color-background-tertiary);
      border: 1px solid var(--pgw-color-primary, #007bff);

      .forumpay-pgw-network-item-name {
        font-weight: bold;
      }
    }

    &-icon {
      margin-right: 8px;
      flex-shrink: 0;

      width: 40px;
      img {
        width: auto;
        height: 30px;
      }
    }

    &-details {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    &-name {
      font-weight: 500;
      font-size: 14px;
      color: var(--pgw-color-text-primary);
      margin-bottom: 2px;
    }

    &-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 11px;
      padding-top: 2px;
    }

    &-amount {
      color: var(--pgw-color-gray-2);
    }

    &-wait-time {
      color: var(--pgw-color-gray-2);
      margin-left: auto;
    }

    &-fee {
      font-size: 12px;
      color: var(--pgw-color-gray-2);
      font-weight: 600;
    }

    &-arrow {
      font-size: 24px;
      color: var(--pgw-color-gray-2);
      font-weight: bold;
    }
  }
}

.forumpay-pgw-currency-icon-wrapper {
  flex-shrink: 0;
  display: flex;

  img {
    height: auto;
    width: 30px;
  }
}
</style>
