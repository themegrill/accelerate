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

	}

endif;

return new Accelerate_admin();
