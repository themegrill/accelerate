<?php
/**
 * Contains all the fucntions and components related to header part.
 *
 * @package           ThemeGrill
 * @subpackage        Accelerate
 * @since             Accelerate 1.0
 */

/****************************************************************************************/
// Filter the get_header_image_tag() for option of displaying the header image in old way
function accelerate_header_image_markup( $html, $header, $attr ) {
	$output       = '';
	$header_image = get_header_image();

	if ( ! empty( $header_image ) ) {
		$output .= '<div class="header-image-wrap"><div class="inner-wrap"><img src="' . esc_url( $header_image ) . '" class="header-image" width="' . get_custom_header()->width . '" height="' . get_custom_header()->height . '" alt="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '"></div></div>';
	}

	return $output;
}

function accelerate_header_image_markup_filter() {
	add_filter( 'get_header_image_tag', 'accelerate_header_image_markup', 10, 3 );
}

add_action( 'accelerate_header_image_markup_render', 'accelerate_header_image_markup_filter' );

/****************************************************************************************/

if ( ! function_exists( 'accelerate_render_header_image' ) ) :
	/**
	 * Shows the small info text on top header part
	 */
	function accelerate_render_header_image() {
		if ( function_exists( 'the_custom_header_markup' ) ) {
			do_action( 'accelerate_header_image_markup_render' );
			the_custom_header_markup();
		} else {
			$header_image = get_header_image();
			if ( ! empty( $header_image ) ) {
				?>
				<div class="header-image-wrap">
					<div class="inner-wrap">
						<img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
					</div>
				</div>
				<?php
			}
		}
	}
endif;

/****************************************************************************************/

if ( ! function_exists( 'accelerate_featured_image_slider' ) ) :
	/**
	 * display featured post slider
	 */
	function accelerate_featured_image_slider() {
		global $post;
		?>
		<section id="featured-slider">
			<div class="slider-cycle inner-wrap">
				<div class="slider-rotate">
					<?php
					for ( $i = 1; $i <= 4; $i ++ ) {
						$accelerate_slider_title        = accelerate_options( 'accelerate_slider_title' . $i, '' );
						$accelerate_slider_text         = accelerate_options( 'accelerate_slider_text' . $i, '' );
						$accelerate_slider_image        = accelerate_options( 'accelerate_slider_image' . $i, '' );
						$accelerate_slide_text_position = accelerate_options( 'accelerate_slide_text_position' . $i, 'right' );
						$accelerate_slider_link         = accelerate_options( 'accelerate_slider_link' . $i, '#' );
						$attachment_post_id             = attachment_url_to_postid( $accelerate_slider_image );
						$image_value                    = wp_get_attachment_image_src( $attachment_post_id, 'full' );
						$image_alt                      = get_post_meta( $attachment_post_id, '_wp_attachment_image_alt', true );
						$image_alt_text                 = ! empty( $image_alt ) ? $image_alt : $accelerate_slider_title;


						if ( ! empty( $accelerate_header_title ) || ! empty( $accelerate_slider_text ) || ! empty( $accelerate_slider_image ) ) {
							if ( $i == 1 ) {
								$classes = "slides displayblock";
							} else {
								$classes = "slides displaynone";
							}

							if ( $accelerate_slide_text_position == 'left' ) {
								$classes2 = "entry-container entry-container-left";
							} else {
								$classes2 = "entry-container";
							}
							?>
							<div class="<?php echo $classes; ?>">
								<figure>
									<img width="<?php echo esc_attr( $image_value[1] ); ?>" height="<?php echo esc_attr( $image_value[2] ); ?>" alt="<?php echo esc_attr( $image_alt_text ); ?>" src="<?php echo esc_url( $accelerate_slider_image ); ?>">
								</figure>
								<div class="<?php echo $classes2; ?>">
									<?php if ( ! empty( $accelerate_slider_title ) || ! empty( $accelerate_slider_text ) ) { ?>
										<?php if ( ! empty( $accelerate_slider_title ) ) { ?>
											<div class="slider-title-head"><h3 class="entry-title">
													<a href="<?php echo esc_url( $accelerate_slider_link ); ?>" title="<?php echo esc_attr( $accelerate_slider_title ); ?>"><?php echo $accelerate_slider_title; ?></a>
												</h3></div>
										<?php } ?>
										<?php if ( ! empty( $accelerate_slider_text ) ) { ?>
											<div class="entry-content"><p><?php echo $accelerate_slider_text; ?></p>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
							<?php
						}
					}
					?>
					<nav id="controllers" class="clearfix"></nav>
				</div>

				<a class="slide-next" href="#"><i class="fa fa-angle-right"></i></a>
				<a class="slide-prev" href="#"><i class="fa fa-angle-left"></i></a>
			</div>
		</section>

		<?php
	}
endif;

/****************************************************************************************/

if ( ! function_exists( 'accelerate_the_custom_logo' ) ) {
	/**
	 * Displays the optional custom logo.
	 */
	function accelerate_the_custom_logo() {
		if ( function_exists( 'the_custom_logo' ) ) {
			the_custom_logo();
		}
	}
}
