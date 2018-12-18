<?php
/**
 * Accelerate functions and definitions
 *
 * This file contains all the functions and it's defination that particularly can't be
 * in other files.
 *
 * @package    ThemeGrill
 * @subpackage Accelerate
 * @since      Accelerate 1.0
 */

/****************************************************************************************/

// Accelerate theme options
function accelerate_options( $id, $default = false ) {
	// assigning theme name
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace( "/\W/", "_", strtolower( $themename ) );

	// getting options value
	$accelerate_options = get_option( $themename );
	if ( isset( $accelerate_options[ $id ] ) ) {
		return $accelerate_options[ $id ];
	} else {
		return $default;
	}
}

/****************************************************************************************/

add_action( 'wp_enqueue_scripts', 'accelerate_scripts_styles_method' );
/**
 * Register jquery scripts
 */
function accelerate_scripts_styles_method() {
	/**
	 * Loads our main stylesheet.
	 */
	wp_enqueue_style( 'accelerate_style', get_stylesheet_uri() );

	wp_register_style( 'accelerate_googlefonts', '//fonts.googleapis.com/css?family=Roboto:400,300,100|Roboto+Slab:700,400' );
	wp_enqueue_style( 'accelerate_googlefonts' );

	/**
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	/**
	 * Register JQuery cycle js file for slider.
	 */
	wp_register_script( 'jquery_cycle', ACCELERATE_JS_URL . '/jquery.cycle2.min.js', array( 'jquery' ), '2.1.6', true );
	wp_register_script( 'jquery-cycle2-swipe', ACCELERATE_JS_URL . '/jquery.cycle2.swipe.min.js', array( 'jquery' ), false, true );

	/**
	 * Enqueue Slider setup js file.
	 */
	if ( is_front_page() && accelerate_options( 'accelerate_activate_slider', '0' ) == '1' ) {
		wp_enqueue_script( 'jquery_cycle');
		wp_enqueue_script( 'jquery-cycle2-swipe');
	}

	wp_enqueue_script( 'accelerate-navigation', ACCELERATE_JS_URL . '/navigation.js', array( 'jquery' ), false, true );
	wp_enqueue_script( 'accelerate-custom', ACCELERATE_JS_URL . '/accelerate-custom.js', array( 'jquery' ) );

	wp_enqueue_style( 'accelerate-fontawesome', get_template_directory_uri() . '/fontawesome/css/font-awesome.css', array(), '4.7.0' );

	wp_enqueue_script( 'html5shiv', ACCELERATE_JS_URL . '/html5shiv.js', array(), '3.7.3', false );
	wp_script_add_data( 'html5shiv', 'conditional', 'lte IE 8' );
}

/****************************************************************************************/

add_filter( 'excerpt_length', 'accelerate_excerpt_length' );
/**
 * Sets the post excerpt length to 40 words.
 *
 * function tied to the excerpt_length filter hook.
 *
 * @uses filter excerpt_length
 */
function accelerate_excerpt_length( $length ) {
	return 40;
}

add_filter( 'excerpt_more', 'accelerate_continue_reading' );
/**
 * Returns a "Continue Reading" link for excerpts
 */
function accelerate_continue_reading() {
	return '';
}

/****************************************************************************************/

/**
 * Removing the default style of wordpress gallery
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * Filtering the size to be medium from thumbnail to be used in WordPress gallery as a default size
 */
function accelerate_gallery_atts( $out, $pairs, $atts ) {
	$atts = shortcode_atts( array(
		'size' => 'medium',
	), $atts );

	$out['size'] = $atts['size'];

	return $out;

}

add_filter( 'shortcode_atts_gallery', 'accelerate_gallery_atts', 10, 3 );

/****************************************************************************************/

add_filter( 'body_class', 'accelerate_body_class' );
/**
 * Filter the body_class
 *
 * Throwing different body class for the different layouts in the body tag
 */
