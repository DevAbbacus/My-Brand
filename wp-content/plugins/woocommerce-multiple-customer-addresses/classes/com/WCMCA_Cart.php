<?php 
class WCMCA_Cart
{
	function __construct()
	{
		add_filter( 'woocommerce_add_cart_item_data', array(&$this, 'check_if_force_individual_cart_item_add_method'), 10, 3 ); 
		add_filter( 'woocommerce_cart_calculate_fees', array(&$this, 'add_shipping_product_handling_fees') ); 
		add_action('wp_ajax_wcmca_update_product_handling_fee_counter', array(&$this, 'update_shipping_product_handling_fee_counter'));
		add_action('wp_ajax_nopriv_wcmca_update_product_handling_fee_counter', array(&$this, 'update_shipping_product_handling_fee_counter'));
	}
	function update_shipping_product_handling_fee_counter()
	{	
		
		if(isset($_POST['increase']))
		{
			if($_POST['increase'] == 0)
			{
				/* error_log("Ajax: resetting"); */
				$this->reset_shipping_product_handling_fee_counter();
			}
			else 
			{
				$product_handling_fee_counter = $this->get_shipping_product_handling_fee_counter();
				$product_handling_fee_counter = $_POST['increase'] == 1 ? $product_handling_fee_counter + 1 : $product_handling_fee_counter - 1;
				$product_handling_fee_counter = $product_handling_fee_counter < 0 ? 0 : $product_handling_fee_counter;
				WC()->session->set( 'wcmca_product_handling_fee_counter', $product_handling_fee_counter );
				//error_log("Ajax: ".$this->get_shipping_product_handling_fee_counter());
			}
		}
		wp_die();
		 
	}
	function get_shipping_product_handling_fee_counter()
	{
		$product_handling_fee_counter = WC()->session->get( 'wcmca_product_handling_fee_counter' );
		
		return isset( $product_handling_fee_counter) && is_numeric($product_handling_fee_counter) ? $product_handling_fee_counter : 0;
	}
	function reset_shipping_product_handling_fee_counter()
	{
		//error_log("Resetting counter");
		WC()->session->set( 'wcmca_product_handling_fee_counter', 0 );
	}
	function check_if_force_individual_cart_item_add_method($cart_item_data, $product_id, $variation_id)
	{
		global $wcmca_option_model;
		
		if($wcmca_option_model->add_product_distinctly_to_cart())
		{
			$cart_item_data['wcmca_unique_id'] = wcmca_random_string();
		}
		return $cart_item_data;
	}
	function add_shipping_product_handling_fees()
	{
		global $woocommerce, $wcmca_option_model;
		
		//error_log("*Cart*");
		if ( !defined( 'DOING_AJAX' ) && !@is_checkout())
		{
			//error_log("Cart: resetting");
			$this->reset_shipping_product_handling_fee_counter();
			return;
		}
		//error_log("Cart: ".$this->get_shipping_product_handling_fee_counter());
		$fee_data = $wcmca_option_model->get_product_shipping_fee_options();
		$fee_value = 0;
		$product_handling_fee_counter = $this->get_shipping_product_handling_fee_counter();
		
		foreach($fee_data['fee_ranges'] as $range)
			if( $product_handling_fee_counter >= $range['min'] && ($range['max'] == 0 || $product_handling_fee_counter  <= $range['max']))
				$fee_value = $range['fee'];
		
		$fee_value *= $product_handling_fee_counter; 
		
		if($fee_value != 0)
			$woocommerce->cart->add_fee( __('Handling fee', 'woocommerce-multiple-customer-addresses'), $fee_value, $fee_data['fee_taxable']);
	}
}
?>