(function($) {
$(document).ready(function() {

	var currentSlideGlobal = 0;

	jQuery.fn.setHeight = function() {

		var galleryheight = 0;

		$('.slide').each(function(index) {
			var slideheight = $(this).outerHeight();

			if ( slideheight > galleryheight ) {
				galleryheight = slideheight;

			}

		});

		$(this).height(galleryheight);

		var galleryheight = 0;

		$('.image').each(function(index) {
			var imageheight = $(this).outerHeight();
			$(this).find('.cursorprev').height(imageheight);
			$(this).find('.cursornext').height(imageheight);
		});

	}


	jQuery.fn.setWidth = function() {
		var gallerywidth = 0;

		$('.slide').each(function(index) {
			var imagewidth = $(this).outerWidth();
			if ( imagewidth > gallerywidth ) gallerywidth = imagewidth;
		});

		if (gallerywidth !== 0 ) $('#gallerywrapper').width(gallerywidth);

	}


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

		$('#nextgallery').hide();		

		$(this).find('.slide').each(function(index) {
			if ( $(this).is(':not(:first-child)') ) {
				$(this).find('.image').prepend('<a class="cursorprev"></a>');
			}
			//if ( $(this).is(':not(:last-child)') ) {
				$(this).find('.image').prepend('<a class="cursornext"></a>');				
			//}
		});

	}


	// Set Up Controls
	jQuery.fn.setupAutomatic = function() {
		$(this).wrap('<div id="gallerywrapper" />');
		
		$('#nextgallery').hide();		

	}




	// Fade Gallery
	jQuery.fn.fadeGallery = function() {

	  function animator() {

		function doTransition() {
			var nextPosition = currentSlideGlobal+1;
			manageGallery(nextPosition);
	  		animator();
		}

		window.setTimeout(doTransition, 4000, true);

	  };


		function manageGallery(nextPosition){
			
			function performChange(gallery, oldSlide) {

				next = $('#slidecontrols .next');
				prev = $('#slidecontrols .prev');

				$(gallery + '.slide:eq('+oldSlide+') img').fadeOut('medium', function() {
				    $(gallery + '.slide:eq('+oldSlide+')').css('display','none');		
				});

			    $(gallery + '.slide:eq('+nextPosition+')').css('display','block');
				$(gallery + '.slide:eq('+nextPosition+') img').fadeIn('medium');



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
					// console.log('last slide');				

					if ( gallery.hasClass('hasnext') ) {
						thehref = $('#nextgallery').attr('href');
						// console.log('has next gallery: ' + thehref);
						document.location.href=thehref;
					} else {
						nextPosition = 0;						
					}


				}

				currentSlideGlobal = nextPosition;

				performChange(gallery, oldSlide)

				
			}

		}

		var imageLength = $(this).find('.slide').length;

		$(this).find('.slide').hide();
		$(this).find('img').hide();
		
		manageGallery(0);

		$(window).load(function(){
			// console.log('ready 2');
			if ( $('.gallery-fade').hasClass('automatic') ) {
				// console.log('hasclass');
				animator();
			}

		});


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


		// The action on the image
		$('.cursornext').click(function(event) {
			var nextPosition = currentSlideGlobal+1;
			manageGallery(nextPosition);

		});
		$('.cursorprev').click(function(event) {
			var nextPosition = currentSlideGlobal-1;
			manageGallery(nextPosition);
		});
		

		// Keyboard action
        function domo(){
			jQuery(document).bind('keydown', 'right',function (evt){
				var nextPosition = currentSlideGlobal+1;
				manageGallery(nextPosition);
			});

			jQuery(document).bind('keydown', 'left',function (evt){
				nextPosition = currentSlideGlobal-1;
				manageGallery(nextPosition);
			});

        }
        
        
        jQuery(document).ready(domo);

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

	if ( theGallery.hasClass('automatic') ) {
		theGallery.setupAutomatic();				
	} else {
		theGallery.setupControls();						
	}

	theGallery.setHeight();
	theGallery.setWidth();
	
	theGallery.fadeGallery();		

});

})(jQuery);