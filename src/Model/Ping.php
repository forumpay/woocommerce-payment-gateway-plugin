<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ForumPayException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ForumPayHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiErrorException;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\InvalidApiResponseException;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\InvalidResponseStatusCodeException;

/**
 * @inheritdoc
 */
class Ping
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

    public function execute(Request $request): ?\ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Ping
    {
        try {
            $this->logger->info('Ping entrypoint called.');

            try {
                $apiEnv = $request->getRequired('apiEnv', true);
                $apiKey = $request->getRequired('apiKey', true);
                $apiSecret = $request->getRequired('apiSecret', true);
                $apiUrlOverride = $request->getRequired('apiUrlOverride', true);
            } catch (\InvalidArgumentException $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                throw new ForumPayException('There has been an error, check WooCommerce logs for more.');
            }

            $response = $this->forumPay->ping($apiEnv, $apiKey, $apiSecret, $apiUrlOverride);

            $this->logger->debug('Ping response.', ['response' => $response->toArray()]);
            $this->logger->info('Ping entrypoint finished.');

            return new \ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Ping('OK');

        } catch (InvalidApiResponseException $e) {
            $this->logger->logApiException($e);
            throw new ForumPayHttpException($e->getMessage(), $e->getCfRayId(), intval(0));
        } catch (InvalidResponseStatusCodeException $e) {
            $this->logger->logApiException($e);
            throw new ForumPayHttpException($e->getMessage(), $e->getCfRayId(),$e->getResponseStatusCode() ?? 500);
        } catch (ApiExceptionInterface $e) {
            $this->logger->logApiException($e);
            throw new ForumPayHttpException($e->getMessage(), $e->getCfRayId(),$e->getCode() ?? 500);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception($e->getMessage(), 500, $e);
        }
    }
}
