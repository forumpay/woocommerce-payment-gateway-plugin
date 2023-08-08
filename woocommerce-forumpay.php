<?php
/**
 * Plugin Name: WooCommerce ForumPay Payment Gateway Plugin
 * Plugin URI: https://forumpay.com
 * Description: Extends WooCommerce with ForumPay gateway.
 * Version: 2.0.0
 * Author: Limitlex
 **/
namespace ForumPay\PaymentGateway\WoocommercePlugin;

use Throwable;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(ABSPATH . 'wp-includes/version.php');

define('FORUMPAY_FILE', __FILE__);
define('FORUMPAY_DIR', dirname(FORUMPAY_FILE));
define('FORUMPAY_VERSION', '2.0.0');

/**
 * When plugin is activated
 */
function forumpay_activation_hook()
{
    forumpay_autoload();
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
