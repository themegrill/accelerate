<?php
/**
 * Accelerate Admin Class.
 *
 * @author  ThemeGrill
 * @package Accelerate
 * @since   Accelerate 1.2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Accelerate_admin' ) ) :

	/**
	 * Accelerate_admin Class.
	 */
	class Accelerate_admin {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Localize array for import button AJAX request.
		 */
		public function enqueue_scripts() {
			wp_enqueue_style( 'accelerate-admin-style', get_template_directory_uri() . '/inc/admin/css/admin.css', array(), ACCELERATE_THEME_VERSION );

			wp_enqueue_script( 'accelerate-plugin-install-helper', get_template_directory_uri() . '/inc/admin/js/plugin-handle.js', array( 'jquery' ), ACCELERATE_THEME_VERSION, true );

			$welcome_data = array(
				'uri'      => esc_url( admin_url( '/themes.php?page=demo-importer&browse=all&accelerate-hide-notice=welcome' ) ),
				'btn_text' => esc_html__( 'Processing...', 'accelerate' ),
				'nonce'    => wp_create_nonce( 'accelerate_demo_import_nonce' ),
			);

			wp_localize_script( 'accelerate-plugin-install-helper', 'accelerateRedirectDemoPage', $welcome_data );
		}

		/**
		 * Show welcome notice.
		 */
		public function welcome_notice() {
			?>
			<div id="message" class="updated accelerate-message">
				<a class="accelerate-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( remove_query_arg( array( 'activated' ), add_query_arg( 'accelerate-hide-notice', 'welcome' ) ), 'accelerate_hide_notices_nonce', '_accelerate_notice_nonce' ) ); ?>">
					<?php esc_html_e( 'Dismiss', 'accelerate' ); ?>
				</a>

				<div class="accelerate-message-wrapper">
					<div class="accelerate-logo">
						<img src="<?php echo esc_url ( get_template_directory_uri() ); ?>/img/accelerate-logo.png" alt="<?php esc_attr_e( 'Accelerate', 'accelerate' ); ?>" /><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped, Squiz.PHP.EmbeddedPhp.SpacingBeforeClose ?>
					</div>

					<p>
						<?php printf( esc_html__( 'Welcome! Thank you for choosing Accelerate! To fully take advantage of the best our theme can offer please make sure you visit our %swelcome page%s.', 'accelerate' ), '<a href="' . esc_url( admin_url( 'themes.php?page=accelerate-options' ) ) . '">', '</a>' ); ?>

						<span class="plugin-install-notice"><?php esc_html_e( 'Clicking the button below will install and activate the ThemeGrill demo importer plugin.', 'accelerate' ); ?></span>
					</p>

					<div class="submit">
						<a class="btn-get-started button button-primary button-hero" href="#" data-name="" data-slug="" aria-label="<?php esc_attr_e( 'Get started with Accelerate', 'accelerate' ); ?>"><?php esc_html_e( 'Get started with Accelerate', 'accelerate' ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}
	}

endif;

return new Accelerate_admin();
