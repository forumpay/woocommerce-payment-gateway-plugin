import getNetworkName from './getNetworkName';
import displayCurrencyNetwork from './displayCurrencyNetwork';

/**
 * Creates a full display name for a currency with its network.
 * Format: "USDT on Ethereum", "USDT on Tron", etc.
 *
 * @param {Object} currency - The currency object or currency string
 * @param {string} currency.currency - The currency code (e.g., 'USDT_ETHEREUM')
 * @param {string} [currency.currencyWithoutNetwork] - Pre-computed base currency (optional)
 * @param {string} [currency.currencyTokenspec] - Pre-computed tokenspec (optional)
 * @returns {string} The formatted display name (e.g., "USDT on Ethereum")
 *
 * @example
 * getNetworkDisplayName({ currency: 'USDT_ETHEREUM' }) // returns 'USDT on Ethereum'
 * getNetworkDisplayName({ currency: 'USDT_TRON' }) // returns 'USDT on Tron'
 */
const getNetworkDisplayName = (currency) => {
  // Handle both string and object input
  const currencyCode = typeof currency === 'string' ? currency : currency.currency;

  // Get base currency (without network suffix)
  const baseCurrency = currency.currencyWithoutNetwork || currencyCode.split('_')[0];

  // Get the token standard/network identifier
  const tokenspec = currency.currencyTokenspec || displayCurrencyNetwork(currencyCode);

  // Convert tokenspec to display name
  const networkName = getNetworkName(tokenspec);

  if (networkName) {
    return `${baseCurrency} on ${networkName}`;
  }

  return baseCurrency;
};

export default getNetworkDisplayName;
