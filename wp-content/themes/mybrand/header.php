<?php
/**
 * The Header template for our theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php do_action( 'woodmart_after_body_open' ); ?>
	
	<div class="website-wrapper">

		<?php if ( woodmart_needs_header() ): ?>

			<!-- HEADER -->
			<header <?php woodmart_get_header_classes(); // location: inc/functions.php ?>>

				<?php 
					whb_generate_header();
				 ?>

			</header><!--END MAIN HEADER-->
			
			<?php woodmart_page_top_part(); ?>

		<?php endif ?>

		<?php  
	

		$form_data = $_COOKIE['yith_wcms_checkout_form'];
		parse_str($form_data, $output); 

		$product_start_date = get_query_var('product_start_date');
		$product_end_date = get_query_var('product_end_date');
        
        if($product_start_date != ''){
        	WC()->session->set( 'filter_start_date', $product_start_date );
        }
        if($product_start_date != ''){
        	WC()->session->set( 'filter_end_date', $product_end_date );
        }
        
        
        
		$startDate = "";
		$endDate = "";
		if (  WC()->session->__isset( 'product_start_date' ) ){
			$startDate = WC()->session->get( 'product_start_date', $product_start_date );
		}

		if (  WC()->session->__isset( 'product_end_date' ) ){
			$endDate = WC()->session->get( 'product_end_date', $product_end_date );
		}
		

		$output['billing_booking_start_date'] = $startDate;
		$output['billing_booking_end_date'] = $endDate;

		$chekout_form_res =  http_build_query($output, '', '&amp;');
		/*$res = implode(" ",$output); */

		?>
		<script>
			jQuery(document).ready(function() {
				var dateVal = '<?php echo $chekout_form_res; ?>';
			    set_cookie('yith_wcms_checkout_form',dateVal);

		    });

		    function getCookie(cname) {
				var name = cname + "=";
				var ca = document.cookie.split(';');
			    for(var i = 0; i < ca.length; i++) {
				    var c = ca[i];
				    while (c.charAt(0) == ' ') {
				      c = c.substring(1);
				    }
				    if (c.indexOf(name) == 0) {
				      return c.substring(name.length, c.length);
				    }
			    }
				return "";
			}

		    function set_cookie(name, value) {
			    document.cookie = name +'='+ value +'; Path=/;';
			}

			function delete_cookie(name) {
			    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
			}
		</script>
<?php

?>
