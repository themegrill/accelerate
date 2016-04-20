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

	// Registering widgets
	register_widget( "accelerate_featured_single_page_widget" );
	register_widget( "accelerate_call_to_action_widget" );
	register_widget( "accelerate_recent_work_widget" );
	register_widget( "accelerate_image_service_widget" );
	register_widget( "accelerate_custom_tag_widget" );
}

/****************************************************************************************/

/**
 * Featured Single page widget.
 *
 */
 class accelerate_featured_single_page_widget extends WP_Widget {
 	function __construct() {
 		$widget_ops = array( 'classname' => 'widget_featured_single_post clearfix', 'description' => __( 'Display Featured Single Page', 'accelerate' ) );
		$control_ops = array( 'width' => 200, 'height' =>250 );
		parent::__construct( false, $name= __( 'TG: Featured Single Page', 'accelerate' ), $widget_ops, $control_ops);
 	}

 	function form( $instance ) {
 		$instance = wp_parse_args( (array) $instance, array( 'page_id' => '', 'title' => '', 'disable_feature_image' => 0, 'image_position' => 'above' ) );
		$title = esc_attr( $instance[ 'title' ] );
		$page_id = absint( $instance[ 'page_id' ] );
		$disable_feature_image = $instance['disable_feature_image'] ? 'checked="checked"' : '';
		$image_position = esc_html( $instance[ 'image_position' ] );
		_e( 'Suitable for Business Sidebar and Left/Right Sidebar.', 'accelerate' );
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'accelerate' ); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p><?php _e( 'Displays the title of the Page if title input is empty.', 'accelerate' ); ?></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'page_id' ); ?>"><?php _e( 'Page', 'accelerate' ); ?>:</label>
			<?php wp_dropdown_pages( array( 'name' => $this->get_field_name( 'page_id' ), 'selected' => $instance['page_id'] ) ); ?>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $disable_feature_image; ?> id="<?php echo $this->get_field_id('disable_feature_image'); ?>" name="<?php echo $this->get_field_name('disable_feature_image'); ?>" /> <label for="<?php echo $this->get_field_id('disable_feature_image'); ?>"><?php _e( 'Remove Featured image', 'accelerate' ); ?></label>
		</p>

	    <?php if( $image_position == 'above' ) { ?>
		<p>
		    <input type="radio" id="<?php echo $this->get_field_id( 'image_position' ); ?>" name="<?php echo $this->get_field_name( 'image_position' ); ?>" value="above" style="" checked /><?php _e( 'Show Image Before Title', 'accelerate' );?><br />
		    <input type="radio" id="<?php echo $this->get_field_id( 'image_position' ); ?>" name="<?php echo $this->get_field_name( 'image_position' ); ?>" value="below" style="" /><?php _e( 'Show Image After Title', 'accelerate' );?><br />
		</p>
		<?php } else { ?>
		<p>
		    <input type="radio" id="<?php echo $this->get_field_id( 'image_position' ); ?>" name="<?php echo $this->get_field_name( 'image_position' ); ?>" value="above" style="" /><?php _e( 'Show Image Before Title', 'accelerate' );?><br />
		    <input type="radio" id="<?php echo $this->get_field_id( 'image_position' ); ?>" name="<?php echo $this->get_field_name( 'image_position' ); ?>" value="below" style="" checked /><?php _e( 'Show Image After Title', 'accelerate' );?><br />
		</p>
		<?php } ?>

	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'page_id' ] = absint( $new_instance[ 'page_id' ] );
		$instance[ 'disable_feature_image' ] = isset( $new_instance[ 'disable_feature_image' ] ) ? 1 : 0;
		$instance[ 'image_position' ] = esc_html( $new_instance[ 'image_position' ] );

		return $instance;
	}

	function widget( $args, $instance ) {
 		extract( $args );
 		extract( $instance );
 		global $post;
 		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
 		$page_id = isset( $instance[ 'page_id' ] ) ? $instance[ 'page_id' ] : '';
 		$disable_feature_image = !empty( $instance[ 'disable_feature_image' ] ) ? 'true' : 'false';
 		$image_position = isset( $instance[ 'image_position' ] ) ? $instance[ 'image_position' ] : 'above' ;

 		if( $page_id ) {
 			$the_query = new WP_Query( 'page_id='.$page_id );
 			while( $the_query->have_posts() ):$the_query->the_post();
 				$page_name = get_the_title();

	 		$output = $before_widget;
	 		if( $image_position == "below" ) {
	 			if( $title ): $output .= $before_title.'<a href="' . get_permalink() . '" title="'.esc_attr( $title ).'">'. esc_html( $title ).'</a>'.$after_title;
	 			else: $output .= $before_title.'<a href="' . get_permalink() . '" title="'.esc_attr( $page_name ).'">'. esc_html( $page_name ) .'</a>'.$after_title;
	 			endif;
	 		}
	 		if( has_post_thumbnail() && $disable_feature_image != "true" ) {
	 			$output.= '<div class="service-image">'.get_the_post_thumbnail( $post->ID, 'featured', array( 'title' => esc_attr( $page_name ), 'alt' => esc_attr( $page_name ) ) ).'</div>';
	 		}

	 		if( $image_position == "above" ) {
		 		if( $title ): $output .= $before_title.'<a href="' . get_permalink() . '" title="'.esc_attr( $title ).'">'. esc_html( $title ) .'</a>'.$after_title;
	 			else: $output .= $before_title.'<a href="' . get_permalink() . '" title="'.esc_attr( $page_name ).'">'. esc_html( $page_name ) .'</a>'.$after_title;
	 			endif;
		 	}
			$output .= '<p>'.get_the_excerpt().'...'.'</p>';
			$output .= '<a class="read-more" href="'. get_permalink() .'">'. __( 'Read more', 'accelerate' ) .'</a>';
	 		$output .= $after_widget;
	 		endwhile;
	 		// Reset Post Data
	 		wp_reset_postdata();
	 		echo $output;
 		}

 	}
}

