<?php
/**
 * Functions for configuring demo importer.
 *
 * @package Importer/Functions
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setup demo importer config.
 *
 * @deprecated 1.5.0
 *
 * @param  array $demo_config Demo config.
 * @return array
 */
function accelerate_demo_importer_packages( $packages ) {
	$new_packages = array(
		'accelerate-free' => array(
			'name'    => esc_html__( 'Accelerate', 'accelerate' ),
			'preview' => 'https://demo.themegrill.com/accelerate/',
		),
		'accelerate-pro'  => array(
			'name'     => esc_html__( 'Accelerate Pro', 'accelerate' ),
			'preview'  => 'https://demo.themegrill.com/accelerate-pro/',
			'pro_link' => 'https://themegrill.com/themes/accelerate/',
		),
	);

	return array_merge( $new_packages, $packages );
}

add_filter( 'themegrill_demo_importer_packages', 'accelerate_demo_importer_packages' );
