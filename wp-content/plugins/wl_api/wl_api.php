<?php

/**
 * Plugin Name: wl api
 * Plugin URI: https://woocommerce.com/
 * Description: this is wl api plugin
 * Version: 5.0.0
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Text Domain: woocommerce
 * Domain Path: /i18n/languages/
 * Requires at least: 5.4
 * Requires PHP: 7.0
 *
 * @package WooCommerce
 */


function wl_post()
{
	$key_data = array('consumer_key' => 'ck_be206cc24a71a758827c3c01ff16d032b6f3fed5', 'consumer_secret' => 'cs_c32e0d0a6ce4be55be0724b58bd3203f585947d6' );
	return $key_data;
}

add_action('rest_api_init',function(){
	register_rest_route('wl/v1','post',[
		'method' => 'Get',
		'callback' => 'wl_post',
	]);
});