<?php 
class WCMCA_CheckoutPage
{
	public function __construct()
	{
		add_filter('plugins_loaded', array(&$this,'init'));
		add_action('wp_footer', array( &$this,'add_custom_css'),99);
		add_action('wp_head', array( &$this,'init_page'),99);
		add_action('woocommerce_before_checkout_form', array(&$this, 'add_popup_html'));
		add_action('woocommerce_before_checkout_billing_form', array(&$this, 'add_billing_address_select_menu'));
		add_action('woocommerce_before_checkout_shipping_form', array(&$this, 'add_shipping_address_select_menu'));
		
		add_action('woocommerce_checkout_update_order_meta', array( &$this, 'save_checkout_extra_field' ));
		
		//Shipping per product woocommerce_checkout_cart_item_quantity
		add_filter('woocommerce_checkout_cart_item_quantity', array(&$this,'add_product_shipping_dropdown_menu'), 10, 3); //woocommerce_cart_item_name
		add_action('wp_ajax_wcmca_load_product_address', array(&$this,'ajax_load_product_address'));
		
		//add_action('woocommerce_checkout_process', array( &$this, 'check_guest_item_addresses' )); //For Debug puprose
		add_filter('woocommerce_get_cart_item_from_session', array( &$this, 'update_cart_item_meta' ),10,3); 
	}
	public function init()
	{
		if(version_compare( WC_VERSION, '3.0.7', '<' ))
			add_action('woocommerce_add_order_item_meta', array( &$this, 'update_order_item_meta' ),10,3); //Update order items meta
		else
			add_action('woocommerce_new_order_item', array( &$this, 'update_order_item_meta' ),10,3);
	}
	public function add_custom_css()
	{	
		global $wcmca_html_helper, $wcmca_cart_model;
		if(@is_checkout())
		{
			$wcmca_html_helper->render_custom_css('my_account_page');
			//$wcmca_cart_model->delete_guest_product_addresses(); //if any
		}
	}
	public function init_page()
	{
		global $wcmca_cart_model;
		$wcmca_cart_model->reset_shipping_product_handling_fee_counter();
	}
	function add_popup_html($checkout)
	{
		global $wcmca_html_helper,$wcmca_address_model;
		
		/* if(!get_current_user_id())
			return; */
		
		$wcmca_html_helper->render_address_form_popup();
		$wcmca_html_helper->render_custom_css('checkout_page');
	}
	function add_billing_address_select_menu($checkout)
	{
		global $wcmca_html_helper,$wcmca_address_model,$wcmca_option_model;
		
		//wcmca_var_dump($checkout);
		//wcmca_var_dump(get_user_meta( get_current_user_id()));
		//wcmca_var_dump($wcmca_address_model->get_address_by_id('def_billing'));
		
		if(!get_current_user_id())
			return;
		$wcmca_html_helper->render_address_select_menu();
	}
	function add_shipping_address_select_menu($checkout)
	{
		global $wcmca_html_helper,$wcmca_option_model;
		
		if(!get_current_user_id())
			return;
		$wcmca_html_helper->render_address_select_menu('shipping');
	}
	public function save_checkout_extra_field($order_id)
	{
		global $wcmca_option_model, $wcev_order_model, $wcmca_order_model;
		if(!$wcmca_option_model->is_vat_identification_number_enabled())
			return;
		
		if(!isset($wcev_order_model) && isset($_POST['billing_vat_number']))
			$wcmca_order_model->save_vat_field($order_id, $_POST['billing_vat_number']);
	}
	
