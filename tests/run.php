<?php
/** Focused behavior tests. WordPress integration is verified separately. */
define('ABSPATH', __DIR__ . '/');
define('WISEPIM_WOOCOMMERCE_VERSION', '0.1.0');
define('WISEPIM_WOOCOMMERCE_FILE', dirname(__DIR__) . '/wisepim-for-woocommerce.php');
$GLOBALS['home'] = 'https://shop.example.test/';
$GLOBALS['options'] = array('permalink_structure' => '/%postname%/');
$GLOBALS['capability'] = true;
$GLOBALS['nonce'] = true;
$GLOBALS['hooks'] = array();
function __( $text, $domain = '' ) { return $text; }
function esc_html__( $text, $domain = '' ) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function home_url( $path = '' ) { return $GLOBALS['home']; }
function untrailingslashit( $value ) { return rtrim($value, '/\\'); }
function wp_parse_url( $url ) { return parse_url($url); }
function get_option( $name, $default = false ) { return isset($GLOBALS['options'][$name]) ? $GLOBALS['options'][$name] : $default; }
function delete_option( $name ) { unset($GLOBALS['options'][$name]); }
function update_option( $name, $value, $autoload = null ) { $GLOBALS['options'][$name] = $value; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query($args, '', '&', PHP_QUERY_RFC3986); }
function current_user_can( $cap ) { return $GLOBALS['capability']; }
function wp_die( $message, $title = '', $args = array() ) { throw new RuntimeException($message, $args['response']); }
function check_admin_referer( $action ) {
    if ('wisepim_woocommerce_connect' !== $action || !$GLOBALS['nonce']) { throw new RuntimeException('Nonce failed', 403); }
}
function wp_redirect( $url, $status = 302, $by = '' ) { $GLOBALS['redirect'] = $url; throw new RuntimeException('redirect', $status); }
function add_action( $hook, $callback ) { $GLOBALS['hooks'][] = $hook; }
function add_filter( $hook, $callback ) { $GLOBALS['hooks'][] = $hook; }
function plugin_basename( $path ) { return basename($path); }
require dirname(__DIR__) . '/includes/class-wisepim-woocommerce-plugin.php';

$passed = 0;
function check( $truth, $message ) {
    global $passed;
    if (!$truth) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
    $passed++;
}
function action_status() {
    try { WISEPIM_WooCommerce_Plugin::handle_connect(); } catch (RuntimeException $e) { return $e->getCode(); }
    return 0;
}

WISEPIM_WooCommerce_Plugin::init();
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'initialization must not create a connection attempt');
check(in_array('admin_post_wisepim_woocommerce_connect', $GLOBALS['hooks'], true), 'authenticated action registered');
check(!in_array('admin_post_nopriv_wisepim_woocommerce_connect', $GLOBALS['hooks'], true), 'no unauthenticated action registered');
check(!WISEPIM_WooCommerce_Plugin::can_connect(), 'WooCommerce missing blocks connection');
eval('class WooCommerce {}');
check(WISEPIM_WooCommerce_Plugin::can_connect(), 'HTTPS WooCommerce with pretty permalinks can connect');

foreach (array('http://shop.example.test', 'https://user:secret@shop.example.test', 'https://shop.example.test/?x=1', 'https://shop.example.test/#fragment', 'not-a-url') as $invalid) {
    $GLOBALS['home'] = $invalid;
    check(!WISEPIM_WooCommerce_Plugin::can_connect(), 'invalid store address rejected: ' . $invalid);
}
$GLOBALS['home'] = 'https://shop.example.test/store/';
check(WISEPIM_WooCommerce_Plugin::store_url() === 'https://shop.example.test/store', 'subdirectory store URL preserved');
$GLOBALS['options']['permalink_structure'] = '';
check(!WISEPIM_WooCommerce_Plugin::can_connect(), 'plain permalinks block connection');
$GLOBALS['options']['permalink_structure'] = '/%postname%/';
$url = WISEPIM_WooCommerce_Plugin::connection_url();
$parts = parse_url($url);
parse_str($parts['query'], $query);
check($parts['scheme'] === 'https' && $parts['host'] === 'wisepim.com', 'fixed secure destination origin');
check($parts['path'] === '/integrations/woocommerce/install', 'known install route');
check($query['store_url'] === 'https://shop.example.test/store', 'store URL round trips as one parameter');
check($query['utm_source'] === 'wordpress' && $query['utm_medium'] === 'plugin', 'channel attribution');
check(!isset($query['consumer_key']) && !isset($query['consumer_secret']), 'no credentials in URL');

$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['capability'] = false;
check(action_status() === 403, 'unauthorized action denied');
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'unauthorized action does not write state');
$GLOBALS['capability'] = true;
$_SERVER['REQUEST_METHOD'] = 'GET';
check(action_status() === 405, 'GET does not initiate connection');
$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['nonce'] = false;
check(action_status() === 403, 'bad nonce denied');
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'bad nonce does not write state');
$GLOBALS['nonce'] = true;
$GLOBALS['home'] = 'http://shop.example.test';
check(action_status() === 400, 'invalid prerequisites reject connect action');
$GLOBALS['home'] = 'https://shop.example.test/store/';
$_POST['redirect_to'] = 'https://attacker.example';
check(action_status() === 303, 'authorized connection uses POST/redirect/GET');
check($GLOBALS['redirect'] === WISEPIM_WooCommerce_Plugin::connection_url(), 'caller cannot replace redirect destination');
check(is_int($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'only local attempt timestamp recorded');
check(count($GLOBALS['options']) === 2, 'no credentials or remote connection claim persisted');
$GLOBALS['options']['merchant_unrelated_option'] = 'preserve';
define('WP_UNINSTALL_PLUGIN', 'wisepim-for-woocommerce/wisepim-for-woocommerce.php');
require dirname(__DIR__) . '/uninstall.php';
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'uninstall removes local attempt timestamp');
check($GLOBALS['options']['merchant_unrelated_option'] === 'preserve', 'uninstall preserves unrelated merchant options');
check($GLOBALS['options']['permalink_structure'] === '/%postname%/', 'uninstall preserves site configuration');
fwrite(STDOUT, "Passed $passed behavior assertions.\n");
