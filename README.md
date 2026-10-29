# WISEPIM for WooCommerce

Connect a WooCommerce store to [WISEPIM](https://wisepim.com) without copying API keys. This small WordPress plugin provides a store-aware connection entry point, local setup checks, and links to manage the integration.

## Merchant installation

1. Download `wisepim-for-woocommerce.zip` from a GitHub release, or build it below.
2. In WordPress, choose **Plugins → Add New Plugin → Upload Plugin**, upload the ZIP, and activate it.
3. Open **WooCommerce → WISEPIM** and resolve any setup checks.
4. Choose **Connect to WISEPIM**, sign in or create an account, select the intended project, and approve WooCommerce access.
5. Continue in WISEPIM to import and improve your catalog.

ZIP installations currently receive updates by manually uploading a newer release ZIP. There is no automatic GitHub updater.

You need WooCommerce, HTTPS, pretty permalinks, and a WISEPIM account. Service features and usage are governed by your WISEPIM plan. Installing the plugin does not publish or modify products.

## What the plugin does

- Detects the current store address, including WordPress installations in a subdirectory.
- Checks WooCommerce availability, HTTPS, and pretty permalinks locally.
- Starts the existing WooCommerce application authorization flow through WISEPIM.
- Shows a clear launch state without claiming a store is connected merely because someone clicked a button.
- Provides access to WISEPIM and WooCommerce's API-key management.

Catalog processing remains in WISEPIM. The plugin does not run background catalog imports, embed a remote application, or inject storefront code.

## Privacy and permissions

The plugin does not contact WISEPIM when activated or while displaying local checks. Clicking **Connect to WISEPIM** navigates the administrator's browser to `https://wisepim.com`, passing the store URL, plugin version, and campaign labels (`utm_source=wordpress`, `utm_medium=plugin`, `utm_campaign=woocommerce-connect`). These labels identify this referral source; they contain no personal identifier or API secret.

After sign-in and explicit approval, WooCommerce sends generated API credentials directly to WISEPIM's authorization callback. WISEPIM then accesses the store through the existing connector. Credentials are not stored by this plugin. Removing this plugin does **not** revoke WooCommerce API credentials or erase catalog data held in WISEPIM. Revoke the relevant key under **WooCommerce → Settings → Advanced → REST API**, and manage your store connection in WISEPIM.

See [WISEPIM privacy policy](https://wisepim.com/privacy) and [terms](https://wisepim.com/terms). The local last-launch timestamp is removed on uninstall.

## Development

PHP 7.4+ and Python 3.9+ are used for local verification and packaging. WordPress and WooCommerce are runtime dependencies; no bundled PHP or JavaScript libraries are required.

```sh
php tests/run.php
python3 scripts/package.py
python3 scripts/test_package.py
```

Without a local PHP installation:

```sh
docker run --rm -v "$PWD:/app" -w /app php:8.2-cli php tests/run.php
docker run --rm -v "$PWD:/app" -w /app php:8.2-cli sh -c 'find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l'
```

[Local WordPress testing](docs/TESTING.md) covers real installation and the manual connection journey. [Release instructions](docs/RELEASING.md) explain ZIP validation and directory submission. Source code is GPL-2.0-or-later; contributions are welcome.
