<?php
/**
 * Settings Tab
 *
 * @package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$help = array(

	'help' => array(

		'help-options' => array(
			'title' => __( 'Help', YITH_WAPO_LOCALIZE_SLUG ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith-wapo-help-options',
		),
		'help-options-end' => array(
			'id'	=> 'yith-wapo-help-options',
			'type'	=> 'sectionend',
		),
	),
);

return apply_filters( 'yith_wapo_panel_help_options', $help );