	public function add_product_shipping_dropdown_menu($text, $cart_item, $cart_item_key)
	{
		global $wcmca_html_helper, $wcmca_option_model;
			
		$options = $wcmca_option_model->shipping_per_product_related_options();	
		if((!$options['product_address_show_selector_even_for_one_item'] && WC()->cart->get_cart_contents_count() < 2) || !$wcmca_option_model->shipping_per_product())
			return $text;
		
		echo $text."<br/>";
		if(get_current_user_id() > 0)
			$wcmca_html_helper->render_address_select_menu_for_product($cart_item_key, $cart_item);
		else 
			$wcmca_html_helper->render_add_product_address_for_guest_user($cart_item_key, $cart_item);
	}
	public function ajax_load_product_address()
	{
		global $wcmca_html_helper;
		$cart_item_id = isset($_POST['cart_item_key']) ? $_POST['cart_item_key'] : null;
		$address_id = isset($_POST['address_id']) ? $_POST['address_id'] : null;
		$type = isset($_POST['type']) ? $_POST['type'] : 'shipping';
		
		if(get_current_user_id() && isset($address_id))
			$wcmca_html_helper->render_product_address_preview($address_id, get_current_user_id(),  $type);
		
		wp_die();
	}
	public function check_guest_item_addresses($checkout_fields)
	{
		global $wcmca_customer_model;
		wcmca_var_dump($wcmca_customer_model->get_guest_cart_product_addresses());
		wc_add_notice( "WCMCA stop" ,'error');					
	}
	public function update_cart_item_meta($session_data, $values, $key)
	//public function update_cart_item_meta($posted_data)
	{
		global $wcmca_customer_model;
		if(!isset($_POST) || empty($_POST))
			return $session_data;
		
		$cart = WC()->cart->cart_contents ;
		$posted_data = $_POST;
		if(isset($posted_data['wcmca_product_address']))
		{
			foreach($posted_data['wcmca_product_address'] as $product_address)
			{
				$ids = explode("-||-",$product_address); //[0] = cart_item_key; [1] = address_id; [2] = address_type
				//[2]: not used. The [1] is an unique ID so, it will be lately used to load the right one without the need if it the address is a shipping or billing type.
				
				if($key == $ids[0] && $ids[2] == 'collect_from_store')
					 $session_data['wcmca_shipping_address'] = 'collect_from_store'; 
				else if($key == $ids[0] && $ids[2] != 'same_as_billing')
					 $session_data['wcmca_shipping_address'] = $ids[1]; 
			}
		}
		if(isset($posted_data['wcmca_product_fields'])) //used for extra fields. For now only for notes
		{
			foreach($posted_data['wcmca_product_fields'] as $item_key => $field_array)
			{
				if($key == $item_key)
				 {
					 if(!isset($session_data['wcmca_shipping_fields']))
						 $session_data['wcmca_shipping_fields'] = array();
					  
					 foreach($field_array as $field_name => $field_value )
						$session_data['wcmca_shipping_fields'][$field_name] = $field_value;
				 }
			}
		}
		if(isset($posted_data['wcmca_product_address_for_guest_user']))
		{
			/*foreach($posted_data['wcmca_product_address_for_guest_user'] as $guest_item_key => $guest_item_value)
			{
				 if($guest_item_value != "same_as_billing" && $key == $item_key)
				{
					$address = $wcmca_customer_model->get_guest_cart_product_address($key);
					foreach($address as $field_name => $field_value )
						$session_data['wcmca_shipping_fields'][$field_name] = $field_value;
				} 
			}*/
			$session_data['wcmca_product_address_for_guest_user'] = $posted_data['wcmca_product_address_for_guest_user'];
		}
		
		//wcmca_var_dump($session_data['wcmca_shipping_fields']);
		//wc_add_notice( __('Stop test','woocommerce-multiple-customer-addresses') ,'error');  
		return $session_data; 
	}
	function update_order_item_meta($item_id, $values, $cart_item_key)
									// $item_id, $item, $order_id -> woocommerce_new_order_item
	{
		global $wcmca_customer_model, $wcmca_address_model;
		
		if ( is_a( $values, 'WC_Order_Item_Product' ) ) 
		{
			$values = $values->legacy_values;
			//$cart_item_key = $values->legacy_cart_item_key;
			
		} 
				
		if(isset($values['wcmca_shipping_address']))
		{
			
			if( $values['wcmca_shipping_address'] == 'collect_from_store')
			{
				wc_add_order_item_meta($item_id, '_wcmca_collect_from_store', true, true);
			}
			else
			{
				$address = $wcmca_address_model->get_address_by_id($values['wcmca_shipping_address']);
				if(!empty($address))
					foreach($address as $key => $field)
					{
						//$type = $field['type'];
						wc_add_order_item_meta($item_id, '_wcmca_'.$key, $field, true);
					}
			}
		}
		
		//Only for special field "Notes" (but for now reads and stores all the fields)
		if(isset($values['wcmca_shipping_fields']))
		{
			foreach($values['wcmca_shipping_fields'] as  $field_name => $field_value)
			{
				//if($field_name == 'notes')
					wc_add_order_item_meta($item_id, '_wcmca_'.$field_name, $field_value, true);
			}
		}
		
		if(isset($values['wcmca_product_address_for_guest_user']))
		{
			$none_was_found = true;
			foreach($values['wcmca_product_address_for_guest_user'] as $guest_item_key => $guest_item_value)
			{
				if($guest_item_key == $values["key"])
				{
					if( $guest_item_value == 'collect_from_store')
					{
						$none_was_found = false;				
						wc_add_order_item_meta($item_id, '_wcmca_collect_from_store', true, true);
					}
					else if( $guest_item_value != 'same_as_billing')
					{
						$none_was_found = false;					
						$address = $wcmca_customer_model->get_guest_cart_product_address($guest_item_key);
						foreach($address as $field_name => $field_value )
							wc_add_order_item_meta($item_id, '_wcmca_'.$field_name, $field_value, true);
					}
				}
			}
			if($none_was_found)
			{
				$address = $wcmca_customer_model->get_address_by_id(0, 'checkout_data');
				foreach($address as $field_name => $field_value )
						wc_add_order_item_meta($item_id, '_wcmca_'.$field_name, $field_value, true);
			}
		}
		
		/* wcmca_var_dump($values);
		wp_die();    */
	}

}
?>