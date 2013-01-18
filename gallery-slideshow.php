<?php
/*
Plugin Name: Gallery Slideshow
Version: September 15 2012
Author: Marty Spellerberg
*/

function gs_gallery_scripts_method() {

    $gsStyle = plugins_url( 'assets/slideshow.css' , __FILE__ ); // Static
    wp_register_style('gsStyle',$gsStyle);
    wp_enqueue_style( 'gsStyle');

	wp_enqueue_script( 'jquery');

	// Hotkeys
	$jquery_hotkeys = plugins_url( '/assets/jquery.hotkeys.js' , __FILE__ );
	wp_register_script('jquery_hotkeys',$jquery_hotkeys);
	wp_enqueue_script( 'jquery_hotkeys',array('jquery'));

	// Slideshow
	$gsgalleryjs = plugins_url( '/assets/slideshow.js' , __FILE__ );
	wp_register_script('gsgalleryjs',$gsgalleryjs);
	wp_enqueue_script( 'gsgalleryjs',array('jquery','jquery_hotkeys'));

}

add_action('wp_enqueue_scripts', 'gs_gallery_scripts_method');


// Custom WordPress Meta Box
// http://www.farinspace.com/how-to-create-custom-wordpress-meta-box/


add_action('admin_init','gs_meta_init');

function gs_meta_init() {
	// http://codex.wordpress.org/Function_Reference/add_meta_box

	$gsgalleryadminjs = plugins_url( '/gallery-slideshow.js' , __FILE__ );
	wp_register_script('gsgalleryadminjs',$gsgalleryadminjs);
	wp_enqueue_script( 'gsgalleryadminjs',array('jquery'));


	foreach (array('post','page') as $type) {
		add_meta_box('gs_all_meta', 'Gallery Slideshow', 'gs_meta_setup', $type, 'side', 'default');
	}
	
	add_action('save_post','gs_meta_save');
}

function gs_meta_setup() {
	global $post;

	// TODO: For backwards compatiblity we need to check _martygallery in addition to _gsgallery

	$meta = get_post_meta($post->ID,'_gsgallery',TRUE);

	if ( $meta[transition] == 'slide') {
		$transition = 'slide';
	} else {
		$transition = 'fade';		
	}

	echo '<p><strong>Mode</strong></p><p><select id="gsgallery_mode" name="_gsgallery[mode]">';

	echo '<option value="normal" ';
		if( $meta['mode'] == 'normal' || !$meta['mode'] || $meta['mode'] == '' ) echo 'selected="selected"';
	echo ' />Normal</option>';

	echo '<option value="automatic" ';
		if( $meta['mode'] == 'automatic' ) echo 'selected="selected"';
	echo ' />Automatic</option>';

	echo '</select></p>';




	$metathumbs = get_post_meta($post->ID,'_gsgallery[thumbs]',TRUE);

	echo '<p><strong>Options</strong></p> <p>Thumbnails: &nbsp; ';

	echo '<input type="radio" name="_gsgallery[thumbs]" value="on" ';
	if( $meta[thumbs] !== 'off' ) echo 'checked="checked"';
	echo ' /> On &nbsp; ';

	echo '<input type="radio" name="_gsgallery[thumbs]" value="off" ';
	if( $meta[thumbs] == 'off' ) echo 'checked="checked"';
	echo ' /> Off &nbsp; </p>';


	echo '<label for="gsgallery_hidecaption"><input type="checkbox" id="gsgallery_hidecaption" name="_gsgallery[hidecaption]" value="yes"';
		if( $meta['hidecaption'] == true ) echo 'checked="checked"';	
	echo '> Hide image captions</label>';

	echo '<div class="gsgallery_hidefeatured"><label for="gsgallery_hidefeatured"><input type="checkbox" id="gsgallery_hidefeatured" name="_gsgallery[hidefeatured]" value="yes"';
		if( $meta['hidefeatured'] == true ) echo 'checked="checked"';	
	echo '> Exclude Featured Image</label></div>';

/*
	echo ' &nbsp; &nbsp;  Next gallery after last slide: &nbsp; ';

	echo '<input type="radio" name="_gsgallery[next]" value="on" ';
	if( $meta[next] == 'on' ) echo 'checked="checked"';
	echo ' /> On &nbsp; ';

	echo '<input type="radio" name="_gsgallery[next]" value="off" ';
	if( $meta[next] !== 'on' ) echo 'checked="checked"';
	echo ' /> Off &nbsp; ';


	echo '</p>';
 */
	// create a custom nonce for submit verification later
	echo '<input type="hidden" name="gs_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
}
 
