<?php
/**
 * The left sidebar widget area.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */
?>

<div id="secondary">
	<?php do_action( 'accelerate_before_sidebar' ); ?>
		<?php 
			if( is_page_template( 'page-templates/contact.php' ) ) {
				$sidebar = 'accelerate_contact_page_sidebar';
			}
			else {
				$sidebar = 'accelerate_left_sidebar';
			}
		?>

		<?php if ( ! dynamic_sidebar( $sidebar ) ) : ?>

			<aside id="search" class="widget widget_search">
				<?php get_search_form(); ?>
			</aside>

			<aside id="archives" class="widget">
				<h3 class="widget-title"><span><?php _e( 'Archives', 'accelerate' ); ?></span></h3>
				<ul>
					<?php wp_get_archives( array( 'type' => 'monthly' ) ); ?>
				</ul>
			</aside>

			<aside id="meta" class="widget">
				<h3 class="widget-title"><span><?php _e( 'Meta', 'accelerate' ); ?></span></h3>
				<ul>
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<?php wp_meta(); ?>
				</ul>
			</aside>

		<?php endif; ?>
	<?php do_action( 'accelerate_after_sidebar' ); ?>
</div>