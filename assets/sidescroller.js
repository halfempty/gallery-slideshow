(function($) {
	$(document).ready(function() {

//		var isiPad = navigator.userAgent.match(/iPad/i) != null;

		$(function(){
		  $('.category').masonry({
		    // options
		    itemSelector : '.thumbnail',
		    columnWidth : 280
		  });
		});


		var currentSlideGlobal = 0;


		// Scroll Gallery
		jQuery.fn.scrollGallery = function() {
			//console.log('doing scrollGallery');


			$(this).wrap('<div id="scrollwrapper" />');

			var slideWidth = 0;
			$('.column').each(function() {
			    slideWidth += $(this).outerWidth( true );
			});

			$(this).css('width', slideWidth);
//			if (!isiPad) $('#scrollwrapper').append( $('.gallerycontent') );


		}// end Scroll Gallery


		// resizeWrapper
		// function resizeWrapper() {
		// 
		// 
		// 	if (!isiPad) {
		// 
		// 	var windowheight = $(window).height();
		// 	
		// 	var adminbar = $('#wpadminbar').outerHeight();
		// 
		// 	var headerheight = $('#header').outerHeight();
		// 	
		// 	var adjustedheight = windowheight - headerheight - adminbar - 2;
		// 
		// 	$('#scrollwrapper').height(adjustedheight);
		// 
		// 	}
		// 
		// }// end resizeWrapper


		var theGallery = $('.column:not(:only-child)').parent();

		if ( theGallery.hasClass('scrollgallery') ) {
			theGallery.scrollGallery();		
			resizeWrapper();
			$(window).resize(function() {
				resizeWrapper();
			});

		} 

	});
})(jQuery);