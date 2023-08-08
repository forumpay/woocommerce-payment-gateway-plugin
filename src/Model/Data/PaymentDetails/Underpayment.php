<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\PaymentDetails;

/**
 * Dto of payment underpayment
 */
class Underpayment
{
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
     * Underpayment DTO constructor
     *
     * @param string $address
     * @param string $missingAmount
     * @param string $qr
     * @param string $qrAlt
     * @param string $qrImg
     * @param string $qrAltImg
     */
    public function __construct(
        string $address,
        string $missingAmount,
        string $qr,
        string $qrAlt,
        string $qrImg,
        string $qrAltImg
    ) {
        $this->address = $address;
        $this->missingAmount = $missingAmount;
        $this->qr = $qr;
        $this->qrAlt = $qrAlt;
        $this->qrImg = $qrImg;
        $this->qrAltImg = $qrAltImg;
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
     * Return associative array of all the properties
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'missing_amount' => $this->missingAmount,
            'qr' => $this->qr,
            'qr_alt' => $this->qrAlt,
            'qr_img' => $this->qrImg,
            'qr_alt_img' => $this->qrAltImg,
        ];
    }
}
