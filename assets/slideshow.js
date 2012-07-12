(function($) {
$(document).ready(function() {

	var isiPad = navigator.userAgent.match(/iPad/i) != null;

	var currentSlideGlobal = 0;


	// Set Up Controls
	jQuery.fn.setupControls = function() {
		$(this).wrap('<div id="gallerywrapper" />');
		
		var theControls = '<p id="slidecontrols">'
		theControls += '<a title="Previous" class="prev"><span>&larr; Previous</span></a> &nbsp; ';


		if ( this.hasClass('hasthumbs') ) {
		 	theControls += '<a title="Thumbnails" class="thumbs"><span>Thumbnails</span></a> &nbsp; '; 
		}

		theControls += '<a title="Next" class="next"><span>Next &rarr;</span></a>';
		theControls += '</p>';
		
		$(this).parent().after(theControls);		


		console.log('controls setup');
	}

	// Fade Gallery
	jQuery.fn.fadeGallery = function() {
		//console.log('doing fadeGallery');

		function manageGallery(nextPosition){

			function performChange(gallery, oldSlide) {

				next = $('#slidecontrols .next');
				prev = $('#slidecontrols .prev');

				$(gallery + '.slide:eq('+oldSlide+') img').fadeOut('fast', function() {
				    $(gallery + '.slide:eq('+oldSlide+')').css('display','none');		

				    $(gallery + '.slide:eq('+nextPosition+')').css('display','block');
					$(gallery + '.slide:eq('+nextPosition+') img').fadeIn();

				});

				nextPosition == maxPosition ? $(next).addClass('inactive') : $(next).removeClass('inactive');	
				nextPosition == 0 ? $(prev).addClass('inactive') : $(prev).removeClass('inactive');
				
			}
			
			$('.image').each(function() {
				var imagewidth = $(this).find('img').outerWidth();
				$(this).width(imagewidth);
			});

			gallery = $('.gallery-fade');
			maxPosition = gallery.find('.slide').length -1;


			if (nextPosition >= 0) {

				var oldSlide = currentSlideGlobal;

				if ( nextPosition > maxPosition ) {
 					// We were on last slide
					nextPosition = 0;
					console.log('last slide');
				}

				currentSlideGlobal = nextPosition;

				performChange(gallery, oldSlide)

				
			}

		}

		var imageLength = $(this).find('.slide').length;

		$(this).find('.slide').hide();
		$(this).find('img').hide();
		
		manageGallery(0);

		// The action on the controls
		$('#slidecontrols a').click(function(event) {

			var theAttribute = $(this).attr('title');

			if ( theAttribute == 'Next' ) {
				var nextPosition = currentSlideGlobal+1;
				manageGallery(nextPosition);
			} else if ( theAttribute == 'Previous' ) {
				nextPosition = currentSlideGlobal-1;
				manageGallery(nextPosition);
			} else if ( theAttribute == 'Thumbnails' ) {				
				// Show thumbs
				$('#slidecontrols').fadeOut('fast');
				$('#gallerywrapper').fadeOut('fast', function() {
				    // Animation complete.
					$('.thumbnails').fadeIn();
				  });

			} else {
				// This is an error
			}

		});

		// The action on the images
		$('.slide img').click(function(event) {
			var nextPosition = currentSlideGlobal+1;
			manageGallery(nextPosition);

		});

		// The action on the thumbs
		$('.thumbnails .thumb').click(function(event) {

			var whichimage = $(this).index();
			manageGallery(whichimage);

			$('.thumbnails').fadeOut('fast', function() {
			    // Animation complete.
				$('#gallerywrapper').fadeIn();
				$('#slidecontrols').fadeIn();
			  });

		});

	}// end Fade Gallery


	var theGallery = $('.slide:not(:only-child)').parent();

	theGallery.setupControls();		
	theGallery.fadeGallery();		



});
})(jQuery);