function accelerate_body_class( $classes ) {
	global $post;

	if ( $post ) {
		$layout_meta = get_post_meta( $post->ID, 'accelerate_page_layout', true );
	}

	if ( is_home() ) {
		$queried_id  = get_option( 'page_for_posts' );
		$layout_meta = get_post_meta( $queried_id, 'accelerate_page_layout', true );
	}
	if ( empty( $layout_meta ) || is_archive() || is_search() ) {
		$layout_meta = 'default_layout';
	}
	$accelerate_default_layout = accelerate_options( 'accelerate_default_layout', 'right_sidebar' );

	$accelerate_default_page_layout = accelerate_options( 'accelerate_pages_default_layout', 'right_sidebar' );
	$accelerate_default_post_layout = accelerate_options( 'accelerate_single_posts_default_layout', 'right_sidebar' );

	if ( $layout_meta == 'default_layout' ) {
		if ( is_page() ) {
			if ( $accelerate_default_page_layout == 'right_sidebar' ) {
				$classes[] = '';
			} elseif ( $accelerate_default_page_layout == 'left_sidebar' ) {
				$classes[] = 'left-sidebar';
			} elseif ( $accelerate_default_page_layout == 'no_sidebar_full_width' ) {
				$classes[] = 'no-sidebar-full-width';
			} elseif ( $accelerate_default_page_layout == 'no_sidebar_content_centered' ) {
				$classes[] = 'no-sidebar';
			}
		} elseif ( is_single() ) {
			if ( $accelerate_default_post_layout == 'right_sidebar' ) {
				$classes[] = '';
			} elseif ( $accelerate_default_post_layout == 'left_sidebar' ) {
				$classes[] = 'left-sidebar';
			} elseif ( $accelerate_default_post_layout == 'no_sidebar_full_width' ) {
				$classes[] = 'no-sidebar-full-width';
			} elseif ( $accelerate_default_post_layout == 'no_sidebar_content_centered' ) {
				$classes[] = 'no-sidebar';
			}
		} elseif ( $accelerate_default_layout == 'right_sidebar' ) {
			$classes[] = '';
		} elseif ( $accelerate_default_layout == 'left_sidebar' ) {
			$classes[] = 'left-sidebar';
		} elseif ( $accelerate_default_layout == 'no_sidebar_full_width' ) {
			$classes[] = 'no-sidebar-full-width';
		} elseif ( $accelerate_default_layout == 'no_sidebar_content_centered' ) {
			$classes[] = 'no-sidebar';
		}
	} elseif ( $layout_meta == 'right_sidebar' ) {
		$classes[] = '';
	} elseif ( $layout_meta == 'left_sidebar' ) {
		$classes[] = 'left-sidebar';
	} elseif ( $layout_meta == 'no_sidebar_full_width' ) {
		$classes[] = 'no-sidebar-full-width';
	} elseif ( $layout_meta == 'no_sidebar_content_centered' ) {
		$classes[] = 'no-sidebar';
	}

	if ( accelerate_options( 'accelerate_new_menu', '1' ) == '1' ) {
		$classes[] = 'better-responsive-menu';
	}
	if ( accelerate_options( 'accelerate_posts_page_display_type', 'large_image' ) == 'small_image' ) {
		$classes[] = 'blog-small';
	}
	if ( accelerate_options( 'accelerate_posts_page_display_type', 'large_image' ) == 'small_image_alternate' ) {
		$classes[] = 'blog-alternate-small';
	}

	if ( accelerate_options( 'accelerate_site_layout', 'wide' ) == 'wide' ) {
		$classes[] = 'wide';
	} elseif ( accelerate_options( 'accelerate_site_layout', 'wide' ) == 'box' ) {
		$classes[] = '';
	}

	return $classes;
}

/****************************************************************************************/

