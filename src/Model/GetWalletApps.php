<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ApiHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\WalletAppList;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\WalletAppList\WalletApp;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;

/**
 * @inheritdoc
 */
class GetWalletApps
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

    public function execute(Request $request): ?WalletAppList
    {
        try {
            $this->logger->info('GetWalletApps entrypoint called.');

            $response = $this->forumPay->getWalletApps();

            /** @var WalletApp[] $walletAppDtos */
            $walletAppDtos = [];

            /** @var \ForumPay\PaymentGateway\PHPClient\Response\GetWalletApps\WalletApp $walletApp */
            foreach ($response->getWalletApps() as $walletApp) {
                $walletAppDto = new WalletApp(
                    $walletApp->getId(),
                    $walletApp->getName(),
                    $walletApp->getImage(),
                    $walletApp->getImageDarkmode()
                );
                $walletAppDtos[] = $walletAppDto;
            }

            $this->logger->debug('GetWalletApps response.', ['response' => $walletAppDtos]);
            $this->logger->info('GetWalletApps entrypoint finished.');

            return new WalletAppList($walletAppDtos);
        } catch (ApiExceptionInterface $e) {
            $this->logger->logApiException($e);
            throw new ApiHttpException($e, 1050);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception($e->getMessage(), 1100, $e);
        }
    }
}
