<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment;

use WC_Order;
use WC_Order_Item_Fee;

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
}