if ( ! function_exists( 'accelerate_sidebar_select' ) ) :
	/**
	 * Fucntion to select the sidebar
	 */
	function accelerate_sidebar_select() {
		global $post;

		if ( $post ) {
			$layout_meta = get_post_meta( $post->ID, 'accelerate_page_layout', true );
		}

		if ( is_home() ) {
			$queried_id  = get_option( 'page_for_posts' );
			$layout_meta = get_post_meta( $queried_id, 'accelerate_page_layout', true );
		}

		if ( empty( $layout_meta ) || is_archive() || is_search() ) {
			$layout_meta = 'default_layout';
		}
		$accelerate_default_layout = accelerate_options( 'accelerate_default_layout', 'right_sidebar' );

		$accelerate_default_page_layout = accelerate_options( 'accelerate_pages_default_layout', 'right_sidebar' );
		$accelerate_default_post_layout = accelerate_options( 'accelerate_single_posts_default_layout', 'right_sidebar' );

		if ( $layout_meta == 'default_layout' ) {
			if ( is_page() ) {
				if ( $accelerate_default_page_layout == 'right_sidebar' ) {
					get_sidebar();
				} elseif ( $accelerate_default_page_layout == 'left_sidebar' ) {
					get_sidebar( 'left' );
				}
			}
			if ( is_single() ) {
				if ( $accelerate_default_post_layout == 'right_sidebar' ) {
					get_sidebar();
				} elseif ( $accelerate_default_post_layout == 'left_sidebar' ) {
					get_sidebar( 'left' );
				}
			} elseif ( $accelerate_default_layout == 'right_sidebar' ) {
				get_sidebar();
			} elseif ( $accelerate_default_layout == 'left_sidebar' ) {
				get_sidebar( 'left' );
			}
		} elseif ( $layout_meta == 'right_sidebar' ) {
			get_sidebar();
		} elseif ( $layout_meta == 'left_sidebar' ) {
			get_sidebar( 'left' );
		}
	}
endif;

/****************************************************************************************/

if ( ! function_exists( 'accelerate_posts_listing_display_type_select' ) ) :
	/**
	 * Function to select the posts listing display type
	 */
	function accelerate_posts_listing_display_type_select() {
		if ( accelerate_options( 'accelerate_posts_page_display_type', 'large_image' ) == 'large_image' ) {
			$format = 'blog-large-image';
		} elseif ( accelerate_options( 'accelerate_posts_page_display_type', 'large_image' ) == 'small_image' ) {
			$format = 'blog-small-image';
		} elseif ( accelerate_options( 'accelerate_posts_page_display_type', 'large_image' ) == 'small_image_alternate' ) {
			$format = 'blog-small-image';
		} else {
			$format = get_post_format();
		}

		return $format;
	}
endif;

/****************************************************************************************/

