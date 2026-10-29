<?php
/**
 * Remove only this plugin's local setup metadata.
 *
 * WooCommerce credentials belong to the merchant's WISEPIM connection and are
 * intentionally not guessed at or removed. Revoke those in WooCommerce before
 * uninstalling when disconnecting the service is intended.
 *
 * @package WISEPIM_WooCommerce
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'wisepim_woocommerce_last_connection_attempt' );
