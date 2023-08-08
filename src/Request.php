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
     * @return mixed
     */
    public function getRequired($param)
    {
        $param = $this->get($param, null);
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
     * @return mixed
     */
    public function get($param, $default = null)
    {
        $params = $this->getAllParams();

        if (isset($params[$param])) {
            return sanitize_text_field(wp_unslash($params[$param]));
        }

        return $default;
    }

    private function getAllParams() {
        $order = [
            'orderId' => WC()->session->get( 'order_awaiting_payment')
        ];
        return array_merge(
            $_REQUEST,
            $this->getBodyParameters(),
            $order
        );
    }

    private function getBodyParameters() {
        $bodyContent = file_get_contents('php://input');
        return json_decode($bodyContent, true) ?? [];
    }
}
