<?php
/*
Plugin Name: iWantWorkwear Quote
Plugin URI:
Description: Allows for free bulk quotes and customization quotes
Version: 0.1
Author: Gregory Bastianelli
Author URI: http://d.iwantworkwear.com
*/
add_action( 'wp_enqueue_scripts', 'iww_quote_scripts', 100);
add_action( 'woocommerce_process_product_meta', 'iww_add_can_customize_save' );
add_action( 'iww_quote_tab', 'bulk_quote_html', 1 );
add_action( 'iww_quote_tab', 'custom_quote', 2 );

add_shortcode( 'bulkquote', 'bulk_quote_form');
add_shortcode( 'customize', 'custom_quote_form');

function iww_quote_scripts(){
	wp_enqueue_script('quote_js', plugins_url( 'iww_quote.js', __FILE__ ), array( 'jquery' ), rand(0,199), true);
}

function custom_quote(){
	global $product;
	$url = site_url() . '/customize/?id=' . $product->get_id();
	?>
	<h3 class="mt-2">Customization Quote</h3>
	<p>Add your name or company logo. Select from screen printing, vinyl heat press or embroidery on most items. Quotes are typically processed within 1 business day.</p>
	<a id="custom-quote-link" href="<?php echo $url; ?>" role="button" class="btn btn-primary">Customization Quote</a>
	<?php
}

function custom_quote_form(){
	$id = $_GET['id'];
	$product = wc_get_product( $id );
	if( $product ){
		// echo $product->get_type();
		?>
		<div class="card">
			<div class="card-header">
				<b>Step 1: Item Options</b>
			</div>
			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" enctype="multipart/form-data">
				<div class="card-body">
					<table class="table">
						<tr>
							<td width="25%"><?php echo $product->get_image(); ?></td>
							<td>
								<p><?php echo $product->get_name(); ?></p>
								<p><?php echo iww_get_color( $product ); ?></p>
								<p>
									<label>Quantity: </label>
									<input type="tel" class="form-control" name="product[qty]" />
									<small>Just a rough estimate for now.</small>
								</p>
							</td>
						</tr>
					</table>
				</div>
				<?php get_customization_options( 2 ); ?>
				<?php get_contact_information( 3 ); ?>
				<input type="hidden" name="product[id]" value="<?php echo $product->id; ?>">
				<input type="hidden" name="action" value="send_custom_quote">
				<button type="submit" class="btn btn-primary btn-block">Submit</button>
			</form>
		</div>
		<?php
	}
}

function iww_get_color( $product ){
	$atts = preg_split( "/, /", $product->get_attribute( 'pa_all_color' ) );
	if( sizeof( $atts ) > 1 ){ ?>
		<label>Color: </label>
		<select class="form-control" name="product[color]">
			<?php foreach( $atts as $att ) : ?>
				<option value="<?php echo $att; ?>"><?php echo $att; ?></option>
			<?php endforeach; ?>
		</select> <?php
	} else {
		return '<p>Color: ' . $att . '</p>';
	}
}

function bulk_quote_html(){
	global $product;
	$url = site_url() . '/large-quantity/?id=' . $product->get_id();
	?>
	<h3>Bulk Quote</h3>
	<p>Submit a request for bulk discount rates. Use this for large quantity orders that exceed the item quantity price breaks. Quotes are typically processed within 1 business day.</p>
	<a id="bulk-quote-link" href="<?php echo $url; ?>" role="button" class="btn btn-primary">Free Bulk Quote</a>
	<hr style="width: unset; margin-top: 1rem; margin-bottom: unset; border-top: 1px solid rgba(0, 0, 0, 0.15);">
	<?php
}

add_action( 'woocommerce_product_options_general_product_data', 'iww_add_can_customize' );

// add checkbox to product data - general tab
function iww_add_can_customize(){
  global $woocommerce, $post;
  echo '<div class="options_group">';
  $checked = get_post_meta( get_the_ID(), 'iww_can_customize', true );
  // Checkbox
  woocommerce_wp_checkbox(
    array(
    	'id'            => 'iww_can_customize',
    	'wrapper_class' => '',
    	'label'         => __('Customizable', 'woocommerce' ),
    	'description'   => __( 'Can it be customized?', 'woocommerce' ),
      'cbvalue'       => 1,
    )
  );
  echo '</div>';
}