if ( ! function_exists( 'accelerate_entry_meta' ) ) :
	function accelerate_entry_meta() {
		echo '<div class="entry-meta">';
		?>
		<span class="byline"><span class="author vcard"><i class="fa fa-user"></i><a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" title="<?php echo get_the_author(); ?>"><?php echo esc_html( get_the_author() ); ?></a></span></span>
		<?php

		$categories_list = get_the_category_list( __( ', ', 'accelerate' ) );
		if ( $categories_list ) {
			printf( __( '<span class="cat-links"><i class="fa fa-folder-open"></i>%1$s</span>', 'accelerate' ), $categories_list );
		}
		$post_format_icon = '';
		if ( 'gallery' == get_post_format() ) {
			$post_format_icon = 'fa-picture-o';
		} elseif ( 'video' == get_post_format() ) {
			$post_format_icon = 'fa-youtube-play';
		} elseif ( 'quote' == get_post_format() ) {
			$post_format_icon = 'fa-quote-left';
		} elseif ( 'link' == get_post_format() ) {
			$post_format_icon = 'fa-link';
		} elseif ( 'image' == get_post_format() ) {
			$post_format_icon = 'fa-picture-o';
		} elseif ( 'audio' == get_post_format() ) {
			$post_format_icon = 'fa-headphones';
		} elseif ( 'aside' == get_post_format() ) {
			$post_format_icon = 'fa-dot-circle-o';
		} elseif ( 'chat' == get_post_format() ) {
			$post_format_icon = 'fa-comments-o';
		} elseif ( 'status' == get_post_format() ) {
			$post_format_icon = 'fa-pencil';
		}

		if ( is_sticky() ) {
			$post_format_icon = 'fa-paperclip';
		}
		?>

		<span class="sep"><span class="post-format"><i class="fa <?php echo $post_format_icon; ?>"></i></span></span>

		<?php

		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
		}
		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);
		printf( '<span class="posted-on"><a href="%1$s" title="%2$s" rel="bookmark"><i class="fa fa-calendar-o"></i> %3$s</a></span>',
			esc_url( get_permalink() ),
			esc_attr( get_the_time() ),
			$time_string
		);

		$tags_list = get_the_tag_list( '<span class="tag-links"><i class="fa fa-tags"></i>', __( ', ', 'accelerate' ), '</span>' );
		if ( $tags_list ) {
			echo $tags_list;
		}

		if ( ! post_password_required() && comments_open() ) { ?>
			<span class="comments-link"><?php comments_popup_link( __( '<i class="fa fa-comment"></i> 0 Comment', 'accelerate' ), __( '<i class="fa fa-comment"></i> 1 Comment', 'accelerate' ), __( '<i class="fa fa-comments"></i> % Comments', 'accelerate' ) ); ?></span>
		<?php }

		edit_post_link( __( 'Edit', 'accelerate' ), '<span class="edit-link"><i class="fa fa-edit"></i>', '</span>' );

		echo '</div>';
	}
endif;

/****************************************************************************************/
if ( ! function_exists( 'accelerate_darkcolor' ) ) :
	/**
	 * Generate darker color
	 * Source: http://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
	 */
	function accelerate_darkcolor( $hex, $steps ) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max( - 255, min( 255, $steps ) );

		// Normalize into a six character long hex string
		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Split into three parts: R, G and B
		$color_parts = str_split( $hex, 2 );
		$return      = '#';

		foreach ( $color_parts as $color ) {
			$color  = hexdec( $color ); // Convert to decimal
			$color  = max( 0, min( 255, $color + $steps ) ); // Adjust color
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT ); // Make two char hex code
		}

		return $return;
	}
endif;

/****************************************************************************************/

add_action( 'wp_head', 'accelerate_custom_css', 100 );
/**
 * Hooks the Custom Internal CSS to head section
 */
