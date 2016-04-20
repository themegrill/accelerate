<?php 
/**
 * Theme Footer Section for our theme.
 * 
 * Displays all of the footer section and closing of the #main div.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */
?>

		</div><!-- .inner-wrap -->
	</div><!-- #main -->	
	<?php do_action( 'accelerate_before_footer' ); ?>
		<footer id="colophon" class="clearfix">	
			<?php get_sidebar( 'footer' ); ?>	
			<div class="footer-socket-wrapper clearfix">
				<div class="inner-wrap">
					<div class="footer-socket-area">
						<?php do_action( 'accelerate_footer_copyright' ); ?>
						<nav class="footer-menu" class="clearfix">
							<?php
								if ( has_nav_menu( 'footer' ) ) {									
										wp_nav_menu( array( 'theme_location' => 'footer',
																 'depth'           => -1
																 ) );
								}
							?>
		    			</nav>
					</div>
				</div>
			</div>			
		</footer>
		<a href="#masthead" id="scroll-up"><i class="fa fa-long-arrow-up"></i></a>	
	</div><!-- #page -->
	<?php wp_footer(); ?>
</body>
</html>