<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ForumPayException;
use ForumPay\PaymentGateway\WoocommercePlugin\ForumPayPaymentGateway;
use ForumPay\PaymentGateway\PHPClient\Response\GetTransactions\TransactionInvoice;
use ForumPay\PaymentGateway\PHPClient\PaymentGatewayApi;
use ForumPay\PaymentGateway\PHPClient\PaymentGatewayApiInterface;
use ForumPay\PaymentGateway\PHPClient\Response\CheckPaymentResponse;
use ForumPay\PaymentGateway\PHPClient\Response\GetCurrencyListResponse;
use ForumPay\PaymentGateway\PHPClient\Response\GetRateResponse;
use ForumPay\PaymentGateway\PHPClient\Response\StartPaymentResponse;
use Psr\Log\LoggerInterface;
use WC_Meta_Data;

/**
 * ForumPay payment method model
 */
class ForumPay
{
    /**
     * @var PaymentGatewayApiInterface
     */
    private PaymentGatewayApiInterface $apiClient;

    /**
     * @var ForumPayPaymentGateway
     */
    private ForumPayPaymentGateway $gateway;

    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $psrLogger;

    public function __construct(
        ForumPayPaymentGateway $gateway,
        OrderManager $orderManager,
        LoggerInterface $psrLogger
    ) {
        $this->apiClient = new PaymentGatewayApi(
            $gateway->getApiUrl(),
            $gateway->getMerchantApiUser(),
            $gateway->getMerchantApiSecret(),
            sprintf(
                "fp-pgw[%s] WC %s WP %s on PHP %s",
                $gateway->getPluginVersion(),
                'WC',
                $gateway->getWordpressVersion(),
                $gateway->getWooCommerceVersion(),
                phpversion()
            ),
            $gateway->getStoreLocale(),
            null,
            $psrLogger
        );

        $this->gateway = $gateway;
        $this->orderManager = $orderManager;
        $this->psrLogger = $psrLogger;
    }

    /**
     * Return the list of all available currencies as defined on merchant account
     *
     * @param string $orderId
     * @return GetCurrencyListResponse
     * @throws \Exception
     */
    public function getCryptoCurrencyList(string $orderId): GetCurrencyListResponse
    {
        $currency = $this->orderManager->getOrderCurrency($orderId);

        if (empty($currency)) {
            throw new \Exception('Store currency could not be determined');
        }

        return $this->apiClient->getCurrencyList($currency);
    }

    /**
     * Get rate for a requested currency
     *
     * @param string $orderId
     * @param string $currency
     * @return GetRateResponse
     * @throws \Exception
     */
    public function getRate(string $orderId, string $currency): GetRateResponse
    {
        $order = $this->orderManager->getOrder($orderId);
        if (!$order) {
            throw new \Exception("Order is not active. Order is already created.");
        }

        return $this->apiClient->getRate(
            $this->gateway->getPosId(),
            $this->orderManager->getOrderCurrency($orderId),
            (string)$this->orderManager->getOrderTotal($orderId),
            $currency,
            $this->gateway->isAcceptZeroConfirmations() ? 'true' : 'false',
            null,
            null,
            null
        );
    }

    /**
     * Initiate a start payment and crate order on ForumPay
     *
     * @param string $orderId
     * @param string $currency
     * @param string $paymentId
     * @return StartPaymentResponse
     */
    public function startPayment(
        string $orderId,
        string $currency,
        string $paymentId
    ): StartPaymentResponse
    {
        $response = $this->apiClient->startPayment(
            $this->gateway->getPosId(),
            $this->orderManager->getOrderCurrency($orderId),
            $paymentId,
            (string)$this->orderManager->getOrderTotal($orderId),
            $currency,
            $orderId,
            $this->gateway->isAcceptZeroConfirmations() ? 'true' : 'false',
            $this->orderManager->getOrderCustomerIpAddress($orderId),
            $this->orderManager->getOrderCustomerEmail($orderId),
            $this->orderManager->getOrderCustomerId($orderId),
            false,
            false,
            false,
            null,
            null,
            null,
            null
        );

        $this->orderManager->saveOrderMetaData($orderId, 'startPayment', $response->toArray());
        $this->orderManager->saveOrderMetaData($orderId, 'payment_formumpay_paymentId_last', $paymentId, true);

        $this->cancelAllPayments($orderId, $response->getPaymentId());

        return $response;
    }

