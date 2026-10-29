<?php
/**
 * Branded setup page. Variables are prepared by render_page().
 *
 * @package WISEPIM_WooCommerce
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap wisepim-admin">
	<header class="wisepim-hero">
		<div class="wisepim-topbar">
			<div class="wisepim-brand">
				<img src="<?php echo esc_url( plugins_url( 'assets/wisepim-logo.png', WISEPIM_WOOCOMMERCE_FILE ) ); ?>" alt="WISEPIM" width="180" height="60" />
				<span class="wisepim-platform"><?php esc_html_e( 'for WooCommerce', 'wisepim-for-woocommerce' ); ?></span>
			</div>
			<a class="wisepim-open-app" href="<?php echo esc_url( self::APP_URL . '/integrations' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open WISEPIM', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">↗</span></a>
		</div>
		<div class="wisepim-hero-copy">
			<p class="wisepim-eyebrow"><?php esc_html_e( 'PRODUCT CONTENT, MADE EASIER.', 'wisepim-for-woocommerce' ); ?></p>
			<h1><?php esc_html_e( 'Turn product data into better product pages.', 'wisepim-for-woocommerce' ); ?></h1>
			<p><?php esc_html_e( 'Connect your catalog to write descriptions, translate content, and manage product details in one place.', 'wisepim-for-woocommerce' ); ?></p>
		</div>
		<img class="wisepim-owl" src="<?php echo esc_url( plugins_url( 'assets/owl-connect.webp', WISEPIM_WOOCOMMERCE_FILE ) ); ?>" alt="" width="250" height="250" />
	</header>

	<main class="wisepim-content">
		<section class="wisepim-connect-card" aria-labelledby="wisepim-connect-heading">
			<div class="wisepim-connect-main">
				<h2 id="wisepim-connect-heading"><?php esc_html_e( 'Connect your WooCommerce catalog', 'wisepim-for-woocommerce' ); ?></h2>
				<p class="wisepim-description"><?php esc_html_e( 'Your store address is ready. Continue to sign in or create your WISEPIM account.', 'wisepim-for-woocommerce' ); ?></p>
				<div class="wisepim-store"><span class="dashicons dashicons-store" aria-hidden="true"></span><div><span class="wisepim-label"><?php esc_html_e( 'Your WooCommerce store', 'wisepim-for-woocommerce' ); ?></span><strong><?php echo esc_html( self::store_url() ); ?></strong></div></div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>" />
					<?php wp_nonce_field( self::ACTION ); ?>
					<button type="submit" class="button button-primary button-hero wisepim-connect-button" <?php disabled( ! $can_connect ); ?>><?php esc_html_e( 'Connect to WISEPIM', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">→</span></button>
					<?php if ( ! $can_connect ) : ?>
						<p class="wisepim-blocked" role="status"><?php esc_html_e( 'Complete the setup checks to continue.', 'wisepim-for-woocommerce' ); ?> <a href="#wisepim-checks-heading"><?php esc_html_e( 'Review setup checks', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">↓</span></a></p>
					<?php else : ?>
						<p class="wisepim-reassurance"><span class="dashicons dashicons-lock" aria-hidden="true"></span><?php esc_html_e( 'You approve access. Your products stay unchanged until you choose to update them.', 'wisepim-for-woocommerce' ); ?></p>
					<?php endif; ?>
					<p class="wisepim-consent"><?php esc_html_e( 'Continuing shares your store address, plugin version, and WordPress referral information with WISEPIM. An account is required; paid plans may apply.', 'wisepim-for-woocommerce' ); ?></p>
				</form>
				<div class="wisepim-legal-links">
					<a href="https://wisepim.com/pricing" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Plans', 'wisepim-for-woocommerce' ); ?></a>
					<a href="https://wisepim.com/privacy" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy', 'wisepim-for-woocommerce' ); ?></a>
					<a href="https://wisepim.com/terms" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms', 'wisepim-for-woocommerce' ); ?></a>
				</div>
			</div>
			<aside class="wisepim-readiness" aria-labelledby="wisepim-checks-heading">
				<span class="wisepim-readiness-symbol dashicons <?php echo $can_connect ? 'dashicons-shield-alt' : 'dashicons-admin-tools'; ?>" aria-hidden="true"></span>
				<h2 id="wisepim-checks-heading"><?php echo $can_connect ? esc_html__( 'Local checks passed', 'wisepim-for-woocommerce' ) : esc_html__( 'Finish your store setup', 'wisepim-for-woocommerce' ); ?></h2>
				<p><?php echo $can_connect ? esc_html__( 'Next, you will approve access in WooCommerce.', 'wisepim-for-woocommerce' ) : esc_html__( 'A few store settings need your attention.', 'wisepim-for-woocommerce' ); ?></p>
				<ul class="wisepim-checks">
					<?php foreach ( $checks as $wisepim_check_key => $wisepim_check ) : ?>
						<li class="<?php echo $wisepim_check['passed'] ? 'wisepim-check-pass' : 'wisepim-check-fail'; ?>">
							<span class="dashicons <?php echo $wisepim_check['passed'] ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>" aria-hidden="true"></span>
							<div><strong><?php echo esc_html( $wisepim_check['label'] ); ?></strong><span class="screen-reader-text"><?php echo $wisepim_check['passed'] ? esc_html__( 'Passed', 'wisepim-for-woocommerce' ) : esc_html__( 'Action needed', 'wisepim-for-woocommerce' ); ?></span>
								<?php if ( ! $wisepim_check['passed'] ) : ?>
									<p><?php echo esc_html( $wisepim_check['message'] ); ?></p>
									<?php if ( 'https' === $wisepim_check_key && current_user_can( 'manage_options' ) ) : ?>
										<a class="wisepim-settings-link" href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>"><?php esc_html_e( 'Open site address settings', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">→</span></a>
									<?php elseif ( 'permalinks' === $wisepim_check_key && current_user_can( 'manage_options' ) ) : ?>
										<a class="wisepim-settings-link" href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>"><?php esc_html_e( 'Open permalink settings', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">→</span></a>
									<?php elseif ( 'woocommerce' === $wisepim_check_key && current_user_can( 'activate_plugins' ) ) : ?>
										<a class="wisepim-settings-link" href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'Open installed plugins', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">→</span></a>
									<?php else : ?>
										<p><?php esc_html_e( 'Ask your WordPress administrator to update this setting.', 'wisepim-for-woocommerce' ); ?></p>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="wisepim-readiness-note"><?php esc_html_e( 'Authorization happens next. These checks do not confirm a connection to WISEPIM.', 'wisepim-for-woocommerce' ); ?></p>
			</aside>
		</section>


		<?php if ( $catalog_snapshot['available'] ) : ?>
			<section class="wisepim-catalog" aria-labelledby="wisepim-catalog-heading">
				<div class="wisepim-catalog-copy">
					<h2 id="wisepim-catalog-heading"><?php esc_html_e( 'Your WooCommerce catalog', 'wisepim-for-woocommerce' ); ?></h2>
					<?php if ( $catalog_snapshot['empty'] ) : ?>
						<p><?php esc_html_e( 'No products yet? You can connect now and add products later.', 'wisepim-for-woocommerce' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>"><?php esc_html_e( 'Add your first product', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">→</span></a>
					<?php else : ?>
						<p><?php esc_html_e( 'Counts from WooCommerce. Check WISEPIM for import status.', 'wisepim-for-woocommerce' ); ?></p>
					<?php endif; ?>
				</div>
				<dl class="wisepim-catalog-counts">
					<div><dt><?php esc_html_e( 'Products', 'wisepim-for-woocommerce' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $catalog_snapshot['products'] ) ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Variations', 'wisepim-for-woocommerce' ); ?></dt><dd><?php echo esc_html( number_format_i18n( $catalog_snapshot['variations'] ) ); ?></dd></div>
				</dl>
			</section>
		<?php endif; ?>

		<section class="wisepim-next" aria-labelledby="wisepim-next-heading">
			<h2 id="wisepim-next-heading"><?php esc_html_e( 'What happens next', 'wisepim-for-woocommerce' ); ?></h2>
			<ol class="wisepim-steps">
				<li><span class="wisepim-step-number" aria-hidden="true">01</span><div><h3><?php esc_html_e( 'Choose your workspace', 'wisepim-for-woocommerce' ); ?></h3><p><?php esc_html_e( 'Sign in or create an account, then confirm the project for your store.', 'wisepim-for-woocommerce' ); ?></p></div></li>
				<li><span class="wisepim-step-number" aria-hidden="true">02</span><div><h3><?php esc_html_e( 'Approve the connection', 'wisepim-for-woocommerce' ); ?></h3><p><?php esc_html_e( 'WooCommerce asks for your permission. No API keys to copy or paste.', 'wisepim-for-woocommerce' ); ?></p></div></li>
				<li><span class="wisepim-step-number" aria-hidden="true">03</span><div><h3><?php esc_html_e( 'Bring in your products', 'wisepim-for-woocommerce' ); ?></h3><p><?php esc_html_e( 'Choose what to import and start improving your catalog in WISEPIM.', 'wisepim-for-woocommerce' ); ?></p></div></li>
			</ol>
		</section>

		<section class="wisepim-help" aria-labelledby="wisepim-help-heading">
			<div class="wisepim-help-title"><h2 id="wisepim-help-heading"><?php esc_html_e( 'Questions before you connect?', 'wisepim-for-woocommerce' ); ?></h2><div class="wisepim-help-links"><a href="https://docs.wisepim.com/en/integrations/woocommerce" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the documentation', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">↗</span></a><a href="https://wisepim.com/contact" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Talk to our team', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">↗</span></a></div></div>
			<details><summary><?php esc_html_e( 'I have already connected my store', 'wisepim-for-woocommerce' ); ?></summary><div class="wisepim-detail-content"><p><?php esc_html_e( 'Open WISEPIM to check your connection, manage imports, or reconnect an existing store. If setup was interrupted, you can start again with the Connect button above.', 'wisepim-for-woocommerce' ); ?></p>
				<?php if ( $last_attempt ) : ?>
					<p class="wisepim-muted">
						<?php
						/* translators: %s: local date and time when setup was last launched. */
						echo esc_html( sprintf( __( 'Setup last opened: %s. This does not confirm a completed connection.', 'wisepim-for-woocommerce' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_attempt ) ) );
						?>
					</p>
				<?php endif; ?>
				<a href="<?php echo esc_url( self::APP_URL . '/integrations' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open WISEPIM', 'wisepim-for-woocommerce' ); ?> <span aria-hidden="true">↗</span></a></div></details>
			<details><summary><?php esc_html_e( 'What access am I giving WISEPIM?', 'wisepim-for-woocommerce' ); ?></summary><div class="wisepim-detail-content"><p><?php esc_html_e( 'The next step asks you to approve read and write access to the WooCommerce REST API. WooCommerce sends the generated credentials directly to WISEPIM. Connecting alone does not change your products. You decide what to import and which updates to send back.', 'wisepim-for-woocommerce' ); ?></p></div></details>
			<details><summary><?php esc_html_e( 'Setup is not completing', 'wisepim-for-woocommerce' ); ?></summary><div class="wisepim-detail-content"><p><?php esc_html_e( 'Check that your store is publicly accessible over HTTPS and that pretty permalinks are enabled. Hosting firewalls and security plugins may block WooCommerce authorization or API access even when the local checks pass. Your hosting provider or our support team can help.', 'wisepim-for-woocommerce' ); ?></p></div></details>
			<details><summary><?php esc_html_e( 'How do I disconnect or uninstall?', 'wisepim-for-woocommerce' ); ?></summary><div class="wisepim-detail-content"><p><?php esc_html_e( 'To stop access, revoke the WISEPIM API key in WooCommerce. Deactivating or deleting this plugin does not revoke existing API keys or delete data already imported into WISEPIM.', 'wisepim-for-woocommerce' ); ?></p>
				<?php if ( self::woocommerce_active() ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=keys' ) ); ?>"><?php esc_html_e( 'Manage WooCommerce API keys', 'wisepim-for-woocommerce' ); ?></a>
				<?php endif; ?>
			</div></details>
		</section>
		<p class="wisepim-footer">
			<?php
			/* translators: %s: plugin version. */
			echo esc_html( sprintf( __( 'WISEPIM for WooCommerce · Version %s', 'wisepim-for-woocommerce' ), WISEPIM_WOOCOMMERCE_VERSION ) );
			?>
		</p>
	</main>
</div>
