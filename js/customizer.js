/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

(function ( $ ) {
	// Site title
	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( to ) {
			$( '#site-title a' ).text( to );
		} );
	} );

	// Site description.
	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( to ) {
			$( '#site-description' ).text( to );
		} );
	} );

	// Site layout
	wp.customize( 'accelerate[accelerate_site_layout]', function ( value ) {
		value.bind( function ( layout ) {
			var layout_options = layout;
			if ( layout_options == 'wide' ) {
				$( 'body' ).addClass( 'wide' );
			} else if( layout == 'box' ) {
				$( 'body' ).removeClass( 'wide' );
			}
		});
	});

})( jQuery );