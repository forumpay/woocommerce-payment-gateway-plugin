<?php

declare(strict_types=1);

namespace ForumPay\PaymentGateway\WoocommercePlugin;

use ForumPay\PaymentGateway\WoocommercePlugin\Blocks\ForumPayPaymentBlocksSupport;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\OrderManager;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\PrivateTokenMasker;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\WcPsrLoggerAdapter;
use WC_Admin_Settings;
use WC_Logger;
use WC_Order;
use WC_Payment_Gateway;

/**
 * Forumpay Payment Gateway.
 *
 * Provides a Crypto Payment Gateway.
 */
class ForumPayPaymentGateway extends WC_Payment_Gateway
{
    /**
     * Constructor for the gateway.
     */
    public function __construct(
    ) {
        $this->id = 'forumpay';
        $this->method_title = __('Forumpay', 'forumpay');
        $this->method_description = "Pay with Crypto (by ForumPay)";
        $this->icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(FORUMPAY_FILE)) . '/images/logo-forumpay-1.svg';

        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->settings['title'] ?? '';
        $this->description = $this->settings['description'] ?? '';
        $this->pos_id = $this->settings['pos_id'] ?? '';
        $this->api_url = $this->settings['api_url'] ?? '';
        $this->api_user = $this->settings['api_user'] ?? '';
        $this->api_key = $this->settings['api_key'] ?? '';
        $this->accept_zero_confirmations = $this->settings['accept_zero_confirmations'] == 'yes' ? true : false;
        $this->api_url_override = $this->settings['api_url_override'] ?? '';
        $this->currency = get_woocommerce_currency();

        if (version_compare(WOOCOMMERCE_VERSION, '3.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
        }

        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page_forumpay'));
        add_action('woocommerce_api_wc_forumpay', [ $this, 'on_api_callback' ]);
        add_filter('woocommerce_payment_gateways', array($this, 'add_forumpay_gateway'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_admin_payment_id'));

        // Registers WooCommerce Blocks integration.
        add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'woocommerce_forumpay_gateway_block_support' ) );
    }

    /**
     * Check If The Gateway Is Available For Use.
     *
     * @return bool
     */
    public function is_available()
    {
        return true;
    }

