<?php

/**
 *	Blocks Tab
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$blocks = array(
	'blocks' => array(
		'blocks-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wapo_show_blocks_tab'
		),
	),
);

return apply_filters( 'yith_wapo_panel_blocks_options', $blocks );