function accelerate_custom_css() {
	$accelerate_internal_css = '';

	$primary_color = accelerate_options( 'accelerate_primary_color', '#77CC6D' );
	$primary_dark  = accelerate_darkcolor( $primary_color, - 50 );
	if ( $primary_color != '#77CC6D' ) {
		$accelerate_internal_css .= ' .accelerate-button,blockquote,button,input[type=button],input[type=reset],input[type=submit]{background-color:' . $primary_color . '}a{color:' . $primary_color . '}#page{border-top:3px solid ' . $primary_color . '}#site-title a:hover{color:' . $primary_color . '}#search-form span,.main-navigation a:hover,.main-navigation ul li ul li a:hover,.main-navigation ul li ul li:hover>a,.main-navigation ul li.current-menu-ancestor a,.main-navigation ul li.current-menu-item a,.main-navigation ul li.current-menu-item ul li a:hover,.main-navigation ul li.current_page_ancestor a,.main-navigation ul li.current_page_item a,.main-navigation ul li:hover>a,.main-small-navigation li:hover > a{background-color:' . $primary_color . '}.site-header .menu-toggle:before{color:' . $primary_color . '}.main-small-navigation li:hover{background-color:' . $primary_color . '}.main-small-navigation ul>.current-menu-item,.main-small-navigation ul>.current_page_item{background:' . $primary_color . '}.footer-menu a:hover,.footer-menu ul li.current-menu-ancestor a,.footer-menu ul li.current-menu-item a,.footer-menu ul li.current_page_ancestor a,.footer-menu ul li.current_page_item a,.footer-menu ul li:hover>a{color:' . $primary_color . '}#featured-slider .slider-read-more-button,.slider-title-head .entry-title a{background-color:' . $primary_color . '}a.slide-prev,a.slide-next,.slider-title-head .entry-title a{background-color:' . $primary_color . '}#controllers a.active,#controllers a:hover{background-color:' . $primary_color . ';color:' . $primary_color . '}.format-link .entry-content a{background-color:' . $primary_color . '}#secondary .widget_featured_single_post h3.widget-title a:hover,.widget_image_service_block .entry-title a:hover{color:' . $primary_color . '}.pagination span{background-color:' . $primary_color . '}.pagination a span:hover{color:' . $primary_color . ';border-color:' . $primary_color . '}#content .comments-area a.comment-edit-link:hover,#content .comments-area a.comment-permalink:hover,#content .comments-area article header cite a:hover,.comments-area .comment-author-link a:hover{color:' . $primary_color . '}.comments-area .comment-author-link span{background-color:' . $primary_color . '}#wp-calendar #today,.comment .comment-reply-link:hover,.nav-next a,.nav-previous a{color:' . $primary_color . '}.widget-title span{border-bottom:2px solid ' . $primary_color . '}#secondary h3 span:before,.footer-widgets-area h3 span:before{color:' . $primary_color . '}#secondary .accelerate_tagcloud_widget a:hover,.footer-widgets-area .accelerate_tagcloud_widget a:hover{background-color:' . $primary_color . '}.footer-widgets-area a:hover{color:' . $primary_color . '}.footer-socket-wrapper{border-top:3px solid ' . $primary_color . '}.footer-socket-wrapper .copyright a:hover{color:' . $primary_color . '}a#scroll-up{background-color:' . $primary_color . '}.entry-meta .byline i,.entry-meta .cat-links i,.entry-meta a,.post .entry-title a:hover{color:' . $primary_color . '}.entry-meta .post-format i{background-color:' . $primary_color . '}.entry-meta .comments-link a:hover,.entry-meta .edit-link a:hover,.entry-meta .posted-on a:hover,.main-navigation li.menu-item-has-children:hover,.entry-meta .tag-links a:hover{color:' . $primary_color . '}.more-link span,.read-more{background-color:' . $primary_color . '}.woocommerce #respond input#submit, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce #respond input#submit.alt, .woocommerce a.button.alt, .woocommerce button.button.alt, .woocommerce input.button.alt,.woocommerce span.onsale {background-color: ' . $primary_color . ';}.woocommerce ul.products li.product .price .amount,.entry-summary .price .amount,.woocommerce .woocommerce-message::before{color: ' . $primary_color . ';},.woocommerce .woocommerce-message { border-top-color: ' . $primary_color . ';}';
	}

	if ( ! empty( $accelerate_internal_css ) ) {
		?>
		<style type="text/css"><?php echo $accelerate_internal_css; ?></style>
		<?php
	}

	$accelerate_custom_css = accelerate_options( 'accelerate_custom_css' );
	if ( $accelerate_custom_css && ! function_exists( 'wp_update_custom_css_post' ) ) {
		?>
		<style type="text/css"><?php echo $accelerate_custom_css; ?></style>
		<?php
	}
}

/**************************************************************************************/

/**
 * Removing the more link jumping to middle of content
 */
function accelerate_remove_more_jump_link( $link ) {
	$offset = strpos( $link, '#more-' );
	if ( $offset ) {
		$end = strpos( $link, '"', $offset );
	}
	if ( $end ) {
		$link = substr_replace( $link, '', $offset, $end - $offset );
	}

	return $link;
}

add_filter( 'the_content_more_link', 'accelerate_remove_more_jump_link' );

/**************************************************************************************/

