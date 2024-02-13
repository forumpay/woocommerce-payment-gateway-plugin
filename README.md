# ForumPay Payments for WooCommerce
# Installation guide

## Requirements

> Make sure you have at least WordPress Version 6.2 or higher and WooCommerce plugin is up-to-date.

> You should already have downloaded the latest release of ForumPay plugin from [this link](https://github.com/forumpay/woocommerce-payment-gateway-plugin/releases/latest).
Download the file named forumpay-payments-for-woocommerce-v2.1.4

## Installation

### Using admin panel

Open your admin panel and select **Plugins** tab. Click **Add New** and then **Upload Plugin**.
Select the downloaded ForumPay module .zip file.

### Manual method (through filesystem):

Transfer the downloaded .zip archive to your server and unzip it, so the **woocommerce-forumpay** directory goes into the **/wp-content/plugins/** directory **relative** to your **WordPress** root.
Directory structure should be as follows: **/wp-content/plugins/woocommerce-forumpay**.

After the module is installed, go back to the **Plugins** page and activate it.

## Upgrade from previous version

Open your admin panel and select **Plugins** tab. Click **Add New** and then **Upload Plugin**.
Select the downloaded ForumPay module .zip file.

### Manual method (through filesystem):

As you already have **/wp-content/plugins/forumpay-payments-for-woocommerce** or **/wp-content/plugins/woocommerce-forumpay** folder from previous versions, you need to remove them first.
Transfer the downloaded .zip archive to your server and unzip it, so the **forumpay-payments-for-woocommerce** directory goes into the **/wp-content/plugins/** directory **relative** to your **Wordpress** root.
Directory structure should be as follows: **/wp-content/plugins/forumpay-payments-for-woocommerce**.

After the module is installed, go back to the **Plugins** page and activate it.

## Configuration

Once the plugin has been activated, go to the configuration page.

Navigate to **WooCommerce > Settings > Payments**, find the Forumpay module and click **Manage**.

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

## Webhook setup

**Webhook** allows us to check order status **independently** of the customer's actions.

For example, if the customer **closes tab** after the payment is started, the webshop cannot determine what the status of the order is.

If you do not set the webhook notifications, orders may stay in the *Pending* status **forever**.

### Webhook setup:

Webhook configuration is in your [Profile](https://dashboard.forumpay.com/pay/userPaymentGateway.api_settings#webhook_notifications). You can find the webhook URL by scrolling down.

Insert **URL** in the webhook URL field:
`YOUR_WEBSHOP/index.php?wc-api=wc_forumpay&act=webhook`

**YOUR_WEBSHOP** is the URL of your webshop. An example:
`https://my.webshop.com/index.php?wc-api=wc_forumpay&act=webhook`

## Functionality

When the customer clicks on the **Place order** button they are being redirected to the payment page, where cryptocurrency can be selected.

When the currency is selected, details for the cryptocurrency payment will be displayed: Amount, Rate, Fee, Total, Expected time.

After the customer clicks the **START PAYMENT** button, they have 5 minutes to pay for the order by scanning the **QR Code** or manually using the blockchain address shown under the QR Code.

## Troubleshooting

**Can not select cryptocurrency, there is no dropdown:**

This issue probably happens because webshop's backend cannot access ForumPay.
Please check if your API keys in the configuration are correct.

**The plugin has been installed and activated successfully, but it does not appear in the WooCommerce payments settings**

Please ensure that you have installed the latest release of the ForumPay Payment Gateway plugin from [this](https://github.com/forumpay/woocommerce-payment-gateway-plugin/releases/latest) url.

**Server returns **413 Request Entity Too Large** error when uploading the plugin zip file**

Please check your Nginx configuration. **client_max_body_size** and **post_max_size** properties should be larger than the uploaded .zip file
```
server {
  client_max_body_size 20M;
  post_max_size 20M;

  ...
}
```

## Logs

To access logs, navigate to the admin dashboard and select WooCommerce > Status > Logs
