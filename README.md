# Wordpress Woocommerce Forumpay payment module <br> Installation guide

## Requirements

> Make sure you have at least Wordpress Version 5.0 or higher and Woocommerce plugin is up to date.

> You should already have downloaded .zip archive with Forumpay plugin

## Installation

Open your admin panel and select **Plugins** tab. Click **Add New** and then **Upload Plugin**.
Select the downloaded ForumPay module .zip file.

### Manual method (through filesystem):

Transfer the downloaded .zip archive to your server and unzip it, so the **module** directory goes into the **/wp-content/plugins/** directory **relative** to your **Wordpress** root.
Directory structure should be as follows: **/wp-content/plugins/woocommerce-forumpay**.

After the module is installed, go back to the **Plugins** page and activate it.

## Upgrade from previous version

Open your admin panel and select **Plugins** tab. Click **Add New** and then **Upload Plugin**.
Select the downloaded ForumPay module .zip file.

### Manual method (through filesystem):
As you already have **/wp-content/plugins/woocommerce-forumpay** folder from previous version, you need to remove it first.
Transfer the downloaded .zip archive to your server and unzip it, so the **module** directory goes into the **/wp-content/plugins/** directory **relative** to your **Wordpress** root.
Directory structure should be as follows: **/wp-content/plugins/woocommerce-forumpay**.

After the module is installed, go back to the **Plugins** page and activate it.

## Configuration

Once the plugin has been activated, go to the configuration page.

Navigate to **WooCommerce > Settings > Payments**, find the Forumpay module and click **Manage**.

### Configuration details:

1. **Title**
   The label of the payment method that is displayed when your customer is prompted to choose one.
   You can leave default or set it to something like *Pay with crypto*.
2. **Description**
   Additional information along with *Title*.
3. **POS ID**
   Identifier for payments from this webshop to be identified in your ForumPay dashboard.
   Must be a unique string. E.g.: woocommerce1
4. **API User**
   Unique ForumPay API-key identifier that you have to generate in the Forumpay dashboard.
   It can be found in your **Profile** section.
   [Go to profile >](https://dashboard.forumpay.com/pay/userPaymentGateway.api_settings)
5. **API Secret**
   *Important:* never share it to anyone!
   Think of it as a password.
   API Secret consists of two parts: the first one will be displayed in your profile.
   The second part will be sent to your e-mail.

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

> **Can not select cryptocurrency, there is no dropdown:**
This issue probably happens because webshop's backend cannot access ForumPay.
Please check if your API keys in the configuration are correct.
