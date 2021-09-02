<?php

/**
 *	Settings Tab
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$style = array(
	'style' => array(
		'style-options' => array(
			'title' => __( 'Style Options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith-wapo-style-options',
		),
		'form-style' => array(
			'id'		=> 'yith-wapo-form-style',
			'name'		=> __( 'Form style', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Choose the general style for form: checkbox, radio, select, input, textarea, ecc.', YITH_WAPO_LOCALIZE_SLUG ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'default'	=> 'theme',
			'options'	=> array(
				'theme'		=> __( 'Theme style', YITH_WAPO_LOCALIZE_SLUG ),
				'plugin'	=> __( 'Plugin style', YITH_WAPO_LOCALIZE_SLUG ),
				'custom'	=> __( 'Custom style', YITH_WAPO_LOCALIZE_SLUG ),
			),
		),
		'show-toggle' => array(
			'id'		=> 'yith-wapo-show-tooltips',
			'name'		=> __( 'Show tooltips', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable if you want to show tooltips in product options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),
		'tooltip-color' => array(
			'id'		=> 'yith-wapo-tooltip-color',
			'name'		=> __( 'Tooltip color', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Set the color for this heading', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'name'		=> __( 'BACKGROUND', YITH_WAPO_LOCALIZE_SLUG ),
					'id'		=> 'background',
					'default'	=> '#AF2323'
				),
				array(
					'name'		=> __( 'TEXT', YITH_WAPO_LOCALIZE_SLUG ),
					'id'		=> 'text',
					'default'	=> '#AF2323'
				),
			)
		),
		'tooltip-position' => array(
			'id'		=> 'yith-wapo-tooltip-position',
			'name'		=> __( 'Tooltip position', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Choose the default position for tooltips', YITH_WAPO_LOCALIZE_SLUG ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'default'	=> 'top',
			'options'	=> array(
				'top'		=> __( 'Top', YITH_WAPO_LOCALIZE_SLUG ),
				'bottom'	=> __( 'Bottom', YITH_WAPO_LOCALIZE_SLUG ),
			),
		),
		'tooltip-icon' => array(
			'id'		=> 'yith-wapo-tooltip-icon',
			'name'		=> __( 'Tooltip icon', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Upload an optional icon to identify a tooltip element', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'upload',
		),
		'show-in-toggle' => array(
			'id'		=> 'yith-wapo-show-in-toggle',
			'name'		=> __( 'Show options in toggle', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable if you want to show the options blocks in toggle sections', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),
		'show-toggle-opened' => array(
			'id'		=> 'yith-wapo-show-toggle-opened',
			'name'		=> __( 'Show toggle opened by default', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable if you want to show the toggle opened by default', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),

		'style-options-end' => array(
			'id'	=> 'yith-wapo-style-options',
			'type'	=> 'sectionend',
		),
	),
);

return apply_filters( 'yith_wapo_panel_style_options', $style );
