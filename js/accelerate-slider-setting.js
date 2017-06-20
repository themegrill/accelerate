/**
 * Slider Setting
 *
 * Contains all the slider settings for the featured post/page slider.
 */

var slides = jQuery('.slider-rotate').children().length;
if(slides <= 1) {
	jQuery('.slide-next, .slide-prev').css('display', 'none');
}

jQuery(window).load(function() {
jQuery('.slider-rotate').cycle({
	fx:					'fade',
	slides:				'> div',
	pager:				'> #controllers',
	prev:				'.slide-prev',
	next:				'.slide-next',
	pagerActiveClass:	'active',
	pagerTemplate:		'<a></a>',
	timeout:			4000,
	speed:				1000,
	pause:				1,
	pauseOnPagerHover: 	1,
	width:				'100%',
	containerResize: 	0,
	autoHeight:			'container',
	fit:				1,
	after: 				function ()	{
							jQuery(this).parent().css('height', jQuery(this).height());
						},
	cleartypeNoBg:		true,
	log:				false,
	swipe:				true

});

});