function iww_add_can_customize_save( $post_id ){
  $woocommerce_checkbox = isset( $_POST['iww_can_customize'] ) ? 1 : 0;
	update_post_meta( $post_id, 'iww_can_customize', $woocommerce_checkbox );
}

function bulk_quote_form(){
	echo '<h1>Bulk Quantity Quote</h1>';
	$id = $_GET['id'];
	$product = wc_get_product( $id );
	if( $product ){
		$type = $product->get_type();
		if( $type == 'variable' ){
			$children = $product->get_children();
		}
		?>
		<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
		<div class="card">
			<div class="card-header">
				<b>Step 1: Item Options</b>
			</div>
			<div class="card-body">
				<table>
					<tr>
						<td width="50%" style="max-height: 100px;"><?php echo $product->get_image(); ?></td>
						<td><?php echo 'Item #: ' . $product->get_sku(); ?></td>
					</tr>
				</table>
				<?php echo ( isset( $children ) ) ? '<p>How many of each option would you like?</p>' : ''; ?>
				<table id="bulk_quote_table">
					<?php ( isset( $children ) ) ? iww_var_form( $children ) : iww_simple_form( $product ); ?>
				</table>
				<h3>Total: <span id="quote_total"></span></h3>
			</div>
		</div>
		<br>
		<?php get_contact_information(); ?>
		<input type="hidden" name="action" value="send_bulk_quote">
		<button type="submit" class="btn btn-iww">Submit</button>
		</form>
		<?php
	}
}

function get_contact_information( $step = 2 ){
	?>
	<div class="card">
		<div class="card-header">
			<b>Step <?php echo $step; ?>: Contact Information</b>
		</div>
		<div class="card-body">
			<div class="card-title">Contact Information</div>
				<div class="form-group">
					<label>Name:</label>
					<input type="text" class="form-control" name="name">
				</div>
				<div class="form-group">
					<label>Email:</label>
					<input type="text" class="form-control" name="email" required>
					<small>Used only for further communications</small>
				</div>
				<div class="form-group">
					<label>Company:</label>
					<input type="text" class="form-control" name="company">
				</div>
				<div class="form-group">
					<label>Street Address:</label>
					<input type="text" class="form-control" name="street">
				</div>
				<div class="form-group">
					<label>Zip:</label>
					<input type="text" class="form-control" name="zip">
					<small>Used for shipping estimate.</small>
				</div>
			</div>
		</div>
		<?php
}

function get_customization_options( $step = 3 ){
	?>
	<div class="card">
		<div class="card-header">
			<b>Step <?php echo $step; ?>: Customization Options</b>
		</div>
		<div class="card-body">
			<label>Where would you like your customization?</label>
			<div class="form-check form-check-inline">
			  <input class="form-check-input custom-loc" type="checkbox" value="front">
			  <label class="form-check-label" for="loc_front">Front</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input custom-loc" type="checkbox" value="back">
			  <label class="form-check-label" for="loc_front">Back</label>
			</div>
			<div id="customize-loc"></div>

		</div>
	</div>
	<?php
}

function iww_var_form( $children ){
	if( !empty( $children ) ){
		foreach( $children as $id ){
			$product = wc_get_product( $id );
			$atts = wc_get_formatted_variation( $product->get_variation_attributes(), true, false, true );
			?>
			<tr>
				<td width="25%"><input type="tel" min=0 name="ids[<?php echo $id; ?>]" /></td>
				<td><?php echo $atts; ?></td>
				<td><span class="price">$<?php echo $product->get_price(); ?></span></td>
			</tr>
			<?php
		}
	}
}

function iww_simple_form( $product ){
	echo '<p>How many would you like?</p>';
	?>
	<tr>
		<td>
			<label>Quantity: </label>
			<input style="width: 25%" type="tel" min=0 name="ids[<?php echo $product->get_id(); ?>]" />
		</td>
	</tr>
	<?php
}



