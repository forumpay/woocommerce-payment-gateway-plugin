<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\PaymentDetails\Underpayment;

/**
 * Dto for detailed payment information
 */
class PaymentDetails
{
    /**
     * Payment reference number, Magento incrementalID is sent in start payment
     * @var string|null
     */
    private ?string $referenceNo;

    /**
     * Date and time of the transaction
     *
     * @var string
     */
    private string $inserted;

    /**
     * Amount on invoice for FIAT currency
     *
     * @var string|null
     */
    private ?string $invoiceAmount;

    /**
     * Type of order
     *
     * @var string
     */
    private string $type;

    /**
     * Currency code for FIAT currency on invoice (EUR, USD, etc.)
     *
     * @var string
     */
    private string $invoiceCurrency;

    /**
     * Total amount to pay
     *
     * @var string|null
     */
    private ?string $amount;

    /**
     * Original total amount to pay (before auto-accept adjustment)
     *
     * @var string|null
     */
    private ?string $originalAmount;

    /**
     * Minimum confirmations to wait (this is informal data. Always wait till confirmed=true)
     *
     * @var int
     */
    private int $minConfirmations;

    /**
     * Return true if accepts zero confirmation
     *
     * @var bool
     */
    private bool $acceptZeroConfirmations;

    /**
     * Require KYT check to succeed before confirming the payment
     *
     * @var bool
     */
    private bool $requireKytForConfirmation;

    /**
     * Cryptocurrency symbol (BTC, ETH..) to exchange the fiat into
     *
     * @var string
     */
    private string $currency;

    /**
     * Is transaction successful and confirmed
     *
     * @var bool
     */
    private bool $confirmed;

    /**
     * Time of confirmation
     *
     * @var string|null
     */
    private ?string $confirmedTime;

    /**
     * Consumer's reason for payment cancellation
     *
     * @var string|null
     */
    private ?string $reason;

    /**
     * Received amount of payment
     *
     * @var string|null
     */
    private ?string $payment;

    /**
     * Sub Account ID
     *
     * @var string|null
     */
    private ?string $sid;

    /**
     * Received confirmations of payment
     *
     * @var string
     */
    private string $confirmations;

    /**
     * Access token
     *
     * @var string|null
     */
    private ?string $accessToken;

    /**
     * Public URL for check payment preview
     *
     * @var string|null
     */
    private ?string $accessUrl;

    /**
     * Expected time to confirm
     *
     * @var string|null
     */
    private ?string $waitTime;

    /**
     * Payment status description for user
     *
     * @var string
     */
    private string $status;

    /**
     * Is cancellation successful
     *
     * @var bool
     */
    private bool $cancelled;

    /**
     * Time of cancellation
     *
     * @var string|null
     */
    private ?string $cancelledTime;

    /**
     * Status in human-readable string
     *
     * @var string|null
     */
    private ?string $printString;

    /**
     * Payment states: "waiting", "processing", "cancelled", "confirmed", "zero_confirmed", "underpayment", "blocked"
     *
     * @var string
     */
    private string $state;

    /**
     * Underpayment status of the payment
     *
     * @var Underpayment|null
     */
    private ?Underpayment $underpayment;

    /**
     * Item name being purchased
     *
     * @var string|null
     */
    private ?string $itemName;

    /**
     * Processing fee/surcharge amount
     *
     * @var string|null
     */
    private ?string $invoiceSurchargeAmount;

    /**
     * Total invoice amount including surcharge
     *
     * @var string|null
     */
    private ?string $invoiceAmountWithSurcharge;

    /**
     * Surcharge percentage
     *
     * @var string|null
     */
    private ?string $invoiceSurchargePercent;

    /**
     * PaymentDetails DTO constructor
     *
     * @param string|null $referenceNo
     * @param string $inserted
     * @param string|null $invoiceAmount
     * @param string $type
     * @param string $invoiceCurrency
     * @param string|null $amount
     * @param string|null $originalAmount
     * @param int $minConfirmations
     * @param bool $acceptZeroConfirmations
     * @param bool $requireKytForConfirmation
     * @param string $currency
     * @param bool $confirmed
     * @param string|null $confirmedTime
     * @param string|null $reason
     * @param string|null $payment
     * @param string|null $sid
     * @param string $confirmations
     * @param string|null $accessToken
     * @param string|null $accessUrl
     * @param string|null $waitTime
     * @param string $status
     * @param bool $cancelled
     * @param string|null $cancelledTime
     * @param string|null $printString
     * @param string $state
     * @param Underpayment|null $underpayment
     * @param string|null $itemName
     * @param string|null $invoiceSurchargeAmount
     * @param string|null $invoiceAmountWithSurcharge
     * @param string|null $invoiceSurchargePercent
     */
    public function __construct(
        ?string $referenceNo,
        string $inserted,
        ?string $invoiceAmount,
        string $type,
        string $invoiceCurrency,
        ?string $amount,
        ?string $originalAmount,
        int $minConfirmations,
        bool $acceptZeroConfirmations,
        bool $requireKytForConfirmation,
        string $currency,
        bool $confirmed,
        ?string $confirmedTime,
        ?string $reason,
        ?string $payment,
        ?string $sid,
        string $confirmations,
        ?string $accessToken,
        ?string $accessUrl,
        ?string $waitTime,
        string $status,
        bool $cancelled,
        ?string $cancelledTime,
        ?string $printString,
        string $state,
        ?Underpayment $underpayment = null,
        ?string $itemName = null,
        ?string $invoiceSurchargeAmount = null,
        ?string $invoiceAmountWithSurcharge = null,
        ?string $invoiceSurchargePercent = null
    ) {
        $this->referenceNo = $referenceNo;
        $this->inserted = $inserted;
        $this->invoiceAmount = $invoiceAmount;
        $this->type = $type;
        $this->invoiceCurrency = $invoiceCurrency;
        $this->amount = $amount;
        $this->originalAmount = $originalAmount;
        $this->minConfirmations = $minConfirmations;
        $this->acceptZeroConfirmations = $acceptZeroConfirmations;
        $this->requireKytForConfirmation = $requireKytForConfirmation;
        $this->currency = $currency;
        $this->confirmed = $confirmed;
        $this->confirmedTime = $confirmedTime;
        $this->reason = $reason;
        $this->payment = $payment;
        $this->sid = $sid;
        $this->confirmations = $confirmations;
        $this->accessToken = $accessToken;
        $this->accessUrl = $accessUrl;
        $this->waitTime = $waitTime;
        $this->status = $status;
        $this->cancelled = $cancelled;
        $this->cancelledTime = $cancelledTime;
        $this->printString = $printString;
        $this->state = $state;
        $this->underpayment = $underpayment;
        $this->itemName = $itemName;
        $this->invoiceSurchargeAmount = $invoiceSurchargeAmount;
        $this->invoiceAmountWithSurcharge = $invoiceAmountWithSurcharge;
        $this->invoiceSurchargePercent = $invoiceSurchargePercent;
    }