    /**
     * Get detailed payment information for ForumPay
     *
     * @param string $paymentId
     * @return CheckPaymentResponse
     * @throws ForumPayException
     */
    public function checkPayment(string $orderId, string $paymentId): CheckPaymentResponse
    {
        $meta = $this->getStartPaymentMetaData($orderId, $paymentId);

        $address = $meta['address'];
        $cryptoCurrency = $meta['currency'];

        $response = $this->apiClient->checkPayment(
            $this->gateway->getPosId(),
            $cryptoCurrency,
            $paymentId,
            $address
        );

        if (strtolower($response->getStatus()) === 'cancelled') {
            if (!$this->checkAllPaymentsAreCanceled($orderId)) {
                return $response;
            }
        }

        $this->orderManager->updateOrderStatus($orderId, $response->getStatus(), $paymentId);
        $this->orderManager->saveOrderMetaData($orderId, 'payment_formumpay_paymentId_last', $paymentId, true);

        return $response;
    }

    /**
     * Cancel give payment on ForumPay
     *
     * @param string $orderId
     * @param string $paymentId
     * @param string $reason
     * @param string $description
     */
    public function cancelPaymentByPaymentId(string $orderId, string $paymentId, string $reason = '', string $description = '')
    {
        $meta = $this->getStartPaymentMetaData($orderId, $paymentId);
        $currency = $meta['currency'];
        $address = $meta['address'];
        $this->cancelPayment($paymentId, $currency, $address, $reason, $description);
    }

    /**
     * Cancel give payment on ForumPay
     *
     * @param string $paymentId
     * @param string $currency
     * @param string $address
     */
    public function cancelPayment(string $paymentId, string $currency, string $address, string $reason = '', string $description = '')
    {
        $this->apiClient->cancelPayment(
            $this->gateway->getPosId(),
            $currency,
            $paymentId,
            $address,
            $reason,
            substr($description, 0, 255),
        );
    }

    /**
     * Cancel all except existingPayment on ForumPay
     *
     * @param string $orderId
     * @param $existingPaymentId
     */
    private function cancelAllPayments(string $orderId, $existingPaymentId)
    {
        $existingPayments = $this->apiClient->getTransactions(null, null, $orderId);

        /** @var TransactionInvoice $existingPayment */
        foreach ($existingPayments->getInvoices() as $existingPayment) {

            if (
                $existingPayment->getPaymentId() === $existingPaymentId
                || strtolower($existingPayment->getStatus()) !== 'waiting'
            ) {
                //newly created
                continue;
            }

            $this->cancelPayment(
                $existingPayment->getPaymentId(),
                $existingPayment->getCurrency(),
                $existingPayment->getAddress()
            );
        }
    }

    /**
     * Check if all payments for a given order are canceled on ForumPay
     *
     * @param string $orderId
     * @return bool
     */
    private function checkAllPaymentsAreCanceled(string $orderId): bool
    {
        $existingPayments = $this->apiClient->getTransactions(null, null, $orderId);

        /** @var TransactionInvoice $existingPayment */
        foreach ($existingPayments->getInvoices() as $existingPayment) {
            if (strtolower($existingPayment->getStatus()) !== 'cancelled') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get return startPayment response from metadata for given paymentId
     *
     * @param string $orderId
     * @param string $paymentId
     * @return array
     */
    private function getStartPaymentMetaData(string $orderId, string $paymentId): ?array
    {
        $startPaymentResponses = $this->orderManager->getOrderMetaData($orderId, 'startPayment');

        /** @var WC_Meta_Data $response */
        foreach ($startPaymentResponses as $response) {
            if ($response->get_data()['value']['payment_id'] === $paymentId) {
                return $response->get_data()['value'];
            }
        }

        return null;
    }
}
