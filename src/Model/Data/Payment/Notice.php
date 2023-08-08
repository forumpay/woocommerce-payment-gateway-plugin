<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payment;

/**
 * Dto of payment notice
 */
class Notice
{
    /**
     * Get notice identification code
     *
     * @var string
     */
    private string $code;

    /**
     * Get notice message
     *
     * @var string
     */
    private string $message;

    /**
     * Notice DTO constructor
     *
     * @param string $code
     * @param string $message
     */
    public function __construct(
        string $code,
        string $message
    ) {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
