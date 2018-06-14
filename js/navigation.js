/**
 * navigation.js
 *
 * Handles toggling the navigation menu for small screens.
 */
( function() {

	var container, button, menu;

	container = document.getElementById( 'site-navigation' );
	if ( ! container ) {
		return;
	}

	button = container.getElementsByTagName( 'h3' )[ 0 ];
	if ( 'undefined' === typeof button ) {
		return;
	}

	menu = container.getElementsByTagName( 'ul' )[ 0 ];

	// Hide menu toggle button if menu is empty and return early.
	if ( 'undefined' === typeof menu ) {
		button.style.display = 'none';
		return;
	}

	if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
		menu.className += 'nav-menu';
	}

	button.onclick = function() {
		if ( -1 !== container.className.indexOf( 'main-small-navigation' ) ) {
			container.className = container.className.replace( 'main-small-navigation', 'main-navigation' );
		} else {
			container.className = container.className.replace( 'main-navigation', 'main-small-navigation' );
		}
	};

} )();
jQuery( document ).ready( function() {

	jQuery( '#site-navigation .menu-item-has-children' ).append( '<span class="sub-toggle"> <i class="fa fa-caret-down"></i> </span>' );

	jQuery( '#site-navigation .sub-toggle' ).click( function() {

		jQuery( this ).parent( '.menu-item-has-children' ).children( 'ul.sub-menu' ).first().slideToggle( '1000' );
		jQuery( this ).children( '.fa-caret-right' ).first().toggleClass( 'fa-caret-down' );
		jQuery( this ).toggleClass( 'active' );

	} );

} );

// Show Submenu on click on touch enabled deviced
( function() {

	var container = document.getElementById( 'site-navigation' );

	/**
	 * Toggles `focus` class to allow submenu access on tablets.
	 */
	( function( container ) {

		var touchStartFn, i,
		    parentLink = container.querySelectorAll( '.menu-item-has-children > a, .page_item_has_children > a' );

		if ( 'ontouchstart' in window ) {
			touchStartFn = function( e ) {
				var menuItem = this.parentNode, i;

				if ( ! menuItem.classList.contains( 'focus' ) ) {
					e.preventDefault();
					for ( i = 0; i < menuItem.parentNode.children.length; ++i ) {
						if ( menuItem === menuItem.parentNode.children[ i ] ) {
							continue;
						}
						menuItem.parentNode.children[ i ].classList.remove( 'focus' );
					}
					menuItem.classList.add( 'focus' );
				} else {
					menuItem.classList.remove( 'focus' );
				}
			};

			for ( i = 0; i < parentLink.length; ++i ) {
				parentLink[ i ].addEventListener( 'touchstart', touchStartFn, false );
			}
		}

	}( container ) );

} )();

/**
 * Fix: menu out of viewport.
 */
( function() {

	// Create a custom function.
	jQuery.fn.isInViewport = function() {

		// Return if no valid element.
		if ( this.length < 1 )
			return;

		var subMenu = this;

		if ( 'function' === typeof jQuery && subMenu instanceof jQuery ) {
			subMenu = subMenu[ 0 ];
		}

		// In case browser doesn't support getBoundingClientRect function.
		if ( 'function' === typeof subMenu.getBoundingClientRect ) {

			var rect = subMenu.getBoundingClientRect(),
			    html = html || document.documentElement;

			if ( rect.right > ( window.innerWidth || html.clientWidth ) ) {
				return 'sub-menu--left'; // menu goes out of viewport from right.
			} else if ( rect.left < 0 ) {
				return 'sub-menu--right'; // menu goes out of viewport from left.
			} else {
				return false;
			}
		}

	};

	jQuery( window ).resize( function() {

		var subMenu,
		    menuItem = jQuery( '#site-navigation .menu-item-has-children, #site-navigation .page_item_has_children' );

		menuItem.hover( function() {

			subMenu = jQuery( this ).children( 'ul.sub-menu, ul.children' );

			var viewportClass = subMenu.isInViewport();

			if ( false !== viewportClass ) {
				subMenu.addClass( viewportClass );
			}

		}, function() {

			subMenu.removeClass( 'sub-menu--left sub-menu--right' );

		} );

	} ).resize();

} )();
