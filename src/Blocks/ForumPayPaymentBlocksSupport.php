<?php

declare(strict_types=1);

namespace ForumPay\PaymentGateway\WoocommercePlugin\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use ForumPay\PaymentGateway\WoocommercePlugin\ForumPayPaymentGateway;

/**
 * ForumPay Payments Blocks integration
 */
final class ForumPayPaymentBlocksSupport extends AbstractPaymentMethodType {

    /**
     * The gateway instance.
     *
     * @var ForumPayPaymentGateway
     */
    private ForumPayPaymentGateway $gateway;

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'forumpay';

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option( 'woocommerce_forumpay_settings', [] );
        $gateways       = WC()->payment_gateways->payment_gateways();
        $this->gateway  = $gateways[ $this->name ];
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        return $this->gateway->is_available();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        $script_path = '/assets/js/frontend/blocks.js';
        $script_asset_path = trailingslashit(plugin_dir_path(FORUMPAY_FILE)) . 'assets/js/frontend/blocks.asset.php';
        $script_asset = file_exists($script_asset_path)
            ? require($script_asset_path)
            : array(
                'dependencies' => array(),
                'version'      => $this->gateway->getPluginVersion(),
            );
        $script_url = untrailingslashit(plugins_url('/', FORUMPAY_FILE)) . $script_path;

        wp_register_script(
            'wc-forumpay-payments-blocks',
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        return ['wc-forumpay-payments-blocks'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        return [
            'title' => $this->gateway->getTitle(),
            'description' => $this->gateway->getDescription(),
            'supports' => array_filter($this->gateway->supports, [$this->gateway, 'supports'])
        ];
    }
}
