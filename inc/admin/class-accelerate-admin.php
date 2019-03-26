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
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
			add_action( 'wp_loaded', array( $this, 'admin_notice' ) );
			add_action( 'wp_ajax_import_button', array( $this, 'accelerate_ajax_import_button_handler' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'accelerate_ajax_enqueue_scripts' ) );
		}

		/**
		 * Localize array for import button AJAX request.
		 */
		public function accelerate_ajax_enqueue_scripts() {

			wp_enqueue_script( 'updates' );
			wp_enqueue_script( 'accelerate-plugin-install-helper', get_template_directory_uri() . '/inc/admin/js/plugin-handle.js', array( 'jquery' ), 1, true );
			wp_localize_script(
				'accelerate-plugin-install-helper', 'accelerate_plugin_helper',
				array(
					'activating' => esc_html__( 'Activating ', 'accelerate' ),
				)
			);

			$translation_array = array(
				'uri'      => esc_url( admin_url( '/themes.php?page=demo-importer&browse=all&accelerate-hide-notice=welcome' ) ),
				'btn_text' => esc_html__( 'Processing...', 'accelerate' ),
				'nonce'    => wp_create_nonce( 'accelerate_demo_import_nonce' ),
			);

			wp_localize_script( 'accelerate-plugin-install-helper', 'accelerate_redirect_demo_page', $translation_array );

		}

		/**
		 * Handle the AJAX process while import or get started button clicked.
		 */
		public function accelerate_ajax_import_button_handler() {

			check_ajax_referer( 'accelerate_demo_import_nonce', 'security' );

			$state = '';
			if ( is_plugin_active( 'themegrill-demo-importer/themegrill-demo-importer.php' ) ) {
				$state = 'activated';
			} elseif ( file_exists( WP_PLUGIN_DIR . '/themegrill-demo-importer/themegrill-demo-importer.php' ) ) {
				$state = 'installed';
			}

			if ( 'activated' === $state ) {
				$response['redirect'] = admin_url( '/themes.php?page=demo-importer&browse=all&accelerate-hide-notice=welcome' );
			} elseif ( 'installed' === $state ) {
				$response['redirect'] = admin_url( '/themes.php?page=demo-importer&browse=all&accelerate-hide-notice=welcome' );
				if ( current_user_can( 'activate_plugin' ) ) {
					$result = activate_plugin( 'themegrill-demo-importer/themegrill-demo-importer.php' );

					if ( is_wp_error( $result ) ) {
						$response['errorCode']    = $result->get_error_code();
						$response['errorMessage'] = $result->get_error_message();
					}
				}
			} else {
				wp_enqueue_script( 'plugin-install' );

				$response['redirect'] = admin_url( '/themes.php?page=demo-importer&browse=all&accelerate-hide-notice=welcome' );

				/**
				 * Install Plugin.
				 */
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

				$api = plugins_api( 'plugin_information', array(
					'slug'   => sanitize_key( wp_unslash( 'themegrill-demo-importer' ) ),
					'fields' => array(
						'sections' => false,
					),
				) );

				$skin     = new WP_Ajax_Upgrader_Skin();
				$upgrader = new Plugin_Upgrader( $skin );
				$result   = $upgrader->install( $api->download_link );
				if ( $result ) {
					$response['installed'] = 'succeed';
				} else {
					$response['installed'] = 'failed';
				}

				// Activate plugin.
				if ( current_user_can( 'activate_plugin' ) ) {
					$result = activate_plugin( 'themegrill-demo-importer/themegrill-demo-importer.php' );

					if ( is_wp_error( $result ) ) {
						$response['errorCode']    = $result->get_error_code();
						$response['errorMessage'] = $result->get_error_message();
					}
				}
			}

			wp_send_json( $response );

			exit();

		}

		/**
		 * Add admin menu.
		 */
		public function admin_menu() {
			$theme = wp_get_theme( get_template() );

			$page = add_theme_page( esc_html__( 'About', 'accelerate' ) . ' ' . $theme->display( 'Name' ), esc_html__( 'About', 'accelerate' ) . ' ' . $theme->display( 'Name' ), 'activate_plugins', 'accelerate-sitelibrary', array(
				$this,
				'sitelibrary_screen',
			) );
			add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_styles' ) );
		}

		/**
		 * Enqueue styles.
		 */
		public function enqueue_styles() {
			global $accelerate_version;

			wp_enqueue_style( 'accelerate-welcome', get_template_directory_uri() . '/css/admin/welcome.css', array(), $accelerate_version );
		}

		/**
		 * Add admin notice.
		 */
		public function admin_notice() {
			global $accelerate_version, $pagenow;

			wp_enqueue_style( 'accelerate-message', get_template_directory_uri() . '/css/admin/message.css', array(), $accelerate_version );

			// Let's bail on theme activation.
			$notice_nag = get_option( 'accelerate_admin_notice_welcome' );
			if ( ! $notice_nag ) {
				add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
			}
		}

		/**
		 * Hide a notice if the GET variable is set.
		 */
		public static function hide_notices() {
			if ( isset( $_GET['accelerate-hide-notice'] ) && isset( $_GET['_accelerate_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_accelerate_notice_nonce'], 'accelerate_hide_notices_nonce' ) ) {
					wp_die( __( 'Action failed. Please refresh the page and retry.', 'accelerate' ) );
				}

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( __( 'Cheatin&#8217; huh?', 'accelerate' ) );
				}

				$hide_notice = sanitize_text_field( $_GET['accelerate-hide-notice'] );
				update_option( 'accelerate_admin_notice_' . $hide_notice, 1 );

				// Hide.
				if ( 'welcome' === $_GET['accelerate-hide-notice'] ) {
					update_option( 'accelerate_admin_notice_' . $hide_notice, 1 );
				} else { // Show.
					delete_option( 'accelerate_admin_notice_' . $hide_notice );
				}
			}
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
						<img src="<?php echo get_template_directory_uri(); ?>/img/accelerate-logo.png" alt="<?php esc_html_e( 'Accelerate', 'accelerate' ); ?>" /><?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped, Squiz.PHP.EmbeddedPhp.SpacingBeforeClose ?>
					</div>

					<p>
						<?php printf( esc_html__( 'Welcome! Thank you for choosing Accelerate! To fully take advantage of the best our theme can offer please make sure you visit our %swelcome page%s.', 'accelerate' ), '<a href="' . esc_url( admin_url( 'themes.php?page=accelerate-welcome' ) ) . '">', '</a>' ); ?>
					</p>

					<div class="submit">
						<a class="btn-get-started button button-primary button-hero" href="#" data-name="" data-slug="" aria-label="<?php esc_html_e( 'Get started with Accelerate', 'accelerate' ); ?>"><?php esc_html_e( 'Get started with Accelerate', 'accelerate' ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Intro text/links shown to all about pages.
		 *
		 * @access private
		 */
		private function intro() {
			global $accelerate_version;

			$theme = wp_get_theme( get_template() );
			?>
			<div class="header">
				<div class="info">
					<h1>
						<?php esc_html_e( 'About', 'accelerate' ); ?>
						<?php echo $theme->display( 'Name' ); ?>
						<span class="version-container"><?php echo esc_html( $accelerate_version ); ?></span>
					</h1>

					<div class="tg-about-text about-text">
						<?php echo $theme->display( 'Description' ); ?>
					</div>

					<a href="https://themegrill.com/" target="_blank" class="wp-badge tg-welcome-logo"></a>
				</div>
			</div>

			<p class="accelerate-actions">
				<a href="<?php echo esc_url( 'https://themegrill.com/themes/accelerate/?utm_source=accelerate-about&utm_medium=theme-info-link&utm_campaign=theme-info' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'Theme Info', 'accelerate' ); ?></a>

				<a href="<?php echo esc_url( 'https://demo.themegrill.com/accelerate/' ); ?>" class="button button-secondary docs" target="_blank"><?php esc_html_e( 'View Demo', 'accelerate' ); ?></a>

				<a href="<?php echo esc_url( 'https://themegrill.com/themes/accelerate/?utm_source=accelerate-about&utm_medium=view-pro-link&utm_campaign=view-pro#free-vs-pro' ); ?>" class="button button-primary docs" target="_blank"><?php esc_html_e( 'View PRO version', 'accelerate' ); ?></a>

				<a href="<?php echo esc_url( 'https://wordpress.org/support/theme/accelerate/reviews/?filter=5' ); ?>" class="button button-secondary docs" target="_blank"><?php esc_html_e( 'Rate this theme', 'accelerate' ); ?></a>
			</p>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( empty( $_GET['tab'] ) && $_GET['page'] == 'accelerate-sitelibrary' ) {
					echo 'nav-tab-active';
				} ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'accelerate-sitelibrary' ), 'themes.php' ) ) );
				?>">
					<?php esc_html_e( 'Site Library', 'accelerate' ); ?>
				</a>
				<a class="nav-tab <?php if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'welcome' ) {
					echo 'nav-tab-active';
				} ?>" href="<?php echo esc_url( admin_url( add_query_arg( array(
					'page' => 'accelerate-sitelibrary',
					'tab'  => 'welcome',
				), 'themes.php' ) ) ); ?>">
					<?php esc_html_e( 'Getting Started', 'accelerate' ); ?>
				</a>
				<a class="nav-tab <?php if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'supported_plugins' ) {
					echo 'nav-tab-active';
				} ?>" href="<?php echo esc_url( admin_url( add_query_arg( array(
					'page' => 'accelerate-sitelibrary',
					'tab'  => 'supported_plugins',
				), 'themes.php' ) ) ); ?>">
					<?php esc_html_e( 'Recommended Plugins', 'accelerate' ); ?>
				</a>
				<a class="nav-tab <?php if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'free_vs_pro' ) {
					echo 'nav-tab-active';
				} ?>" href="<?php echo esc_url( admin_url( add_query_arg( array(
					'page' => 'accelerate-sitelibrary',
					'tab'  => 'free_vs_pro',
				), 'themes.php' ) ) ); ?>">
					<?php esc_html_e( 'Free Vs Pro', 'accelerate' ); ?>
				</a>
				<a class="nav-tab <?php if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'changelog' ) {
					echo 'nav-tab-active';
				} ?>" href="<?php echo esc_url( admin_url( add_query_arg( array(
					'page' => 'accelerate-sitelibrary',
					'tab'  => 'changelog',
				), 'themes.php' ) ) ); ?>">
					<?php esc_html_e( 'Changelog', 'accelerate' ); ?>
				</a>
			</h2>
			<?php
		}

		/**
		 * Sitelibrary screen page.
		 */
		public function sitelibrary_screen() {
			$current_tab = empty( $_GET['tab'] ) ? 'library' : sanitize_title( $_GET['tab'] );

			// Look for a {$current_tab}_screen method.
			if ( is_callable( array( $this, $current_tab . '_screen' ) ) ) {
				return $this->{$current_tab . '_screen'}();
			}

			// Fallback to about screen.
			return $this->sitelibrary_display_screen();
		}

		/**
		 * Render site library.
		 */
		public function sitelibrary_display_screen() {
			?>
			<div class="wrap about-wrap">
				<?php
				$this->intro();

				// Display site library.
				echo Accelerate_Site_Library::accelerate_site_library_page_content();
				?>
			</div>
			<?php
		}

		/**
		 * Welcome screen page.
		 */
		public function welcome_screen() {
			$this->about_screen();
		}

		/**
		 * Output the about screen.
		 */
		public function about_screen() {
			$theme = wp_get_theme( get_template() );
			?>
			<div class="wrap about-wrap">

				<?php $this->intro(); ?>

				<div class="changelog point-releases">
					<div class="under-the-hood two-col">
						<div class="col">
							<h3><?php esc_html_e( 'Import Demo', 'accelerate' ); ?></h3>
							<p><?php esc_html_e( 'Needs ThemeGrill Demo Importer plugin.', 'accelerate' ) ?></p>

							<div class="submit">
								<a class="btn-get-started button button-primary button-hero" href="#" data-name="" data-slug="" aria-label="<?php esc_html_e( 'Import', 'accelerate' ); ?>"><?php esc_html_e( 'Import', 'accelerate' ); ?></a>
							</div>
						</div>

						<div class="col">
							<h3><?php esc_html_e( 'Theme Customizer', 'accelerate' ); ?></h3>
							<p><?php esc_html_e( 'All Theme Options are available via Customize screen.', 'accelerate' ) ?></p>
							<p>
								<a href="<?php echo admin_url( 'customize.php' ); ?>" class="button button-secondary"><?php esc_html_e( 'Customize', 'accelerate' ); ?></a>
							</p>
						</div>

						<div class="col">
							<h3><?php esc_html_e( 'Documentation', 'accelerate' ); ?></h3>
							<p><?php esc_html_e( 'Please view our documentation page to setup the theme.', 'accelerate' ) ?></p>
							<p>
								<a href="<?php echo esc_url( 'https://docs.themegrill.com/accelerate/?utm_source=accelerate-about&utm_medium=documentation-link&utm_campaign=documentation' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'Documentation', 'accelerate' ); ?></a>
							</p>
						</div>

						<div class="col">
							<h3><?php esc_html_e( 'Got theme support question?', 'accelerate' ); ?></h3>
							<p><?php esc_html_e( 'Please put it in our dedicated support forum.', 'accelerate' ) ?></p>
							<p>
								<a href="<?php echo esc_url( 'https://themegrill.com/support-forum/?utm_source=accelerate-about&utm_medium=support-forum-link&utm_campaign=support-forum' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'Support Forum', 'accelerate' ); ?></a>
							</p>
						</div>

						<div class="col">
							<h3><?php esc_html_e( 'Need more features?', 'accelerate' ); ?></h3>
							<p><?php esc_html_e( 'Upgrade to PRO version for more exciting features.', 'accelerate' ) ?></p>
							<p>
								<a href="<?php echo esc_url( 'https://themegrill.com/themes/accelerate/?utm_source=accelerate-about&utm_medium=view-pro-link&utm_campaign=view-pro#free-vs-pro' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'View Pro', 'accelerate' ); ?></a>
							</p>
						</div>

						<div class="col">
							<h3><?php esc_html_e( 'Got sales related question?', 'accelerate' ); ?></h3>
							<p><?php esc_html_e( 'Please send it via our sales contact page.', 'accelerate' ) ?></p>
							<p>
								<a href="<?php echo esc_url( 'https://themegrill.com/contact/?utm_source=accelerate-about&utm_medium=contact-page-link&utm_campaign=contact-page' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'Contact Page', 'accelerate' ); ?></a>
							</p>
						</div>

						<div class="col">
							<h3>
								<?php
								esc_html_e( 'Translate', 'accelerate' );
								echo ' ' . $theme->display( 'Name' );
								?>
							</h3>
							<p><?php esc_html_e( 'Click below to translate this theme into your own language.', 'accelerate' ) ?></p>
							<p>
								<a href="<?php echo esc_url( 'http://translate.wordpress.org/projects/wp-themes/accelerate' ); ?>" class="button button-secondary">
									<?php
									esc_html_e( 'Translate', 'accelerate' );
									echo ' ' . $theme->display( 'Name' );
									?>
								</a>
							</p>
						</div>
					</div>
				</div>

				<div class="return-to-dashboard accelerate">
					<?php if ( current_user_can( 'update_core' ) && isset( $_GET['updated'] ) ) : ?>
						<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>">
							<?php is_multisite() ? esc_html_e( 'Return to Updates', 'accelerate' ) : esc_html_e( 'Return to Dashboard &rarr; Updates', 'accelerate' ); ?>
						</a> |
					<?php endif; ?>
					<a href="<?php echo esc_url( self_admin_url() ); ?>"><?php is_blog_admin() ? esc_html_e( 'Go to Dashboard &rarr; Home', 'accelerate' ) : esc_html_e( 'Go to Dashboard', 'accelerate' ); ?></a>
				</div>
			</div>
			<?php
		}

		/**
		 * Output the changelog screen.
		 */
		public function changelog_screen() {
			global $wp_filesystem;

			?>
			<div class="wrap about-wrap">

				<?php $this->intro(); ?>

				<p class="about-description"><?php esc_html_e( 'View changelog below:', 'accelerate' ); ?></p>

				<?php
				$changelog_file = apply_filters( 'accelerate_changelog_file', get_template_directory() . '/readme.txt' );

				// Check if the changelog file exists and is readable.
				if ( $changelog_file && is_readable( $changelog_file ) ) {
					WP_Filesystem();
					$changelog      = $wp_filesystem->get_contents( $changelog_file );
					$changelog_list = $this->parse_changelog( $changelog );

					echo wp_kses_post( $changelog_list );
				}
				?>
			</div>
			<?php
		}

		/**
		 * Parse changelog from readme file.
		 *
		 * @param  string $content
		 *
		 * @return string
		 */
		private function parse_changelog( $content ) {
			$matches   = null;
			$regexp    = '~==\s*Changelog\s*==(.*)($)~Uis';
			$changelog = '';

			if ( preg_match( $regexp, $content, $matches ) ) {
				$changes = explode( '\r\n', trim( $matches[1] ) );

				$changelog .= '<pre class="changelog">';

				foreach ( $changes as $index => $line ) {
					$changelog .= wp_kses_post( preg_replace( '~(=\s*Version\s*(\d+(?:\.\d+)+)\s*=|$)~Uis', '<span class="title">${1}</span>', $line ) );
				}

				$changelog .= '</pre>';
			}

			return wp_kses_post( $changelog );
		}

		/**
		 * Output the supported plugins screen.
		 */
		public function supported_plugins_screen() {
			?>
			<div class="wrap about-wrap">

				<?php $this->intro(); ?>

				<p class="about-description"><?php esc_html_e( 'This theme recommends following plugins:', 'accelerate' ); ?></p>
				<ol>
					<li>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/social-icons/' ); ?>" target="_blank"><?php esc_html_e( 'Social Icons', 'accelerate' ); ?></a>
						<?php esc_html_e( ' by ThemeGrill', 'accelerate' ); ?>
					</li>
					<li>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/easy-social-sharing/' ); ?>" target="_blank"><?php esc_html_e( 'Easy Social Sharing', 'accelerate' ); ?></a>
						<?php esc_html_e( ' by ThemeGrill', 'accelerate' ); ?>
					</li>
					<li>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/contact-form-7/' ); ?>" target="_blank"><?php esc_html_e( 'Contact Form 7', 'accelerate' ); ?></a>
					</li>
					<li>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/wp-pagenavi/' ); ?>" target="_blank"><?php esc_html_e( 'WP-PageNavi', 'accelerate' ); ?></a>
					</li>
					<li>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/woocommerce/' ); ?>" target="_blank"><?php esc_html_e( 'WooCommerce', 'accelerate' ); ?></a>
						<?php esc_html_e( 'Fully Compatible in Pro Version', 'accelerate' ); ?>
					</li>
					<li>
						<a href="<?php echo esc_url( 'https://wordpress.org/plugins/polylang/' ); ?>" target="_blank"><?php esc_html_e( 'Polylang', 'accelerate' ); ?></a>
						<?php esc_html_e( 'Fully Compatible in Pro Version', 'accelerate' ); ?>
					</li>
					<li>
						<a href="<?php echo esc_url( 'https://wpml.org/' ); ?>" target="_blank"><?php esc_html_e( 'WPML', 'accelerate' ); ?></a>
						<?php esc_html_e( 'Fully Compatible in Pro Version', 'accelerate' ); ?>
					</li>
				</ol>

			</div>
			<?php
		}

		/**
		 * Output the free vs pro screen.
		 */
		public function free_vs_pro_screen() {
			?>
			<div class="wrap about-wrap">

				<?php $this->intro(); ?>

				<p class="about-description"><?php esc_html_e( 'Upgrade to PRO version for more exciting features.', 'accelerate' ); ?></p>

				<table>
					<thead>
					<tr>
						<th class="table-feature-title"><h3><?php esc_html_e( 'Features', 'accelerate' ); ?></h3></th>
						<th><h3><?php esc_html_e( 'Accelerate', 'accelerate' ); ?></h3></th>
						<th><h3><?php esc_html_e( 'Accelerate Pro', 'accelerate' ); ?></h3></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td><h3><?php esc_html_e( 'Slider', 'accelerate' ); ?></h3></td>
						<td><?php esc_html_e( '4', 'accelerate' ); ?></td>
						<td><?php esc_html_e( 'Unlimited Slides', 'accelerate' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Google Fonts Option', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><?php esc_html_e( '600+', 'accelerate' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Font Size options', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Primary Color', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Multiple Color Options', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><?php esc_html_e( '35+ color options', 'accelerate' ); ?></td>
					<tr>
						<td><h3><?php esc_html_e( 'Business Template', 'accelerate' ); ?></h3></td>
						<td><?php esc_html_e( '1', 'accelerate' ); ?></td>
						<td><?php esc_html_e( '5', 'accelerate' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Custom Menu', 'accelerate' ); ?></h3></td>
						<td><?php esc_html_e( '2', 'accelerate' ); ?></td>
						<td><?php esc_html_e( '3', 'accelerate' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Boxed & Wide layout option', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Social Icons', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></td>
						<td><span class="dashicons dashicons-yes"></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'RTL Support', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></td>
						<td><span class="dashicons dashicons-yes"></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Woocommerce Compatible', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Footer Widget Area', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Footer Copyright Editor', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Content Demo', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Support', 'accelerate' ); ?></h3></td>
						<td><?php esc_html_e( 'Forum', 'accelerate' ); ?></td>
						<td><?php esc_html_e( 'Forum + Emails/Support Ticket', 'accelerate' ); ?></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Translation Ready', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'WPML Compatible', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'Polylang Compatible', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Call to Action widget', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Custom Tag Cloud', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Featured Single Page', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Featured Widget', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Image Services', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-yes"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Our Clients', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Featured Posts', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Testimonial', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Pricing Table', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Team', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td><h3><?php esc_html_e( 'TG: Fun Facts', 'accelerate' ); ?></h3></td>
						<td><span class="dashicons dashicons-no"></span></td>
						<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td class="btn-wrapper">
							<a href="<?php echo esc_url( apply_filters( 'accelerate_pro_theme_url', 'https://themegrill.com/themes/accelerate/?utm_source=accelerate-free-vs-pro-table&utm_medium=view-pro-link&utm_campaign=view-pro#free-vs-pro' ) ); ?>" class="button button-secondary docs" target="_blank"><?php esc_html_e( 'View Pro', 'accelerate' ); ?></a>
						</td>
					</tr>
					</tbody>
				</table>

			</div>
			<?php
		}
	}

endif;

return new Accelerate_admin();
