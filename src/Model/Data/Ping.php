<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

/**
 * Dto for ping response
 */
class Ping
{
    /**
     * Message
     *
     * @var string
     */
    private string $message;

    /**
     * Webhook Success
     *
     * @var string|null
     */
    private ?string $webhookSuccess;

    /**
     * Webhook Ping Response
     *
     * @var WebhookPingResponse|null
     */
    private ?WebhookPingResponse $webhookPingResponse;

    /**
     * Ping DTO constructor
     *
     */
    public function __construct(
        string $message,
        ?string $webhookSuccess = null,
        ?WebhookPingResponse $webhookPingResponse = null
    ) {
        $this->message = $message;
        $this->webhookSuccess = $webhookSuccess;
        $this->webhookPingResponse = $webhookPingResponse;
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
            'webhookSuccess' => $this->webhookSuccess,
            'webhookPingResponse' => $this->webhookPingResponse->toArray() ?? $this->webhookPingResponse,
        ];
    }
}
