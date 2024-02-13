<?php
/**
 * Plugin Name: ForumPay Crypto Payments for WooCommerce
 * Plugin URI: https://forumpay.com
 * Description: Accept payments in WooCommerce with the official ForumPay plugin
 * Version: 2.1.5
 * Author: ForumPay
 **/
namespace ForumPay\PaymentGateway\WoocommercePlugin;

use Throwable;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-includes/version.php');

define('FORUMPAY_FILE', __FILE__);
define('FORUMPAY_DIR', dirname(FORUMPAY_FILE));
define('FORUMPAY_VERSION', '2.1.5');

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

function handleException(Throwable $throwable)
{
   throw  $throwable;
}

function initialize()
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
        handleException($throwable);
    }
}

add_action('plugins_loaded', __NAMESPACE__ . '\\initialize');

register_activation_hook(FORUMPAY_FILE, __NAMESPACE__ . '\forumpay_activation_hook');
