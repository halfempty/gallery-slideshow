(function($) {
	$(document).ready(function() {


		function manageOptions(){

			$('.gsgallery_thumbnails').hide();

			var target = $('#gsgallery_mode option:selected').val();

			if(target == "normal") {
				$('.gsgallery_thumbnails').show();
			}

		}

		manageOptions()

		$('#gsgallery_mode').change(function() {
			manageOptions()
		});
		
	});
})(jQuery);