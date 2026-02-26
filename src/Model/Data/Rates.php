<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

/**
 * DTO for multiple cryptocurrency exchange rates information
 */
class Rates
{
    /**
     * Payment ID received from API
     *
     * @var string
     */
    private string $paymentId;

    /**
     * Amount on invoice for FIAT currency
     *
     * @var string
     */
    private string $invoiceAmount;

    /**
     * Currency code for FIAT currency on invoice (EUR, USD, etc.)
     *
     * @var string
     */
    private string $invoiceCurrency;

    /**
     * Sub Account ID
     *
     * @var string|null
     */
    private ?string $sid;

    /**
     * Map of currency codes to rate data
     *
     * @var array
     */
    private array $currencies;

    /**
     * Rates DTO constructor
     *
     * @param string $paymentId
     * @param string $invoiceAmount
     * @param string $invoiceCurrency
     * @param string|null $sid
     * @param array $currencies
     */
    public function __construct(
        string $paymentId,
        string $invoiceAmount,
        string $invoiceCurrency,
        ?string $sid,
        array $currencies
    ) {
        $this->paymentId = $paymentId;
        $this->invoiceAmount = $invoiceAmount;
        $this->invoiceCurrency = $invoiceCurrency;
        $this->sid = $sid;
        $this->currencies = $currencies;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * @return string
     */
    public function getInvoiceAmount(): string
    {
        return $this->invoiceAmount;
    }

    /**
     * @return string
     */
    public function getInvoiceCurrency(): string
    {
        return $this->invoiceCurrency;
    }

    /**
     * @return string|null
     */
    public function getSid(): ?string
    {
        return $this->sid;
    }

    /**
     * @return array
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
            'payment_id' => $this->paymentId,
            'invoice_amount' => $this->invoiceAmount,
            'invoice_currency' => $this->invoiceCurrency,
            'sid' => $this->sid,
            'currencies' => $this->currencies,
        ];
    }
}
