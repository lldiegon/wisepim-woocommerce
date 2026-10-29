# Security policy

Report vulnerabilities privately using GitHub's **Report a vulnerability** feature on this repository when enabled. If that option is unavailable, use the contact channel listed at https://wisepim.com and request a private security contact. Do not put merchant credentials or exploit details in a public issue.

Include the plugin version, WordPress and WooCommerce versions, reproduction steps, impact, and a minimal example without real customer data. No response-time guarantee is implied.

The plugin's trust boundary is an authorized WordPress administrator choosing to connect their store. Connection actions require `manage_woocommerce` and a nonce. The destination service origin is fixed. The remote WISEPIM service separately authenticates the account and obtains WooCommerce authorization. The plugin must never treat a launch timestamp as proof of a successful connection.
