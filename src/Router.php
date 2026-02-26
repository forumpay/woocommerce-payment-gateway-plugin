<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ApiHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ForumPayException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ForumPayHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\CancelPayment;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\CheckPayment;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\GetCurrencyList;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\GetCurrencyRate;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\GetCurrencyRates;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\GetWalletApps;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Ping;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\RestoreCart;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\StartPayment;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Webhook;

/**
 * Maps action parameter to the responsible action.
 */
class Router
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
     * Available routes
     *
     * @var array
     */
    private array  $routes = [];

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

        $this->initRoutes();
    }

    protected function initRoutes()
    {
        $this->routes = [
            'ping' => new Ping($this->forumPay, $this->logger),
            'currencies' => new GetCurrencyList($this->forumPay, $this->logger),
            'getRate' => new GetCurrencyRate($this->forumPay, $this->logger),
            'getRates' => new GetCurrencyRates($this->forumPay, $this->logger),
            'startPayment' => new StartPayment($this->forumPay, $this->logger),
            'checkPayment' => new CheckPayment($this->forumPay, $this->logger),
            'cancelPayment' => new CancelPayment($this->forumPay, $this->logger),
            'webhook' => new Webhook($this->forumPay, $this->logger),
            'restoreCart' => new RestoreCart($this->forumPay, $this->logger),
            'syncPayment' => new CheckPayment($this->forumPay, $this->logger),
            'getWalletApps' => new GetWalletApps($this->forumPay, $this->logger),
        ];
    }

    /**
     * Execute HTTP request and return serialized response
     *
     * @param Request $request
     * @return string|null
     */
    public function execute(Request $request): ?string
    {
        try {
            $routePrecheck = filter_input(INPUT_GET, 'act', FILTER_SANITIZE_STRING);
            if (in_array($routePrecheck, ['webhook', 'ping', 'syncPayment'])) {
                $route = $routePrecheck;
            } else {
                $route = $request->getRequired('act', true);
            }

            if (array_key_exists($route, $this->routes)) {
                $service = $this->routes[$route];
                $response = $service->execute($request);
                if ($response !== null) {
                    return $this->serializeResponse($response);
                }
            }
            $this->logger->info('Webhook entrypoint called.', ['request' => $request]);
        } catch (ApiHttpException $e) {
            return $this->serializeError($e);
        } catch (ForumPayException $e) {
            return $this->serializeError(
                new ForumPayHttpException(
                    $e->getMessage(),
                    '',
                    $e->getCode(),
                    ForumPayHttpException::HTTP_BAD_REQUEST
                )
            );
        } catch (ForumPayHttpException $e) {
            return $this->serializeError($e);
        } catch (\Exception $e) {
            return $this->serializeError(
                new ForumPayHttpException(
                    $e->getMessage(),
                    '',
                    $e->getCode(),
                    ForumPayHttpException::HTTP_INTERNAL_ERROR,
                )
            );
        }

        return null;
    }

    /**
     * @param $response
     * @return false|string
     */
    private function serializeResponse($response)
    {
        return wp_json_encode($response->toArray());
    }

    /**
     * @param ForumPayHttpException $e
     * @return false|string
     */
    private function serializeError(ForumPayHttpException $e) {
        $repose = new Response();
        $repose->setHttpResponseCode($e->getHttpCode());
        return wp_json_encode([
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'cfray_id' => $e->getCfRayId(),
        ]);
    }
}
