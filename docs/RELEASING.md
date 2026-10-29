# Releases and WordPress.org submission

## Before a public release

1. Verify the complete new-account and existing-account journeys. Confirm project selection, denied approval, retry, and a successful catalog read.
2. Run the checks in `TESTING.md` against the release commit. Record the actual WordPress, WooCommerce, and PHP versions tested. Only then add/update `Tested up to` in `readme.txt` and WooCommerce compatibility header metadata; do not infer compatibility from syntax checks.
3. Keep the version in `wisepim-for-woocommerce.php` and `Stable tag` in `readme.txt` identical. Update the changelog.
4. Run `python3 scripts/package.py` and `python3 scripts/test_package.py`. Install the generated ZIP on a clean WordPress site.
5. Tag the reviewed commit `v0.2.1` (or the matching version). The release workflow reruns checks, builds the ZIP and checksum, and creates a **draft** GitHub release. Review the draft before publishing it.

The exact runtime manifest in `scripts/package.py` must be updated when deliberately adding an asset or runtime file. Unlisted files are never packaged.

The package contains only runtime files, the WordPress readme, and license. It excludes tests, CI configuration, documentation, development tooling, caches, secrets, and repository metadata.

## WordPress.org

GitHub ZIP users currently update manually. There is no custom updater; WordPress.org-distributed releases use the directory update mechanism.

The repository is not a WordPress.org listing. Submit the finished ZIP through the official plugin submission flow after service verification. Use the verified WordPress.org contributor username in the `Contributors` field.

Prepare real screenshots, a plugin icon, and banner images from the final tested interface. Store directory-only media in the directory SVN `assets/` folder, separate from runtime `assets/`. Validate the readme with the official validator. Run Plugin Check and resolve material findings before submission. Record any review-required changes in this repository.

After approval, WordPress.org provides an SVN repository. Release code belongs at the root of `trunk/` and immutable version directories under `tags/`. The `Stable tag` points to the release tag. Use WordPress.org's update mechanism; this plugin includes no custom updater or remote-code loader.

Official references:
- https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/
- https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
