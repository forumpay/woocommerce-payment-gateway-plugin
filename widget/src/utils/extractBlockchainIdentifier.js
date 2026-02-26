/**
 * Extracts the raw blockchain identifier from a currency code WITHOUT converting it.
 * This preserves the original blockchain name (POLYGON, ETHEREUM, TRON, etc.)
 * for proper network display name mapping.
 *
 * @param {string} currency - The currency code (e.g., 'USDT_POLYGON', 'USDT_ETHEREUM')
 * @returns {string|null} The blockchain identifier (e.g., 'POLYGON', 'ETHEREUM', 'TRC20') or null
 *
 * @example
 * extractBlockchainIdentifier('USDT_POLYGON') // returns 'POLYGON'
 * extractBlockchainIdentifier('USDT_ETHEREUM') // returns 'ETHEREUM'
 * extractBlockchainIdentifier('USDT_TRC20') // returns 'TRC20'
 * extractBlockchainIdentifier('BTC') // returns null
 */
const extractBlockchainIdentifier = (currency) => {
  const currencyParts = currency.split('_');
  if (currencyParts.length === 2) {
    return currencyParts[1];
  }
  return null;
};

export default extractBlockchainIdentifier;
