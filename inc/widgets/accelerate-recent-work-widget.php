<?php
/**
 * Featured recent work widget to show pages.
 */

class accelerate_recent_work_widget extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'classname'                    => 'widget_recent_work',
			'description'                  => __( 'Show your some pages as recent work. Best for Business Top or Bottom sidebar.', 'accelerate' ),
			'customize_selective_refresh' => true,
		);
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
					$image_id        = get_post_thumbnail_id( get_the_ID() );
					$image_alt       = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					$image_alt_text  = ! empty( $image_alt ) ? $image_alt : $title_attribute;
					echo'<div class="service-image"><a title="'.get_the_title().'" href="'.get_permalink().'">'.get_the_post_thumbnail( $post->ID, 'featured-recent-work', array( 'title' => esc_attr( $title_attribute ), 'alt' => esc_attr( $image_alt_text ) ) ).'</a></div>';
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

