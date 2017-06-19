/*! swipe plugin for Cycle2;  version: 20121120 */
(function($) {
"use strict";

// this script adds support for touch events.  the logic is lifted from jQuery Mobile.
// if you have jQuery Mobile installed, you do NOT need this script

var supportTouch = 'ontouchend' in document;

$.event.special.swipe = $.event.special.swipe || {
    scrollSupressionThreshold: 10,   // More than this horizontal displacement, and we will suppress scrolling.
    durationThreshold: 1000,         // More time than this, and it isn't a swipe.
    horizontalDistanceThreshold: 30, // Swipe horizontal displacement must be more than this.
    verticalDistanceThreshold: 75,   // Swipe vertical displacement must be less than this.

    setup: function() {
        var $this = $( this );

        $this.bind( 'touchstart', function( event ) {
            var data = event.originalEvent.touches ? event.originalEvent.touches[ 0 ] : event;
            var stop, start = {
                time: ( new Date() ).getTime(),
                coords: [ data.pageX, data.pageY ],
                origin: $( event.target )
            };

            function moveHandler( event ) {
                if ( !start )
                    return;

                var data = event.originalEvent.touches ? event.originalEvent.touches[ 0 ] : event;

                stop = {
                    time: ( new Date() ).getTime(),
                    coords: [ data.pageX, data.pageY ]
                };

                // prevent scrolling
                if ( Math.abs( start.coords[ 0 ] - stop.coords[ 0 ] ) > $.event.special.swipe.scrollSupressionThreshold ) {
                    event.preventDefault();
                }
            }

            $this.bind( 'touchmove', moveHandler )
                .one( 'touchend', function( event ) {
                    $this.unbind( 'touchmove', moveHandler );

                    if ( start && stop ) {
                        if ( stop.time - start.time < $.event.special.swipe.durationThreshold &&
                                Math.abs( start.coords[ 0 ] - stop.coords[ 0 ] ) > $.event.special.swipe.horizontalDistanceThreshold &&
                                Math.abs( start.coords[ 1 ] - stop.coords[ 1 ] ) < $.event.special.swipe.verticalDistanceThreshold ) {

                            start.origin.trigger( "swipe" )
                                .trigger( start.coords[0] > stop.coords[ 0 ] ? "swipeleft" : "swiperight" );
                        }
                    }
                    start = stop = undefined;
                });
        });
    }
};

$.event.special.swipeleft = $.event.special.swipeleft || {
    setup: function() {
        $( this ).bind( 'swipe', $.noop );
    }
};
$.event.special.swiperight = $.event.special.swiperight || $.event.special.swipeleft;

})(jQuery);

/*! css3 flip transition plugin for Cycle2;  version: 20140128 */
/*! originally written by Laubeee (https://github.com/Laubeee) */
(function($) {
"use strict";

var backface,
    style = document.createElement('div').style,
    tx = $.fn.cycle.transitions,
    supported = style.transform !== undefined ||
        style.MozTransform !== undefined ||
        style.webkitTransform !== undefined ||
        style.oTransform !== undefined ||
        style.msTransform !== undefined;

if ( supported && style.msTransform !== undefined ) {
    style.msTransform = 'rotateY(0deg)';
    if ( ! style.msTransform )
        supported = false;
}

if ( supported ) {
    tx.flipHorz = getTransition( getRotate('Y') );
    tx.flipVert = getTransition( getRotate('X') );
    backface = {
        '-webkit-backface-visibility': 'hidden',
        '-moz-backface-visibility': 'hidden',
        '-o-backface-visibility': 'hidden',
        'backface-visibility': 'hidden'
    };
}
else {
    // fallback to scroll tx for browsers that don't support transforms
    tx.flipHorz = tx.scrollHorz;
    tx.flipVert = tx.scrollVert || tx.scrollHorz;
}


function getTransition( rotateFn ) {
    // return C2 transition object
    return {
        preInit: function( opts ) {
            opts.slides.css( backface );
        },
        transition: function( slideOpts, currEl, nextEl, fwd, callback ) {
            var opts = slideOpts,
                curr = $(currEl),
                next = $(nextEl),
                speed = opts.speed / 2;

            // css before transition start
            rotateFn.call(next, -90);
            next.css({
                'display': 'block',
                'visibility': 'visible',
                'background-position': '-90px',
                'opacity': 1
            });

            curr.css('background-position', '0px');

            curr.animate({ backgroundPosition: 90 }, {
                step: rotateFn,
                duration: speed,
                easing: opts.easeOut || opts.easing,
                complete: function() {
                    slideOpts.API.updateView( false, true );
                    next.animate({ backgroundPosition: 0 }, {
                        step: rotateFn,
                        duration: speed,
                        easing: opts.easeIn || opts.easing,
                        complete: callback
                    });
                }
            });
        }
    };
}

function getRotate( dir ) {
    return function( degrees ) {
        /*jshint validthis:true */
        var el = $(this);
        el.css({
            '-webkit-transform': 'rotate'+dir+'('+degrees+'deg)',
            '-moz-transform': 'rotate'+dir+'('+degrees+'deg)',
            '-ms-transform': 'rotate'+dir+'('+degrees+'deg)',
            '-o-transform': 'rotate'+dir+'('+degrees+'deg)',
            'transform': 'rotate'+dir+'('+degrees+'deg)'
        });
    };
}

})(jQuery);

