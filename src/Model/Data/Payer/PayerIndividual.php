<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payer;

/**
 * Dto of payer - individual
 */
class PayerIndividual extends Payer
{
    /**
     * @var string
     */
    private string $type;

    /**
     * @var string
     */
    private string $firstName;

    /**
     * @var string
     */
    private string $lastName;

    /**
     * @var string
     */
    private string $countryOfResidence;

    /**
     * @var string
     */
    private string $dateOfBirth;

    /**
     * @var string
     */
    private string $countryOfBirth;

    public function __construct(
        string $type,
        string $firstName,
        string $lastName,
        string $countryOfResidence,
        string $dateOfBirth,
        string $countryOfBirth
    ) {
        $this->type = $type;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->countryOfResidence = $countryOfResidence;
        $this->dateOfBirth = $dateOfBirth;
        $this->countryOfBirth = $countryOfBirth;
    }

    public function toArray(): array
    {
        return [
            'payer_type' => $this->type,
            'payer_first_name' => $this->firstName,
            'payer_last_name' => $this->lastName,
            'payer_country' => $this->countryOfResidence,
            'payer_date_of_birth' => $this->dateOfBirth,
            'payer_country_of_birth' => $this->countryOfBirth,
        ];
    }
}