/**************************************************************************************/

/**
 * Featured call to action widget.
 */
class accelerate_call_to_action_widget extends WP_Widget {
 	function __construct() {
 		$widget_ops = array( 'classname' => 'widget_call_to_action', 'description' => __( 'Use this widget to show the call to action section.', 'accelerate' ) );
		$control_ops = array( 'width' => 200, 'height' =>250 );
		parent::__construct( false, $name = __( 'TG: Call To Action Widget', 'accelerate' ), $widget_ops, $control_ops);
 	}

 	function form( $instance ) {
 		$accelerate_defaults[ 'text_main' ] = '';
 		$accelerate_defaults[ 'text_additional' ] = '';
 		$accelerate_defaults[ 'button_text' ] = '';
 		$accelerate_defaults[ 'button_url' ] = '';
 		$accelerate_defaults[ 'new_tab' ] = '0';
 		$instance = wp_parse_args( (array) $instance, $accelerate_defaults );
		$text_main = esc_textarea( $instance[ 'text_main' ] );
		$text_additional = esc_textarea( $instance[ 'text_additional' ] );
		$button_text = esc_attr( $instance[ 'button_text' ] );
		$button_url = esc_url( $instance[ 'button_url' ] );
		$new_tab = $instance['new_tab'] ? 'checked="checked"' : '';
		?>


		<?php _e( 'Call to Action Main Text','accelerate' ); ?>
		<textarea class="widefat" rows="3" cols="20" id="<?php echo $this->get_field_id('text_main'); ?>" name="<?php echo $this->get_field_name('text_main'); ?>"><?php echo $text_main; ?></textarea>
		<?php _e( 'Call to Action Additional Text','accelerate' ); ?>
		<textarea class="widefat" rows="3" cols="20" id="<?php echo $this->get_field_id('text_additional'); ?>" name="<?php echo $this->get_field_name('text_additional'); ?>"><?php echo $text_additional; ?></textarea>
		<p>
			<label for="<?php echo $this->get_field_id('button_text'); ?>"><?php _e( 'Button Text:', 'accelerate' ); ?></label>
			<input id="<?php echo $this->get_field_id('button_text'); ?>" name="<?php echo $this->get_field_name('button_text'); ?>" type="text" value="<?php echo $button_text; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('button_url'); ?>"><?php _e( 'Button Redirect Link:', 'accelerate' ); ?></label>
			<input id="<?php echo $this->get_field_id('button_url'); ?>" name="<?php echo $this->get_field_name('button_url'); ?>" type="text" value="<?php echo $button_url; ?>" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $new_tab; ?> id="<?php echo $this->get_field_id('new_tab'); ?>" name="<?php echo $this->get_field_name('new_tab'); ?>" /> <label for="<?php echo $this->get_field_id('new_tab'); ?>"><?php _e( 'Open in new tab', 'accelerate' ); ?></label>
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( current_user_can('unfiltered_html') )
			$instance['text_main'] =  $new_instance['text_main'];
		else
			$instance['text_main'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text_main']) ) ); // wp_filter_post_kses() expects slashed

		if ( current_user_can('unfiltered_html') )
			$instance['text_additional'] =  $new_instance['text_additional'];
		else
			$instance['text_additional'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text_additional']) ) ); // wp_filter_post_kses() expects slashed

		$instance[ 'button_text' ] = strip_tags( $new_instance[ 'button_text' ] );
		$instance[ 'button_url' ] = esc_url_raw( $new_instance[ 'button_url' ] );
		$instance[ 'new_tab' ] = isset( $new_instance[ 'new_tab' ] ) ? 1 : 0;

		return $instance;
	}

	function widget( $args, $instance ) {
 		extract( $args );
 		extract( $instance );

 		global $post;
 		$new_tab = !empty( $instance[ 'new_tab' ] ) ? 'true' : 'false';
 		$text_main = empty( $instance['text_main'] ) ? '' : $instance['text_main'];
 		$text_additional = empty( $instance['text_additional'] ) ? '' : $instance['text_additional'];
 		$button_text = isset( $instance[ 'button_text' ] ) ? $instance[ 'button_text' ] : '';
 		$button_url = isset( $instance[ 'button_url' ] ) ? $instance[ 'button_url' ] : '#';

		echo $before_widget;
		?>
			<div class="call-to-action-content-wrapper clearfix">
				<div class="call-to-action-content">
					<?php
					if( !empty( $text_main ) ) {
					?>
					<h3><?php echo esc_html( $text_main ); ?></h3>
					<?php
					}
					if( !empty( $text_additional ) ) {
					?>
					<p><?php echo esc_html( $text_additional ); ?></p>
					<?php
					}
					?>
				</div>
				<?php
				if( !empty( $button_text ) ) {
				?>
					<a class="read-more" <?php if( $new_tab == 'true' ) { echo 'target="_blank"'; } ?> href="<?php echo esc_url( $button_url ); ?>" title="<?php echo esc_attr( $button_text ); ?>"><?php echo esc_html( $button_text ); ?></a>
				<?php
				}
				?>
			</div>
		<?php
		echo $after_widget;
 	}
}

/**************************************************************************************/

/**
 * Featured recent work widget to show pages.
 */
 class accelerate_recent_work_widget extends WP_Widget {
 	function __construct() {
 		$widget_ops = array( 'classname' => 'widget_recent_work', 'description' => __( 'Show your some pages as recent work. Best for Business Top or Bottom sidebar.', 'accelerate' ) );
		$control_ops = array( 'width' => 200, 'height' =>250 );
		parent::__construct( false, $name = __( 'TG: Featured Widget', 'accelerate' ), $widget_ops, $control_ops);
 	}

 	function form( $instance ) {
 		$defaults = array();
 		$defaults[ 'title' ] = '';
 		$defaults[ 'text' ] = '';
 		for ( $i=0; $i<4; $i++ ) {
 			$var = 'page_id'.$i;
 			$defaults[$var] = '';
 		}
 		$instance = wp_parse_args( (array) $instance, $defaults );
 		$title = esc_attr( $instance['title'] );
		$text = esc_textarea($instance['text']);
 		for ( $i=0; $i<4; $i++ ) {
 			$var = 'page_id'.$i;
 			$var = absint( $instance[ $var ] );
		}
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'accelerate' ); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_html($title); ?>" />
		</p>
		<?php _e( 'Description','accelerate' ); ?>
		<textarea class="widefat" rows="10" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_textarea( $text ); ?></textarea>
		<?php for( $i=0; $i<4; $i++) { ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'page_id'.$i ); ?>"><?php _e( 'Page', 'accelerate' ); ?>:</label>
				<?php wp_dropdown_pages( array( 'show_option_none' =>' ','name' => $this->get_field_name( 'page_id'.$i ), 'selected' => $instance[ 'page_id'.$i ] ) ); ?>
			</p>
		<?php
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) );

