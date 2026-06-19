<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ForumPayHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use WC_Order;
use WC_Order_Item_Fee;
use WC_Session_Handler;

/**
 * Manages internal states of the order and provides
 * and interface for dealing with Woocommerce internal
 */
class OrderManager
{
    /**
     * @var \WooCommerce
     */
    private $woocommerce;

    /**
     * OrderManager constructor
     *
     */
    public function __construct(
    ) {
        $this->woocommerce = WC();
    }

    /**
     * Get order by order id from db
     *
     * @param string $orderId
     * @return WC_Order
     */
    public function getOrder(string $orderId): WC_Order
    {
        return new WC_Order($orderId);
    }

    /**
     * Get currency customer used when creating order
     *
     * @param $orderId
     * @return string
     */
    public function getOrderCurrency($orderId) {
        $order = new WC_Order($orderId);
        return $order->get_currency();
    }

    /**
     * Get order total by order id from db
     *
     * @param $orderId
     * @return string
     */
    public function getOrderTotal($orderId) {
        $order = new WC_Order($orderId);
        return number_format($order->get_total(), 2, '.', '');
    }

    /**
     * Get customer IP address that was used when order is created
     *
     * @param $orderId
     * @return string
     */
    public function getOrderCustomerIpAddress($orderId) {
        $order = new WC_Order($orderId);
        return $order->get_customer_ip_address();
    }

    /**
     * Get customer email address that was used when order is created
     *
     * @param $orderId
     * @return string
     */
    public function getOrderCustomerEmail($orderId) {
        $order = new WC_Order($orderId);
        return $order->get_billing_email();
    }

    /**
     * Get customer ID if registered customer or construct one for guests
     *
     * @param $orderId
     * @return int|string
     */
    public function getOrderCustomerId($orderId) {
        $order = new WC_Order($orderId);
        return $order->get_customer_id() != false
            ? $order->get_customer_id()
            : sprintf('guest_%s', $orderId);
    }

    /**
     * Update order with new status
     *
     * @param $orderId
     * @param $newStatus
     * @param $paymentId
     */
    public function updateOrderStatus($orderId, $newStatus, $paymentId, $amountPayed, $currency, $underPayFeeDescription, $overPayFeeDescription) {
        $order = new WC_Order($orderId);
        $orderTotal = floatval($this->getOrderTotal($orderId));
        $amountPayed = floatval($amountPayed);
        $paymentType = 'payment';

        if (strtolower($newStatus) === 'confirmed') {
            if ($order->is_paid()) {
                return;
            }

            if ($amountPayed < $orderTotal) {
                $paymentType = 'underpayment';
                if (!empty($underPayFeeDescription)) {
                    $itemFee = new WC_Order_Item_Fee();

                    $itemFee->set_name($underPayFeeDescription);
                    $itemFee->set_amount($amountPayed-$orderTotal);
                    $itemFee->set_tax_class('');
                    $itemFee->set_tax_status( 'none');
                    $itemFee->set_total($amountPayed-$orderTotal);
                    $itemFee->calculate_taxes($amountPayed-$orderTotal);

                    $order->add_item($itemFee);
                    $order->calculate_totals();
                    $order->save();
                }

                $order = new WC_Order($orderId);
            }

            if ($amountPayed > $orderTotal) {
                $paymentType = 'overpayment';
                if (!empty($overPayFeeDescription)) {
                    $itemFee = new WC_Order_Item_Fee();

                    $itemFee->set_name($overPayFeeDescription);
                    $itemFee->set_amount($amountPayed-$orderTotal);
                    $itemFee->set_tax_class('');
                    $itemFee->set_tax_status( 'none');
                    $itemFee->set_total($amountPayed-$orderTotal);
                    $itemFee->calculate_taxes($amountPayed-$orderTotal);

                    $order->add_item($itemFee);
                    $order->calculate_totals();
                    $order->save();
                }

                $order = new WC_Order($orderId);
            }

            $order->payment_complete();
            $order->add_order_note("ForumPay Payment Successful, received {$paymentType} for {$amountPayed} {$currency}");
            $this->saveOrderMetaData($orderId, 'payment_formumpay_paymentId', $paymentId, true);
            if ($this->woocommerce && $this->woocommerce->cart) {
                $this->woocommerce->cart->empty_cart();
            }
        } else if (strtolower($newStatus) === 'cancelled') {
            $order->update_status(
                'failed',
                'Failed, Payment Status : Cancelled'
            );
        }
    }

