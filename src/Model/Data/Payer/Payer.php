<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payer;

/**
 * Dto of payer
 */
abstract class Payer
{
    abstract public function toArray(): array;

    public static function valueOf(array $payer): ?self
    {
        if ($payer['payer_type'] === 'company') {
            return new PayerCompany(
                $payer['payer_type'],
                $payer['payer_company'],
                $payer['payer_country_of_incorporation'],
                $payer['payer_date_of_incorporation']
            );
        }

        if ($payer['payer_type'] === 'individual') {
            return new PayerIndividual(
                $payer['payer_type'],
                $payer['payer_first_name'],
                $payer['payer_last_name'],
                $payer['payer_country_of_residence'],
                $payer['payer_date_of_birth'],
                $payer['payer_country_of_birth']
            );
        }

        return null;
    }
}