		for( $i=0; $i<4; $i++ ) {
			$var = 'page_id'.$i;
			$instance[ $var] = absint( $new_instance[ $var ] );
		}

		return $instance;
	}

	function widget( $args, $instance ) {
 		extract( $args );
 		extract( $instance );

 		global $post;
 		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
 		$text = isset( $instance[ 'text' ] ) ? $instance[ 'text' ] : '';
 		$page_array = array();
 		for( $i=0; $i<4; $i++ ) {
 			$var = 'page_id'.$i;
 			$page_id = isset( $instance[ $var ] ) ? $instance[ $var ] : '';

 			if( !empty( $page_id ) )
 				array_push( $page_array, $page_id );// Push the page id in the array
 		}
		$get_featured_pages = new WP_Query( array(
			'posts_per_page' 			=> -1,
			'post_type'					=>  array( 'page' ),
			'post__in'		 			=> $page_array,
			'orderby' 		 			=> 'post__in'
		) );
		echo $before_widget;
			if ( !empty( $title ) ) { echo $before_title . esc_html( $title ) . $after_title; }
			if ( !empty( $text ) ) { echo '<p>'.esc_textarea( $text ).'</p>'; }
			$i = 1;
 			while( $get_featured_pages->have_posts() ):$get_featured_pages->the_post();
				$page_title = get_the_title();
				if ( $i % 4 == 0 ) { $class = 'tg-one-fourth tg-one-fourth-last'.' tg-column-'.$i; }
 				elseif( $i % 3 == 0 ) { $class= 'tg-one-fourth tg-after-two-blocks-clearfix'.' tg-column-'.$i; }
 				else { $class = 'tg-one-fourth'.' tg-column-'.$i; }
				?>
				<div class="<?php echo $class; ?>">
					<?php
					if ( has_post_thumbnail() ) {
						$title_attribute = get_the_title( $post->ID );
						echo'<div class="service-image"><a title="'.get_the_title().'" href="'.get_permalink().'">'.get_the_post_thumbnail( $post->ID, 'featured-recent-work', array( 'title' => esc_attr( $title_attribute ), 'alt' => esc_attr( $title_attribute ) ) ).'</a></div>';
					}
					?>
					<a class="recent_work_title" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>">
						<div class="title_box">
							<?php echo '<h5>'.$page_title.'</h5>'; ?>
						</div>
					</a>
				</div>
				<?php $i++; ?>
			<?php endwhile;
	 		// Reset Post Data
 			wp_reset_query();
 			?>
		<?php
		echo $after_widget;
 	}
}

