<?php

/**
 *	Premium Tab
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WCBM' ) || exit; // Exit if accessed directly.

$premium = array(
	'premium' => array(
		'landing' => array(
			'type'         => 'custom_tab',
			'action'       => 'yith_wapo_premium_tab',
			'hide_sidebar' => true,
		),
	),
);

return apply_filters( 'yith_wapo_panel_premium_options', $blocks );