function gs_meta_save($post_id) {
	if (!wp_verify_nonce($_POST['gs_meta_noncename'],__FILE__)) return $post_id;
	if ($_POST['post_type'] == 'page') {
		if (!current_user_can('edit_page', $post_id)) return $post_id;
	} else {
		if (!current_user_can('edit_post', $post_id)) return $post_id;
	}

	$current_data = get_post_meta($post_id, '_gsgallery', TRUE);	
 
	$new_data = $_POST['_gsgallery'];

	gs_meta_clean($new_data);
	
	if ($current_data) {
		if (is_null($new_data)) delete_post_meta($post_id,'_gsgallery');
		else update_post_meta($post_id,'_gsgallery',$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,'_gsgallery',$new_data,TRUE);
	}

	return $post_id;
}

function gs_meta_clean(&$arr)
{
	if (is_array($arr))
	{
		foreach ($arr as $i => $v)
		{
			if (is_array($arr[$i])) 
			{
				gs_meta_clean($arr[$i]);

				if (!count($arr[$i])) 
				{
					unset($arr[$i]);
				}
			}
			else 
			{
				if (trim($arr[$i]) == '') 
				{
					unset($arr[$i]);
				}
			}
		}

		if (!count($arr)) 
		{
			$arr = NULL;
		}
	}
}



// Custom Galley
// http://www.wpoutfitters.com/2011/01/wordpress-image-attachment-gallery-revisited/

function gs_get_images($post_id) {
	global $post;

	$images = get_children( array('post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );

	if ($images) :

		$gsgallery = get_post_meta($post_id, '_gsgallery', true);

		$hidecaption = $gsgallery['hidecaption'];

		$hidefeatured = $gsgallery['hidefeatured'];

		if ( $hidefeatured  == true ) {

			$thumbnail_ID = get_post_thumbnail_id();

			unset( $images[$thumbnail_ID] );

		}


		// Setup options
		if ( empty($gsgallery['thumbs']) || $gsgallery['thumbs'] == 'on' ) $thumboption = ' hasthumbs';
		if ( $gsgallery['next'] == 'on' ) $nextoption = ' hasnext'; 	


		if ( $gsgallery['mode'] == 'automatic' ) {
			echo '<div class="gallery-fade automatic">';		
		} else {
			echo '<div class="gallery-fade controls' . $thumboption . $nextoption . '">';			
		}

		foreach ($images as $attachment_id => $image) :
	

			$img_title = $image->post_title;   // title.
			$img_caption = $image->post_excerpt; // caption.
			$img_description = $image->post_content; // description.

			$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true); //alt
			if ($img_alt == '') : $img_alt = $img_title; endif;
				
			$big_array = image_downsize( $image->ID, 'large' );
	 		$img_url = $big_array[0];
			$img_width = $big_array[1];
			$img_height = $big_array[2];
			
			
			?>

			<div class="slide">
				<div class="slideinner" style="width: <?php echo $img_width; ?>px">
					<div class="image" >
						<p><img src="<?php echo $img_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" width="<?php echo $img_width; ?>" height="<?php echo $img_height; ?>" /></p>
					</div>

					<?php if ($hidecaption != true ) { ?>
					<div  class="description">
						<?php if ($img_caption) : echo wpautop($img_caption); endif; ?>
						<?php if ($img_description) : echo wpautop($img_description); endif; ?>
					</div>
					<?php } ?>

				</div>
			</div>

		<?php endforeach; ?>

		</div><!-- End gallery -->

		<?php if ( $thumboption == ' hasthumbs' ) : ?>

			<div class="thumbnails" style="display: none;">

				<?php 

				foreach ($images as $attachment_id => $image) :

					$thumb_title = $image->post_title;   // title.
					$thumb_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true); //alt
					if ($thumb_alt == '') : $thumb_alt = $thumb_title; endif;

					$thumb_array = image_downsize( $image->ID, 'thumbnail' );
	 				$thumb_url = $thumb_array[0];

				?>

					<div class="thumb"><img src="<?php echo $thumb_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" /></div>

				<?php endforeach; ?>

			</div>		

		<?php endif;?>

		<?php if ( $nextoption == ' hasnext' ) : ?>
				<?php 
				
				$posttype = get_post_type( $post_id );
			
			
			if ( $posttype == "post" ) {
				// Post type: post";				

			} else if ( $posttype == "page") {
				// Post type: page";

				$pagelist = get_pages('sort_column=menu_order&sort_order=asc');
				$pages = array();
				foreach ($pagelist as $page) {
				   $pages[] += $page->ID;
				}

				$current = array_search($post_id, $pages);
				$nextID = $pages[$current+1];

			} else {
				// Error: Unknown post type";

			}
			
			?>

			<a id="nextgallery" href="<?php echo get_permalink($nextID); ?>">Next Gallery &rarr;</a>
			

		<?php endif;?>

	<?php endif;

} 
// End


?>
