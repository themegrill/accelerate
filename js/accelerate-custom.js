jQuery( document ).ready( function () {
	jQuery( '#scroll-up' ).hide();
	jQuery( function () {
		jQuery( window ).scroll( function () {
			if ( jQuery( this ).scrollTop() > 1000 ) {
				jQuery( '#scroll-up' ).fadeIn();
			} else {
				jQuery( '#scroll-up' ).fadeOut();
			}
		} );
		jQuery( 'a#scroll-up' ).click( function () {
			jQuery( 'body,html' ).animate( {
				scrollTop : 0
			}, 800 );
			return false;
		} );
	} );
} );

/**
 * Slider Setting
 *
 * Contains all the slider settings for the featured post/page slider.
 */

var slides = jQuery( '.slider-rotate' ).children().length;
if ( slides <= 1 ) {
	jQuery( '.slide-next, .slide-prev' ).css( 'display', 'none' );
}

jQuery( window ).load( function () {

	if ( typeof jQuery.fn.cycle !== 'undefined' ) {
		jQuery( '.slider-rotate' ).cycle( {
			fx                : 'fade',
			slides            : '> div',
			pager             : '> #controllers',
			prev              : '.slide-prev',
			next              : '.slide-next',
			pagerActiveClass  : 'active',
			pagerTemplate     : '<a></a>',
			timeout           : 4000,
			speed             : 1000,
			pause             : 1,
			pauseOnPagerHover : 1,
			width             : '100%',
			containerResize   : 0,
			autoHeight        : 'container',
			fit               : 1,
			after             : function () {
				jQuery( this ).parent().css( 'height', jQuery( this ).height() );
			},
			cleartypeNoBg     : true,
			log               : false,
			swipe             : true

		} );
	}

} );

