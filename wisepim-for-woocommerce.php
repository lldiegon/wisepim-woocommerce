<?php
/**
 * Plugin Name: WISEPIM for WooCommerce
 * Plugin URI: https://wisepim.com/connections/woocommerce
 * Description: Connect your WooCommerce catalog to WISEPIM to enrich product content, prepare translations, and manage catalog quality.
 * Version: 0.2.1
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: WISEPIM
 * Author URI: https://wisepim.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wisepim-for-woocommerce
 *
 * @package WISEPIM_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

define( 'WISEPIM_WOOCOMMERCE_VERSION', '0.2.1' );
define( 'WISEPIM_WOOCOMMERCE_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-wisepim-woocommerce-plugin.php';

add_action( 'before_woocommerce_init', array( 'WISEPIM_WooCommerce_Plugin', 'declare_compatibility' ) );
add_action( 'plugins_loaded', array( 'WISEPIM_WooCommerce_Plugin', 'init' ) );
