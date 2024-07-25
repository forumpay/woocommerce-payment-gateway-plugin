=== ForumPay Crypto Payments for WooCommerce ===

Contributors: forumpay
Tags: cryptocurrency, gateway, payment, woocommerce
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.2.2
License: GPLv2 or later

ForumPay Payment Gateway Module for Woocommerce

== Description ==

Crypto payment gateway for your business
Accept any crypto from any wallet and get paid in cash, instantly.

Crypto consumers are one of the fastest growing segments in the world with over 300 million wallets issued to date. Studies show that crypto consumers spend 2x AOV of a typical credit card consumer and that 40% are in fact new customers. Crypto is more efficient than traditional payment methods as there are less parties involved in a payment process.

== Installation ==

###Using admin panel

Open your admin panel and select **Plugins** tab. Click **Add New** and then **Upload Plugin**.
Select the downloaded ForumPay module .zip file.

###Manual method (through filesystem):

Transfer the downloaded .zip archive to your server and unzip it, so the **forumpay-crypto-payments** directory goes into the **/wp-content/plugins/** directory **relative** to your **WordPress** root.
The directory structure should be as follows: **/wp-content/plugins/forumpay-crypto-payments**.

After the module is installed, go back to the **Plugins** page and activate it.

### Configuration details:

1. **Title**
   The label of the payment method that is displayed when user is prompted to choose one. You can leave default or set it to something like *Pay with crypto*.
2. **Description**
   The additional description of the payment method that is displayed under the title.
3. **Environment**
   Dropdown lets you switch between 'Production' and 'Sandbox' modes.
   Use 'Production' for processing real transactions in a live environment and
   'Sandbox' for safe testing without financial implications.
4. **API User**
   This is our identifier that we need to access the payment system.
   It can be found in your **Profile**.
   [Go to profile >](https://dashboard.forumpay.com/pay/userPaymentGateway.api_settings)
5. **API Secret**
   _Important:_ never share it to anyone!
   Think of it as a password.
   API Secret consists of two parts. When generated in [ForumPay dashboard](https://dashboard.forumpay.com/pay/userPaymentGateway.api_settings),
   the first one will be displayed in your profile, while the second part will be sent to your e-mail.
   You need to enter both parts here (one after the other).
6. **POS ID**
   This is how payments coming to your wallets are going to be identified.
   Special characters are not allowed. Allowed characters are: `[A-Za-z0-9._-]` (e.g. `my-shop`, `my_shop`).
7. **Accept Instant (Zero) Confirmations**
   Allows immediate transaction approval without waiting for network confirmations, enhancing speed but with increased risk.
8. **Custom environment URL**
    Optional: URL to the API server. This value will override the default setting. Only used for debugging.

Don't forget to click *Save changes* button after the settings are filled in.

== Frequently Asked Questions ==

=Can not select cryptocurrency, there is no dropdown:=
This issue probably happens because webshop\'s backend cannot access ForumPay.
Please check if your API keys in the configuration are correct.

=The plugin has been installed and activated successfully, but it does not appear in the WooCommerce payments settings=
Please ensure that you have installed the latest release of the ForumPay Payment Gateway plugin.

=Server returns **413 Request Entity Too Large** error when uploading the plugin zip file=
Please check your Nginx configuration. **client_max_body_size** and **post_max_size** properties should be larger than the uploaded .zip file

`
server {
    client_max_body_size 20M;
    post_max_size 20M;

    ...
}
`

== Upgrade Notice ==

**Manual method (through filesystem):**
As you already have **/wp-content/plugins/forumpay-crypto-payments** or **/wp-content/plugins/woocommerce-forumpay** folder from previous versions, you need to remove them first.
Transfer the downloaded .zip archive to your server and unzip it, so the **forumpay-crypto-payments** directory goes into the **/wp-content/plugins/** directory **relative** to your **Wordpress** root.
Directory structure should be as follows: **/wp-content/plugins/forumpay-crypto-payments**.

After the module is installed, go back to the **Plugins** page and activate it.

**Upgrade from previous version**
Open your admin panel and select **Plugins** tab. Click **Add New** and then **Upload Plugin**.
Select the downloaded ForumPay module .zip file.

== Screenshots ==

1. Order checkout currency selection
2. Order checkout wallet QR code

== Changelog ==

= 2.1.3 =
* Plugin released.
