=== WISEPIM for WooCommerce ===
Tags: woocommerce, product information, product descriptions, catalog management, ai
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect your store to WISEPIM. Improve product descriptions, prepare translations, and manage your catalog. No API keys to copy.

== Description ==

Write and translate WooCommerce product content in one place.

Connect your WooCommerce store to WISEPIM, import your catalog, and start improving your product content. You choose when to send changes back to your store.

There are no API keys to copy. Open WooCommerce → WISEPIM in your WordPress dashboard, sign in or create an account, and approve access to your store.

= What you can do =

* Improve product descriptions with WISEPIM's AI content tools.
* Translate product content into the languages your customers use.
* Manage product content across your catalog in one place.
* Send your changes back to WooCommerce when you choose to export them.

The plugin connects your store. You do the catalog work in WISEPIM. Installing the plugin or clicking Connect does not import, edit, or publish products.

= Need a hand? =

WooCommerce connection guide: https://docs.wisepim.com/en/integrations/woocommerce
WISEPIM documentation: https://docs.wisepim.com

Find help with connecting your store, importing products, improving content, and translating your catalog.

Need help with your store? Contact the WISEPIM team: https://wisepim.com/contact

= What you need =

The plugin is free. A WISEPIM account is required, and service features and usage depend on your plan. See https://wisepim.com/pricing for current plans.

WooCommerce must be active, your store must use HTTPS, and WordPress permalinks must be set to something other than Plain. The setup page checks these requirements and shows what needs attention.

= External service and data use =

WISEPIM is an external product information management service at https://wisepim.com. It provides the catalog importing, content improvement, translation, and export tools used with this connector.

Activating the plugin and viewing its setup checks do not contact WISEPIM. When you click Connect to WISEPIM, your browser opens wisepim.com with your store URL, the plugin version, and fixed referral labels identifying this WordPress connector. The link contains no API keys.

After you sign in and approve access in WooCommerce, WooCommerce sends the generated API credentials directly to WISEPIM. The service uses that access for the catalog workflows you choose. The plugin does not store the credentials or copy your catalog data.

Service terms: https://wisepim.com/terms
Privacy policy: https://wisepim.com/privacy

== Installation ==

1. Install the plugin: upload the ZIP through Plugins > Add New Plugin > Upload Plugin, then activate it.
2. Connect your store: open WooCommerce > WISEPIM and click Connect to WISEPIM. Sign in or create an account, choose your project, and approve access.
3. Start with your catalog: open the connected store in WISEPIM and import your catalog. Improve your content, then export the changes you want to use.

== Frequently Asked Questions ==

= Is the plugin free? =

Yes. A WISEPIM account is required to work on your catalog. WISEPIM features and usage depend on your plan; see https://wisepim.com/pricing for current plans.

= Do I need to create API keys? =

No. WooCommerce creates the credentials and sends them to WISEPIM after you approve access.

= Will connecting change my products? =

Connecting does not import, edit, or publish products. You start those workflows in WISEPIM and choose when to export changes to your store.

= How do I know the connection worked? =

Open your store in WISEPIM to check its connection. The WordPress page shows local setup checks and when you last started connecting; it does not monitor the remote connection.

= How do I disconnect? =

Manage the store connection in WISEPIM and revoke its API key under WooCommerce > Settings > Advanced > REST API. Deactivating or deleting this plugin alone does not revoke access or delete data held in WISEPIM.

= What happens when I delete the plugin? =

The plugin removes its saved setup timestamp. Your products and WooCommerce API keys stay intact.

= Why can't I connect? =

Check the WISEPIM setup page first. WooCommerce must be active, your store must use HTTPS, and permalinks must not be set to Plain. If those checks pass but authorization fails, a hosting firewall or security plugin may be blocking WooCommerce access. Ask your store administrator to check it.

== Changelog ==

= 0.2.0 =
* See local product and variation counts before connecting.
* Clearer setup guidance, improved mobile layout, and more accessible controls.

= 0.1.3 =
* Add direct documentation and support links.

= 0.1.2 =
* Clarify product benefits, setup steps, pricing, and access controls.

= 0.1.1 =
* Harden setup validation and multisite cleanup.
* Restrict release packaging to the reviewed runtime files.

= 0.1.0 =
* Initial guided WISEPIM connection and local setup diagnostics.
* Explicit service consent, automatic store URL, and referral-source attribution.