    /**
     * Registers WooCommerce Blocks integration.
     *
     */
    public static function woocommerce_forumpay_gateway_block_support() {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function(\Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new ForumPayPaymentBlocksSupport());
                }
            );
        }
    }

    /**
     * Display the info about the last successful paymentId on the order detail page.
     *
     * @param $order
     */
    function display_admin_payment_id($order) {
        $successPaymentId = $order->get_meta('payment_formumpay_paymentId_last', true);
        $lastPaymentId = $order->get_meta('payment_formumpay_paymentId_last', true);
        $paymentId = $successPaymentId ?? $lastPaymentId;

        if ($paymentId) {
            echo '<p><strong>'.__('ForumPay reference').':</strong> <br/>' . $paymentId . '</p>';
        }
    }

    /**
     * Add the Gateway to WooCommerce
     **/
    function add_forumpay_gateway($methods)
    {
        $methods[] = $this;
        return $methods;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable ForumPay Payment Module.', 'forumpay'),
                'default' => 'no'),
            'title' => array(
                'title' => __('Title:', 'forumpay'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'forumpay'),
                'default' => __('Pay with Crypto', 'forumpay')),
            'description' => array(
                'title' => __('Description:', 'forumpay'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'forumpay'),
                'default' => __('Pay with Crypto (by ForumPay)', 'forumpay')),
            'api_url' => array(
                'title' => __('Environment', 'forumpay'),
                'description' => __('ForumPay environment'),
                'type' => 'select',
                'default' => 'Production',
                'options' => array(
                    'https://api.forumpay.com/pay/v2/' => 'Production',
                    'https://sandbox.api.forumpay.com/pay/v2/' => 'Sandbox',
                ),
            ),
            'api_user' => array(
                'title' => __('API User', 'forumpay'),
                'type' => 'text',
                'description' => __('You can generate API key in your ForumPay Account.')),

            'api_key' => array(
                'title' => __('API Secret', 'forumpay'),
                'type' => 'password',
                'description' => __('You can generate API secret in your ForumPay Account.')),

            'pos_id' => array(
                'title' => __('POS ID', 'forumpay'),
                'type' => 'text',
                'description' => __('Enter your webshop identifier (POS ID). Special characters not allowed. Allowed are: [A-Za-z0-9._-] Eg woocommerce-3, Woocommerce-3')),

            'accept_zero_confirmations' => array(
                'title' => __('Accept Zero Confirmations', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable Accept Zero Confirmations.', 'forumpay'),
                'default' => 'yes'),

            'api_url_override' => array(
                'title' => __('Custom environment URL', 'forumpay'),
                'type' => 'text',
                'description' => __('Optional: URL to the API server. This value will override the default setting. Only used for debugging.')),
        );
    }

    /**
     * Add validation rule to Title field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_title_field($key, $value) {
        if (empty($value)) {
            WC_Admin_Settings::add_error('Title field is required');
        }

        return $value;
    }

    /**
     * Add validation rule to API User field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_api_user_field($key, $value) {
        if (empty($value)) {
            WC_Admin_Settings::add_error('API User field is required');
        }

        return $value;
    }

    /**
     * Add validation rule to API Secret field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_api_key_field($key, $value) {
        if (empty($value)) {
            WC_Admin_Settings::add_error('API Secret field is required');
        }

        return $value;
    }

    /**
     * Add validation rule to POS ID field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_pos_id_field($key, $value) {
        if (empty($value)) {
            WC_Admin_Settings::add_error('POS ID field is required');
        }

        if (preg_match('/[^A-Za-z0-9._\\-\:]/', $value)) {
            WC_Admin_Settings::add_error('POS ID field includes invalid characters. Allowed are: A-Za-z0-9._-');
        }

        return $value;
    }

    /**
     * Add validation rule to Custom environment URL field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_api_url_override_field($key, $value)
    {
        if (empty($value)) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            WC_Admin_Settings::add_error('Custom environment URL must be valid URL');
        }

        return $value;
    }

    /**
     * Output the admin options table.
     */
    public function admin_options()
    {
        echo '<h3>' . __('ForumPay Payment Gateway', 'forumpay') . '</h3>';
        echo '<p>' . __('Pay with Crypto (by ForumPay)') . '</p>';
        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';

    }

    /**
     * Output the payment page.
     */
    function receipt_page_forumpay($order_id)
    {
        echo $this->generate_forumpay_form($order_id);
    }

    public function generate_forumpay_form($order_id)
    {
        $order = new WC_Order($order_id);
        $apibase = get_site_url() . '/wc-api/wc_forumpay';
        $base_path = WP_PLUGIN_URL . "/" . plugin_basename(dirname(FORUMPAY_FILE));
        $return_url = $this->get_return_url($order);
        $cancel_url = wc_get_cart_url();

        $extahtm = '';

        $extahtm .= '<span id="forumpay-nonce" data="' . wp_create_nonce('forumpay-payment-gateway') . '"></span>';
        $extahtm .= '<span id="forumpay-apibase" data="' . $apibase . '"></span>';
        $extahtm .= '<span id="forumpay-returnurl" data="' . $return_url . '"></span>';
        $extahtm .= '<span id="forumpay-cancelurl" data="' . $cancel_url . '"></span>';

        $extahtm .= '<link rel="stylesheet"  href="' . $base_path . '/css/forumpay.css" />';
        $extahtm .= '<link rel="stylesheet"  href="' . $base_path . '/css/forumpay_widget.css" />';
        $extahtm .= '<script type="text/javascript" src="' . $base_path . '/js/forumpay_widget.js"></script>';
        $extahtm .= '<script type="text/javascript" src="' . $base_path . '/js/forumpay.js"></script>';

        $templatehtml = '<div id="ForumPayPaymentGatewayWidgetContainer">{{message}}</div>' . $extahtm;

        return $templatehtml;
    }

    /**
     * Return to success page
     *
     * @param int $order_id
     * @return array
     */
    function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        WC()->session->set( 'order_awaiting_payment', $order_id);
        return [
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        ];
    }

    /**
     * Entry point for all the Client API Interactions with the plugin.
     */
    function on_api_callback()
    {
        $forumPayLogger = new ForumPayLogger(new WcPsrLoggerAdapter(new WC_Logger(), 'ForumPayWebApi'));
        $forumPayLogger->addParser(new PrivateTokenMasker());
        $forumPay = new ForumPay(
            $this,
            new OrderManager(),
            $forumPayLogger
        );

        $router = new Router($forumPay, $forumPayLogger);
        $response = $router->execute(new Request());

        echo $response;die;
    }

    /**
     * @return mixed|string Plugin title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get api url from settings
     *
     * @return mixed|string
     */
    public function getApiUrl()
    {
        if (!empty($this->api_url_override)) {
            return $this->api_url_override;
        }

        return $this->api_url;
    }

    /**
     * Get Api key from settings
     *
     * @return mixed|string
     */
    public function getMerchantApiUser()
    {
        return $this->api_user;
    }

    /**
     * Get Api secret from settings
     *
     * @return mixed|string
     */
    public function getMerchantApiSecret()
    {
        return $this->api_key;
    }

    /**
     * Get default store locale
     *
     * @return mixed
     */
    public function getStoreLocale()
    {
        return get_locale();
    }

    /**
     * Get WordPress installation version if possible
     *
     * @return string
     */
    public function getWordpressVersion()
    {
        global $wp_version;
        return isset($wp_version) ? $wp_version : '--no version--';
    }

    /**
     * Get WooCommerce installation version if possible
     *
     * @return string
     */
    public function getWooCommerceVersion()
    {
        return defined('WC_VERSION') ? WC_VERSION : '--no version--';
    }

    /**
     * Get current ForumPay gateway installation version
     *
     * @return string
     */
    public function getPluginVersion()
    {
        return FORUMPAY_VERSION;
    }

    /**
     * Return POS ID from settings
     *
     * @return mixed|string
     */
    public function getPosId()
    {
        return $this->pos_id;
    }

    /**
     * Return weather or not zero confirmation is checked in settings
     *
     * @return bool
     */
    public function isAcceptZeroConfirmations()
    {
        return $this->accept_zero_confirmations;
    }
}
