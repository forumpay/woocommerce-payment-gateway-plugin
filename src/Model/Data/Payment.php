<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payment\Notice;

/**
 * Dto for basic payment information
 */
class Payment
{
    /**
     * Get unique payment id from ForumPay
     *
     * @var string
     */
    private string $paymentId;

    /**
     * Get payment address connected to the payment id
     *
     * @var string
     */
    private string $address;

    /**
     * Amount missing from initial payment to complete payment.
     *
     * @var string
     */
    private string $missingAmount;

    /**
     * Minimum confirmation pears needed
     *
     * @var int
     */
    private int $minConfirmations;

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
     * String for qr code that includes cryptocurrency, address and amount
     *
     * @var string
     */
    private string $qr;

    /**
     * Alternative string for qr code for legacy wallets including address only
     *
     * @var string
     */
    private string $qrAlt;

    /**
     * URL for qr code image download
     *
     * @var string
     */
    private string $qrImg;

    /**
     * URL for qr_alt code image download
     *
     * @var string
     */
    private string $qrAltImg;

    /**
     * Returns the list of all notices
     *
     * @var Notice[]
     */
    private array $notices;

    /**
     * Payment DTO constructor
     *
     * @param string $paymentId
     * @param string $address
     * @param string $missingAmount
     * @param int $minConfirmations
     * @param string|null $fastTransactionFee
     * @param string|null $fastTransactionFeeCurrency
     * @param string $qr
     * @param string $qrAlt
     * @param string $qrImg
     * @param string $qrAltImg
     * @param array $notices
     */
    public function __construct(
        string $paymentId,
        string $address,
        string $missingAmount,
        int $minConfirmations,
        ?string $fastTransactionFee,
        ?string $fastTransactionFeeCurrency,
        string $qr,
        string $qrAlt,
        string $qrImg,
        string $qrAltImg,
        array $notices = []
    ) {
        $this->paymentId = $paymentId;
        $this->address = $address;
        $this->missingAmount = $missingAmount;
        $this->minConfirmations = $minConfirmations;
        $this->fastTransactionFee = $fastTransactionFee;
        $this->fastTransactionFeeCurrency = $fastTransactionFeeCurrency;
        $this->qr = $qr;
        $this->qrAlt = $qrAlt;
        $this->qrImg = $qrImg;
        $this->qrAltImg = $qrAltImg;
        $this->notices = $notices;
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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getMissingAmount(): string
    {
        return $this->missingAmount;
    }

    /**
     * @return int
     */
    public function getMinConfirmations(): int
    {
        return $this->minConfirmations;
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
    public function getQr(): string
    {
        return $this->qr;
    }

    /**
     * @return string
     */
    public function getQrAlt(): string
    {
        return $this->qrAlt;
    }

    /**
     * @return string
     */
    public function getQrImg(): string
    {
        return $this->qrImg;
    }

    /**
     * @return string
     */
    public function getQrAltImg(): string
    {
        return $this->qrAltImg;
    }

    /**
     * @return array|Notice[]
     */
    public function getNotices(): array
    {
        return $this->notices;
    }

    /**
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'missing_amount' => $this->missingAmount,
            'min_confirmations' => $this->minConfirmations,
            'fast_transaction_fee' => $this->fastTransactionFee,
            'fast_transaction_fee_currency' => $this->fastTransactionFeeCurrency,
            'qr' => $this->qr,
            'qr_alt' => $this->qrAlt,
            'qr_img' => $this->qrImg,
            'qr_alt_img' => $this->qrAltImg,
            'notices' => array_map(
                fn (Notice $notice) => $notice->toArray(),
                $this->notices
            ),
            'payment_id' => $this->paymentId,
        ];
    }
}
