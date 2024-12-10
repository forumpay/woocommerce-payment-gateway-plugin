<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

/**
 * Dto for ping response
 */
class WebhookTest
{
    /**
     * Message
     *
     * @var string
     */
    private string $message;

    /**
     * Ping DTO constructor
     *
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Return empty array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