/*! shuffle transition plugin for Cycle2;  version: 20140128 */
(function($) {
"use strict";

$.fn.cycle.transitions.shuffle = {

    transition: function( opts, currEl, nextEl, fwd, callback ) {
        $( nextEl ).css({
            display: 'block',
            visibility: 'visible'
        });
        var width = opts.container.css( 'overflow', 'visible' ).width();
        var speed = opts.speed / 2; // shuffle has 2 transitions
        var element = fwd ? currEl : nextEl;

        opts = opts.API.getSlideOpts( fwd ? opts.currSlide : opts.nextSlide );
        var props1 = { left:-width, top:15 };
        var props2 =  opts.slideCss || { left:0, top:0 };

        if ( opts.shuffleLeft !== undefined ) {
            props1.left = props1.left + parseInt(opts.shuffleLeft, 10) || 0;
        }
        else if ( opts.shuffleRight !== undefined ) {
            props1.left = width + parseInt(opts.shuffleRight, 10) || 0;
        }
        if ( opts.shuffleTop ) {
            props1.top = opts.shuffleTop;
        }

        // transition slide in 3 steps: move, re-zindex, move
        $( element )
            .animate( props1, speed, opts.easeIn || opts.easing )
            .queue( 'fx', $.proxy(reIndex, this))
            .animate( props2, speed, opts.easeOut || opts.easing, callback );

        function reIndex(nextFn) {
            /*jshint validthis:true */
            this.stack(opts, currEl, nextEl, fwd);
            nextFn();
        }
    },

    stack: function( opts, currEl, nextEl, fwd ) {
        var i, z;

        if (fwd) {
            opts.API.stackSlides( nextEl, currEl, fwd );
            // force curr slide to bottom of the stack
            $(currEl).css( 'zIndex', 1 );
        }
        else {
            z = 1;
            for (i = opts.nextSlide - 1; i >= 0; i--) {
                $(opts.slides[i]).css('zIndex', z++);
            }
            for (i = opts.slideCount - 1; i > opts.nextSlide; i--) {
                $(opts.slides[i]).css('zIndex', z++);
            }
            $(nextEl).css( 'zIndex', opts.maxZ );
            $(currEl).css( 'zIndex', opts.maxZ - 1 );
        }
    }
};

})(jQuery);

