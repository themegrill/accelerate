<?php
/**
 * The template used for displaying blog small image post.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php do_action( 'accelerate_before_post_content' ); ?>
	<header class="entry-header">
		<h2 class="entry-title">
			<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute();?>"><?php the_title(); ?></a>
		</h2>
	</header>

	<?php accelerate_entry_meta(); ?>

	<div class="post-image-content-wrap clearfix">
		<?php
			if( has_post_thumbnail() ) {
				$image = '';
	     		$title_attribute = get_the_title( $post->ID );
	     		$image .= '<figure class="post-featured-image">';
	  			$image .= '<a href="' . get_permalink() . '" title="'.the_title_attribute( 'echo=0' ).'">';
	  			$image .= get_the_post_thumbnail( $post->ID, 'featured-blog-small', array( 'title' => esc_attr( $title_attribute ), 'alt' => esc_attr( $title_attribute ) ) ).'</a>';
	  			$image .= '</figure>';
	  			echo $image;
	  		}
		?>

		<div class="entry-content clearfix">
			<?php
				global $more;
				$more = 0;
				the_content( '<span>'.__( 'Read more', 'accelerate' ).'</span>' );
			?>
		</div>
	</div>

	<?php do_action( 'accelerate_after_post_content' ); ?>
</article>