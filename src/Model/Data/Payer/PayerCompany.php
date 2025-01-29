<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payer;

/**
 * DTO of payer - company
 */
class PayerCompany extends Payer
{
    /**
     * @var string
     */
    private string $type;

    /**
     * @var string
     */
    private string $companyName;

    /**
     * @var string
     */
    private string $countryOfIncorporation;

    /**
     * @var string
     */
    private string $dateOfIncorporation;

    public function __construct(
        string $type,
        string $companyName,
        string $countryOfIncorporation,
        string $dateOfIncorporation
    ) {
        $this->type = $type;
        $this->companyName = $companyName;
        $this->countryOfIncorporation = $countryOfIncorporation;
        $this->dateOfIncorporation = $dateOfIncorporation;
    }

    public function toArray(): array
    {
        return [
            'payer_type' => $this->type,
            'payer_company' => $this->companyName,
            'payer_country' => $this->countryOfIncorporation,
            'payer_date_of_incorporation' => $this->dateOfIncorporation,
        ];
    }
}
