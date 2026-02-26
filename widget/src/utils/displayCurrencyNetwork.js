import { BLOCKCHAIN_TO_STANDARD } from './blockchainMappings';

const displayCurrencyNetwork = (currency) => {
  if (['USDT', 'USDC'].includes(currency)) {
    return 'ERC-20';
  }
  const currencyParts = currency.split('_');
  if (currencyParts.length === 2) {
    if (currencyParts[1] === 'TRC20') {
      return 'TRC-20';
    }

    // Map blockchain names to token standards using shared mapping
    return BLOCKCHAIN_TO_STANDARD[currencyParts[1]] || currencyParts[1];
  }
  return null;
};

export default displayCurrencyNetwork;
