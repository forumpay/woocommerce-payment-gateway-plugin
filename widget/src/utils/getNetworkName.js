import { NETWORK_DISPLAY_NAMES } from './blockchainMappings';

/**
 * Converts a blockchain identifier or token standard to a human-readable network name.
 * This handles both raw blockchain identifiers (POLYGON, ETHEREUM, TRON, TRC20)
 * and standardized token formats (ERC-20, TRC-20, SPL, BEP-20).
 *
 * @param {string} tokenspec - The blockchain identifier or token standard
 * @returns {string|null} The display name (e.g., 'Ethereum', 'Tron', 'Polygon')
 *   or the original tokenspec if no mapping exists
 *
 * @example
 * getNetworkName('POLYGON') // returns 'Polygon'
 * getNetworkName('ETHEREUM') // returns 'Ethereum'
 * getNetworkName('TRC20') // returns 'Tron'
 * getNetworkName('ERC-20') // returns 'Ethereum'
 * getNetworkName('TRC-20') // returns 'Tron'
 */
const getNetworkName = (tokenspec) => {
  if (!tokenspec) return null;

  return NETWORK_DISPLAY_NAMES[tokenspec] || tokenspec;
};

export default getNetworkName;