/****************************************************************************************/

/**
 * Featured service widget to show pages.
 */
class accelerate_image_service_widget extends WP_Widget {
 	function __construct() {
 		$widget_ops = array( 'classname' => 'widget_image_service_block', 'description' => __( 'Display some pages as services. Best for Business Top or Bottom sidebar.', 'accelerate' ) );
		$control_ops = array( 'width' => 200, 'height' =>250 );
		parent::__construct( false, $name = __( 'TG: Image Services', 'accelerate' ), $widget_ops, $control_ops);
 	}

 	function form( $instance ) {
 		for ( $i=0; $i<6; $i++ ) {
 			$var = 'page_id'.$i;
 			$defaults[$var] = '';
 		}
      $defaults['display_read_more'] = 0;
 		$instance = wp_parse_args( (array) $instance, $defaults );
 		for ( $i=0; $i<6; $i++ ) {
 			$var = 'page_id'.$i;
 			$var = absint( $instance[ $var ] );
		}
      $display_read_more = $instance['display_read_more'] ? 'checked="checked"' : '';

		for( $i=0; $i<6; $i++) { ?>
			<p>
				<label for="<?php echo $this->get_field_id( key($defaults) ); ?>"><?php _e( 'Page', 'accelerate' ); ?>:</label>
				<?php wp_dropdown_pages( array( 'show_option_none' =>' ','name' => $this->get_field_name( key($defaults) ), 'selected' => $instance[key($defaults)] ) ); ?>
			</p>
		<?php
		next( $defaults );// forwards the key of $defaults array
		} ?>
      <p>
         <input class="checkbox" type="checkbox" <?php echo $display_read_more; ?> id="<?php echo $this->get_field_id( 'display_read_more' ); ?>" name="<?php echo $this->get_field_name( 'display_read_more' ); ?>" /> <label for="<?php echo $this->get_field_id( 'display_read_more' ); ?>"><?php _e( 'Display Read more', 'accelerate' ); ?></label>
      </p>
	<?php }

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		for( $i=0; $i<6; $i++ ) {
			$var = 'page_id'.$i;
			$instance[ $var] = absint( $new_instance[ $var ] );
		}
      $instance[ 'display_read_more' ] = isset( $new_instance[ 'display_read_more' ] ) ? 1 : 0;

