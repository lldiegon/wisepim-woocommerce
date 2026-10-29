# Contributing

Use focused pull requests and include the user-visible reason for a change. Never commit merchant credentials, WordPress database exports, or personal information.

Runtime PHP belongs in the main plugin file and `includes/`; admin assets belong in `assets/`. Keep all user-facing PHP strings translatable under `wisepim-for-woocommerce`. Escape at output, validate at boundaries, check `manage_woocommerce` and a WordPress nonce before administrative actions, and keep outbound destinations fixed to approved WISEPIM origins.

Run the PHP behavior tests, PHP syntax checks, packaging tests, and a real WordPress smoke test described in `docs/TESTING.md`. A stubbed unit test does not prove WordPress integration or a successful production connection. Do not add network calls on activation, remote code, tracking without consent, or automatic catalog mutations.

This project accepts contributions under GPL-2.0-or-later. By submitting changes, you confirm you have the right to contribute them under that license.
