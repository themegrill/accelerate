<?php
/**
 * Implements a custom header for Accelerate.
 * See http://codex.wordpress.org/Custom_Headers
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */

/**
 * Setup the WordPress core custom header feature.
 */
function accelerate_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'accelerate_custom_header_args', array(
		'default-image'          => '',
		'header-text'				 => '',
		'default-text-color'     => '',
		'width'                  => 1100,
		'height'                 => 300,
		'flex-width'				 => true,
		'flex-height'            => true,
		'wp-head-callback'       => '',
		'admin-head-callback'    => '',
		'admin-preview-callback' => 'accelerate_admin_header_image',
	) ) );	
}
add_action( 'after_setup_theme', 'accelerate_custom_header_setup' );

if ( ! function_exists( 'accelerate_admin_header_image' ) ) :
/**
 * Custom header image markup displayed on the Appearance > Header admin panel.
 */
function accelerate_admin_header_image() {
?>
	<div id="headimg">
		<?php if ( get_header_image() ) : ?>
		<img src="<?php header_image(); ?>" alt="<?php bloginfo( 'name' ); ?>">
		<?php endif; ?>
	</div>
<?php
}
endif; // accelerate_admin_header_image
?>