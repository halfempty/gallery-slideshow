<?php
/*
Plugin Name: Gallery Slideshow
Author: Marty Spellerberg
*/


// Plugin Options 
require_once( dirname(__FILE__) . '/gallery-slideshow-options.php' );



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



add_action('admin_init','gs_meta_init');

function gs_meta_init() {
	// http://codex.wordpress.org/Function_Reference/add_meta_box


	global $pagenow;
	if ( ($pagenow == 'options-general.php') && ($_GET['page'] == 'gallery-slideshow-options') ) {
		wp_enqueue_media();
	}


	$gsgalleryadminjs = plugins_url( '/gallery-slideshow.js' , __FILE__ );
	wp_register_script('gsgalleryadminjs',$gsgalleryadminjs);
	wp_enqueue_script( 'gsgalleryadminjs',array('jquery'));

	$gsgalleryoptionsjs = plugins_url( '/gallery-slideshow-options.js' , __FILE__ );
	wp_register_script('gsgalleryoptionsjs',$gsgalleryoptionsjs);
	wp_enqueue_script( 'gsgalleryoptionsjs',array('jquery'));


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


// Attachmnet Options
// http://net.tutsplus.com/tutorials/wordpress/creating-custom-fields-for-attachments-in-wordpress/

function gs_slide_options($form_fields, $post) {

	$slidetype = get_post_meta($post->ID, "_slidetype", true);

	$form_fields["slidetype"]["label"] = __("Slide style: ");
	$form_fields["slidetype"]["input"] = "html";
	$select = "<select name='attachments[{$post->ID}][slidetype]' id='attachments[{$post->ID}][slidetype]'>";

	$select .= "<option value='normal'";
		if ( $slidetype != 'both' && $slidetype != 'text' ) $select .= ' selected="selected"';
		$select .= ">Image</option>";

	$select .= "<option value='both'";
		if ( $slidetype == 'both' ) $select .= ' selected="selected"';
		$select .= ">Image &amp; Description</option>";

	$select .= "<option value='text'";
		if ( $slidetype == 'text' ) $select .= ' selected="selected"';
		$select .= ">Description Only</option>";
	
	$select .= "</select>";

	$form_fields["slidetype"]["html"] = $select;

	return $form_fields;

}

add_filter("attachment_fields_to_edit", "gs_slide_options", null, 2);



function gs_slide_options_save($post, $attachment) {  
    if( isset($attachment['slidetype']) ){  
        update_post_meta($post['ID'], '_slidetype', $attachment['slidetype']);  
    }  
    return $post;  
}

add_filter("attachment_fields_to_save", "gs_slide_options_save", null, 2);




// Custom Galley
// http://www.wpoutfitters.com/2011/01/wordpress-image-attachment-gallery-revisited/

function gs_get_images($post_id) { 
	
	
	 $options = get_option( 'gallery_slideshow_options' ); 
	
	if( $options['cursor'] == 'styleb' ) :
		$next = plugins_url( 'assets/images/cursor-b-next.png' , __FILE__ );
		$prev = plugins_url( 'assets/images/cursor-b-prev.png' , __FILE__ );
	elseif( $options['cursor'] == 'stylec' ) :
		$next = plugins_url( 'assets/images/cursor-c-next.png' , __FILE__ );
		$prev = plugins_url( 'assets/images/cursor-c-prev.png' , __FILE__ );
	else :
		$next = plugins_url( 'assets/images/cursor-a-next.png' , __FILE__ );
		$prev = plugins_url( 'assets/images/cursor-a-prev.png' , __FILE__ );
	endif;


	$clocation = '';
	if( $options['clocation'] == 'inside' ) :
		$clocation = ' innercontrols';
	endif;

	?>

<style>
	.cursornext { cursor: url("<?php echo $next; ?>"), pointer;}
	.cursorprev { cursor: url("<?php echo $prev; ?>"), pointer;}
</style>

<?php 

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
			echo '<div class="gallery-fade controls' . $thumboption . $nextoption . $clocation .'">';			
		}

		foreach ($images as $attachment_id => $image) :
	
			$slidetype = get_post_meta($attachment_id, '_slidetype', true);

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

			<?php if ($slidetype == 'text' && $img_description) : ?>
				<div class="slide textslide" style="width: <?php echo get_option( 'large_size_w' ); ?>px">
					<div class="longdescription">
						<?php echo wpautop($img_description); ?>
					</div>
				</div>
			<?php elseif ($slidetype == 'both' && $img_description ) : ?>
				<div class="slide spreadslide" style="width: <?php echo get_option( 'large_size_w' ); ?>px">

					<div class="imagewrap" style="width: <?php echo $img_width; ?>px">
						<div class="image" >
							<p><img src="<?php echo $img_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" width="<?php echo $img_width; ?>" height="<?php echo $img_height; ?>" /></p>
						</div>

						<?php if ($hidecaption != true ) : ?>
							<div class="description">
								<?php if ($img_caption) : echo wpautop($img_caption); endif; ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="longdescription">
					<?php echo wpautop($img_description); ?>
					</div>

			</div>
			<?php else: ?>
				<div class="slide imageslide" style="width: <?php echo get_option( 'large_size_w' ); ?>px">

					<div class="imagewrap" style="width: <?php echo $img_width; ?>px;">
						<div class="image" >
							<p><img src="<?php echo $img_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" width="<?php echo $img_width; ?>" height="<?php echo $img_height; ?>" /></p>
						</div>

						<?php if ($img_caption || $img_description && $hidecaption != true ) { ?>
							<div class="description">
								<?php if ($img_caption) : echo wpautop($img_caption); endif; ?>
								<?php if ($img_description) : echo wpautop($img_description); endif; ?>
							</div>
						<?php } ?>
					</div>

				</div>

			<?php endif; ?>


		<?php endforeach; ?>

		</div><!-- End gallery -->

		<?php if ( $thumboption == ' hasthumbs' ) : ?>

			<div class="thumbnails" style="display: none;">

				<?php 

				$options = get_option( 'gallery_slideshow_options' );

				foreach ($images as $attachment_id => $image) :

					$slidetype = get_post_meta($attachment_id, '_slidetype', true);

					$thumb_title = $image->post_title;   // title.
					$thumb_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true); //alt
					if ($thumb_alt == '') : $thumb_alt = $thumb_title; endif;

					$thumb_array = image_downsize( $image->ID, 'thumbnail' );
	 				$thumb_url = $thumb_array[0];

				?>
				<div class="thumb">
					<?php if ($slidetype == 'text' && $options['textthumb'] ) : ?>
						<?php echo wp_get_attachment_image( $options['textthumb'], 'thumbnail' ); ?>
					<?php else : ?>
						<img src="<?php echo $thumb_url; ?>" alt="<?php echo $img_alt; ?>" title="<?php echo $img_title; ?>" />
					<?php endif; ?>
				</div>
				
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
