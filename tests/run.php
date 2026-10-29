<?php
/** Focused behavior tests. WordPress integration is verified separately. */
define('ABSPATH', __DIR__ . '/');
$GLOBALS['home'] = 'https://shop.example.com/';
$GLOBALS['options'] = array('permalink_structure' => '/%postname%/');
$GLOBALS['capability'] = true;
$GLOBALS['nonce'] = true;
$GLOBALS['hooks'] = array();
$GLOBALS['multisite'] = false;
$GLOBALS['blog_id'] = 1;
$GLOBALS['blog_stack'] = array();
$GLOBALS['site_queries'] = array();
$GLOBALS['denied_caps'] = array();
$GLOBALS['count_calls'] = array();
$GLOBALS['count_throws'] = false;
$GLOBALS['count_values'] = array();
function __( $text, $domain = '' ) { return $text; }
function esc_html__( $text, $domain = '' ) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function home_url( $path = '' ) { return $GLOBALS['home']; }
function untrailingslashit( $value ) { return rtrim($value, '/\\'); }
function wp_parse_url( $url ) { return parse_url($url); }
function get_option( $name, $default = false ) { return isset($GLOBALS['options'][$name]) ? $GLOBALS['options'][$name] : $default; }
function delete_option( $name ) {
    if ($GLOBALS['multisite']) { unset($GLOBALS['site_options'][$GLOBALS['blog_id']][$name]); }
    else { unset($GLOBALS['options'][$name]); }
}
function is_multisite() { return $GLOBALS['multisite']; }
function get_sites( $args ) {
    $GLOBALS['site_queries'][] = $args;
    return array_slice(array_keys($GLOBALS['site_options']), $args['offset'], $args['number']);
}
function switch_to_blog( $id ) { $GLOBALS['blog_stack'][] = $GLOBALS['blog_id']; $GLOBALS['blog_id'] = $id; }
function restore_current_blog() { $GLOBALS['blog_id'] = array_pop($GLOBALS['blog_stack']); }
function update_option( $name, $value, $autoload = null ) { $GLOBALS['options'][$name] = $value; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query($args, '', '&', PHP_QUERY_RFC3986); }
function current_user_can( $cap ) {
    $GLOBALS['last_capability'] = $cap;
    return $GLOBALS['capability'] && !in_array($cap, $GLOBALS['denied_caps'], true);
}
function wp_count_posts( $type, $permission = '' ) {
    $GLOBALS['count_calls'][] = array($type, $permission, $GLOBALS['blog_id']);
    if ($GLOBALS['count_throws']) { throw new RuntimeException('Count provider unavailable'); }
    return $GLOBALS['count_values'][$GLOBALS['blog_id']][$type];
}
function wp_die( $message, $title = '', $args = array() ) { throw new RuntimeException($message, $args['response']); }
function check_admin_referer( $action ) {
    if ('wisepim_woocommerce_connect' !== $action || !$GLOBALS['nonce']) { throw new RuntimeException('Nonce failed', 403); }
}
function wp_redirect( $url, $status = 302, $by = '' ) { $GLOBALS['redirect'] = $url; throw new RuntimeException('redirect', $status); }
function add_action( $hook, $callback ) { $GLOBALS['hooks'][] = $hook; }
function add_filter( $hook, $callback ) { $GLOBALS['hooks'][] = $hook; }
function plugin_basename( $path ) { return basename($path); }
require dirname(__DIR__) . '/wisepim-for-woocommerce.php';

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

check(in_array('plugins_loaded', $GLOBALS['hooks'], true), 'real plugin bootstrap registers initialization');
check(in_array('before_woocommerce_init', $GLOBALS['hooks'], true), 'real bootstrap registers compatibility hook');
WISEPIM_WooCommerce_Plugin::declare_compatibility();
WISEPIM_WooCommerce_Plugin::init();
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'initialization must not create a connection attempt');
check(in_array('admin_post_wisepim_woocommerce_connect', $GLOBALS['hooks'], true), 'authenticated action registered');
check(!in_array('admin_post_nopriv_wisepim_woocommerce_connect', $GLOBALS['hooks'], true), 'no unauthenticated action registered');
check(!WISEPIM_WooCommerce_Plugin::can_connect(), 'WooCommerce missing blocks connection');
check(!WISEPIM_WooCommerce_Plugin::catalog_snapshot()['available'], 'missing WooCommerce hides catalog summary');
check(count($GLOBALS['count_calls']) === 0, 'missing WooCommerce does not query product counts');
$_SERVER['REQUEST_METHOD'] = 'POST';
check(action_status() === 400, 'connect action blocks missing WooCommerce without fatal error');
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'missing WooCommerce action does not record attempt');
eval('class WooCommerce {}');
check(WISEPIM_WooCommerce_Plugin::can_connect(), 'HTTPS WooCommerce with pretty permalinks can connect');

