<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ApiHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\OrderNotFoundException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\CurrencyList;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\CurrencyList\Currency;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;

/**
 * @inheritdoc
 */
class GetCurrencyList
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

    public function execute(Request $request): ?CurrencyList
    {
        try {
            $this->logger->info('GetCurrencyList entrypoint called.');

            try {
                $orderId = $request->getRequired('orderId');
            } catch (\InvalidArgumentException $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                throw new OrderNotFoundException(1005);
            }

            $response = $this->forumPay->getCryptoCurrencyList($orderId);

            /** @var CurrencyList[] $currencyDtos */
            $currencyDtos = [];

            /** @var \ForumPay\PaymentGateway\PHPClient\Response\GetCurrencyList\Currency $currency */
            foreach ($response->getCurrencies() as $currency) {
                if ($currency->getStatus() !== 'OK') {
                    continue;
                }

                $currencyDto = new Currency(
                    $currency->getCurrency(),
                    $currency->getDescription(),
                    $currency->getSellStatus(),
                    (bool)$currency->getZeroConfirmationsEnabled(),
                    $currency->getCurrencyFiat(),
                    $currency->getRate()
                );
                $currencyDtos[] = $currencyDto;
            }

            $this->logger->debug('GetCurrencyList response.', ['response' => $currencyDtos]);
            $this->logger->info('GetCurrencyList entrypoint finished.');

            return new CurrencyList($currencyDtos);
        } catch (ApiExceptionInterface $e) {
            $this->logger->logApiException($e);
            throw new ApiHttpException($e, 1050);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception($e->getMessage(), 1100, $e);
        }
    }
}
