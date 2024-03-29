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
        $params = $this->getAllParams($nonceCheckRequired);

        if (isset($params[$param])) {
            return sanitize_text_field(wp_unslash($params[$param]));
        }

        return $default;
    }

    /**
     * @param $nonceCheckRequired
     * @return array
     */
    private function getAllParams($nonceCheckRequired = true) {
        $bodyParams = $this->getBodyParameters();
        if ($nonceCheckRequired) {
          $nonceVerified = wp_verify_nonce($bodyParams['forumpay_nonce'], 'forumpay-payment-gateway');

          if (!$nonceVerified) {
             wp_nonce_ays(''); //returns 403 response
          }
        }

        $order = [
            'orderId' => WC()->session->get( 'order_awaiting_payment')
        ];

        return array_merge(
            $_REQUEST,
            $bodyParams,
            $order
        );
    }

    private function getBodyParameters() {
        $bodyContent = file_get_contents('php://input');
        return json_decode($bodyContent, true) ?? [];
    }
}
