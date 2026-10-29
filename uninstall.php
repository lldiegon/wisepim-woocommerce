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

if ( is_multisite() ) {
	// Site-local attempts can exist even when the plugin was not network activated.
	$wisepim_site_offset = 0;
	do {
		$wisepim_site_ids = get_sites(
			array(
				'fields'  => 'ids',
				'number'  => 100,
				'offset'  => $wisepim_site_offset,
				'orderby' => 'id',
				'order'   => 'ASC',
			)
		);
		foreach ( $wisepim_site_ids as $wisepim_site_id ) {
			switch_to_blog( $wisepim_site_id );
			delete_option( 'wisepim_woocommerce_last_connection_attempt' );
			restore_current_blog();
		}
		$wisepim_site_offset += 100;
		$wisepim_page_count   = count( $wisepim_site_ids );
	} while ( 100 === $wisepim_page_count );
} else {
	delete_option( 'wisepim_woocommerce_last_connection_attempt' );
}
