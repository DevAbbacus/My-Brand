<?php 
/*
Plugin Name: WooCommerce Multiple Customer Addresses
Description: Manage multiple customer shipping and billing addresses 
Author: Lagudi Domenico
Version: 18.2
*/

/* 
Copyright: WooCommerce Multiple Customer Addresses uses the ACF PRO plugin. ACF PRO files are not to be used or distributed outside of the WooCommerce Multiple Customer Addresses plugin.
*/

define('WCMCA_PLUGIN_PATH', rtrim(plugin_dir_url(__FILE__), "/") )  ;
define('WCMCA_PLUGIN_ABS_PATH', plugin_dir_path( __FILE__ ) );

if ( !defined('WP_CLI') && ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ||
					   (is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins') ))
					 )	
	)
{
	//For some reasins the theme editor in some installtion won't work. This directive will prevent that.
	if(isset($_POST['action']) && $_POST['action'] == 'edit-theme-plugin-file')
		return;
	
	if(isset($_REQUEST ['context']) && $_REQUEST['context'] == 'edit') //rest api
		return;
		
	if(isset($_POST['action']) && strpos($_POST['action'], 'health-check') !== false) //health check
		return;
		
	//com
	$wcmca_id = 16127030;
	$wcmca_name = "WooCommerce Multiple Customer Addresses";
	$wcmca_activator_slug = "wcmca-activator";
	
	include_once( "classes/com/WCMCA_Acf.php"); 
	include_once( "classes/com/WCMCA_Global.php"); 
	require_once('classes/admin/WCMCA_ActivationPage.php');
	
	//add_action('admin_menu', 'wcmca_init_admin_panel');
	//add_action('admin_init', 'wcmca_admin_init');
	add_action('init', 'wcmca_init');
	add_action('admin_notices', 'wcmca_admin_notices' );
	add_action('admin_menu', 'wcmca_init_act');
	if(defined('DOING_AJAX') && DOING_AJAX)
		wcmca_init_act();
}
function wcmca_admin_notices()
{
	global $wcmca_notice, $wcmca_name, $wcmca_activator_slug;
	if($wcmca_notice && (!isset($_GET['page']) || $_GET['page'] != $wcmca_activator_slug))
	{
		 ?>
		<div class="notice notice-success">
			<p><?php echo sprintf(__( 'To complete the <span style="color:#96588a; font-weight:bold;">%s</span> plugin activation, you must verify your purchase license. Click <a href="%s">here</a> to verify it.', 'woocommerce-multiple-customer-addresses' ), $wcmca_name, get_admin_url()."admin.php?page=".$wcmca_activator_slug); ?></p>
		</div>
		<?php
	}
}
function wcmca_init()
{
	//wcmca_var_dump(get_locale());
	load_plugin_textdomain('woocommerce-multiple-customer-addresses', false, basename( dirname( __FILE__ ) ) . '/languages' );
	/* if(is_admin())
		wcmca_init_act(); */
}
function wcmca_init_act()
{
	global $wcmca_activator_slug, $wcmca_name, $wcmca_id;
	new WCMCA_ActivationPage($wcmca_activator_slug, $wcmca_name, 'woocommerce-multiple-customer-addresses', $wcmca_id, WCMCA_PLUGIN_PATH);
}
function wcmca_setup()
{
	global $wcmca_wpml_helper, $wcmca_html_helper, $wcmca_customer_model, $wcmca_address_model, $wcmca_order_model, $wcmca_email_model,
		   $wcmca_option_model, $wcmca_cart_model, $wcmca_session_model, $wcmca_my_account_page_addon, $wcmca_checkout_page_addon, 
		   $wcmca_frontend_order_details_page_addon, $wcmca_emails_addon, $wcmca_shop_page_addon, $wcmca_admin_order_details_page_addon,
		   $wcmca_option_page, $wcmca_user_profile_page;
		   
	if(!class_exists('WCMCA_Wpml'))
	{
		require_once('classes/com/WCMCA_Wpml.php');
		$wcmca_wpml_helper = new WCMCA_Wpml();
	}
	if(!class_exists('WCMCA_Html'))
	{
		require_once('classes/com/WCMCA_Html.php');
		$wcmca_html_helper = new WCMCA_Html();
	}
	if(!class_exists('WCMCA_Customer'))
	{
		require_once('classes/com/WCMCA_Customer.php');
		$wcmca_customer_model = new WCMCA_Customer();
	}
	if(!class_exists('WCMCA_Address'))
	{
		require_once('classes/com/WCMCA_Address.php');
		$wcmca_address_model = new WCMCA_Address();
	}
	if(!class_exists('WCMCA_Order'))
	{
		require_once('classes/com/WCMCA_Order.php');
		$wcmca_order_model = new WCMCA_Order();
	}
	if(!class_exists('WCMCA_Email'))
	{
		require_once('classes/com/WCMCA_Email.php');
		$wcmca_email_model = new WCMCA_Email();
	}
	if(!class_exists('WCMCA_Option'))
	{
		require_once('classes/com/WCMCA_Option.php');
		$wcmca_option_model = new WCMCA_Option();
	}
	if(!class_exists('WCMCA_Cart'))
	{
		require_once('classes/com/WCMCA_Cart.php');
		$wcmca_cart_model = new WCMCA_Cart();
	}
	if(!class_exists('WCMCA_Session.php'))
	{
		require_once('classes/com/WCMCA_Session.php');
		$wcmca_session_model = new WCMCA_Session();
	}
	//frontend
	if(!class_exists('WCMCA_MyAccountPage'))
	{
		require_once('classes/frontend/WCMCA_MyAccountPage.php');
		$wcmca_my_account_page_addon = new WCMCA_MyAccountPage();
	}
	if(!class_exists('WCMCA_CheckoutPage'))
	{
		require_once('classes/frontend/WCMCA_CheckoutPage.php');
		$wcmca_checkout_page_addon = new WCMCA_CheckoutPage();
	}
	if(!class_exists('WCMCA_OrderDetailsPage'))
	{
		require_once('classes/frontend/WCMCA_OrderDetailsPage.php');
		$wcmca_frontend_order_details_page_addon = new WCMCA_OrderDetailsPage();
	}
	if(!class_exists('WCMCA_Emails'))
	{
		require_once('classes/frontend/WCMCA_Emails.php');
		$wcmca_emails_addon = new WCMCA_Emails();
	}
	if(!class_exists('WCMCA_ShopPage'))
	{
		require_once('classes/frontend/WCMCA_ShopPage.php');
		$wcmca_shop_page_addon = new WCMCA_ShopPage();
	}
	//admin
	if(!class_exists('WCMCA_AdminOrderDetailsPage'))
	{
		require_once('classes/admin/WCMCA_AdminOrderDetailsPage.php');
		$wcmca_admin_order_details_page_addon = new WCMCA_AdminOrderDetailsPage();
	}
	if(!class_exists('WCMCA_OptionPage'))
	{
		require_once('classes/admin/WCMCA_OptionPage.php');
		$wcmca_option_page = new WCMCA_OptionPage();
	}
	if(!class_exists('WCMCA_UserProfilePage'))
	{
		require_once('classes/admin/WCMCA_UserProfilePage.php');
		$wcmca_user_profile_page = new WCMCA_UserProfilePage();
	}
	
}
function wcmca_admin_init()
{
	$remove = remove_submenu_page( 'woocommerce-multiple-customer-addresses', 'woocommerce-multiple-customer-addresses');
	$remove = remove_submenu_page( 'woocommerce-multiple-customer-addresses', 'woocommerce-multiple-customer-addresses-edit-user');
}	
function wcmca_init_admin_panel()
{
	global $wcmca_html_helper;
	$place = wcmca_get_free_menu_position(55, 0.1);
	
	//$hookname  = add_menu_page( __( 'WooCommerce Multiple Addresses', 'woocommerce-multiple-customer-addresses' ), __( 'WooCommerce Multiple Addresses', 'woocommerce-multiple-customer-addresses' ), 'manage_woocommerce', 'woocommerce-multiple-customer-addresses', null, '', (string)$place );
	//add_submenu_page('woocommerce-multiple-customer-addresses', __('Edit page','woocommerce-multiple-customer-addresses'), __('Edit page','woocommerce-multiple-customer-addresses'), 'edit_shop_orders', 'woocommerce-multiple-customer-addresses-edit-user', 'wcmca_render_admin_user_addresses_edit_page');

	
}

function wcmca_get_free_menu_position($start, $increment = 0.1)
{
	foreach ($GLOBALS['menu'] as $key => $menu) {
		$menus_positions[] = $key;
	}
	
	if (!in_array($start, $menus_positions)) return $start;

	/* the position is already reserved find the closet one */
	while (in_array($start, $menus_positions)) 
	{
		$start += $increment;
	}
	return $start;
}
function wcmca_var_dump($data)
{
	echo "<pre>";
	var_dump($data);
	echo "</pre>";
}
function wcmca_is_wcbcf_active()
{
	if (in_array( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ))
		return true;
	
	return false;
}

?>