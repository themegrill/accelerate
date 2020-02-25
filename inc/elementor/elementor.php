<?php
/**
 * Elementor compatibility.
 *
 * @package Accelerate
 */

// Filter the rendering of the default WordPress widgets.
add_filter( 'elementor/widgets/wordpress/widget_args', 'accelerate_elementor_widget_render_filter' );

/**
 * Render the default WordPress widget settings, ie, divs
 *
 * @param $args the widget id
 *
 * @return array register sidebar divs
 *
 * @since 1.4.5
 */

function accelerate_elementor_widget_render_filter( $args ) {
	return array(
		'before_widget' => '<aside class="widget ' . accelerate_widget_class_names( $args['widget_id'] ) . '">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
	);
}

/**
 * Render the widget classes for Elementor plugin compatibility
 *
 * @param $widgets_id the widgets of the id
 *
 * @return mixed the widget classes of the id passed
 *
 * @since 1.4.5
 */
function accelerate_widget_class_names( $widgets_id ) {

	$widgets_id = str_replace( 'wp-widget-', '', $widgets_id );

	$classes = accelerate_widgets_classes();

	$return_value = isset( $classes[ $widgets_id ] ) ? $classes[ $widgets_id ] : '';

	return $return_value;
}

/**
 * Return all the arrays of widgets classes and classnames of same respectively
 *
 * @return array the array of widget classnames and its respective classes
 *
 * @since 1.4.5
 */
function accelerate_widgets_classes() {

	return array(
		'accelerate_featured_single_page_widget' => 'widget_featured_single_post',
		'accelerate_recent_work_widget'          => 'widget_recent_work',
		'accelerate_image_service_widget'        => 'widget_image_service_block',
		'accelerate_custom_tag_widget'           => 'accelerate_tagcloud_widget',
	);
}
