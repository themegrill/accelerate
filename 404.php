<?php
/**
 * The template for displaying 404 pages (Page Not Found).
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */
?>

<?php get_header(); ?>

	<?php do_action( 'accelerate_before_body_content' ); ?>

	<div id="primary">
		<div id="content" class="clearfix">
			<section class="error-404 not-found">
				<div class="page-content">

					<?php if ( ! dynamic_sidebar( 'accelerate_error_404_page_sidebar' ) ) : ?>				
						<header class="page-header">
							<h1 class="page-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'accelerate' ); ?></h1>
						</header>
						<p><?php _e( 'It looks like nothing was found at this location. Try the search below.', 'accelerate' ); ?></p>
						<?php get_search_form(); ?>
					<?php endif; ?>

				</div><!-- .page-content -->
			</section><!-- .error-404 -->
		</div><!-- #content -->
	</div><!-- #primary -->

	<?php accelerate_sidebar_select(); ?>

	<?php do_action( 'accelerate_after_body_content' ); ?>

<?php get_footer(); ?>