const formatCurrencyDisplayName = (currency, withoutNetwork = false) => {
  // JS reimplementation of PayCurrencies::GetDisplayName()
  let currencyParts = currency.split('_');
  if (currency === 'USDCE_POLYGON') {
    currencyParts = ['USDC.e', 'POLYGON'];
  }
  if (withoutNetwork) {
    return currencyParts[0];
  }
  if (currencyParts[0] === 'USDT' && currencyParts.length < 2) {
    currencyParts = ['USDT', 'ERC-20'];
  }
  if (currencyParts[0] === 'USDC' && currencyParts.length < 2) {
    currencyParts = ['USDC', 'ERC-20'];
  }
  if (currencyParts.length > 1) {
    if (currencyParts[1] === 'TRC20') {
      currencyParts[1] = 'TRC-20';
    }
    return `${currencyParts[0]} (${currencyParts[1]})`;
  }
  return currencyParts[0];
};

export default formatCurrencyDisplayName;
