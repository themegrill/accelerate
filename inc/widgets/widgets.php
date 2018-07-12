<?php
/**
 * Contains all the functions related to sidebar and widget.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */

add_action( 'widgets_init', 'accelerate_widgets_init');
/**
 * Function to register the widget areas(sidebar) and widgets.
 */
function accelerate_widgets_init() {

	// Registering main right sidebar
	register_sidebar( array(
		'name' 				=> __( 'Right Sidebar', 'accelerate' ),
		'id' 					=> 'accelerate_right_sidebar',
		'description'   	=> __( 'Shows widgets at Right side.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering main left sidebar
	register_sidebar( array(
		'name' 				=> __( 'Left Sidebar', 'accelerate' ),
		'id' 					=> 'accelerate_left_sidebar',
		'description'   	=> __( 'Shows widgets at Left side.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering Header sidebar
	register_sidebar( array(
		'name' 				=> __( 'Header Sidebar', 'accelerate' ),
		'id' 					=> 'accelerate_header_sidebar',
		'description'   	=> __( 'Shows widgets in header section just above the main navigation menu.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title">',
		'after_title'   	=> '</h3>'
	) );

	// Registering Business Page template top section sidebar
	register_sidebar( array(
		'name' 				=> __( 'Business Sidebar', 'accelerate' ),
		'id' 					=> 'accelerate_business_sidebar',
		'description'   	=> __( 'Shows widgets on Business Page Template.', 'accelerate' ),
		'before_widget' 	=> '<section id="%1$s" class="widget %2$s clearfix">',
		'after_widget'  	=> '</section>',
		'before_title'  	=> '<h3 class="widget-title">',
		'after_title'   	=> '</h3>'
	) );

	// Registering contact Page sidebar
	register_sidebar( array(
		'name' 				=> __( 'Contact Page Sidebar', 'accelerate' ),
		'id' 					=> 'accelerate_contact_page_sidebar',
		'description'   	=> __( 'Shows widgets on Contact Page Template.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering Error 404 Page sidebar
	register_sidebar( array(
		'name' 				=> __( 'Error 404 Page Sidebar', 'accelerate' ),
		'id' 					=> 'accelerate_error_404_page_sidebar',
		'description'   	=> __( 'Shows widgets on Error 404 page.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering footer sidebar one
	register_sidebar( array(
		'name' 				=> __( 'Footer Sidebar One', 'accelerate' ),
		'id' 					=> 'accelerate_footer_sidebar_one',
		'description'   	=> __( 'Shows widgets at footer sidebar one.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering footer sidebar two
	register_sidebar( array(
		'name' 				=> __( 'Footer Sidebar Two', 'accelerate' ),
		'id' 					=> 'accelerate_footer_sidebar_two',
		'description'   	=> __( 'Shows widgets at footer sidebar two.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering footer sidebar three
	register_sidebar( array(
		'name' 				=> __( 'Footer Sidebar Three', 'accelerate' ),
		'id' 					=> 'accelerate_footer_sidebar_three',
		'description'   	=> __( 'Shows widgets at footer sidebar three.', 'accelerate' ),
		'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  	=> '</aside>',
		'before_title'  	=> '<h3 class="widget-title"><span>',
		'after_title'   	=> '</span></h3>'
	) );

	// Registering widgets.
	register_widget( 'accelerate_featured_single_page_widget' );
	register_widget( 'accelerate_call_to_action_widget' );
	register_widget( 'accelerate_recent_work_widget' );
	register_widget( 'accelerate_image_service_widget' );
	register_widget( 'accelerate_custom_tag_widget' );
}

// Require file for TG: Featured Single Page.
require ACCELERATE_WIDGETS_DIR . '/accelerate-featured-single-page-widget.php';

// Require file for TG: Call To Action Widget.
require ACCELERATE_WIDGETS_DIR . '/accelerate-call-to-action-widget.php';

// Require file for TG: Featured Widget.
require ACCELERATE_WIDGETS_DIR . '/accelerate-recent-work-widget.php';

// Require file for TG: Image Services.
require ACCELERATE_WIDGETS_DIR . '/accelerate-image-service-widget.php';

// Require file for TG: Custom Tag Cloud.
require ACCELERATE_WIDGETS_DIR . '/accelerate-custom-tag-widget.php';
