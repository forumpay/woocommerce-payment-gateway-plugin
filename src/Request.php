<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin;

/**
 * Encapsulates request parameter
 */
class Request
{
    /**
     * Return expected parameter for Request, throw \InvalidArgumentException otherwise.
     *
     * @param $param
     * @param bool $nonceCheckRequired
     * @return mixed
     */
    public function getRequired($param, $nonceCheckRequired = true)
    {
        $param = $this->get($param, null, $nonceCheckRequired);
        if ($param === null) {
            throw new \InvalidArgumentException(sprintf('Missing required parameter %s', $param));
        }

        return $param;
    }

    /**
     * Return parameter for Request or default one if request one is not found
     *
     * @param $param
     * @param null $default
     * @param bool $nonceCheckRequired
     * @return mixed
     */
    public function get($param, $default = null, $nonceCheckRequired = true)
    {
        if ($param === 'orderId') {
            return WC()->session->get( 'order_awaiting_payment');
        }

        $bodyParams = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($nonceCheckRequired) {
            $nonceVerified = isset($bodyParams['forumpay_nonce']) && wp_verify_nonce($bodyParams['forumpay_nonce'], 'forumpay-payment-gateway');

            if (!$nonceVerified) {
                wp_nonce_ays(''); //returns 403 response
            }
        }

        if (isset($bodyParams[$param])) {
            return sanitize_text_field(wp_unslash($bodyParams[$param]));
        }

        return $default;
    }
}
