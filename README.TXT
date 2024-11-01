=== WooCommerce Visma integration (WooVisma) ===
Contributors:		Onlineforce
Plugin Name:		WooCommerce Visma integration (WooVisma)
Plugin URI:			www.onlineforce.net
Tags: 				Visma, WooCommerce Visma integration, Visma integration, Visma eAccounting, eAccounting, WooVisma, e-ekonomi, visma eekonomi, visma e-ekonomi, 
Author URI:			www.uniwin.se
Author:				Uniwin
Requires at least:	3.8
Tested up to:		4.8.1
Stable tag:			1.3.0
Version:			1.3.0
License:			GPLv2 or later
License URI:		http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Visma integration (WooVisma) synchronizes WooCommerce Products to your Visma eAccounting system. This accounting automation plugin makes it easy to run your eCommerce business and focus on sales instead of administration. Enjoy!

== Description ==
WooCommerce Visma integration (WooVisma) synchronizes your WooCommerce Orders, Customers and Products to your Visma eAccounting system. 
This plugin requires WooCommerce plugin. The INVOICE and PRODUCT sync features require a license purchase from http://woovisma.com. WooCommerce Visma integration plugin 
connects to license server hosted at http://onlineforce.net to check the validity of the license key you type in the settings page.

= Data export to Visma: =
	*	PRODUCT (Simple):
		*	Product name
			*	ArticleNumber (SKU + product prefix)
			*	Regular Price / Sale Price
			*	Inventory stock quantity
			*	Product Tax Rate Setting
			*	Variable  Product and External/Affiliate  Product Sync
	*	CUSTOMER:
		*	Billing Company / Last Name
		*	Billing Last Name
		*	Billing First Name
		*	Email
		*	Billing Address 1
		*	Billing Address 2
		*	Billing Country
		*	Billing City
		*	Billing Postcode
		*	Shipping Address 1
		*	Shipping Address 2
		*	Shipping Country
		*	Shipping City
		*	Shipping Postcode
		*   VAT Zone


	* 	ORDER:
		*	Order ID (as reference)
		*	Order date
		*	Customer number
		*	Delivery Address
		*	Delivery City
		*	Delivery Postcode
		*	Delivery Country
		*	Product Title
		*	Product quantity
		*	Product Price
		*	Shipping cost (as orderline - workaround) 
		*	Coupon discount (as orderline - workaround)
		*	Currency
        
= Features of WooCommerce Visma integration (WooVisma): =
1.	Automatic sync of all products from WooCommerce to Visma eAccounting Articles. This function also sync products data modified after initial sync. Supports variable products.
2.	Automatic sync of all Orders from WooCommerce to Visma eAccounting system. Automatic sync is initiated when customer creates an order and Admin updates an existing order.
3.	Automatic sync of all Customers from WooCommerce to Visma eAccounting system.
4.	Manual sync of all Shipping methods from WooCommerce to Visma eAccounting system.
5.	To sync orders created before WooCommerce Visma integration (WooVisma) installation use "Activate old orders sync" option in plugin settings.
6.	"Activate product sync" option syncs product information from WooCommerce to Visma eAccounting system. (Stock information is updated regardless of this setting)
7.	Prefix added to the products stored from WooCommerce to Visma eAccounting system using "Product prefix" option.
8.	Multishop support. Use "Order reference prefix" to add a prefix to the order reference of an Order synced from WooCommerce to Visma eAccounting system.
        
== Plugin Requirement ==
*	Visma eAccounting API account.
*	HTTPS / SSL is mandatory
*	PHP version: 5.3 or higher, tested up to 5.5.X
*	WordPress: WordPress 3.8 or higher
*	Woocommerce: 2.6.0 or higher, tested upto 2.7.0

== Installation ==
1.	Install WooCommerce Visma Integration (WooVisma) either via the WordPress.org plugin directory, or by uploading the files to your server
2.	Activate the plugin in your WordPress Admin and go to the admin panel WooVisma->Settings menu.
4.	Configure your plugin as needed.
5.	That's it. You're ready to focus on sales, marketing and other cool stuff :-)

= How To =
* If you use https, you need to register a redirect URI pointing to your wordpress URL and update the configuration in settings page. 
* If you use http, no need to register redirect URI

== Frequently Asked Questions ==
http://woovisma.com/faq

== Changelog ==

= 1.3.0 =
* Tweak - Improvement in shipping cost handling

= 1.2.9 =
* Tweak - Improvement and enhancement

= 1.2.8 =
* Tweak - Improvement and enhancement

= 1.2.7 =
* Tweak - Improvement and enhancement

= 1.2.6 =
* Tweak - Improvement and bug fix

= 1.2.5 =
* Dev - added Terms of payment settings

= 1.2.4 =
* Dev - added Social Security Number (person number) support in checkout

= 1.2.3 =
* Dev - added product prefix multi shop feature

= 1.2.2 =
* Tweak - Improvement in order handling and User Interface

= 1.2.1 =
* Tweak - Plugin settings updated with new options

= 1.2.0 =
* Dev - Coupon handling

= 1.1.9 =
* Tweak - Improvement in user interface and performance

= 1.1.8 =
* Tweak - Plugin settings updated with new options

= 1.1.7 =
* Tweak - improvement in user interface

= 1.1.6.0 =
* Dev - improved manual sync features

= 1.1.5.2 =
* Tweak - Improvement in unsynced product, customer and order

= 1.1.5.1 =
* Dev - Added new tab to show unsynced product, customer and order

= 1.1.5.0 =
* Order Sync

= 1.1.4.0 =
* Customer Sync

= 1.1.3.0 =
* Variable  Product and External/Affiliate  Product Sync

= 1.1.2.3 =
* Visma Product Tax Rate block enhanced

= 1.1.2.2 =
* Added tax rate settings on product page

= 1.1.2.1 =
* Bug fixes

= 1.1.2 =
* Sitewide product tax setting added

= 1.1 =
* Support for HTTP protocol added.

= 1.0 =
* Initial Release