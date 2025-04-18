<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin;

use WC_Session_Handler;

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
        $value = $this->get($param, null, $nonceCheckRequired);
        if ($value === null) {
            throw new \InvalidArgumentException(sprintf('Missing required parameter %s', $param));
        }

        return $value;
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
        $bodyParams = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($param === 'orderId' && !isset($bodyParams['orderId'])) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();

            return WC()->session->get( 'order_awaiting_payment');
        }

        if ($nonceCheckRequired) {
            $nonceVerified = isset($bodyParams['forumpay_nonce']) && wp_verify_nonce($bodyParams['forumpay_nonce'], 'wp_rest');

            if (!$nonceVerified) {
                wp_nonce_ays(''); //returns 403 response
            }
        }

        if (isset($bodyParams[$param])) {
            if (is_array($bodyParams[$param])) {
                return array_map('sanitize_text_field', wp_unslash($bodyParams[$param]));
            }
            return sanitize_text_field(wp_unslash($bodyParams[$param]));
        }

        return $default;
    }
}
