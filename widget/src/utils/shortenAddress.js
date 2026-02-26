const shortenAddress = (address, visibleChars = 9) => {
  if (address.length <= visibleChars * 2 + 3) return address;
  return `${address.substring(0, visibleChars)}...${address.slice(-visibleChars)}`;
};

export default shortenAddress;