if ( ! function_exists( 'accelerate_content_nav' ) ) :
	/**
	 * Display navigation to next/previous pages when applicable
	 */
	function accelerate_content_nav( $nav_id ) {
		global $wp_query, $post;

		// Don't print empty markup on single pages if there's nowhere to navigate.
		if ( is_single() ) {
			$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
			$next     = get_adjacent_post( false, '', false );

			if ( ! $next && ! $previous ) {
				return;
			}
		}

		// Don't print empty markup in archives if there's only one page.
		if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) ) {
			return;
		}

		$nav_class = ( is_single() ) ? 'post-navigation' : 'paging-navigation';

		?>
		<nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo $nav_class; ?>">
			<h3 class="screen-reader-text"><?php _e( 'Post navigation', 'accelerate' ); ?></h3>

			<?php if ( is_single() ) : // navigation links for single posts ?>

				<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'accelerate' ) . '</span> %title' ); ?>
				<?php next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'accelerate' ) . '</span>' ); ?>

			<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

				<?php if ( get_next_posts_link() ) : ?>
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'accelerate' ) ); ?></div>
				<?php endif; ?>

				<?php if ( get_previous_posts_link() ) : ?>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'accelerate' ) ); ?></div>
				<?php endif; ?>

			<?php endif; ?>

		</nav><!-- #<?php echo esc_html( $nav_id ); ?> -->
		<?php
	}
endif; // accelerate_content_nav

/**************************************************************************************/

if ( ! function_exists( 'accelerate_comment' ) ) :
	/**
	 * Template for comments and pingbacks.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 */
	function accelerate_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
				<p><?php _e( 'Pingback:', 'accelerate' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'accelerate' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default :
				// Proceed with normal comments.
				global $post;
				?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<header class="comment-meta comment-author vcard">
						<?php
						echo get_avatar( $comment, 74 );
						printf( '<div class="comment-author-link"><i class="fa fa-user"></i>%1$s%2$s</div>',
							get_comment_author_link(),
							// If current post author is also comment author, make it known visually.
							( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'accelerate' ) . '</span>' : ''
						);
						printf( '<div class="comment-date-time"><i class="fa fa-calendar-o"></i>%1$s</div>',
							sprintf( __( '%1$s at %2$s', 'accelerate' ), get_comment_date(), get_comment_time() )
						);
						printf( '<a class="comment-permalink" href="%1$s"><i class="fa fa-link"></i>Permalink</a>', esc_url( get_comment_link( $comment->comment_ID ) ) );
						edit_comment_link();
						?>
					</header><!-- .comment-meta -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'accelerate' ); ?></p>
					<?php endif; ?>

					<section class="comment-content comment">
						<?php comment_text(); ?>
						<?php comment_reply_link( array_merge( $args, array(
							'reply_text' => __( 'Reply', 'accelerate' ),
							'after'      => '',
							'depth'      => $depth,
							'max_depth'  => $args['max_depth'],
						) ) ); ?>
					</section><!-- .comment-content -->

				</article><!-- #comment-## -->
				<?php
				break;
		endswitch; // end comment_type check
	}
endif;

/**************************************************************************************/

add_action( 'accelerate_footer_copyright', 'accelerate_footer_copyright', 10 );
/**
 * function to show the footer info, copyright information
 */
if ( ! function_exists( 'accelerate_footer_copyright' ) ) :
	function accelerate_footer_copyright() {
		$site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . get_bloginfo( 'name', 'display' ) . '</span></a>';

		$wp_link = '<a href="' . esc_url( 'http://wordpress.org' ) . '" target="_blank" title="' . esc_attr__( 'WordPress', 'accelerate' ) . '"><span>' . __( 'WordPress', 'accelerate' ) . '</span></a>';

		$tg_link = '<a href="' . esc_url( 'https://themegrill.com/themes/accelerate' ) . '" target="_blank" title="' . esc_attr__( 'ThemeGrill', 'accelerate' ) . '" rel="author"><span>' . __( 'ThemeGrill', 'accelerate' ) . '</span></a>';

		$default_footer_value = sprintf( __( 'Copyright &copy; %1$s %2$s.', 'accelerate' ), date( 'Y' ), $site_link ) . ' ' . sprintf( __( 'Powered by %s.', 'accelerate' ), $wp_link ) . ' ' . sprintf( __( 'Theme: %1$s by %2$s.', 'accelerate' ), 'Accelerate', $tg_link );

		$accelerate_footer_copyright = '<div class="copyright">' . $default_footer_value . '</div>';
		echo $accelerate_footer_copyright;
	}
