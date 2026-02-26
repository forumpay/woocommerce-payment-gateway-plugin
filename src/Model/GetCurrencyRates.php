<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ApiHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\OrderNotFoundException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Rates;

/**
 * Get rates for multiple cryptocurrencies
 */
class GetCurrencyRates
{
    /**
     * ForumPay payment model
     *
     * @var ForumPay
     */
    private ForumPay $forumPay;

    /**
     * @var ForumPayLogger
     */
    private ForumPayLogger $logger;

    /**
     * Constructor
     *
     * @param ForumPay $forumPay
     * @param ForumPayLogger $logger
     */
    public function __construct(
        ForumPay $forumPay,
        ForumPayLogger $logger
    ) {
        $this->forumPay = $forumPay;
        $this->logger = $logger;
    }

    public function execute(Request $request): Rates
    {
        try {
            try {
                $orderId = $request->getRequired('orderId');
            } catch (\InvalidArgumentException $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                throw new OrderNotFoundException(2005);
            }

            $currencies = $request->getRequired('currencies');
            $this->logger->info('GetCurrencyRates entrypoint called.', ['currencies' => $currencies]);

            $response = $this->forumPay->getRates($orderId, $currencies);

            // Response is now a typed GetRatesResponse object
            $rates = new Rates(
                $response->getPaymentId(),
                $response->getInvoiceAmount(),
                $response->getInvoiceCurrency(),
                $response->getSid(),
                $response->getCurrencies()
            );

            $this->logger->info('GetCurrencyRates entrypoint finished.');

            return $rates;
        } catch (ApiExceptionInterface $e) {
            $this->logger->logApiException($e);
            throw new ApiHttpException($e, 2050);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception($e->getMessage(), 2100, $e);
        }
    }
}
