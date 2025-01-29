<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payment;

/**
 * Dto of payment beneficiary vasp details
 */
class BeneficiaryVaspDetails
{
    /**
     * @var string
     */
    private string $beneficiaryName;

    /**
     * @var string
     */
    private string $beneficiaryVasp;

    /**
     * @var string|null
     */
    private ?string $beneficiaryVaspDid;

    /**
     * Beneficiary vasp details DTO constructor
     *
     * @param string $beneficiaryName
     * @param string $beneficiaryVasp
     * @param string|null $beneficiaryVaspDid
     */
    private function __construct(
        string $beneficiaryName,
        string $beneficiaryVasp,
        ?string $beneficiaryVaspDid
    ) {
        $this->beneficiaryName = $beneficiaryName;
        $this->beneficiaryVasp = $beneficiaryVasp;
        $this->beneficiaryVaspDid = $beneficiaryVaspDid;
    }

    public static function fromArray(?array $details): ?self
    {
        if (!$details) {
            return null;
        }

        return new BeneficiaryVaspDetails(
            $details['beneficiary_name'] ?? '',
            $details['beneficiary_vasp'] ?? '',
            $details['beneficiary_vasp_did']
        );
    }

    /**
     * @return string
     */
    public function getBeneficiaryName(): string
    {
        return $this->beneficiaryName;
    }

    /**
     * @return string
     */
    public function getBeneficiaryVasp(): string
    {
        return $this->beneficiaryVasp;
    }

    /**
     * @return string|null
     */
    public function getBeneficiaryVaspDid(): ?string
    {
        return $this->beneficiaryVaspDid;
    }

    public function toArray(): array
    {
        return [
            'beneficiary_name' => $this->beneficiaryName,
            'beneficiary_vasp' => $this->beneficiaryVasp,
            'beneficiary_vasp_did' => $this->beneficiaryVaspDid,
        ];
    }
}
