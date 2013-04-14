(function($) {
$(document).ready(function() {

	var currentSlideGlobal = 0;

	jQuery.fn.setHeight = function() {

		var galleryheight = 0;

		$('.slide').each(function(index) {
			var imagewrap_height = $(this).find('.imagewrap').outerHeight();
			var longdesc_height = $(this).find('.longdescription').outerHeight();

			// This here is to accomodate slides where the description
			// floats beside the image. The CSS float doesn't kick in until
			// too late which causes the slideheight to be too tall, if measured
			// directly. Not a perfect solution, as it's assumed these elements
			// are floated and not stacked.
			
			if ( imagewrap_height > longdesc_height ) {
				var slideheight = imagewrap_height;
			} else {
				var slideheight = longdesc_height;
			}

			if ( slideheight > galleryheight ) {
				galleryheight = slideheight;
			}
			
		});

		$(this).height(galleryheight);

		var galleryheight = 0;

		$('.image').each(function(index) {
			var imageheight = $(this).outerHeight();
			$(this).find('.dragmask').height(imageheight);
			$(this).find('.cursorprev').height(imageheight);
			$(this).find('.cursornext').height(imageheight);
		});

	}


	jQuery.fn.setWidth = function() {

		var gallerywidth = 0;

		$('.slide').each(function(index) {
			var slidewidth = $(this).outerWidth();
			if ( slidewidth > gallerywidth ) gallerywidth = slidewidth;
		});

		if (gallerywidth !== 0 ) $('#gallerywrapper').width(gallerywidth);

		
	}


	// Set Up Controls
	jQuery.fn.setupControls = function() {
		$(this).wrap('<div id="gallerywrapper" />');
		
		var theControls = '<p id="slidecontrols">'
		theControls += '<a title="Previous" class="prev"><span>&larr;<span class="label"> Previous</span></span></a> &nbsp; ';


		if ( this.hasClass('hasthumbs') ) {
		 	theControls += '<a title="Thumbnails" class="thumbs"><span class="label">Thumbnails</span></a> &nbsp; '; 
		}

		theControls += '<a title="Next" class="next"><span><span class="label">Next </span>&rarr;</span></a>';
		theControls += '</p>';
		
		$(this).parent().after(theControls);		

		$('#nextgallery').hide();		

		$(this).find('.slide').each(function(index) {
			$(this).find('.image').prepend('<div class="dragmask"><a class="cursornext"></a></div');
			
			if ( $(this).is(':not(:first-child)') ) {
				$(this).find('.image .dragmask').prepend('<a class="cursorprev"></a>');
			}
				

		});

	}


	// Set Up Controls
	jQuery.fn.setupAutomatic = function() {
		$(this).wrap('<div id="gallerywrapper" />');
		cursornext
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

				if ( nextPosition != oldSlide ) {

					$(gallery).find('.slide').eq(oldSlide).find('img').fadeOut('medium', function() {
					    $(gallery).find('.slide').eq(oldSlide).css('display','none');		
					});

					$(gallery).find('.slide').eq(oldSlide).find('.description').fadeOut('medium', function() {});
					$(gallery).find('.slide').eq(oldSlide).fadeOut('medium', function() {});
				
				}

			    $(gallery).find('.slide').eq(nextPosition).fadeIn('medium');
				$(gallery).find('.slide').eq(nextPosition).find('img').fadeIn('medium');
				$(gallery).find('.slide').eq(nextPosition).find('.description').fadeIn('medium');

				nextPosition == maxPosition ? $(next).addClass('inactive') : $(next).removeClass('inactive');	
				nextPosition == 0 ? $(prev).addClass('inactive') : $(prev).removeClass('inactive');
				
			}
			
			$('.imagewrap').each(function() {
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
			if ( $('.gallery-fade').hasClass('automatic') ) {
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
					$('.slide').hide();
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
        function domo() {
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