<script>
import CurrencyIcon from './CurrencyIcon.vue';

const CurrencyList = {
  components: {
    CurrencyIcon,
  },
  props: {
    currencies: {
      type: Array,
      required: true,
    },
    selectedCurrency: {
      type: Object,
      default: null,
    },
    currencyRates: {
      type: Object,
      default: () => ({}),
    },
    ratesLoading: {
      type: Boolean,
      default: false,
    },
    showSelection: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['select'],
  methods: {
    selectCurrency(currency) {
      this.$emit('select', currency);
    },
    formatAmount(currency) {
      if (currency.amountMin && currency.amountMax) {
        const min = parseFloat(currency.amountMin);
        const max = parseFloat(currency.amountMax);

        if (min === max) {
          return `≈ ${currency.amountMin} ${currency.currencyWithoutNetwork}`;
        }
        return `≈ ${currency.amountMin} - ${currency.amountMax} ${currency.currencyWithoutNetwork}`;
      }
      return '';
    },
    getWaitTime(currency) {
      return currency.waitTime || '';
    },
    hasRateData(currency) {
      return currency.amountMin && !currency.err;
    },
    isSelected(currency) {
      return this.showSelection
        && this.selectedCurrency
        && this.selectedCurrency.description === currency.description;
    },
  },
};

export default CurrencyList;
</script>

<template>
  <div class="forumpay-pgw-currency-list">
    <div class="forumpay-pgw-currency-list-items">
      <div
        v-for="currency in currencies"
        :key="currency.description"
        class="forumpay-pgw-currency-list-item"
        :class="{ selected: isSelected(currency) }"
        role="button"
        tabindex="0"
        @click="() => selectCurrency(currency)"
        @keyup.enter="() => selectCurrency(currency)"
      >
        <div class="currency-icon">
          <CurrencyIcon :currency="currency" />
        </div>
        <div class="currency-info">
          <div class="currency-name">
            {{ currency.description }} ({{ currency.currencyWithoutNetwork }})
          </div>
          <div
            v-if="hasRateData(currency)"
            class="currency-details"
          >
            <span class="currency-rate">
              {{ formatAmount(currency) }}
            </span>
            <span class="currency-wait-time">
              {{ getWaitTime(currency) }}
            </span>
          </div>
          <div
            v-else-if="ratesLoading"
            class="currency-details"
          >
            <span class="currency-loading">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.forumpay-pgw-currency-list {
  width: 100%;

  &-items {
    max-height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-right: 4px; // Space for scrollbar

    // Custom scrollbar styling
    &::-webkit-scrollbar {
      width: 8px;
    }

    &::-webkit-scrollbar-track {
      background: transparent;
    }

    &::-webkit-scrollbar-thumb {
      background-color: rgba(0, 0, 0, 0.4);
      border-radius: 4px;
    }
  }

  &-item {
    padding: 12px;
    border-radius: 12px;
    background-color: var(--pgw-color-background-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 60px;

    outline-width: 0!important;
    &:hover {
      background-color: var(--pgw-color-background-tertiary);

      .currency-name {
        text-shadow: 0 0 1px currentColor;
      }
    }

    &.selected {
      background-color: var(--pgw-color-background-tertiary);
      border: 1px solid var(--pgw-color-primary, #007bff);

      .currency-name {
        font-weight: bold;
      }
    }

    .currency-name {
      font-size: 14px;
      color: var(--pgw-color-text-primary);
    }

    .currency-icon {
      flex-shrink: 0;
      display: flex;

      width: 40px;
      img {
        height: 30px;
        width: auto;
      }
    }

    .currency-info {
      width: 100%;
      flex: 1;
    }

    .currency-name {
      font-weight: 500;
      font-size: 14px;
      color: var(--pgw-color-text-primary);
    }

    .currency-details {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 11px;
      padding-top: 2px;
    }

    .currency-rate {
      color: var(--pgw-color-gray-2);
    }

    .currency-wait-time {
      color: var(--pgw-color-gray-2);
      margin-left: auto;
    }

    .currency-loading {
      color: var(--pgw-color-gray-2);
      font-style: italic;
      font-size: 12px;
    }
  }
}
</style>