function send_bulk_quote(){
	$quote = array();
	var_dump($_POST);
	foreach( $_POST['ids'] as $id => $qty ){
		if( !empty( $qty ) ){
			array_push( $quote, array( $id => $qty ) );
		}
	}

	$headers = array('Reply-To: '. $_POST['email']);

	if( !empty( $quote ) ){
		$message .= $_POST['name'] . ' would like a quote of the following: <br><br>';
		foreach( $quote as $item ){
			foreach( $item as $id => $qty ){
				$product = wc_get_product( $id );
				$message .= 'QTY: '. $qty .' - SKU: ' . $product->get_sku() . '<br>';
			}
		}

		$message .= '<br>Company: ' . $_POST['company'] . '<br>';
		$message .= 'Ship to: ' . $_POST['street'] . '<br>';
		$message .= 'Zip: ' . $_POST['zip'] . '<br><br>';
		$message .= $headers[0];
	}

	echo $message;
	if( !empty( $message ) ) wp_mail( 'info@iwantworkwear.com', 'Request for quote', $message, $headers );
	wp_mail( $_POST['email'], 'We got your quote request!', 'We got your email, expect a response same or next business day! <br> Thank you!<br><br> Here is a copy of your quote request for your reference:<br><br>' . $message );
	wp_redirect( '/' );
	exit;
}

function iww_process_img( $files ){
	$arr = array();
	$upload_dir = wp_upload_dir();
	foreach( $files as $file ){
		// pre_arr($file);
		$name = make_random_name();
		$imageFileType = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		$target_file = $upload_dir['basedir'] . '/' . $name . '.' . $imageFileType;
		$url = $upload_dir['baseurl'] . '/' . $name . '.' . $imageFileType;
		$is_image = getimagesize( $file['tmp_name'] );

		if( $is_image != false ){
			if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg"){
				if ( move_uploaded_file( $file['tmp_name'], $target_file ) ){
					array_push( $arr, $target_file );
				}
			}
		}
	}
	return $arr;
}

function iww_process_locations( $loc_arr ){
	$str = '<h4 style="margin-bottom: 0px;">Customizations: </h4>';
	foreach( $loc_arr as $key => $location ){
		$str .= '<br>' . ucwords( $key ) . ': <br>';
		foreach( $location as $key => $attr ){
			$str .= ucwords( $key ) . ': ' . $attr . '<br>';
		}
	}
	return $str;
}


function send_custom_quote(){
	$target_files = array();
	$message = '';
	$subject = $_POST['name'] . ' is looking for a custom quote!';
	$product = wc_get_product( $_POST['product']['id'] );

	$message .= '<h3>Custom Quote Request</h3><br>';
	if( $product ){
		$message .= 'Product: ' . $product->get_name() . '<br>';
		$message .= 'QTY: ' . $_POST['product']['qty'];
	}

	if( !empty( $_POST['location'] ) ){
		$message .= iww_process_locations( $_POST['location'] );
	}

	$subject = $_POST['name'] . ' is looking for a custom quote.';

	$message .= '<h4>Contact Information</h4>';
	$message .= 'Name: ' . $_POST['name'] . '<br>';
	$message .= 'Email: ' . $_POST['email'] . '<br>';
	$message .= 'Company: ' . $_POST['company'] . '<br>';
	$message .= 'Street: ' . $_POST['street'] . '<br>';
	$message .= 'Zip: ' . $_POST['zip'] . '<br>';

	// handle uploaded images
	if( isset( $_FILES ) ){
		$target_files = iww_process_img( $_FILES );
	}

	$headers = array('Reply-To: '. $_POST['email']);

	wp_mail( 'info@iwantworkwear.com', $subject, $message, $headers, $target_files );

	wp_mail( $_POST['email'],
	'We got your custom quote request',
	'We got your quote request, expect a response same or next business day. <br><br> For your reference, this is the email we got: <br>' . $message  );

	iww_rm_files( $target_files );
	wp_redirect( '/' );
	exit;
}

function iww_rm_files( $files ){
	foreach( $files as $file ){
		unlink( $file );
	}
}

function make_random_name(){
	return sha1( microtime() );
}

add_action( 'admin_post_nopriv_send_bulk_quote', 'send_bulk_quote' );
add_action( 'admin_post_send_bulk_quote', 'send_bulk_quote' );
add_action( 'admin_post_nopriv_send_custom_quote', 'send_custom_quote' );
add_action( 'admin_post_send_custom_quote', 'send_custom_quote' );
