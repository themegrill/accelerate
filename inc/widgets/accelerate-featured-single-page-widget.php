<?php
/**
 * Featured Single page widget.
 *
 */

class accelerate_featured_single_page_widget extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_featured_single_post clearfix',
			'description'                 => __( 'Display Featured Single Page', 'accelerate' ),
			'customize_selective_refresh' => true,
		);
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