endif;

/**************************************************************************************/
add_action( 'admin_init', 'accelerate_textarea_sanitization_change', 100 );
/**
 * Override the default textarea sanitization.
 */
function accelerate_textarea_sanitization_change() {
	remove_filter( 'of_sanitize_textarea', 'of_sanitize_textarea' );
	add_filter( 'of_sanitize_textarea', 'accelerate_sanitize_textarea_custom', 10, 2 );
}

/**
 * sanitize the input for custom css
 */
function accelerate_sanitize_textarea_custom( $input, $option ) {
	if ( $option['id'] == "accelerate_custom_css" ) {
		$output = wp_filter_nohtml_kses( $input );
	} else {
		$output = $input;
	}

	return $output;
}

/**
 * Making the theme Woocommrece compatible
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

add_filter( 'woocommerce_show_page_title', '__return_false' );

add_action( 'woocommerce_before_main_content', 'accelerate_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'accelerate_wrapper_end', 10 );

function accelerate_wrapper_start() {
	echo '<div id="primary">';
}

function accelerate_wrapper_end() {
	echo '</div>';
}

/**
 * Migrate any existing theme CSS codes added in Customize Options to the core option added in WordPress 4.7
 */
function accelerate_custom_css_migrate() {
	if ( function_exists( 'wp_update_custom_css_post' ) ) {
		$custom_css = accelerate_options( 'accelerate_custom_css' );
		if ( $custom_css ) {
			// assigning theme name
			$themename = get_option( 'stylesheet' );
			$themename = preg_replace( "/\W/", "_", strtolower( $themename ) );
			$core_css  = wp_get_custom_css(); // Preserve any CSS already added to the core option.
			$return    = wp_update_custom_css_post( $core_css . $custom_css );

			if ( ! is_wp_error( $return ) ) {

				$theme_options = get_option( $themename );

				// Remove the old theme_mod, so that the CSS is stored in only one place moving forward.
				foreach ( $theme_options as $option_key => $option_value ) {
					if ( in_array( $option_key, array( 'accelerate_custom_css' ) ) ) {
						unset( $theme_options[ $option_key ] );
					}
				}
				// Finally, update accelerate theme options.
				update_option( $themename, $theme_options );
			}
		}
	}
}

add_action( 'after_setup_theme', 'accelerate_custom_css_migrate' );

if ( ! function_exists( 'accelerate_related_posts_function' ) ) {

	/**
	 * Display the related posts
	 */
	function accelerate_related_posts_function() {
		wp_reset_postdata();
		global $post;

		// Define shared post arguments
		$args = array(
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => 1,
			'orderby'                => 'rand',
			'post__not_in'           => array( $post->ID ),
			'posts_per_page'         => 3,
		);

		// Related by categories.
		if ( accelerate_options( 'accelerate_related_posts', 'categories' ) == 'categories' ) {
			$cats = wp_get_post_categories( $post->ID, array( 'fields' => 'ids' ) );
			$args['category__in'] = $cats;
		}

		// Related by tags.
		if ( accelerate_options( 'accelerate_related_posts', 'categories' ) == 'tags' ) {
			$tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );
			$args['tag__in'] = $tags;

			// If no tags added, return.
			if (!$tags) {
				$break = true;
			}
		}
		$query = ! isset( $break ) ? new WP_Query( $args ) : new WP_Query;

		return $query;

	}
}
