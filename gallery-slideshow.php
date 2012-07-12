<?php
/*
Plugin Name: Gallery Slideshow
Version: Carolyn 7/12
Author: Marty Spellerberg
*/

function marty_gallery_scripts_method() {

    $kStyle = plugins_url( 'assets/slideshow.css' , __FILE__ ); // Static
    wp_register_style('kStyle',$kStyle);
    wp_enqueue_style( 'kStyle');

	wp_enqueue_script( 'jquery');

	// Fade
	$martygallery_fadejs = plugins_url( '/assets/fade.js' , __FILE__ );
	wp_register_script('martygallery_fadejs',$martygallery_fadejs);
	wp_enqueue_script( 'martygallery_fadejs',array('jquery'));

	// All Slideshow
	$martygalleryjs = plugins_url( '/assets/slideshow.js' , __FILE__ );
	wp_register_script('martygalleryjs',$martygalleryjs);
	wp_enqueue_script( 'martygalleryjs',array('jquery','martygallery_fadejs'));

}

add_action('wp_enqueue_scripts', 'marty_gallery_scripts_method');


// Custom WordPress Meta Box
// http://www.farinspace.com/how-to-create-custom-wordpress-meta-box/


add_action('admin_init','my_meta_init');

function my_meta_init() {
	// http://codex.wordpress.org/Function_Reference/add_meta_box

	foreach (array('post','page') as $type) 
	{
		add_meta_box('my_all_meta', 'Gallery Slideshow', 'my_meta_setup', $type, 'normal', 'high');
	}
	
	add_action('save_post','my_meta_save');
}

function my_meta_setup()
{
	global $post;
 
	// using an underscore, prevents the meta variable
	// from showing up in the custom fields section
	$meta = get_post_meta($post->ID,'_martygallery',TRUE);

	if ( $meta[transition] == 'slide') {
		$transition = 'slide';
	} else {
		$transition = 'fade';		
	}


	echo '<p>Transition: &nbsp; ';

	echo '<input type="radio" name="_martygallery[transition]" value="fade" ';
	if ( $transition == 'fade' ) echo 'checked="checked"';
	echo ' /> Fade &nbsp; ';
/*
	echo '<input type="radio" name="_martygallery[transition]" value="slide" ';
	if( $transition == 'slide' ) echo 'checked="checked"';
	echo ' /> Slide &nbsp; ';
*/



	$metathumbs = get_post_meta($post->ID,'_martygallery[thumbs]',TRUE);

	echo ' &nbsp; &nbsp; Thumbnails: &nbsp; ';

	echo '<input type="radio" name="_martygallery[thumbs]" value="on" ';
	if( $meta[thumbs] !== 'off' ) echo 'checked="checked"';
	echo ' /> On &nbsp; ';

	echo '<input type="radio" name="_martygallery[thumbs]" value="off" ';
	if( $meta[thumbs] == 'off' ) echo 'checked="checked"';
	echo ' /> Off &nbsp; ';
	echo '</p>';
 
	// create a custom nonce for submit verification later
	echo '<input type="hidden" name="my_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
}
 
function my_meta_save($post_id) {
	if (!wp_verify_nonce($_POST['my_meta_noncename'],__FILE__)) return $post_id;
	if ($_POST['post_type'] == 'page') {
		if (!current_user_can('edit_page', $post_id)) return $post_id;
	} else {
		if (!current_user_can('edit_post', $post_id)) return $post_id;
	}

	$current_data = get_post_meta($post_id, '_martygallery', TRUE);	
 
	$new_data = $_POST['_martygallery'];

	my_meta_clean($new_data);
	
	if ($current_data) {
		if (is_null($new_data)) delete_post_meta($post_id,'_martygallery');
		else update_post_meta($post_id,'_martygallery',$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,'_martygallery',$new_data,TRUE);
	}

	return $post_id;
}

function my_meta_clean(&$arr)
{
	if (is_array($arr))
	{
		foreach ($arr as $i => $v)
		{
			if (is_array($arr[$i])) 
			{
				my_meta_clean($arr[$i]);

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

function marty_get_images($post_id, $description) {
	global $post;

	$images = get_children( array('post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID') );

	if ($images) :

		$martygallery = get_post_meta($post_id, '_martygallery', true);
	
		// Setup transition style
		if ( !empty( $martygallery['transition'] ) ) {
			$transition = $martygallery['transition'];
		} else {
			$transition = 'fade';
		}

		$wrapper = 'gallery-' . $transition;

		// Setup thumbnails
		if ( empty($martygallery['thumbs']) || $martygallery['thumbs'] == 'on' ) $thumboption = ' hasthumbs';

		echo '<div class="' . $wrapper . $thumboption . '">';

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
				<div class="image">
					<p><img src="<?php echo $img_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" width="<?php echo $img_width; ?>" height="<?php echo $img_height; ?>" /></p>
				</div>

				<?php if ($description == true ) { ?>
				<div  class="description" style="width: <?php echo $img_width; ?>px">
					<?php if ($img_description) : echo wpautop($img_description); endif; ?>
				</div>
				<?php } ?>

			</div>

		<?php endforeach; ?>

		</div><!-- End gallery -->

		<?php if ( $thumboption == ' hasthumbs' ) : ?>

			<div class="thumbnails">

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

	<?php endif;

} 
// End




?>