    /**
     * Save metadata to order
     *
     * @param $orderId
     * @param $key
     * @param $data
     * @param false $unique
     */
    public function saveOrderMetaData($orderId, $key, $data, $unique = false)
    {
        $order = $this->getOrder($orderId);
        $order->add_meta_data($key, $data, $unique);
        $order->save();
    }

    /**
     * Fetch metadata from order
     *
     * @param $orderId
     * @param $key
     * @return mixed
     */
    public function getOrderMetaData($orderId, $key)
    {
        $order = $this->getOrder($orderId);
        return $order->get_meta($key, false);
    }

    /**
     * Resolve order ID from request (forumpay_order_id or orderId with session fallback).
     *
     * @param Request $request
     * @return string
     * @throws ForumPayHttpException
     */
    public function resolveOrderId(Request $request): string
    {
        $orderId = $request->get('forumpay_order_id');
        if ($orderId !== null && $orderId !== '') {
            return (string)$orderId;
        }

        try {
            return (string)$request->getRequired('orderId');
        } catch (\InvalidArgumentException $e) {
            throw $this->accessDeniedOrder();
        }
    }

    /**
     * Validate that the current user has admin access (payment gateway settings).
     *
     * @throws ForumPayHttpException
     */
    public function validateAdminAccess(): void
    {
        if (current_user_can('manage_woocommerce')) {
            return;
        }

        throw new ForumPayHttpException(
            'Access denied. Admin privileges required.',
            '',
            4003,
            ForumPayHttpException::HTTP_FORBIDDEN
        );
    }

    /**
     * Validate that the current user/session has access to the given order.
     *
     * @param string $orderId
     * @throws ForumPayHttpException
     */
    public function validateOrderAccess(string $orderId): void
    {
        if (current_user_can('edit_shop_orders')) {
            return;
        }

        $order = wc_get_order((int)$orderId);
        if (!$order) {
            throw $this->accessDeniedOrder();
        }

        $currentUserId = get_current_user_id();
        $orderCustomerId = (int)$order->get_customer_id();

        if ($currentUserId > 0 && $currentUserId === $orderCustomerId) {
            return;
        }

        if ($currentUserId === 0 && $orderCustomerId === 0) {
            $session = WC()->session;
            if (!$session) {
                $session = new WC_Session_Handler();
                $session->init();
            }
            $sessionOrderId = $session->get('order_awaiting_payment');
            if ($sessionOrderId !== null && (string)$sessionOrderId === (string)$orderId) {
                return;
            }
        }

        throw $this->accessDeniedOrder();
    }

    /**
     * Validate that the payment belongs to the given order.
     *
     * @param string $orderId
     * @param string $paymentId
     * @throws ForumPayHttpException
     */
    public function validatePaymentBelongsToOrder(string $orderId, string $paymentId): void
    {
        $this->getPaymentMetaData($orderId, $paymentId);
    }

    /**
     * Get startPayment metadata for the given order/payment pair.
     *
     * @param string $orderId
     * @param string $paymentId
     * @return array
     * @throws ForumPayHttpException
     */
    public function getPaymentMetaData(string $orderId, string $paymentId): array
    {
        $startPaymentResponses = $this->getOrderMetaData($orderId, 'startPayment');

        foreach ($startPaymentResponses as $response) {
            $value = $response->get_data()['value'] ?? null;
            if (!is_array($value) || ($value['payment_id'] ?? null) !== $paymentId) {
                continue;
            }

            $currency = $value['currency'] ?? null;
            $address = $value['address'] ?? null;
            if ($currency === null || $currency === '' || $address === null || $address === '') {
                throw $this->accessDeniedOrder();
            }

            return $value;
        }

        throw $this->accessDeniedOrder();
    }

    /**
     * Access-denied error for any order-scoped request the current user/session may not access.
     *
     * @return ForumPayHttpException
     */
    private function accessDeniedOrder(): ForumPayHttpException
    {
        return new ForumPayHttpException(
            'Access denied. You do not have permission to access this order.',
            '',
            4003,
            ForumPayHttpException::HTTP_FORBIDDEN
        );
    }
}
