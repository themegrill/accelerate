<?php
/**
 * The Sidebar containing the footer widget areas.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */
?>

<?php
/**
 * The footer widget area is triggered if any of the areas
 * have widgets. So let's check that first.
 *
 * If none of the sidebars have widgets, then let's bail early.
 */
if( !is_active_sidebar( 'accelerate_footer_sidebar_one' ) &&
	!is_active_sidebar( 'accelerate_footer_sidebar_two' ) &&
	!is_active_sidebar( 'accelerate_footer_sidebar_three' ) ) {
	return;
}
?>
<div class="footer-widgets-wrapper">
	<div class="inner-wrap">
		<div class="footer-widgets-area clearfix">
			<div class="tg-one-third">
				<?php
				if ( !dynamic_sidebar( 'accelerate_footer_sidebar_one' ) ):
				endif;
				?>
			</div>
			<div class="tg-one-third">
				<?php
				if ( !dynamic_sidebar( 'accelerate_footer_sidebar_two' ) ):
				endif;
				?>
			</div>
			<div class="tg-one-third tg-one-third-last">
				<?php
				if ( !dynamic_sidebar( 'accelerate_footer_sidebar_three' ) ):
				endif;
				?>
			</div>
		</div>
	</div>
</div>