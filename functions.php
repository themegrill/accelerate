<?php
/**
 * Accelerate functions related to defining constants, adding files and WordPress core functionality.
 *
 * Defining some constants, loading all the required files and Adding some core functionality.
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menu() To add support for navigation menu.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	 $content_width = 720;

/**
 * $content_width global variable adjustment as per layout option.
 */
function accelerate_content_width() {
	 global $post;
	 global $content_width;

	 if( $post ) { $layout_meta = get_post_meta( $post->ID, 'accelerate_page_layout', true ); }
	 if( empty( $layout_meta ) || is_archive() || is_search() ) { $layout_meta = 'default_layout'; }
	 $accelerate_default_layout = accelerate_options( 'accelerate_default_layout', 'right_sidebar' );

	 if( $layout_meta == 'default_layout' ) {
			if ( $accelerate_default_layout == 'no_sidebar_full_width' ) { $content_width = 1100; /* pixels */ }
			else { $content_width = 720; /* pixels */ }
	 }
	 elseif ( $layout_meta == 'no_sidebar_full_width' ) { $content_width = 1100; /* pixels */ }
	 else { $content_width = 720; /* pixels */ }
}
add_action( 'template_redirect', 'accelerate_content_width' );

add_action( 'after_setup_theme', 'accelerate_setup' );
/**
 * All setup functionalities.
 *
 * @since 1.0
 */
if( !function_exists( 'accelerate_setup' ) ) :
function accelerate_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 */
	load_theme_textdomain( 'accelerate', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// This theme uses Featured Images (also known as post thumbnails) for per-post/per-page.
	add_theme_support( 'post-thumbnails' );

	 // Supporting title tag via add_theme_support (since WordPress 4.1)
	 add_theme_support( 'title-tag' );

	// Gutenberg layout support.
	add_theme_support( 'align-wide' );

	// Added WooCommerce support.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Registering navigation menus.
	register_nav_menus( array(
		'primary' 	=> __( 'Primary/Main Menu', 'accelerate' ),
		'footer' 	=> __( 'Footer Menu', 'accelerate' )
	) );

	// Cropping the images to different sizes to be used in the theme
	add_image_size( 'featured-blog-large', 720, 300, true );
	add_image_size( 'featured-blog-small', 230, 230, true );
	add_image_size( 'featured-service', 600, 330, true );
	add_image_size( 'featured-recent-work', 365, 365, true );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'accelerate_custom_background_args', array(
		'default-color' => 'eaeaea'
	) ) );

	// Enable support for Post Formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link', 'gallery', 'chat', 'audio', 'status' ) );

	// Adding excerpt option box for pages as well
	add_post_type_support( 'page', 'excerpt' );

 	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
 	add_theme_support('html5', array(
	 		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
 	));

	// Adds the support for the Custom Logo introduced in WordPress 4.5
	add_theme_support( 'custom-logo',
	   array(
	      'flex-width' => true,
	      'flex-height' => true,
	   )
	);

	// Support for selective refresh widgets in Customizer
	add_theme_support( 'customize-selective-refresh-widgets' );
}
endif;

/**
 * Define Directory Location Constants
 */
define( 'ACCELERATE_PARENT_DIR', get_template_directory() );
define( 'ACCELERATE_CHILD_DIR', get_stylesheet_directory() );

define( 'ACCELERATE_IMAGES_DIR', ACCELERATE_PARENT_DIR . '/images' );
define( 'ACCELERATE_INCLUDES_DIR', ACCELERATE_PARENT_DIR. '/inc' );
define( 'ACCELERATE_CSS_DIR', ACCELERATE_PARENT_DIR . '/css' );
define( 'ACCELERATE_JS_DIR', ACCELERATE_PARENT_DIR . '/js' );
define( 'ACCELERATE_LANGUAGES_DIR', ACCELERATE_PARENT_DIR . '/languages' );

define( 'ACCELERATE_ADMIN_DIR', ACCELERATE_INCLUDES_DIR . '/admin' );
define( 'ACCELERATE_WIDGETS_DIR', ACCELERATE_INCLUDES_DIR . '/widgets' );

define( 'ACCELERATE_ADMIN_IMAGES_DIR', ACCELERATE_ADMIN_DIR . '/images' );
define( 'ACCELERATE_ADMIN_JS_DIR', ACCELERATE_ADMIN_DIR . '/js' );
define( 'ACCELERATE_ADMIN_CSS_DIR', ACCELERATE_ADMIN_DIR . '/css' );

/**
 * Define URL Location Constants
 */
define( 'ACCELERATE_PARENT_URL', get_template_directory_uri() );
define( 'ACCELERATE_CHILD_URL', get_stylesheet_directory_uri() );

define( 'ACCELERATE_IMAGES_URL', ACCELERATE_PARENT_URL . '/images' );
define( 'ACCELERATE_INCLUDES_URL', ACCELERATE_PARENT_URL. '/inc' );
define( 'ACCELERATE_CSS_URL', ACCELERATE_PARENT_URL . '/css' );
define( 'ACCELERATE_JS_URL', ACCELERATE_PARENT_URL . '/js' );
define( 'ACCELERATE_LANGUAGES_URL', ACCELERATE_PARENT_URL . '/languages' );

define( 'ACCELERATE_ADMIN_URL', ACCELERATE_INCLUDES_URL . '/admin' );
define( 'ACCELERATE_WIDGETS_URL', ACCELERATE_INCLUDES_URL . '/widgets' );

define( 'ACCELERATE_ADMIN_IMAGES_URL', ACCELERATE_ADMIN_URL . '/images' );
define( 'ACCELERATE_ADMIN_JS_URL', ACCELERATE_ADMIN_URL . '/js' );
define( 'ACCELERATE_ADMIN_CSS_URL', ACCELERATE_ADMIN_URL . '/css' );

/** Load functions */
require_once( ACCELERATE_INCLUDES_DIR . '/custom-header.php' );
require_once( ACCELERATE_INCLUDES_DIR . '/functions.php' );
require_once( ACCELERATE_INCLUDES_DIR . '/customizer.php' );
require_once( ACCELERATE_INCLUDES_DIR . '/header-functions.php' );

require_once( ACCELERATE_ADMIN_DIR . '/meta-boxes.php' );

/** Load Widgets and Widgetized Area */
require_once( ACCELERATE_WIDGETS_DIR . '/widgets.php' );

/**
 * Load Demo Importer Configs.
 */
if ( class_exists( 'TG_Demo_Importer' ) ) {
	require get_template_directory() . '/inc/demo-config.php';
}

/**
 * Assign the Accelerate version to a variable.
 */
$theme            = wp_get_theme( 'accelerate' );
$accelerate_version = $theme['Version'];

/**
 * Calling in the admin area for the Welcome Page as well as for the new theme notice too.
 */
if ( is_admin() ) {
	require get_template_directory() . '/inc/admin/class-accelerate-admin.php';
	require get_template_directory() . '/inc/admin/class-accelerate-new-theme-notice.php';
}

/**
 * Load TGMPA Configs.
 */
require_once( ACCELERATE_INCLUDES_DIR . '/tgm-plugin-activation/class-tgm-plugin-activation.php' );
require_once( ACCELERATE_INCLUDES_DIR . '/tgm-plugin-activation/tgmpa-accelerate.php' );

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require ACCELERATE_INCLUDES_DIR . '/jetpack.php';
}
