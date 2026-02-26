/**
 * Maps blockchain names to their token standards.
 * Used for normalizing blockchain identifiers across the application.
 */
export const BLOCKCHAIN_TO_STANDARD = {
  ETHEREUM: 'ERC-20',
  TRON: 'TRC-20',
  TRC20: 'TRC-20',
  POLYGON: 'ERC-20',
  SOLANA: 'SPL',
  BNB: 'BEP-20',
  BSC: 'BEP-20',
};

/**
 * Maps blockchain identifiers and token standards to human-readable network display names.
 * Used for displaying network names in the UI.
 */
export const NETWORK_DISPLAY_NAMES = {
  // Raw blockchain identifiers
  POLYGON: 'Polygon',
  ETHEREUM: 'Ethereum',
  TRON: 'Tron',
  SOLANA: 'Solana',
  BNB: 'BNB Chain',
  BSC: 'BNB Chain',
  // Legacy format
  TRC20: 'Tron',
  // Token standards
  'ERC-20': 'Ethereum',
  'TRC-20': 'Tron',
  'BEP-20': 'BNB Chain',
  SPL: 'Solana',
};