    /**
     * @return string|null
     */
    public function getReferenceNo(): ?string
    {
        return $this->referenceNo;
    }

    /**
     * @return string
     */
    public function getInserted(): string
    {
        return $this->inserted;
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
    public function getType(): string
    {
        return $this->type;
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
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function getOriginalAmount(): ?string
    {
        return $this->originalAmount;
    }

    /**
     * @return int
     */
    public function getMinConfirmations(): int
    {
        return $this->minConfirmations;
    }

    /**
     * @return bool
     */
    public function isAcceptZeroConfirmations(): bool
    {
        return $this->acceptZeroConfirmations;
    }

    /**
     * @return bool
     */
    public function isRequireKytForConfirmation(): bool
    {
        return $this->requireKytForConfirmation;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @return string|null
     */
    public function getConfirmedTime(): ?string
    {
        return $this->confirmedTime;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return string|null
     */
    public function getPayment(): ?string
    {
        return $this->payment;
    }

    /**
     * @return string|null
     */
    public function getSid(): ?string
    {
        return $this->sid;
    }

    /**
     * @return string
     */
    public function getConfirmations(): string
    {
        return $this->confirmations;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @return string|null
     */
    public function getAccessUrl(): ?string
    {
        return $this->accessUrl;
    }

    /**
     * @return string|null
     */
    public function getWaitTime(): ?string
    {
        return $this->waitTime;
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
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * @return string|null
     */
    public function getCancelledTime(): ?string
    {
        return $this->cancelledTime;
    }

    /**
     * @return string|null
     */
    public function getPrintString(): ?string
    {
        return $this->printString;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return Underpayment|null
     */
    public function getUnderpayment(): ?Underpayment
    {
        return $this->underpayment;
    }

    /**
     * @return string|null
     */
    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    /**
     * @return string|null
     */
    public function getInvoiceSurchargeAmount(): ?string
    {
        return $this->invoiceSurchargeAmount;
    }

    /**
     * @return string|null
     */
    public function getInvoiceAmountWithSurcharge(): ?string
    {
        return $this->invoiceAmountWithSurcharge;
    }

    /**
     * @return string|null
     */
    public function getInvoiceSurchargePercent(): ?string
    {
        return $this->invoiceSurchargePercent;
    }

    /**
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'reference_no' => $this->referenceNo,
            'inserted' => $this->inserted,
            'invoice_amount' => $this->invoiceAmount,
            'type' => $this->type,
            'invoice_currency' => $this->invoiceCurrency,
            'amount' => $this->amount,
            'original_amount' => $this->originalAmount,
            'min_confirmations' => $this->minConfirmations,
            'accept_zero_confirmations' => $this->acceptZeroConfirmations,
            'require_kyt_for_confirmation' => $this->requireKytForConfirmation,
            'currency' => $this->currency,
            'confirmed' => $this->confirmed,
            'confirmed_time' => $this->confirmedTime,
            'reason' => $this->reason,
            'payment' => $this->payment,
            'sid' => $this->sid,
            'confirmations' => $this->confirmations,
            'access_token' => $this->accessToken,
            'access_url' => $this->accessUrl,
            'wait_time' => $this->waitTime,
            'status' => $this->status,
            'underpayment' => $this->underpayment !== null ? $this->underpayment->toArray() : null,
            'cancelled' => $this->cancelled,
            'cancelled_time' => $this->cancelledTime,
            'print_string' => $this->printString,
            'state' => $this->state,
            'item_name' => $this->itemName,
            'invoice_surcharge_amount' => $this->invoiceSurchargeAmount,
            'invoice_amount_with_surcharge' => $this->invoiceAmountWithSurcharge,
            'invoice_surcharge_percent' => $this->invoiceSurchargePercent,
        ];
    }
}
