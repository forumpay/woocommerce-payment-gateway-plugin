<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Logger;

class PrivateTokenMasker implements ParserInterface
{
    /**
     * Sensitive data is replaced with this value
     *
     * @var string
     */
    private string $obfuscateMask;

    /**
     * Constructor
     *
     * @param string $obfuscate
     */
    public function __construct(string $obfuscate = '****MASKED****')
    {
        $this->obfuscateMask = $obfuscate;
    }

    /**
     * @inheritdoc
     */
    public function parse(array $keys, array $data): array
    {
        if (!array_key_exists('response', $data)) {
            return $data;
        }

        if (null === $data['response']) {
            return $data;
        }

        if (!is_array($data['response'])) {
            $response = json_decode($data['response'], true);
        } else {
            $response = $data['response'];
        }

        if ($response === null) {
            return $data;
        }

        if (array_key_exists('invoices', $response)) {
            foreach ($response['invoices'] as &$invoice) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $invoice)) {
                        $invoice[$key] = $this->obfuscateMask;
                    }
                }
            }
        } else {
            foreach ($keys as $key) {
                if (array_key_exists($key, $response)) {
                    $response[$key] = $this->obfuscateMask;
                }
            }
        }

        $data['response'] = $response;

        return $data;
    }
}
