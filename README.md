# WISEPIM for WooCommerce

**Write and translate WooCommerce product content in one place.**

Connect your WooCommerce store to [WISEPIM](https://wisepim.com), import your catalog, and start improving your product content. You choose when to send changes back to your store.

There are **no API keys to copy**. Open WooCommerce → WISEPIM in your WordPress dashboard, sign in or create an account, and approve access to your store.

## What you can do

- **Improve product descriptions** with WISEPIM's AI content tools.
- **Translate product content** into the languages your customers use.
- **Manage product content across your catalog** in one place.
- **Send your changes back to WooCommerce** when you choose to export them.

The plugin connects your store. You do the catalog work in WISEPIM.

## Get connected in three steps

This plugin is in preview. Use a test store until a stable [release](https://github.com/lldiegon/wisepim-woocommerce/releases) is published. Developers can build the installable ZIP with the commands below.

1. **Install the plugin.** In WordPress, go to **Plugins → Add New Plugin → Upload Plugin**, upload `wisepim-for-woocommerce.zip`, and activate it.
2. **Connect your store.** Open **WooCommerce → WISEPIM**, click **Connect to WISEPIM**, then sign in or create an account. Choose your project and approve access.
3. **Start with your catalog.** Open the connected store in WISEPIM and import your catalog. Improve your content, then export the changes you want to use.

The setup page checks your store before you connect and shows what needs attention. WooCommerce must be active, your store must use HTTPS, and WordPress permalinks must be set to something other than **Plain**.

**The plugin is free. A WISEPIM account is required.** WISEPIM features and usage depend on your plan. See [plans and pricing](https://wisepim.com/pricing).

## Your store stays under your control

Installing the plugin or clicking Connect does not import, edit, or publish products. You start those workflows in WISEPIM.

To confirm the connection worked, open your store in WISEPIM. The WordPress page shows setup checks and when you last started connecting; it does not monitor the remote connection.

To disconnect, manage the store in WISEPIM and revoke its key under **WooCommerce → Settings → Advanced → REST API**. Deleting the plugin alone does not revoke access or delete data held in WISEPIM.

## Privacy

Activating the plugin and viewing its setup checks do not contact WISEPIM. When you click **Connect to WISEPIM**, your browser opens `wisepim.com` with your store URL, the plugin version, and fixed referral labels identifying this WordPress connector. The link contains no API keys.

After you sign in and approve access, WooCommerce sends the generated API credentials directly to WISEPIM. The plugin does not store them. Uninstalling removes the plugin's saved setup timestamp and leaves your products and WooCommerce keys intact.

Read the [privacy policy](https://wisepim.com/privacy) and [service terms](https://wisepim.com/terms).

## Updates

For a ZIP installation, upload the newer release ZIP through WordPress to update the plugin. There is no automatic GitHub updater.

## For developers

Use PHP 7.4+ and Python 3.9+ for verification and packaging. WordPress and WooCommerce are required to run the plugin. The PHP version must also meet your installed WooCommerce version's requirements.

```sh
php tests/run.php
python3 scripts/test_package.py
python3 scripts/package.py
```

The build produces `dist/wisepim-for-woocommerce.zip` and its SHA-256 checksum.

Without a local PHP installation:

```sh
docker run --rm -v "$PWD:/app" -w /app php:8.2-cli php tests/run.php
docker run --rm -v "$PWD:/app" -w /app php:8.2-cli sh -c 'find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l'
```

See [local WordPress testing](docs/TESTING.md), [release instructions](docs/RELEASING.md), and [contributing](CONTRIBUTING.md). The source is licensed under GPL-2.0-or-later.