		return $instance;
	}

	function widget( $args, $instance ) {
 		extract( $args );
 		extract( $instance );

 		global $post;
 		$page_array = array();
 		for( $i=0; $i<6; $i++ ) {
 			$var = 'page_id'.$i;
 			$page_id = isset( $instance[ $var ] ) ? $instance[ $var ] : '';

 			if( !empty( $page_id ) )
 				array_push( $page_array, $page_id );// Push the page id in the array
 		}
      $display_read_more = !empty( $instance[ 'display_read_more' ] ) ? 1 : 0;
		$get_featured_pages = new WP_Query( array(
			'posts_per_page' 			=> -1,
			'post_type'					=>  array( 'page' ),
			'post__in'		 			=> $page_array,
			'orderby' 		 			=> 'post__in'
		) );
		echo $before_widget; ?>
			<?php
			$j = 1;
 			while( $get_featured_pages->have_posts() ):$get_featured_pages->the_post();
				$page_title = get_the_title();
				if( $j % 3 == 0 ) {
					$service_class = "tg-one-third tg-one-third-last";
				}
				else
					if ( $j % 3 == 1 && $j > 1 ) {
					$service_class = "tg-one-third tg-after-three-blocks-clearfix";
				}
				else {
					$service_class = "tg-one-third";
				}
				?>
				<div class="<?php echo $service_class; ?>">
					<?php
					if ( has_post_thumbnail() ) {
						?>
						<a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php echo'<div class="service-image">'.get_the_post_thumbnail( $post->ID, 'featured-service' ).'</div>';
						?>
						</a>
						<?php
					}
					?>
					<h2 class="entry-title"><a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php echo esc_html( $page_title ); ?></a></h2>
					<?php the_excerpt(); ?>

               <?php if( $display_read_more ) { ?>
                  <a class="more-link" title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php echo '<span>'.__( 'Read more', 'accelerate' ).'</span>' ?></a>
               <?php } ?>

				</div>
				<?php $j++; ?>
			<?php endwhile;
	 		// Reset Post Data
 			wp_reset_postdata();
 			?>
		<?php
		echo $after_widget;
 	}
}

/**************************************************************************************/

/**
 * ThemeGrill Custom Tag Widget
 */
class accelerate_custom_tag_widget extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'accelerate_tagcloud_widget', 'description' => __( 'Custom Tag Cloud', 'accelerate' ) );
		$control_ops = array( 'width' => 200, 'height' => 250 );
		parent::__construct( false, $name = __( 'TG: Custom Tag Cloud', 'accelerate' ) , $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		extract( $instance );
		$title = empty( $instance[ 'title' ] ) ? 'Tags' : $instance[ 'title' ];

		echo $before_widget;

		if ( $title ):
			echo $before_title . $title . $after_title;
		endif;

		wp_tag_cloud( 'smallest=13&largest=13px&unit=px' );

		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( ( array ) $instance, array( 'title'=>'Tags' ) );
		$title = esc_attr( $instance[ 'title' ] );
		?>

		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'accelerate' ); ?></label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
	<?php
	}
}

?>