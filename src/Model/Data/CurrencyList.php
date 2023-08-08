<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\CurrencyList\Currency;

/**
 * Dto collection of the available currency list
 */
class CurrencyList
{
    /**
     * List of all defined cryptocurrencies from ForumPay
     *
     * @var Currency[]
     */
    private array $currencies;

    /**
     * CurrencyList DTO constructor
     *
     * @param Currency[] $currencies
     */
    public function __construct(
        array $currencies
    ) {
        $this->currencies = $currencies;
    }

    /**
     * @return Currency[]
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'currencies' => array_map(
                fn (Currency $currency) => $currency->toArray(),
                $this->currencies
            ),
        ];
    }
}
