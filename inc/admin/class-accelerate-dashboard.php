<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Accelerate_Dashboard {
	private static $instance;

	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->setup_hooks();
	}

	private function setup_hooks() {
		add_action( 'admin_menu', array( $this, 'create_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'accelerate-admin-dashboard', ACCELERATE_ADMIN_CSS_URL . '/admin.css' );
	}

	public function create_menu() {
		$theme = wp_get_theme();

		/* translators: %s: Theme Name. */
		$theme_page_name = sprintf( esc_html__( '%s Options', 'accelerate' ), $theme->Name );

		$page = add_theme_page(
			$theme_page_name,
			$theme_page_name,
			'edit_theme_options',
			'accelerate-options',
			array(
				$this,
				'option_page',
			)
		);

		add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_styles' ) );
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'accelerate-dashboard', ACCELERATE_ADMIN_CSS_URL . '/admin.css', array(), ACCELERATE_THEME_VERSION );
	}

	public function option_page() {
		$theme = wp_get_theme();
		?>
		<div class="wrap">
		<div class="accelerate-header">
			<h1>
				<?php
				/* translators: %s: Theme version. */
				echo sprintf( esc_html__( 'Accelerate %s', 'accelerate' ), ACCELERATE_THEME_VERSION );
				?>
			</h1>
		</div>
		<div class="welcome-panel">
			<div class="welcome-panel-content">
				<h2>
					<?php
					/* translators: %s: Theme Name. */
					echo sprintf( esc_html__( 'Welcome to %s!', 'accelerate' ), $theme->Name );
					?>
				</h2>
				<p class="about-description">
					<?php
					/* translators: %s: Theme Name. */
					echo sprintf( esc_html__( 'Important links to get you started with %s', 'accelerate' ), $theme->Name );
					?>
				</p>

				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h3><?php esc_html_e( 'Get Started', 'accelerate' ); ?></h3>
						<a class="button button-primary button-hero"
						   href="<?php echo esc_url( 'https://docs.themegrill.com/accelerate/#section-1' ); ?>"
						   target="_blank"><?php esc_html_e( 'Learn Basics', 'accelerate' ); ?>
						</a>
					</div>

					<div class="welcome-panel-column">
						<h3><?php esc_html_e( 'Next Steps', 'accelerate' ); ?></h3>
						<ul>
							<li><?php printf( '<a target="_blank" href="%s" class="welcome-icon dashicons-media-text">' . esc_html__( 'Documentation', 'accelerate' ) . '</a>', esc_url( 'https://docs.themegrill.com/accelerate' ) ); ?></li>
							<li><?php printf( '<a target="_blank" href="%s" class="welcome-icon dashicons-layout">' . esc_html__( 'Starter Demos', 'accelerate' ) . '</a>', esc_url( 'https://themegrilldemos.com/accelerate-demos/' ) ); ?></li>
							<li><?php printf( '<a target="_blank" href="%s" class="welcome-icon dashicons-migrate">' . esc_html__( 'Premium Version', 'accelerate' ) . '</a>', esc_url( 'https://themegrill.com/themes/accelerate' ) ); ?></li>
						</ul>
					</div>

					<div class="welcome-panel-column">
						<h3><?php esc_html_e( 'Further Actions', 'accelerate' ); ?></h3>
						<ul>
							<li><?php printf( '<a target="_blank" href="%s" class="welcome-icon dashicons-businesswoman">' . esc_html__( 'Got theme support question?', 'accelerate' ) . '</a>', esc_url( 'https://wordpress.org/support/theme/accelerate/' ) ); ?></li>
							<li><?php printf( '<a target="_blank" href="%s" class="welcome-icon dashicons-thumbs-up">' . esc_html__( 'Leave a review', 'accelerate' ) . '</a>', esc_url( 'https://wordpress.org/support/theme/accelerate/reviews/' ) ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

Accelerate_Dashboard::instance();
