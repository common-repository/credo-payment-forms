=== Credo WooCommerce Payment Gateway ===
Contributors: Credo Software Engineering
Tags: Credo, woocommerce, payment gateway mastercard, visa, verve
Requires at least: 5.8
Tested up to: 6.5.4
Stable tag: 2.0.2
Requires PHP: 7.4
License URI: http://www.gnu.org/licenses/gpl-2.0.txt



== Description ==

Credo enables easier, intelligent, and rewarding payments for businesses and consumers alike, by combining the best of digital payments and digital innovation.

With Credo for WooCommerce, you can accept payments via:

* Credit/Debit Cards:  Visa, Mastercard, Verve
* Bank transfer (Nigeria)
* Many more coming soon

= Why Credo? =

* Easy onboarding. Start receiving payments instantly. Go from sign-up to your first real transaction in as little as 5 minutes
* Settlement the way you want them.
* Simple, transparent pricing—no hidden charges or fees
* Advance fraud protection
* Your business growth our promise
* Understand your customers better through a simple and elegant dashboard
* Access to attentive, empathetic customer support 24/7
* Free updates as we launch new features and payment options
* Integration as easy as ABC

Sign up on [credocentral.com/register](https://credocentral.com/register) to get started.


= Note =

This plugin is meant to be used by merchants in Nigeria

= Plugin Features =

*   __Accept payment__ via Mastercard, Visa, Verve and Bank Transfer,
*   __Seamless integration__ into the WooCommerce checkout page. Accept payment directly on your site


== Installation ==

*   Go to __WordPress Admin__ > __Plugins__ > __Add New__ from the left-hand menu
*   In the search box type __Credo WooCommerce Payment Gateway__
*   Click on Install now when you see __Credo WooCommerce Payment Gateway__ to install the plugin
*   After installation, __activate__ the plugin.


= Credo Setup and Configuration =
*   Go to __WooCommerce > Settings__ and click on the __Payments__ tab
*   You'll see Credo listed along with your other payment methods. Click __Set Up__
*   On the next screen, configure the plugin. There is a selection of options on the screen. Read what each one does below.

1. __Enable/Disable__ - Check this checkbox to Enable Credo on your store's checkout
2. __Title__ - This will represent Credo on your list of Payment options during checkout. It guides users to know which option to select to pay with Credo. __Title__ is set to "Debit/Credit Cards" by default, but you can change it to suit your needs.
3. __Description__ - This controls the message that appears under the payment fields on the checkout page. Use this space to give more details to customers about what Credo is and what payment methods they can use with it.
4. __Test Mode__ - Check this to enable test mode. When selected, the fields in step six will say "Test" instead of "Live." Test mode enables you to test payments before going live. The orders process with test payment methods, no money is involved so there is no risk. You can uncheck this when your store is ready to accept real payments.
5. __Payment Option__ -  Select how Credo gateway displays to your customers. A popup displays Credo Gateway on the same page, while Redirect will redirect your customer to Credo Gateway page for payment.
6. __API Keys__ - The next two text boxes are for your Credo API keys, which you can get from your Credo Dashboard. If you enabled Test Mode in step four, then you'll need to use your test API keys here. Otherwise, you can enter your live keys.
7. __Additional Settings__ - While not necessary for the plugin to function, there are some extra configuration options you have here. You can do things like add custom metadata to your transactions (the data will show up on your Credo dashboard) or use Credo's [Dynamic settlement feature](https://credocentral.com). The tooltips next to the options provide more information on what they do.
8. Click on __Save Changes__ to update the settings.

To account for poor network connections, which can sometimes affect order status updates after a transaction, we __strongly__ recommend that you set a Webhook URL on your Credo dashboard. This way, whenever a transaction is complete on your store, we'll send a notification to the Webhook URL, which will update the order and mark it as paid. You can set this up by using the URL in red at the top of the Settings page. Just copy the URL and save it as your webhook URL on your Credo dashboard under __Settings > webhook tab.

If you do not find Credo on the Payment method options, please go through the settings again and ensure that:

*   You've checked the __"Enable/Disable"__ checkbox
*   You've entered your __API Keys__ in the appropriate field
*   You've clicked on __Save Changes__ during setup

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

*   A Credo merchant account—use an existing account or [create an account here](https://credocentral.com/register)
*   An active [WooCommerce installation](https://docs.woocommerce.com/document/installing-uninstalling-woocommerce/)
*   A valid [SSL Certificate](https://docs.woocommerce.com/document/ssl-and-https/)


== Changelog ==


= 2.0.2 - June 5, 2024 =
*   Update: Changed author and contributor to Credo Software Engineering
*   Update: Add compatibility with Wordpress version 6.5.4



= 2.0.1 - April 5, 2024 =
*   Update: Removed "public" from api base url

= 2.0.0 - January 27, 2024 =
*   New: Add support for WooCommerce checkout block
*   Tweak: WooCommerce 8.0 compatibility
*   Improve: Ensure order amount is in integer when initializing payment on Credo

= 1.0.7 - October 8, 2023 =
*   Added Pop up payment option

= 1.0.6 - September 21, 2023 =
*   Update: Updated banners and icons
*   Update: Updated Dynamic Settlement Settings

= 1.0.1 - August 22, 2023 =
*   Update: Added Assets

= 1.0.0 - July 24, 2023 =
*   First release







== Screenshots ==

1. Credo WooCommerce payment gateway settings page

2. Credo on WooCommerce Checkout
3. Credo payment gateway