/*! tile transition plugin for Cycle2;  version: 20140128 */
(function ($) {
"use strict";

$.fn.cycle.transitions.tileSlide =
$.fn.cycle.transitions.tileBlind = {

    before: function( opts, curr, next, fwd ) {
        opts.API.stackSlides( curr, next, fwd );
        $(curr).css({
            display: 'block',
            visibility: 'visible'
        });
        opts.container.css('overflow', 'hidden');
        // set defaults
        opts.tileDelay = opts.tileDelay || opts.fx == 'tileSlide' ? 100 : 125;
        opts.tileCount = opts.tileCount || 7;
        opts.tileVertical = opts.tileVertical !== false;

        if (!opts.container.data('cycleTileInitialized')) {
            opts.container.on('cycle-destroyed', $.proxy(this.onDestroy, opts.API));
            opts.container.data('cycleTileInitialized', true);
        }
    },

    transition: function( opts, curr, next, fwd, callback ) {
        opts.slides.not(curr).not(next).css('visibility','hidden');

        var tiles = $();
        var $curr = $(curr), $next = $(next);
        var tile, tileWidth, tileHeight, lastTileWidth, lastTileHeight,
            num = opts.tileCount,
            vert = opts.tileVertical,
            height = opts.container.height(),
            width = opts.container.width();

        if ( vert ) {
            tileWidth = Math.floor(width / num);
            lastTileWidth = width - (tileWidth * (num - 1));
            tileHeight = lastTileHeight = height;
        }
        else {
            tileWidth = lastTileWidth = width;
            tileHeight = Math.floor(height / num);
            lastTileHeight = height - (tileHeight * (num - 1));
        }

        // opts.speed = opts.speed / 2;
        opts.container.find('.cycle-tiles-container').remove();

        var animCSS;
        var tileCSS = { left: 0, top: 0, overflow: 'hidden', position: 'absolute', margin: 0, padding: 0 };
        if ( vert ) {
            animCSS = opts.fx == 'tileSlide' ? { top: height } : { width: 0 };
        }
        else {
            animCSS = opts.fx == 'tileSlide' ? { left: width } : { height: 0 };
        }

        var tilesContainer = $('<div class="cycle-tiles-container"></div>');
        tilesContainer.css({
            zIndex: $curr.css('z-index'),
            overflow: 'visible',
            position: 'absolute',
            top: 0,
            left: 0,
            direction: 'ltr' // #250
        });
        tilesContainer.insertBefore( next );

        for (var i = 0; i < num; i++) {
            tile = $('<div></div>')
            .css( tileCSS )
            .css({
                width:  ((num - 1 === i) ? lastTileWidth  : tileWidth),
                height: ((num - 1 === i) ? lastTileHeight : tileHeight),
                marginLeft: vert ? ((i * tileWidth)) : 0,
                marginTop:  vert ? 0 : (i * tileHeight)
            })
            .append($curr.clone().css({
                position: 'relative',
                maxWidth: 'none',
                width: $curr.width(),
                margin: 0, padding: 0,
                marginLeft: vert ? -(i * tileWidth) : 0,
                marginTop: vert ? 0 : -(i * tileHeight)
            }));
            tiles = tiles.add(tile);
        }

        tilesContainer.append(tiles);
        $curr.css('visibility','hidden');
        $next.css({
            opacity: 1,
            display: 'block',
            visibility: 'visible'
        });
        animateTile(fwd ? 0 : num - 1);

        opts._tileAniCallback = function() {
            $next.css({
                display: 'block',
                visibility: 'visible'
            });
            $curr.css('visibility','hidden');
            tilesContainer.remove();
            callback();
        };

        function animateTile(i) {
            tiles.eq(i).animate( animCSS, {
                duration: opts.speed,
                easing: opts.easing,
                complete: function () {
                    if (fwd ? (num - 1 === i) : (0 === i)) {
                        opts._tileAniCallback();
                    }
                }
            });

            setTimeout(function () {
                if (fwd ? (num - 1 !== i) : (0 !== i)) {
                    animateTile(fwd ? (i + 1) : (i - 1));
                }
            }, opts.tileDelay);
        }
    },

    // tx API impl
    stopTransition: function( opts ) {
        opts.container.find('*').stop( true, true );
        if (opts._tileAniCallback)
            opts._tileAniCallback();
    },

    // core API supplement
    onDestroy: function( e ) {
        var opts = this.opts();
        opts.container.find('.cycle-tiles-container').remove();
    }
};

})(jQuery);

