=== WISEPIM for WooCommerce ===
Tags: woocommerce, product information, product descriptions, catalog management, ai
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WooCommerce to WISEPIM to manage and improve product content, with guided setup and no manual API-key copying.

== Description ==

WISEPIM for WooCommerce connects your store to the WISEPIM product information management service. Start from your WordPress dashboard, sign in or create a WISEPIM account, and approve access to your store.

The plugin detects your store URL and checks the local requirements before you connect. Product importing, content improvement, translation, and export workflows run in WISEPIM and depend on your service plan and settings.

* Guided connection from your WooCommerce dashboard.
* Local checks for WooCommerce, HTTPS, and pretty permalinks.
* WooCommerce's built-in application authorization: no copying API keys.
* Links to WISEPIM and WooCommerce API-key management.
* No storefront scripts or background tracking added by this plugin.

A WISEPIM account is required. Paid service plans may be required for particular service features or usage. Learn about the service at https://wisepim.com.

= External service and data use =

This plugin connects to WISEPIM, an external product information management service operated at https://wisepim.com.

Activation and local setup checks do not contact WISEPIM. After you click Connect to WISEPIM, your browser navigates to wisepim.com with your store URL, plugin version, and fixed campaign labels identifying the WordPress plugin referral source. No API keys are included in this link.

After you sign in and approve access in WooCommerce, WooCommerce sends the generated API credentials directly to WISEPIM. The service uses the authorized WooCommerce API access to support your chosen catalog workflows. The plugin itself does not store those credentials or copy catalog data.

Service terms: https://wisepim.com/terms
Privacy policy: https://wisepim.com/privacy

== Installation ==

1. Install and activate WooCommerce, enable HTTPS, and use pretty permalinks.
2. Upload the plugin ZIP through Plugins > Add New Plugin > Upload Plugin and activate it.
3. Open WooCommerce > WISEPIM.
4. Click Connect to WISEPIM, sign in or create an account, choose your intended project, and approve access.
5. Continue in WISEPIM to import your catalog.

== Frequently Asked Questions ==

= Is the plugin free? =

The connector plugin is free software. A WISEPIM account is required, and service capabilities and usage depend on your plan. See the WISEPIM website for current plans.

= Does clicking Connect import or publish products? =

No. Connecting authorizes the store integration. Choose the catalog workflows you want inside WISEPIM.

= Does the plugin show whether my remote connection is healthy? =

The plugin shows local setup checks and when a connection was last started. Those are not confirmation that authorization succeeded. Inspect the connection in WISEPIM to verify it.

= How do I disconnect? =

Manage the store connection in WISEPIM and revoke its API key under WooCommerce > Settings > Advanced > REST API. Deactivating or deleting this plugin does not revoke WooCommerce keys or delete remote WISEPIM data.

= What does uninstall remove? =

Uninstall removes the plugin's local last-connection-attempt timestamp. It preserves products and WooCommerce API keys.

= Why does setup report a problem? =

WooCommerce must be active, your store must use HTTPS, and permalinks must not be set to Plain. Hosting firewalls or security plugins can also block WooCommerce authorization or REST API access; ask your administrator to check those endpoints.

== Changelog ==

= 0.1.0 =
* Initial guided WISEPIM connection and local setup diagnostics.
* Explicit service consent, automatic store URL, and referral-source attribution.
