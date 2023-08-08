<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\CurrencyList;

/**
 * Dto of cryptocurrency
 */
class Currency
{
    /**
     * Cryptocurrency symbol (BTC, ETH, etc.)
     *
     * @var string
     */
    private string $currency;

    /**
     * Currency description
     *
     * @var string
     */
    private string $description;

    /**
     * 'OK' if currency can be used
     *
     * @var string
     */
    private string $status;

    /**
     * If currency supports zero confirmations
     *
     * @var bool
     */
    private bool $zeroConfirmationsEnabled;

    /**
     * FIAT currency
     *
     * @var string
     */
    private string $currencyFiat;

    /**
     * Exchange rate
     *
     * @var string|null
     */
    private ?string $rate;

    /**
     * Currency DTO constructor
     *
     * @param string $currency
     * @param string $description
     * @param string $status
     * @param bool $zeroConfirmationsEnabled
     * @param string $currencyFiat
     * @param string|null $rate
     */
    public function __construct(
        string $currency,
        string $description,
        string $status,
        bool $zeroConfirmationsEnabled,
        string $currencyFiat,
        ?string $rate
    ) {
        $this->currency = $currency;
        $this->description = $description;
        $this->status = $status;
        $this->zeroConfirmationsEnabled = $zeroConfirmationsEnabled;
        $this->currencyFiat = $currencyFiat;
        $this->rate = $rate;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isZeroConfirmationsEnabled(): bool
    {
        return $this->zeroConfirmationsEnabled;
    }

    /**
     * @return string
     */
    public function getCurrencyFiat(): string
    {
        return $this->currencyFiat;
    }

    /**
     * @return string|null
     */
    public function getRate(): ?string
    {
        return $this->rate;
    }

    /**
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'description' => $this->description,
            'status' => $this->status,
            'zero_confirmations_enabled' => $this->zeroConfirmationsEnabled,
            'currency_fiat' => $this->currencyFiat,
            'rate' => $this->rate
        ];
    }
}
