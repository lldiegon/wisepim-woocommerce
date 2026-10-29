<?php
/**
 * WordPress-side setup and local diagnostics. Catalog processing stays in WISEPIM.
 *
 * @package WISEPIM_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/** Connects the merchant to WISEPIM without storing credentials in WordPress. */
final class WISEPIM_WooCommerce_Plugin {

	const CAPABILITY = 'manage_woocommerce';
	const PAGE_SLUG  = 'wisepim-for-woocommerce';
	const ACTION     = 'wisepim_woocommerce_connect';
	const APP_URL    = 'https://wisepim.com';
	const OPTION     = 'wisepim_woocommerce_last_connection_attempt';

	/** No order storage or checkout code is touched by this connector. */
	public static function declare_compatibility() {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WISEPIM_WOOCOMMERCE_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WISEPIM_WOOCOMMERCE_FILE, true );
		}
	}

	/** Register hooks without contacting WISEPIM or changing the catalog. */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle_connect' ) );
		add_action( 'admin_notices', array( __CLASS__, 'dependency_notice' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WISEPIM_WOOCOMMERCE_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/** Whether WooCommerce is loaded on the current site. */
	public static function woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/** The canonical storefront base includes installations in subdirectories. */
	public static function store_url() {
		return untrailingslashit( home_url( '/' ) );
	}

	/** Fixed destination: no caller-supplied redirect or API credentials. */
	public static function connection_url() {
		return add_query_arg(
			array(
				'store_url'      => self::store_url(),
				'utm_source'     => 'wordpress',
				'utm_medium'     => 'plugin',
				'utm_campaign'   => 'woocommerce-connect',
				'plugin_version' => WISEPIM_WOOCOMMERCE_VERSION,
			),
			self::APP_URL . '/integrations/woocommerce/install'
		);
	}

	/** Local checks only: these do not claim the remote API is reachable. */
	public static function prerequisites() {
		$parts  = wp_parse_url( self::store_url() );
		$secure = is_array( $parts ) && isset( $parts['scheme'], $parts['host'] )
			&& 'https' === strtolower( $parts['scheme'] )
			&& ! isset( $parts['user'] ) && ! isset( $parts['pass'] )
			&& ! isset( $parts['query'] ) && ! isset( $parts['fragment'] );
		return array(
			'woocommerce' => array(
				'label'   => __( 'WooCommerce is active', 'wisepim-for-woocommerce' ),
				'passed'  => self::woocommerce_active(),
				'message' => __( 'Install and activate WooCommerce to connect your product catalog.', 'wisepim-for-woocommerce' ),
			),
			'https'       => array(
				'label'   => __( 'Store uses HTTPS', 'wisepim-for-woocommerce' ),
				'passed'  => $secure,
				'message' => __( 'Configure a valid HTTPS site address in WordPress Settings → General before connecting.', 'wisepim-for-woocommerce' ),
			),
			'permalinks'  => array(
				'label'   => __( 'Pretty permalinks are enabled', 'wisepim-for-woocommerce' ),
				'passed'  => '' !== (string) get_option( 'permalink_structure', '' ),
				'message' => __( 'Choose a permalink structure other than Plain in Settings → Permalinks so the WooCommerce API is available.', 'wisepim-for-woocommerce' ),
			),
		);
	}

	/** Whether every local prerequisite is met. */
	public static function can_connect() {
		foreach ( self::prerequisites() as $check ) {
			if ( ! $check['passed'] ) {
				return false;
			}
		}
		return true;
	}

	/** Register the settings page, including a fallback for inactive WooCommerce. */
	public static function admin_menu() {
		add_submenu_page(
			self::woocommerce_active() ? 'woocommerce' : 'options-general.php',
			__( 'WISEPIM', 'wisepim-for-woocommerce' ),
			__( 'WISEPIM', 'wisepim-for-woocommerce' ),
			self::woocommerce_active() ? self::CAPABILITY : 'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Load styles on this plugin's settings page only.
	 *
	 * @param string $hook_suffix Current admin screen hook.
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( 'woocommerce_page_' . self::PAGE_SLUG !== $hook_suffix && 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'wisepim-for-woocommerce-admin', plugins_url( 'assets/admin.css', WISEPIM_WOOCOMMERCE_FILE ), array(), WISEPIM_WOOCOMMERCE_VERSION );
	}

	/**
	 * Add a setup shortcut in the Plugins list.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( ( self::woocommerce_active() ? 'admin.php' : 'options-general.php' ) . '?page=' . self::PAGE_SLUG );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Set up WISEPIM', 'wisepim-for-woocommerce' ) . '</a>' );
		return $links;
	}

	/** Explain the missing dependency without sending any data. */
	public static function dependency_notice() {
		if ( self::woocommerce_active() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'WISEPIM for WooCommerce needs WooCommerce to be installed and activated. No store data has been sent.', 'wisepim-for-woocommerce' ) . '</p></div>';
	}

	/** Explicit, authorized action is required before leaving WordPress. */
	public static function handle_connect() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to connect this store.', 'wisepim-for-woocommerce' ), '', array( 'response' => 403 ) );
		}
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			wp_die( esc_html__( 'Use the Connect to WISEPIM button to start setup.', 'wisepim-for-woocommerce' ), '', array( 'response' => 405 ) );
		}
		check_admin_referer( self::ACTION );
		if ( ! self::can_connect() ) {
			wp_die( esc_html__( 'Resolve the setup checks on the WISEPIM settings page before connecting.', 'wisepim-for-woocommerce' ), '', array( 'response' => 400 ) );
		}
		update_option( self::OPTION, time(), false );
		// Intentional external redirect to the fixed WISEPIM origin, never to user input.
		wp_redirect( self::connection_url(), 303, 'WISEPIM for WooCommerce' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/** Render the setup page for authorized store managers only. */
	public static function render_page() {
		$capability = self::woocommerce_active() ? self::CAPABILITY : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'wisepim-for-woocommerce' ), '', array( 'response' => 403 ) );
		}
		$checks       = self::prerequisites();
		$can_connect  = self::can_connect();
		$last_attempt = absint( get_option( self::OPTION, 0 ) );
		require __DIR__ . '/view-admin.php';
	}
}
