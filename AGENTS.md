# Contributor and agent contract

- Start with `git status --short --branch`; preserve changes made by others.
- Keep this repository suitable for public distribution. Never commit credentials, customer data, private operational notes, or screenshots containing account information. Local credentials belong in ignored `.env`; private notes and artifacts belong in ignored `.internal/` and `outputs/`.
- This is a small WordPress connector. Keep catalog processing in WISEPIM and do not add network requests on activation or during local diagnostics.
- Connection requires explicit user action, the `manage_woocommerce` capability, a nonce, a POST request, and valid local prerequisites. Keep redirect origins fixed; never accept caller-provided destination URLs.
- Escape output, validate inputs, and translate user-visible strings with the `wisepim-for-woocommerce` text domain. Do not store API credentials or infer remote connection success from a local launch timestamp.
- Run `php tests/run.php`, PHP syntax checks, and `python3 scripts/test_package.py` after relevant edits. Run `python3 scripts/package.py` to produce the installable ZIP. Real WordPress and browser testing remain necessary for runtime or interface changes.
- Keep the plugin header version and `readme.txt` stable tag aligned. Update compatibility claims only for versions actually tested. Follow `docs/RELEASING.md` for release verification.
