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
     * @var string
     */
    private $fp_pos_id;

    /**
     * @var string
     */
    private $fp_sid;

    /**
     * @var string
     */
    private $fp_api_url;

    /**
     * @var string
     */
    private $fp_api_user;

    /**
     * @var string
     */
    private $fp_api_key;

    /**
     * @var string|null
     */
    private $fp_webhook_url;

    /**
     * @var bool
     */
    private bool $fp_accept_zero_confirmations;

    /**
     * @var array
     */
    private array $fp_accept_underpayment;

    /**
     * @var array
     */
    private array $fp_accept_overpayment;

    /**
     * @var array
     */
    private array $fp_accept_late_payment;

    /**
     * @var string
     */
    private $fp_api_url_override;

    /**
     * @var string
     */
    private string $fp_currency;

    /**
     * Constructor for the gateway.
     */
    public function __construct(
    ) {
        $this->id = 'forumpay';
        $this->method_title = __('Forumpay', 'forumpay');
        $this->method_description = "Pay with Crypto (by ForumPay)";
        $this->icon = FORUMPAY_ICON;

        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->settings['title'] ?? '';
        $this->description = $this->settings['description'] ?? '';
        $this->fp_pos_id = $this->settings['pos_id'] ?? '';
        $this->fp_sid = $this->settings['sid'] ?? '';
        $this->fp_api_url = $this->settings['api_url'] ?? '';
        $this->fp_api_user = $this->settings['api_user'] ?? '';
        $this->fp_api_key = $this->settings['api_key'] ?? '';
        $this->fp_webhook_url = empty($this->settings['webhook_url'] ?? '') ? null : $this->settings['webhook_url'];

        $this->fp_accept_zero_confirmations = ($this->settings['accept_zero_confirmations'] ?? 'no') === 'yes';
        $this->fp_accept_underpayment = [
            'enabled' => ($this->settings['accept_underpayment'] ?? 'no') === 'yes',
            'threshold' => $this->settings['accept_underpayment_threshold'] ?? null,
            'modify_order' => ($this->settings['accept_underpayment_modify_order_total'] ?? 'no') === 'yes',
            'fee_description' => $this->settings['accept_underpayment_modify_order_total_description'] ?? null,
        ];
        $this->fp_accept_overpayment = [
            'enabled' => ($this->settings['accept_overpayment'] ?? 'no' ) === 'yes',
            'threshold' => null,
            'modify_order' => ($this->settings['accept_overpayment_modify_order_total'] ?? 'no') === 'yes',
            'fee_description' => $this->settings['accept_overpayment_modify_order_total_description'] ?? null,
        ];
        $this->fp_accept_late_payment = [
            'enabled' => ($this->settings['accept_latepayment'] ?? 'no') === 'yes'
        ];

        $this->fp_api_url_override = $this->settings['api_url_override'] ?? '';
        $this->fp_currency = get_woocommerce_currency();

        if (version_compare(WOOCOMMERCE_VERSION, '3.0.0', '>=')) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        } else {
            add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
        }

        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page_forumpay'));
        add_action('woocommerce_api_wc_forumpay', array($this, 'on_api_callback'));
        add_action('rest_api_init', array($this, 'wc_success_register_custom_api_endpoints'));
        add_filter('woocommerce_payment_gateways', array($this, 'add_forumpay_gateway'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_admin_payment_id'));

        // Registers WooCommerce Blocks integration.
        add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'woocommerce_forumpay_gateway_block_support' ) );

        add_action('wp_enqueue_scripts', array($this, 'forumpay_payment_gateway_enqueue_scripts'));
        // Register admin js files
        add_action('admin_enqueue_scripts', array($this, 'forumpay_payment_gateway_enqueue_admin_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'forumpay_payment_gateway_enqueue_order_edit_scripts'));

        add_action('before_woocommerce_init', array($this, 'before_woocommerce_hpos'));
    }

    function before_woocommerce_hpos() {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'forumpay-crypto-payments/forumpay-crypto-payments.php', true );
        }
    }

    function wc_success_register_custom_api_endpoints() {
        register_rest_route('wc-api/', 'wc_forumpay', array(
            'methods' => 'POST',
            'callback' => array($this, 'on_api_callback'),
            'permission_callback' => function () {
                return true;
            }
        ));
    }

    function forumpay_payment_gateway_enqueue_scripts() {
        if (!is_checkout()) {
            return;
        }

        // Register and enqueue a JavaScript file
        wp_register_script('forumpay_payment_gateway_widget_script', FORUMPAY_PLUGIN_DIR . 'js/forumpay_widget.js', [], $this->getAssetVersion());
        wp_enqueue_script('forumpay_payment_gateway_widget_script');
        wp_register_script('forumpay_payment_gateway_init_script', FORUMPAY_PLUGIN_DIR . 'js/forumpay.js', array('jquery'), $this->getAssetVersion(), true);
        wp_enqueue_script('forumpay_payment_gateway_init_script');

        // Register and enqueue a CSS file
        wp_register_style('forumpay_payment_gateway_init_style', FORUMPAY_PLUGIN_DIR . 'css/forumpay.css', [], $this->getAssetVersion());
        wp_enqueue_style('forumpay_payment_gateway_init_style');
        wp_register_style('forumpay_payment_gateway_widget_style', FORUMPAY_PLUGIN_DIR . 'css/forumpay_widget.css', [], $this->getAssetVersion());
        wp_enqueue_style('forumpay_payment_gateway_widget_style');
    }

    function forumpay_payment_gateway_enqueue_admin_scripts($hook) {
        if ('woocommerce_page_wc-settings' !== $hook) {
            return;
        }

        wp_enqueue_script('forumpay_payment_gateway_admin_script', FORUMPAY_PLUGIN_DIR . '/js/admin-gateway-settings.js', array('jquery'), '1.0', true);

        // Pass variables to JavaScript
        wp_localize_script('forumpay_payment_gateway_admin_script', 'gatewaySettings', array(
            'gatewayId' => $this->id,
            'apiUrl' => get_site_url() . '/wp-json/wc-api/wc_forumpay',
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    function forumpay_payment_gateway_enqueue_order_edit_scripts($hook) {
        wp_enqueue_script('forumpay_order_edit_script', FORUMPAY_PLUGIN_DIR . '/js/order-edit.js', array('jquery'), '1.0', true);

        // Pass variables to JavaScript
        wp_localize_script('forumpay_order_edit_script', 'orderEditSettings', array(
            'apiUrl' => get_site_url() . '/wp-json/wc-api/wc_forumpay',
            'nonce' => wp_create_nonce('wp_rest'),
        ));
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
            echo '<p><span><strong>'.esc_html(__('ForumPay reference', 'forumpay')).':</strong></span> <br/> <span class="order_payment_id">' . esc_html($paymentId) . '</span></p>';
            echo '<button id="forumpay_api_sync_payment" class="button-primary">' . esc_html(__('Sync status with ForumPay', 'forumpay')) . '</button>';
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
                'default' => __('Pay with Crypto', 'forumpay')
            ),
            'description' => array(
                'title' => __('Description:', 'forumpay'),
                'type' => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'forumpay'),
                'default' => __('Pay with Crypto (by ForumPay)', 'forumpay')
            ),
            'section_start' => array(
                'title' => __('API settings', 'forumpay'),
                'type' => 'title',
                'description' => '<hr style="border-top: 1px solid #ccc;">'
            ),
            'api_url' => array(
                'title' => __('Environment', 'forumpay'),
                'description' => __('ForumPay environment', 'forumpay'),
                'type' => 'select',
                'default' => 'https://api.forumpay.com/pay/v2/',
                'options' => array(
                    'https://api.forumpay.com/pay/v2/' => 'Production',
                    'https://sandbox.api.forumpay.com/pay/v2/' => 'Sandbox',
                ),
            ),
            'api_user' => array(
                'title' => __('API User', 'forumpay'),
                'type' => 'text',
                'description' => __('You can generate API key in your ForumPay Account.', 'forumpay')
            ),
            'api_key' => array(
                'title' => __('API Secret', 'forumpay'),
                'type' => 'password',
                'description' => __('You can generate API secret in your ForumPay Account.', 'forumpay')
            ),
            'pos_id' => array(
                'title' => __('POS ID', 'forumpay'),
                'type' => 'text',
                'description' => __('Enter your webshop identifier (POS ID). Special characters not allowed. Allowed are: [A-Za-z0-9._-] Eg woocommerce-3, Woocommerce-3', 'forumpay')
            ),
            'sid' => array(
                'title' => __('SID', 'forumpay'),
                'type' => 'text',
                'description' => __('Optional: Enter unique identifier for a sub-account within your main account.', 'forumpay')
            ),
            'webhook_url' => array(
                'title' => __('Webhook URL', 'forumpay'),
                'type' => 'text',
                'description' => sprintf(
                    __('Optional: This URL should point to the endpoint that will handle the webhook events.<br> Typically, it should be: <b><i>%s</i></b><br> This URL will override the default setting for your API keys on your Forumpay account.<br> Ensure that the URL is publicly accessible and can handle the incoming webhook events securely.', 'forumpay'),
                    str_replace('localhost', 'my-site', get_site_url()) . "/wp-json/wc-api/wc_forumpay?act=webhook"
                )
            ),
            'api_url_override' => array(
                'title' => __('Custom environment URL', 'forumpay'),
                'type' => 'text',
                'description' => __('Optional: URL to the API server. This value will override the default setting. Only used for debugging.', 'forumpay')
            ),
            'ping_button' => array(
                'title' => __('', 'forumpay'),
                'type' => 'title',
                'description' => '<button id="woocommerce_forumpay_api_test" class="button-secondary">Test API credentials</button> <p class="description">Click the button to check credentials and connection to ForumPay server. No order will be created.</p>'
            ),

            'section2_start' => array(
                'title' => __('Payment options', 'forumpay'),
                'type' => 'title',
                'description' => '<hr style="border-top: 1px solid #ccc;">'
            ),
            'accept_zero_confirmations' => array(
                'title' => __('Accept Zero Confirmations', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable Accept Zero Confirmations.', 'forumpay'),
                'default' => 'yes'),

            'accept_underpayment' => array(
                'title' => __('Auto-Accept Underpayments', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable this option to automatically accept payments that are slightly less than the total order amount.', 'forumpay'),
                'default' => 'no'),

            'accept_underpayment_threshold' => array(
                'title' => __('', 'forumpay'),
                'type' => 'text',
                'description' => __('Enter the maximum percentage (0-100) of the order total that can be underpaid for the order to be accepted automatically.', 'forumpay')
            ),

            'accept_underpayment_modify_order_total' => array(
                'title' => __('', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable to modify the order total to reflect underpayments as a separate fee. This will be negative to indicate less payment received.', 'forumpay'),
                'default' => 'no'),

            'accept_underpayment_modify_order_total_description' => array(
                'title' => __('', 'forumpay'),
                'type' => 'text',
                'description' => __('Enter a description for the underpayment fee that will appear on the customer\'s invoice.', 'forumpay'),
                'default' => __('ForumPay underpayment', 'forumpay')),

            'accept_overpayment' => array(
                'title' => __('Auto-Accept Overpayments', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable this option to automatically accept payments that exceed the total order amount.', 'forumpay'),
                'default' => 'no'),

            'accept_overpayment_modify_order_total' => array(
                'title' => __('', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Enable to modify the order total to reflect overpayments as a separate fee. This will be positive to indicate extra payment received.', 'forumpay'),
                'default' => 'no'),

            'accept_overpayment_modify_order_total_description' => array(
                'title' => __('', 'forumpay'),
                'type' => 'text',
                'description' => __('Enter a description for the overpayment fee that will appear on the customer\'s invoice.', 'forumpay'),
                'default' => __('ForumPay overpayment', 'forumpay')),

            'accept_latepayment' => array(
                'title' => __('Auto-Accept Late Payments', 'forumpay'),
                'type' => 'checkbox',
                'label' => __('Automatically accept the payment if transaction was received late and either the paid amount is similar to requested or accepting it is allowed by the other Auto-Accept conditions.', 'forumpay'),
                'default' => 'no'),
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
        $value = trim($value);
        $requiredErrorMessage = __('Title field is required.', 'forumpay');
        if (empty($value)) {
            WC_Admin_Settings::add_error($requiredErrorMessage);
            throw new \Exception($requiredErrorMessage);
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
        $value = trim($value);
        $requiredErrorMessage = __('API User field is required.', 'forumpay');
        if (empty($value)) {
            WC_Admin_Settings::add_error($requiredErrorMessage);
            throw new \Exception($requiredErrorMessage);
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
        $value = trim($value);
        $requiredErrorMessage = __('API Secret field is required.', 'forumpay');
        if (empty($value)) {
            WC_Admin_Settings::add_error($requiredErrorMessage);
            throw new \Exception($requiredErrorMessage);
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
        $value = trim($value);
        $errorMessage = __('POS ID field includes invalid characters. Allowed are: A-Za-z0-9._-', 'forumpay');
        $requiredErrorMessage = __('POS ID field is required.', 'forumpay');

        if (empty($value)) {
            WC_Admin_Settings::add_error($requiredErrorMessage);
            throw new \Exception($requiredErrorMessage);
        }

        if (preg_match('/[^A-Za-z0-9._\\-\:]/', $value)) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        return $value;
    }

    /**
     * Add validation rule to Underpayment threshold field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_accept_underpayment_field($key, $value) {
        $errorMessage = __('Invalid underpayment threshold. Please enter a valid percentage between 0 and 100.', 'forumpay');
        $isEnabled = (bool)$value;
        if (!$isEnabled) {
            return 'no';
        }

        $threshold = $this->get_post_data()['woocommerce_forumpay_accept_underpayment_threshold'];

        if (filter_var($threshold, FILTER_VALIDATE_FLOAT) === false) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        if (($threshold < 0) || ($threshold > 100)) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        return 'yes';
    }

    /**
     * Add validation rule to Underpayment fee description field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_accept_underpayment_modify_order_total_field($key, $value) {
        $errorMessage = __('Fee description for underpayment is required.', 'forumpay');
        $isEnabled =
            array_key_exists('woocommerce_forumpay_accept_underpayment', $this->get_post_data())
            && (bool)$this->get_post_data()['woocommerce_forumpay_accept_underpayment']
            && (bool)$value;

        if (!$isEnabled) {
            return 'no';
        }

        $description = trim($this->get_post_data()['woocommerce_forumpay_accept_underpayment_modify_order_total_description']);

        if (empty($description)) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        return 'yes';
    }

    /**
     * Add validation rule to Overpayment fee description field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_accept_overpayment_modify_order_total_field($key, $value) {
        $errorMessage = __('Fee description for overpayment is required.', 'forumpay');
        $isEnabled =
            array_key_exists('woocommerce_forumpay_accept_overpayment', $this->get_post_data())
            && (bool)$this->get_post_data()['woocommerce_forumpay_accept_overpayment']
            && (bool)$value;

        if (!$isEnabled) {
            return 'no';
        }

        $description = trim($this->get_post_data()['woocommerce_forumpay_accept_overpayment_modify_order_total_description']);

        if (empty($description)) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        return 'yes';
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
        $errorMessage = __('Custom environment URL must be valid URL.', 'forumpay');

        if (empty($value)) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        return $value;
    }

    /**
     * Add validation rule to Custom webhook URL field
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_webhook_url_field($key, $value)
    {
        $errorMessage = __('Custom webhook URL must be valid URL.', 'forumpay');

        if (empty($value)) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            WC_Admin_Settings::add_error($errorMessage);
            throw new \Exception($errorMessage);
        }

        return $value;
    }

    /**
     * Output the admin options table.
     */
    public function admin_options()
    {
        echo '<h3>' . esc_html(__('ForumPay Payment Gateway', 'forumpay')) . '</h3>';
        echo '<p>' . esc_html(__('Pay with Crypto (by ForumPay)', 'forumpay')) . '</p>';
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
        $apibase = get_site_url() . '/wp-json/wc-api/wc_forumpay';
        $base_path = WP_PLUGIN_URL . "/" . plugin_basename(dirname(FORUMPAY_FILE));
        $return_url = $this->get_return_url($order);
        $cancel_url = wc_get_cart_url();

        $parsed_url = parse_url($this->getApiUrl());
        $protocol = isset($parsed_url['scheme']) ? $parsed_url['scheme'] : null;
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : null;
        $forumPayApiUrl = '';
        if ($protocol && $host) {
            $forumPayApiUrl = $protocol . "://" . $host;
        }

        $templatehtml = '<div id="ForumPayPaymentGatewayWidgetContainer">{{message}}</div>';

        $templatehtml .= '<span id="forumpay-nonce" data="' . esc_attr(wp_create_nonce('wp_rest')) . '"></span>';
        $templatehtml .= '<span id="forumpay-apibase" data="' . esc_url($apibase) . '"></span>';
        $templatehtml .= '<span id="forumpay-returnurl" data="' . esc_url($return_url) . '"></span>';
        $templatehtml .= '<span id="forumpay-cancelurl" data="' . esc_url($cancel_url) . '"></span>';
        $templatehtml .= '<span id="forumpay-forumpayapiurl" data="' . esc_url($forumPayApiUrl) . '"></span>';

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
        if (!empty($this->fp_api_url_override)) {
            return $this->fp_api_url_override;
        }

        return $this->fp_api_url;
    }

    /**
     * Get Api key from settings
     *
     * @return mixed|string
     */
    public function getMerchantApiUser()
    {
        return $this->fp_api_user;
    }

    /**
     * Get Api secret from settings
     *
     * @return mixed|string
     */
    public function getMerchantApiSecret()
    {
        return $this->fp_api_key;
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
     * @return string
     */
    public function getAssetVersion()
    {
        return $this->getApiUrlOverride()
            ? strval(mt_rand(1, 1000))
            : $this->getPluginVersion();
    }

    /**
     * Return POS ID from settings
     *
     * @return mixed|string
     */
    public function getPosId()
    {
        return $this->fp_pos_id;
    }

    /**
     * Return weather or not zero confirmation is checked in settings
     *
     * @return bool
     */
    public function isAcceptZeroConfirmations()
    {
        return $this->fp_accept_zero_confirmations;
    }

    /**
     * @return string
     */
    public function getSid()
    {
        return $this->fp_sid;
    }

    /**
     * @return string|null
     */
    public function getWebhookUrl()
    {
        return $this->fp_webhook_url;
    }

    /**
     * @return array
     */
    public function getAcceptUnderpayment(): array
    {
        return $this->fp_accept_underpayment;
    }

    /**
     * @return array
     */
    public function getAcceptOverpayment(): array
    {
        return $this->fp_accept_overpayment;
    }

    /**
     * @return array
     */
    public function getAcceptLatePayment(): array
    {
        return $this->fp_accept_late_payment;
    }

    /**
     * @return string
     */
    public function getApiUrlOverride()
    {
        return $this->fp_api_url_override;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->fp_currency;
    }
}