/*! carousel transition plugin for Cycle2;  version: 20130528 */
(function($) {
"use strict";

$( document ).on('cycle-bootstrap', function( e, opts, API ) {
    if ( opts.fx !== 'carousel' )
        return;

    API.getSlideIndex = function( el ) {
        var slides = this.opts()._carouselWrap.children();
        var i = slides.index( el );
        return i % slides.length;
    };

    // override default 'next' function
    API.next = function() {
        var count = opts.reverse ? -1 : 1;
        if ( opts.allowWrap === false && ( opts.currSlide + count ) > opts.slideCount - opts.carouselVisible )
            return;
        opts.API.advanceSlide( count );
        opts.API.trigger('cycle-next', [ opts ]).log('cycle-next');
    };

});


$.fn.cycle.transitions.carousel = {
    // transition API impl
    preInit: function( opts ) {
        opts.hideNonActive = false;

        opts.container.on('cycle-destroyed', $.proxy(this.onDestroy, opts.API));
        // override default API implementation
        opts.API.stopTransition = this.stopTransition;

        // issue #10
        for (var i=0; i < opts.startingSlide; i++) {
            opts.container.append( opts.slides[0] );
        }
    },

    // transition API impl
    postInit: function( opts ) {
        var i, j, slide, pagerCutoffIndex, wrap;
        var vert = opts.carouselVertical;
        if (opts.carouselVisible && opts.carouselVisible > opts.slideCount)
            opts.carouselVisible = opts.slideCount - 1;
        var visCount = opts.carouselVisible || opts.slides.length;
        var slideCSS = { display: vert ? 'block' : 'inline-block', position: 'static' };

        // required styles
        opts.container.css({ position: 'relative', overflow: 'hidden' });
        opts.slides.css( slideCSS );

        opts._currSlide = opts.currSlide;

        // wrap slides in a div; this div is what is animated
        wrap = $('<div class="cycle-carousel-wrap"></div>')
            .prependTo( opts.container )
            .css({ margin: 0, padding: 0, top: 0, left: 0, position: 'absolute' })
            .append( opts.slides );

        opts._carouselWrap = wrap;

        if ( !vert )
            wrap.css('white-space', 'nowrap');

        if ( opts.allowWrap !== false ) {
            // prepend and append extra slides so we don't see any empty space when we
            // near the end of the carousel.  for fluid containers, add even more clones
            // so there is plenty to fill the screen
            // @todo: optimzie this based on slide sizes

            for ( j=0; j < (opts.carouselVisible === undefined ? 2 : 1); j++ ) {
                for ( i=0; i < opts.slideCount; i++ ) {
                    wrap.append( opts.slides[i].cloneNode(true) );
                }
                i = opts.slideCount;
                while ( i-- ) { // #160, #209
                    wrap.prepend( opts.slides[i].cloneNode(true) );
                }
            }

            wrap.find('.cycle-slide-active').removeClass('cycle-slide-active');
            opts.slides.eq(opts.startingSlide).addClass('cycle-slide-active');
        }

        if ( opts.pager && opts.allowWrap === false ) {
            // hide "extra" pagers
            pagerCutoffIndex = opts.slideCount - visCount;
            $( opts.pager ).children().filter( ':gt('+pagerCutoffIndex+')' ).hide();
        }

        opts._nextBoundry = opts.slideCount - opts.carouselVisible;

        this.prepareDimensions( opts );
    },

    prepareDimensions: function( opts ) {
        var dim, offset, pagerCutoffIndex, tmp, j;
        var vert = opts.carouselVertical;
        var visCount = opts.carouselVisible || opts.slides.length;

        if ( opts.carouselFluid && opts.carouselVisible ) {
            if ( ! opts._carouselResizeThrottle ) {
            // fluid container AND fluid slides; slides need to be resized to fit container
                this.fluidSlides( opts );
            }
        }
        else if ( opts.carouselVisible && opts.carouselSlideDimension ) {
            dim = visCount * opts.carouselSlideDimension;
            opts.container[ vert ? 'height' : 'width' ]( dim );
        }
        else if ( opts.carouselVisible ) {
            dim = visCount * $(opts.slides[0])[vert ? 'outerHeight' : 'outerWidth'](true);
            opts.container[ vert ? 'height' : 'width' ]( dim );
        }
        // else {
        //     // fluid; don't size the container
        // }

        offset = ( opts.carouselOffset || 0 );
        if ( opts.allowWrap !== false ) {
            if ( opts.carouselSlideDimension ) {
                offset -= ( (opts.slideCount + opts.currSlide) * opts.carouselSlideDimension );
            }
            else {
                // calculate offset based on actual slide dimensions
                tmp = opts._carouselWrap.children();
                for (j=0; j < (opts.slideCount + opts.currSlide); j++) {
                    offset -= $(tmp[j])[vert?'outerHeight':'outerWidth'](true);
                }
            }
        }

        opts._carouselWrap.css( vert ? 'top' : 'left', offset );
    },

    fluidSlides: function( opts ) {
        var timeout;
        var slide = opts.slides.eq(0);
        var adjustment = slide.outerWidth() - slide.width();
        var prepareDimensions = this.prepareDimensions;

        // throttle resize event
        $(window).on( 'resize', resizeThrottle);

        opts._carouselResizeThrottle = resizeThrottle;
        onResize();

        function resizeThrottle() {
            clearTimeout( timeout );
            timeout = setTimeout( onResize, 20 );
        }

        function onResize() {
            opts._carouselWrap.stop( false, true );
            var slideWidth = opts.container.width() / opts.carouselVisible;
            slideWidth = Math.ceil( slideWidth - adjustment );
            opts._carouselWrap.children().width( slideWidth );
            if ( opts._sentinel )
                opts._sentinel.width( slideWidth );
            prepareDimensions( opts );
        }
    },

    // transition API impl
    transition: function( opts, curr, next, fwd, callback ) {
        var moveBy, props = {};
        var hops = opts.nextSlide - opts.currSlide;
        var vert = opts.carouselVertical;
        var speed = opts.speed;

        // handle all the edge cases for wrapping & non-wrapping
        if ( opts.allowWrap === false ) {
            fwd = hops > 0;
            var currSlide = opts._currSlide;
            var maxCurr = opts.slideCount - opts.carouselVisible;
            if ( hops > 0 && opts.nextSlide > maxCurr && currSlide == maxCurr ) {
                hops = 0;
            }
            else if ( hops > 0 && opts.nextSlide > maxCurr ) {
                hops = opts.nextSlide - currSlide - (opts.nextSlide - maxCurr);
            }
            else if ( hops < 0 && opts.currSlide > maxCurr && opts.nextSlide > maxCurr ) {
                hops = 0;
            }
            else if ( hops < 0 && opts.currSlide > maxCurr ) {
                hops += opts.currSlide - maxCurr;
            }
            else
                currSlide = opts.currSlide;

            moveBy = this.getScroll( opts, vert, currSlide, hops );
            opts.API.opts()._currSlide = opts.nextSlide > maxCurr ? maxCurr : opts.nextSlide;
        }
        else {
            if ( fwd && opts.nextSlide === 0 ) {
                // moving from last slide to first
                moveBy = this.getDim( opts, opts.currSlide, vert );
                callback = this.genCallback( opts, fwd, vert, callback );
            }
            else if ( !fwd && opts.nextSlide == opts.slideCount - 1 ) {
                // moving from first slide to last
                moveBy = this.getDim( opts, opts.currSlide, vert );
                callback = this.genCallback( opts, fwd, vert, callback );
            }
            else {
                moveBy = this.getScroll( opts, vert, opts.currSlide, hops );
            }
        }

        props[ vert ? 'top' : 'left' ] = fwd ? ( "-=" + moveBy ) : ( "+=" + moveBy );

        // throttleSpeed means to scroll slides at a constant rate, rather than
        // a constant speed
        if ( opts.throttleSpeed )
            speed = (moveBy / $(opts.slides[0])[vert ? 'height' : 'width']() ) * opts.speed;

        opts._carouselWrap.animate( props, speed, opts.easing, callback );
    },

    getDim: function( opts, index, vert ) {
        var slide = $( opts.slides[index] );
        return slide[ vert ? 'outerHeight' : 'outerWidth'](true);
    },

    getScroll: function( opts, vert, currSlide, hops ) {
        var i, moveBy = 0;

        if (hops > 0) {
            for (i=currSlide; i < currSlide+hops; i++)
                moveBy += this.getDim( opts, i, vert);
        }
        else {
            for (i=currSlide; i > currSlide+hops; i--)
                moveBy += this.getDim( opts, i, vert);
        }
        return moveBy;
    },

    genCallback: function( opts, fwd, vert, callback ) {
        // returns callback fn that resets the left/top wrap position to the "real" slides
        return function() {
            var pos = $(opts.slides[opts.nextSlide]).position();
            var offset = 0 - pos[vert?'top':'left'] + (opts.carouselOffset || 0);
            opts._carouselWrap.css( opts.carouselVertical ? 'top' : 'left', offset );
            callback();
        };
    },

    // core API override
    stopTransition: function() {
        var opts = this.opts();
        opts.slides.stop( false, true );
        opts._carouselWrap.stop( false, true );
    },

    // core API supplement
    onDestroy: function( e ) {
        var opts = this.opts();
        if ( opts._carouselResizeThrottle )
            $( window ).off( 'resize', opts._carouselResizeThrottle );
        opts.slides.prependTo( opts.container );
        opts._carouselWrap.remove();
    }
};

})(jQuery);