$GLOBALS['count_values'][1] = array(
    'product' => (object) array('publish' => '12', 'private' => 3, 'draft' => 4, 'pending' => 5, 'future' => 6, 'trash' => 900, 'auto-draft' => 800, 'custom' => 700),
    'product_variation' => (object) array('publish' => 5, 'private' => 2, 'trash' => 600),
);
$catalog = WISEPIM_WooCommerce_Plugin::catalog_snapshot();
check($catalog['available'] && $catalog['products'] === 30 && $catalog['variations'] === 7, 'snapshot totals catalog statuses and keeps variations separate');
check(!$catalog['empty'], 'nonempty catalog is identified');
check($GLOBALS['count_calls'] === array(array('product', 'readable', 1), array('product_variation', 'readable', 1)), 'snapshot requests two native readable aggregate counts only');
foreach (array('manage_woocommerce', 'edit_products', 'edit_others_products', 'edit_published_products', 'edit_private_products', 'read_private_products') as $denied_capability) {
    $GLOBALS['denied_caps'] = array($denied_capability);
    $before_queries = count($GLOBALS['count_calls']);
    $catalog = WISEPIM_WooCommerce_Plugin::catalog_snapshot();
    check(!$catalog['available'] && !$catalog['empty'], 'restricted capability hides catalog data: ' . $denied_capability);
    check(count($GLOBALS['count_calls']) === $before_queries, 'restricted role does not query counts');
}
$GLOBALS['denied_caps'] = array();
$GLOBALS['count_values'][1]['product'] = (object) array('publish' => 0, 'trash' => 42, 'auto-draft' => 1);
$GLOBALS['count_values'][1]['product_variation'] = (object) array('publish' => 0);
$catalog = WISEPIM_WooCommerce_Plugin::catalog_snapshot();
check($catalog['available'] && $catalog['empty'] && $catalog['products'] === 0, 'trash and unfinished drafts do not turn an empty store into an active catalog');
check(WISEPIM_WooCommerce_Plugin::can_connect(), 'empty catalog never prevents connection');
foreach (array((object) array(), array('publish' => 0), (object) array('publish' => -1), (object) array('publish' => 'unknown'), (object) array('publish' => 1.5), (object) array('publish' => PHP_INT_MAX, 'private' => 1)) as $malformed_count) {
    $GLOBALS['count_values'][1]['product'] = $malformed_count;
    check(!WISEPIM_WooCommerce_Plugin::catalog_snapshot()['available'], 'malformed native count hides optional summary');
}
$GLOBALS['count_throws'] = true;
check(!WISEPIM_WooCommerce_Plugin::catalog_snapshot()['available'], 'failed count provider does not take down setup');
check(WISEPIM_WooCommerce_Plugin::can_connect(), 'optional count failure does not prevent connection');
$GLOBALS['count_throws'] = false;
$GLOBALS['count_values'][2] = array('product' => (object) array('publish' => 9), 'product_variation' => (object) array('publish' => 10));
$GLOBALS['blog_id'] = 2;
$catalog = WISEPIM_WooCommerce_Plugin::catalog_snapshot();
check($catalog['products'] === 9 && $catalog['variations'] === 10, 'snapshot reads the current site instead of reusing another site total');
$GLOBALS['blog_id'] = 1;
$GLOBALS['count_values'][1] = array('product' => (object) array('publish' => 0), 'product_variation' => (object) array('publish' => 0));


