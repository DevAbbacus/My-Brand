<?php 
class WCMCA_AdminOrderDetailsPage
{
	public function __construct()
	{
		add_action( 'woocommerce_admin_order_data_after_order_details', array( &$this,'add_additional_addresses_loading_tools')); 
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( &$this,'add_custom_billing_fields'), 10, 1 );
		add_action( 'woocommerce_process_shop_order_meta', array( &$this, 'on_save_order_details_admin_page' ), 5, 2 );//save order	
		
		add_action( 'woocommerce_after_order_itemmeta', array( &$this, 'display_product_shipping_address' ), 10, 3 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( &$this, 'hide_private_metakeys' )); //hidden wcmca keys
		
		add_filter( 'woocommerce_admin_shipping_fields',  array( &$this,'add_extra_shipping_fields_to_order_details_fields' )); //admin order details page
		//add_action('woocommerce_order_details_after_customer_details', array( &$this,'add_shipping_fields' ));
		
		
	}
	public function add_extra_shipping_fields_to_order_details_fields($fields)
	{
		global $wcmca_address_model;
		$shipping_email = $wcmca_address_model->get_shipping_email_field_data();
		$phone_email = $wcmca_address_model->get_shipping_phone_field_data();
		if(!empty($shipping_email))
			$fields['email'] = array(
				'label' => __( 'Email', 'woocommerce-eu-vat-field' ),
				'show'  => false //See WCMCA_Order, are automatically showed because they have been entered on the get shipping formatted method
			);
		
		if(!empty($phone_email))
			$fields['phone'] = array(
				'label' => __( 'Phone', 'woocommerce-eu-vat-field' ),
				'show'  => false
			);
		return $fields;
	}
	function display_product_shipping_address($item_id, $item, $_product )
	{
		global $wcmca_order_model, $wcmca_option_model;		
		$get_formatted_item_shipping_address = $wcmca_option_model->shipping_per_product() ? $wcmca_order_model->get_formatted_item_shipping_address($item) : "";
		
		if($get_formatted_item_shipping_address)
		{
			?>
			<!-- <a class="button wcmca_make_order_item_editable"><?php _e( 'Make editable', 'woocommerce-eu-vat-field' ); ?></a>-->
			<?php 
			echo $get_formatted_item_shipping_address;
		}
	}
	public function hide_private_metakeys($keys)
	{
		if(isset($_GET['wcmca_edit_order_item']))
			return $keys;
		
		global $post;
		if(isset($post))
			$order = wc_get_order($post->ID);
		else if(isset($_GET['order_id']))
			$order = wc_get_order($_GET['order_id']);
		else 
			return $keys;
		
		if(!isset($order) || $order == false)
			return $keys;
		
		//$keys_to_remove = array();
		//wcmca_var_dump($post->ID);
		foreach($order->get_items() as $item)
		{
			if(!is_a ($item, 'WC_Order_Item_Product'))
				continue;
			$meta = $item->get_meta_data( );
			foreach($meta as $tmp_meta)
			{
				$content = $tmp_meta->get_data();
				//wcmca_var_dump($content);
				/*foreach($content as $tmp_content) */
				  if(strpos($content["key"], "_wcmca_")  !== false)
					$keys[$content["key"]] = $content["key"]; 
			}
		}
		return $keys;
	}
	public function add_custom_billing_fields($order)
	{
		global $wcmca_option_model, $wcev_order_model;
		if(isset($wcev_order_model) || !$wcmca_option_model->is_vat_identification_number_enabled())
			return;
		$billing_vat_number = get_post_meta(WCMCA_Order::get_id($order), 'billing_vat_number',true);
		$billing_vat_number = $billing_vat_number ? $billing_vat_number : "";
		?>
		<p class="form-row form-row-wide">
			<label class="wpuef_label"><?php _e( 'VAT Identification Number', 'woocommerce-multiple-customer-addresses' ); ?></label>
			<input class="input-text wpuef_input_text" type="text" id="_billing_vat_number" placeholder="<?php _e( 'VAT Identification Number', 'woocommerce-multiple-customer-addresses' ); ?>" value="<?php echo $billing_vat_number; ?>" name="billing_vat_number" />
		</p>
		<?php
	}
	public function on_save_order_details_admin_page( $order_id, $order )
	{
		global $wcev_order_model;
		if(!isset($wcev_order_model) && isset($_POST['billing_vat_number']))
			update_post_meta($order_id,'billing_vat_number', $_POST['billing_vat_number']);
	}
	public function add_additional_addresses_loading_tools($order)
	{
		global $wcmca_html_helper;
		
		?>
		<div id="wcmca_edit_order_item_container">
			<h3 class="wcmca_edit_order_item_title"><?php _e( 'Product addresses edit actions', 'woocommerce-multiple-customer-addresses' ); ?></h3>
			<p><?php _e( 'In case you have enable the <strong>Shipping per product</strong> option and you want to edit the assigned address, please click the following button.<br/><br/>Before proceeding mark the order as <strong>On hold</strong> otherwise WooCommerce won\'t display the edit icon.', 'woocommerce-multiple-customer-addresses' ); ?></p>
			<button class="button wcmca_make_order_item_editable"><?php _e( 'Make product addresses editable', 'woocommerce-eu-vat-field' ); ?></button>
		</div>
		<?php 
		
		$wcmca_html_helper->render_admin_order_page_additional_addresses_loading_tools();
		
	}
}
?>