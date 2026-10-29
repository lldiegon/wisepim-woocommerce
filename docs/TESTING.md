# Testing

## Fast checks

```sh
php tests/run.php
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
python3 scripts/test_package.py
python3 scripts/package.py
```

The PHP harness tests WordPress-boundary behavior using stubs: missing prerequisites, unsafe store URLs, subdirectory preservation, permission and nonce checks, POST-only connection, fixed redirect origin, and minimal local state. It is not a replacement for running WordPress. Packaging tests check ZIP layout, reproducibility, excluded development files, symlink safety, and version consistency.

CI runs PHP checks against 7.4, 8.2, and 8.4 and runs the official WordPress Plugin Check action against the packaged runtime files. The runtime must also meet the PHP requirements of the installed WooCommerce version.

## Local WordPress

Docker Compose provides an isolated WordPress and MariaDB setup at `http://localhost:8889`. Copy `.env.example` to `.env` and set disposable local database passwords. Never use that configuration on a public server. The web port binds only to localhost; the database has no published host port.

```sh
cp .env.example .env
# Edit .env and set local-only database passwords.
docker compose up -d wordpress
# Choose a disposable local administrator password before running this command.
# Set WP_TEST_ADMIN_PASSWORD in your shell; do not save a real account password.
docker compose run --rm cli wp core install --url=http://localhost:8889 --title='Plugin development' --admin_user=plugin-admin --admin_password="$WP_TEST_ADMIN_PASSWORD" --admin_email=admin@example.test --skip-email
docker compose run --rm cli wp rewrite structure '/%postname%/'
docker compose run --rm cli wp plugin install woocommerce --activate
docker compose run --rm cli wp plugin activate wisepim-for-woocommerce
docker compose run --rm cli wp plugin install plugin-check --activate
docker compose run --rm cli wp plugin check wisepim-for-woocommerce --exclude-directories=.github,.internal,dist,docs,scripts,tests --exclude-files=compose.yaml,README.md,CONTRIBUTING.md,SECURITY.md --require=wp-content/plugins/plugin-check/cli.php
```

For release checks, install the built ZIP in a separate clean WordPress environment and run Plugin Check on that installed plugin. The development mount includes documentation and tooling that are deliberately excluded from the release ZIP.

The plain HTTP development site should show the HTTPS requirement and prevent Connect. Use a separately configured HTTPS test store to exercise complete authorization; do not weaken the production HTTPS requirement to accommodate localhost.

Stop the environment with `docker compose down`. `docker compose down --volumes` also deletes the disposable database and WordPress files. Never run that command against data you need to preserve.

## Browser checklist

- Activate on a site with WooCommerce present; verify the WISEPIM menu and setup link.
- Verify the missing-WooCommerce dependency behavior on a separate site.
- Open the page at desktop and mobile widths; tab through links and the connect control.
- Check that HTTP and Plain permalinks each prevent connecting and give a useful correction.
- Verify Connect submits a nonce-protected POST, then redirects to the fixed WISEPIM origin with the correct store URL and campaign parameters.
- Confirm a logged-out merchant can create an account or sign in and return to store setup.
- Confirm the intended project is visible before authorization.
- Approve and deny the WooCommerce access request; verify the resulting WISEPIM state.
- Revisit WordPress after launch: it must show an attempt, not claim verified remote success.
- Verify the API-key management link and manual revocation guidance.
- Delete the plugin on a disposable site: its timestamp should disappear while products and API keys remain.

Record the exact versions and journeys exercised. Local screenshots and observations are evidence only of the environment in which they were produced.
