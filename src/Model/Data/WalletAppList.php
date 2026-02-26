<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\WalletAppList\WalletApp;

/**
 * Dto collection of the available wallet apps
 */
class WalletAppList
{
    /**
     * List of all wallet apps from ForumPay
     *
     * @var WalletApp[]
     */
    private array $walletApps;

    /**
     * WalletAppList DTO constructor
     *
     * @param WalletApp[] $walletApps
     */
    public function __construct(
        array $walletApps
    ) {
        $this->walletApps = $walletApps;
    }

    /**
     * @return WalletApp[]
     */
    public function getWalletApps(): array
    {
        return $this->walletApps;
    }

    /**
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'walletApps' => array_map(
                fn (WalletApp $walletApp) => $walletApp->toArray(),
                $this->walletApps
            ),
        ];
    }
}
