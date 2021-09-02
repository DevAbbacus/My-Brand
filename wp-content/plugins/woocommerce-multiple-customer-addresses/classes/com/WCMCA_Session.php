<?php 
class WCMCA_Session
{
	public function __construct()
	{
	}
	public function get_checkout_item_addresses()
	{
		
		/* if ( ! session_id() ) @ session_start();
		if ( ! isset($_SESSION['wcmca'])) $_SESSION['wcmca'] = array();
		if ( ! isset($_SESSION['wcmca']['checkout_item_addresses'])) $_SESSION['wcmca']['checkout_item_addresses'] = array(); 
		
		return $_SESSION['wcmca']['checkout_item_addresses'];*/
		
		$result = WC()->session->get('wcmca_checkout_item_addresses');
		$result = !isset($result) || !is_array($result) ? array() : $result;
	
		return $result;
	}
	public function set_checkout_item_addresses($addresses = null)
	{
		/* if ( ! session_id() ) @ session_start();
		if ( ! isset($_SESSION['wcmca'])) $_SESSION['wcmca'] = array();
		
		if(isset($addresses) && is_array($addresses))
			$_SESSION['wcmca']['checkout_item_addresses'] = $addresses;
		else
			$_SESSION['wcmca']['checkout_item_addresses'] = array(); */
		
		 WC()->session->set('wcmca_checkout_item_addresses', $addresses);
	}
}
?>