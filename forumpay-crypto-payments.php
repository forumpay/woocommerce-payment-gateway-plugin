<?php
/**
 * Plugin Name: ForumPay Crypto Payments for WooCommerce
 * Plugin URI: https://forumpay.com
 * Description: Accept payments in WooCommerce with the official ForumPay plugin
 * Version: 2.2.10
 * Tested up to: 6.7
 * WC tested up to: 9.7
 * License: GPLv2 or later
 * Author: ForumPay
 **/
namespace ForumPay\PaymentGateway\WoocommercePlugin;

use Throwable;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-includes/version.php');

define('FORUMPAY_ICON', apply_filters('woocommerce_custom_gateway_icon', plugins_url('/images/logo-forumpay-1.svg', __FILE__)));
define('FORUMPAY_FILE', __FILE__);
define('FORUMPAY_DIR', dirname(FORUMPAY_FILE));
define('FORUMPAY_PLUGIN_DIR', plugin_dir_url(__FILE__));
define('FORUMPAY_VERSION', '2.2.10');

/**
 * When plugin is activated
 */
function forumpay_activation_hook()
{
    if (! forumpay_autoload()) {
        die(sprintf(
            'ForumPay Payment Gateway Plugin could not be activated. Make sure you are using the latest %s of the plugin.',
            '<a href="https://github.com/forumpay/woocommerce-payment-gateway-plugin/releases/latest" target="_blank" rel="noopener noreferrer">release<a/>'
        ));
    }
}

function forumpay_autoload()
{
    $autoloader = FORUMPAY_DIR . '/vendor/autoload.php';
    if (file_exists($autoloader)) {
        require $autoloader;
        return true;
    }
    return false;

}

function forumpay_handleException(Throwable $throwable)
{
    throw  $throwable;
}

function forumpay_initialize()
{
    if (!class_exists('WC_Payment_Gateway')) {
        //Woocommerce not installed.
        return;
    }
    try {
        if (!forumpay_autoload()) {
            return;
        }
        $fppg = new ForumPayPaymentGateway();

    } catch (Throwable $throwable) {
        forumpay_handleException($throwable);
    }
}

add_action('plugins_loaded', __NAMESPACE__ . '\\forumpay_initialize');

register_activation_hook(FORUMPAY_FILE, __NAMESPACE__ . '\forumpay_activation_hook');
