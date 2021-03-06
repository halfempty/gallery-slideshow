<?php 
// Options Screen

add_action( 'admin_init', 'gallery_slideshow_options_init' );
add_action( 'admin_menu', 'gallery_slideshow_menu' );

/**
 * Init plugin options to white list our options
 */
function gallery_slideshow_options_init(){
	register_setting( 'gallery_slideshow_options', 'gallery_slideshow_options', 'gallery_slideshow_validate' );
}

/**
 * Load up the menu page
 */

function gallery_slideshow_menu() {
	add_options_page( 'Gallery Slideshow Options', 'Gallery Slideshow', 'manage_options', 'gallery-slideshow-options', 'gallery_slideshow_options' );
}


function gallery_slideshow_options() {

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	} ?>
	
<div class="wrap">	

<?php screen_icon();?>

<h2>Address Geocoder Options</h2>


		<form method="post" action="options.php">
			<?php settings_fields( 'gallery_slideshow_options' ); ?>
			<?php $options = get_option( 'gallery_slideshow_options' ); ?>

<h3>Pointer Style</h3>

<?php

//print_r($options);

$stylea = plugins_url( 'assets/images/cursor-a-next.png' , __FILE__ );
$styleb = plugins_url( 'assets/images/cursor-b-next.png' , __FILE__ );
$stylec = plugins_url( 'assets/images/cursor-c-next.png' , __FILE__ );


?>

<table style="background: #999; padding: .5em 1em;">
<tr>
<td><input id="gallery_slideshow_cursor_a" name="gallery_slideshow_options[cursor]" type="radio" value="stylea" <?php
if( $options['cursor'] != 'styleb' && $options['cursor'] != 'stylec' ) echo 'checked="checked"';	
?> /></td>
<td><label class="description" for="gallery_slideshow_cursor_a"> <img src="<?php echo $stylea; ?>" alt="Style A" /></label></td>
</tr>
<tr>
<td><input id="gallery_slideshow_cursor_b" name="gallery_slideshow_options[cursor]" type="radio" value="styleb" <?php
if( $options['cursor'] == 'styleb' ) echo 'checked="checked"';	
?> /></td>
<td><label class="description" for="gallery_slideshow_cursor_b">  <img src="<?php echo $styleb; ?>" alt="Style B" /></label></td>
</tr>
<tr>
<td><input id="gallery_slideshow_cursor_c" name="gallery_slideshow_options[cursor]" type="radio" value="stylec" <?php
if( $options['cursor'] == 'stylec' ) echo 'checked="checked"';	
?> /></td>
<td><label class="description" for="gallery_slideshow_cursor_c">  <img src="<?php echo $stylec; ?>" alt="Style C" /></label></td>
</tr>

</table>

<h3>Controls Location</h3>
<p>
<input id="gallery_slideshow_clocation_outside" name="gallery_slideshow_options[clocation]" type="radio" value="outside" <?php
if( $options['clocation'] != 'inside' ) echo 'checked="checked"';	
?> />
<label class="description" for="gallery_slideshow_clocation_outside"> <strong>Outside</strong> slide boundary</label>
<br />

<input id="gallery_slideshow_clocation_inside" name="gallery_slideshow_options[clocation]" type="radio" value="inside" <?php
if( $options['clocation'] == 'inside' ) echo 'checked="checked"';	
?> />
<label class="description" for="gallery_slideshow_clocation_inside"> <strong>Inside</strong> slide boundary</label>
</p>


<h3>Thumbnail for text slides:</h3>


<div class="trayuploader">

	
	<div id="traypreview">
	<?php if ( !$options['textthumb'] ) : ?>

		<img src="<?php echo plugins_url( '/preview.png' , __FILE__ ); ?>" />		
		
	<?php else: ?>

 		<?php echo wp_get_attachment_image( $options['textthumb'], 'thumbnail' ); ?>

	<?php endif; ?>
	</div>

	<p><input type="button" class="button-secondary" name="gs_tray_button" id="gs_tray_button" value="Set thumbnail image" title="Thumbanil Image" /></p>

	<input type="hidden" name="gallery_slideshow_options[textthumb]"  value="<?php echo $options['textthumb'] ?>" id="gs_features_tray" />
</div>


<p class="submit">
	<input type="submit" class="button-primary" value="Save Options" />
</p>


	</form>
	</div>

<?php }


/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function gallery_slideshow_validate( $input ) {

	$types = get_post_types('','names'); 

	$alwaysexclude = array('attachment','revision','nav_menu_item');

	foreach ( $types as $key => $value) :
		if ( !in_array( $key, $alwaysexclude ) ) : 
			
			if ( ! isset( $input[$key] ) ) $input[$key] = "exclude";

		endif;
	endforeach;

	return $input;

}

?>