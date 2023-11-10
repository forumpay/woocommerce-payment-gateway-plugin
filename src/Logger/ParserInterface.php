<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Logger;

interface ParserInterface
{
    /**
     * Parse given data.
     *
     * @param array $keys
     * @param array $data
     * @return array
     */
    public function parse(array $keys, array $data): array;
}
