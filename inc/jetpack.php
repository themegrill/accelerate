<?php
/**
 * Jetpack Compatibility File
 *
 * @link       https://jetpack.com/
 *
 * @package    ThemeGrill
 * @subpackage Accelerate
 * @since      Accelerate 1.3.3
 */

/**
 * Jetpack setup function.
 *
 * See: https://jetpack.com/support/responsive-videos/
 */
function accelerate_jetpack_setup() {
	// Add theme support for Responsive Videos.
	add_theme_support( 'jetpack-responsive-videos' );
}

add_action( 'after_setup_theme', 'accelerate_jetpack_setup' );
