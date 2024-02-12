const formatCurrencyName = (currency) => {
  const underscoreIndex = currency.indexOf('_');
  if (underscoreIndex === -1) {
    return currency;
  }

  return `${currency.slice(0, underscoreIndex)} (${currency.slice(underscoreIndex + 1)})`;
};

export default formatCurrencyName;