$invalid_urls = array(
    'http://shop.example.com', 'https://user:secret@shop.example.com', 'https://shop.example.com/?x=1',
    'https://shop.example.com/#fragment', 'not-a-url', 'https://shop.example.com:8443',
    'https://localhost', 'https://store.local', 'https://store.internal', 'https://store.test',
    'https://127.0.0.1', 'https://10.0.0.1', 'https://192.168.1.1', 'https://169.254.169.254',
    'https://100.64.0.1', 'https://198.18.0.1', 'https://224.0.0.1', 'https://0.0.0.0',
    'https://2130706433', 'https://0177.0.0.1', 'https://0x7f000001', 'https://127.1',
    'https://[::1]', 'https://[::ffff:127.0.0.1]', 'https://[fe80::1]', 'https://[fc00::1]',
    'https://shop.example.com/../admin', 'https://shop.example.com/./admin',
    'https://shop.example.com/%2e%2e/admin', 'https://shop.example.com/%2fadmin',
    'https://shop.example.com/%5cadmin', "https://shop.example.com/\r\nHeader: bad",
    'https://shop.example.com/%0d%0aheader', 'https://shop.example.com/' . str_repeat('a', 1000),
);
foreach ($invalid_urls as $invalid) {
    $GLOBALS['home'] = $invalid;
    check(!WISEPIM_WooCommerce_Plugin::can_connect(), 'invalid store address rejected: ' . $invalid);
}
foreach (array('https://shop.example.com', 'https://shop.example.com:443/store', 'https://shop.example.com/store', 'https://8.8.8.8/store', 'https://[2606:4700:4700::1111]/store') as $valid) {
    check(WISEPIM_WooCommerce_Plugin::is_supported_store_url($valid), 'public HTTPS URL accepted: ' . $valid);
}
$GLOBALS['home'] = 'https://shop.example.com/store/';
check(WISEPIM_WooCommerce_Plugin::store_url() === 'https://shop.example.com/store', 'subdirectory store URL preserved');
$GLOBALS['options']['permalink_structure'] = '';
check(!WISEPIM_WooCommerce_Plugin::can_connect(), 'plain permalinks block connection');
$GLOBALS['options']['permalink_structure'] = '/%postname%/';
$url = WISEPIM_WooCommerce_Plugin::connection_url();
$parts = parse_url($url);
parse_str($parts['query'], $query);
check($parts['scheme'] === 'https' && $parts['host'] === 'wisepim.com', 'fixed secure destination origin');
check($parts['path'] === '/integrations/woocommerce/install', 'known install route');
check($query['store_url'] === 'https://shop.example.com/store', 'store URL round trips as one parameter');
check($query['utm_source'] === 'wordpress' && $query['utm_medium'] === 'plugin', 'channel attribution');
check(!isset($query['consumer_key']) && !isset($query['consumer_secret']), 'no credentials in URL');

$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['capability'] = false;
check(action_status() === 403, 'unauthorized action denied');
check($GLOBALS['last_capability'] === 'manage_woocommerce', 'connection checks precise WooCommerce management capability');
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'unauthorized action does not write state');
$GLOBALS['capability'] = true;
foreach (array('GET', 'HEAD', 'PUT', 'DELETE', 'OPTIONS') as $method) {
    $_SERVER['REQUEST_METHOD'] = $method;
    check(action_status() === 405, $method . ' does not initiate connection');
}
unset($_SERVER['REQUEST_METHOD']);
check(action_status() === 405, 'missing request method rejected');
$_SERVER['REQUEST_METHOD'] = 'POST';
$GLOBALS['nonce'] = false;
check(action_status() === 403, 'bad nonce denied');
check(!isset($GLOBALS['options'][WISEPIM_WooCommerce_Plugin::OPTION]), 'bad nonce does not write state');
$GLOBALS['nonce'] = true;
$GLOBALS['home'] = 'http://shop.example.com';
check(action_status() === 400, 'invalid prerequisites reject connect action');
check(strpos(WISEPIM_WooCommerce_Plugin::prerequisites()['https']['message'], 'uses HTTP') !== false, 'HTTP store gets a specific hosting and site-address correction');
$GLOBALS['home'] = 'https://shop.example.com/store/';
$_POST['redirect_to'] = 'https://attacker.example';
$_POST['store_url'] = 'https://attacker.example';
$_POST['consumer_key'] = 'fake-key';
$_POST['consumer_secret'] = 'fake-secret';
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
$GLOBALS['multisite'] = true;
$GLOBALS['site_options'] = array();
for ($site = 1; $site <= 205; $site++) {
    $GLOBALS['site_options'][$site] = array(WISEPIM_WooCommerce_Plugin::OPTION => 123, 'merchant_setting' => 'preserve');
}
require dirname(__DIR__) . '/uninstall.php';
check(count($GLOBALS['site_queries']) === 3, 'multisite uninstall uses bounded pagination');
foreach ($GLOBALS['site_queries'] as $query) {
    check($query['number'] === 100 && $query['fields'] === 'ids', 'multisite query fetches at most 100 IDs');
}
foreach ($GLOBALS['site_options'] as $site_options) {
    check(!isset($site_options[WISEPIM_WooCommerce_Plugin::OPTION]), 'multisite owned option removed');
    check($site_options['merchant_setting'] === 'preserve', 'multisite merchant options preserved');
}
check($GLOBALS['blog_id'] === 1 && count($GLOBALS['blog_stack']) === 0, 'multisite cleanup restores original blog context');
fwrite(STDOUT, "Passed $passed behavior assertions.\n");
