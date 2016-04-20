<?php
/**
 * Displays the searchform of the theme.
 *
 * @package ThemeGrill
 * @subpackage Accelerate
 * @since Accelerate 1.0
 */
?>
<form action="<?php echo esc_url( home_url( '/' ) ); ?>"id="search-form" class="searchform clearfix" method="get">
	<input type="text" placeholder="<?php esc_attr_e( 'Search', 'accelerate' ); ?>" class="s field" name="s">
	<input type="submit" value="<?php esc_attr_e( 'Search', 'accelerate' ); ?>" id="search-submit" name="submit" class="submit">
</form><!-- .searchform -->