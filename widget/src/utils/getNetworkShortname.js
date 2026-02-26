import { BLOCKCHAIN_TO_STANDARD } from './blockchainMappings';
import displayCurrencyNetwork from './displayCurrencyNetwork';

/**
 * Gets the token standard shortname (ERC-20, TRC-20, SPL, etc.) for a currency.
 * If the currency object has a currencyTokenspec, it uses that,
 * otherwise parses the currency string.
 *
 * @param {Object} currency - The currency object
 * @param {string} currency.currency - The currency code (e.g., 'USDT_ETHEREUM')
 * @param {string} [currency.currencyTokenspec] - Pre-computed tokenspec (optional)
 * @returns {string|null} The token standard shortname (e.g., 'ERC-20', 'TRC-20', 'SPL')
 *
 * @example
 * getNetworkShortname({ currency: 'USDT_ETHEREUM' }) // returns 'ERC-20'
 * getNetworkShortname({ currency: 'USDT_POLYGON', currencyTokenspec: 'POLYGON' })
 *   // returns 'ERC-20'
 */
const getNetworkShortname = (currency) => {
  const tokenspec = currency.currencyTokenspec;

  if (!tokenspec) {
    // Fallback to parsing the full currency string
    return displayCurrencyNetwork(currency.currency);
  }

  // If already a standard format (contains hyphen), return as-is
  if (tokenspec.includes('-')) {
    return tokenspec;
  }

  // Map blockchain names to token standards
  return BLOCKCHAIN_TO_STANDARD[tokenspec] || tokenspec;
};

export default getNetworkShortname;
