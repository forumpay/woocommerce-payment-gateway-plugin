<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data;

class WebhookPingResponse
{
    /**
     * Status
     *
     * @var string
     */
    private string $status;

    /**
     * Duration
     *
     * @var float
     */
    private float $duration;

    /**
     * Webhook Url
     *
     * @var string
     */
    private string $webhookUrl;

    /**
     * Response Code
     *
     * @var int|null
     */
    private ?int $responseCode;

    /**
     * Response Body
     *
     * @var string|null
     */
    private ?string $responseBody;

    /**
     * WebhookPing DTO constructor
     *
     */
    public function __construct(
        string $status,
        float $duration,
        string $webhookUrl,
        ?int $responseCode,
        ?string $responseBody
    ) {
        $this->status = $status;
        $this->duration = $duration;
        $this->webhookUrl = $webhookUrl;
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
    }

    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Return array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'duration' => $this->duration,
            'webhookUrl' => $this->webhookUrl,
            'responseCode' => $this->responseCode,
            'responseBody' => $this->responseBody,
        ];
    }
}
