const WALLET_CONNECT_SDK_URL = 'https://walletconnect.forumpay.com/walletconnect.sdk.js';
const WALLET_CONNECT_GLOBAL = 'PaymentWalletConnect';

let scriptPromise = null;

export default function loadWalletConnectSdk() {
  if (scriptPromise) return scriptPromise;

  scriptPromise = new Promise((resolve, reject) => {
    if (window[WALLET_CONNECT_GLOBAL]) {
      resolve(window[WALLET_CONNECT_GLOBAL]);
      return;
    }

    const script = document.createElement('script');
    script.src = WALLET_CONNECT_SDK_URL;
    script.async = true;
    script.type = 'text/javascript';

    script.onload = () => {
      const sdk = window[WALLET_CONNECT_GLOBAL];
      if (sdk) {
        resolve(sdk);
      } else {
        scriptPromise = null;
        reject(new Error(`${WALLET_CONNECT_GLOBAL} not found after script loaded.`));
      }
    };

    script.onerror = () => {
      scriptPromise = null;
      reject(new Error('Failed to load WalletConnect SDK.'));
    };

    document.head.appendChild(script);
  });

  return scriptPromise;
}
