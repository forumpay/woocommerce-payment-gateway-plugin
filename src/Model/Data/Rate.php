<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

/**
 * Dto for specific cryptocurrency exchange rate information
 */
class Rate
{
    /**
     * Currency code for FIAT currency on invoice (EUR, USD, etc.)
     *
     * @var string
     */
    private string $invoiceCurrency;

    /**
     * Amount on invoice for FIAT currency
     *
     * @var string|null
     */
    private ?string $invoiceAmount;

    /**
     * Cryptocurrency symbol (BTC, ETH, etc.)
     *
     * @var string
     */
    private string $currency;

    /**
     * Exchange rate
     *
     * @var string|null
     */
    private ?string $rate;

    /**
     * Amount exchange
     *
     * @var string|null
     */
    private ?string $amountExchange;

    /**
     * Amount needed to transfer cryptocurrency from merchant to exchange
     *
     * @var string
     */
    private string $networkProcessingFee;

    /**
     * Total amount to pay
     *
     * @var string|null
     */
    private ?string $amount;

    /**
     * Expected time to confirm
     *
     * @var string
     */
    private string $waitTime;

    /**
     * Sub Account ID
     *
     * @var string|null
     */
    private ?string $sid;

    /**
     * Minimum TX fee/Gas price for fast transaction.
     *
     * @var string|null
     */
    private ?string $fastTransactionFee;

    /**
     * Currency for fast_transaction_fee (e.g.: BTC/byte, Gwei/Gas)
     *
     * @var string|null
     */
    private ?string $fastTransactionFeeCurrency;

    /**
     * Payment ID received from StartPayment
     *
     * @var string
     */
    private string $paymentId;

    /**
     * Rate DTO constructor
     *
     * @param string $invoiceCurrency
     * @param string|null $invoiceAmount
     * @param string $currency
     * @param string|null $rate
     * @param string|null $amountExchange
     * @param string $networkProcessingFee
     * @param string|null $amount
     * @param string $waitTime
     * @param string|null $sid
     * @param string|null $fastTransactionFee
     * @param string|null $fastTransactionFeeCurrency
     * @param string $paymentId
     */
    public function __construct(
        string $invoiceCurrency,
        ?string $invoiceAmount,
        string $currency,
        ?string $rate,
        ?string $amountExchange,
        string $networkProcessingFee,
        ?string $amount,
        string $waitTime,
        ?string $sid,
        ?string $fastTransactionFee,
        ?string $fastTransactionFeeCurrency,
        string $paymentId
    ) {
        $this->invoiceCurrency = $invoiceCurrency;
        $this->invoiceAmount = $invoiceAmount;
        $this->currency = $currency;
        $this->rate = $rate;
        $this->amountExchange = $amountExchange;
        $this->networkProcessingFee = $networkProcessingFee;
        $this->amount = $amount;
        $this->waitTime = $waitTime;
        $this->sid = $sid;
        $this->fastTransactionFee = $fastTransactionFee;
        $this->fastTransactionFeeCurrency = $fastTransactionFeeCurrency;
        $this->paymentId = $paymentId;
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
    public function getInvoiceAmount(): ?string
    {
        return $this->invoiceAmount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string|null
     */
    public function getRate(): ?string
    {
        return $this->rate;
    }

    /**
     * @return string|null
     */
    public function getAmountExchange(): ?string
    {
        return $this->amountExchange;
    }

    /**
     * @return string
     */
    public function getNetworkProcessingFee(): string
    {
        return $this->networkProcessingFee;
    }

    /**
     * @return string|null
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getWaitTime(): string
    {
        return $this->waitTime;
    }

    /**
     * @return string|null
     */
    public function getSid(): ?string
    {
        return $this->sid;
    }

    /**
     * @return string|null
     */
    public function getFastTransactionFee(): ?string
    {
        return $this->fastTransactionFee;
    }

    /**
     * @return string|null
     */
    public function getFastTransactionFeeCurrency(): ?string
    {
        return $this->fastTransactionFeeCurrency;
    }

    /**
     * @return string
     */
    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    /**
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'invoice_currency' => $this->invoiceCurrency,
            'invoice_amount' => $this->invoiceAmount,
            'currency' => $this->currency,
            'rate' => $this->rate,
            'amount_exchange' => $this->amountExchange,
            'network_processing_fee' => $this->networkProcessingFee,
            'amount' => $this->amount,
            'wait_time' => $this->waitTime,
            'sid' => $this->sid,
            'fast_transaction_fee' => $this->fastTransactionFee,
            'fast_transaction_fee_currency' => $this->fastTransactionFeeCurrency,
            'payment_id' => $this->paymentId,
        ];
    }
}
