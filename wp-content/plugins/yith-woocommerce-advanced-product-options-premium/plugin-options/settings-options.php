<?php
/**
 *	Settings Tab
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$settings = array(

	'settings' => array(
		'general-options' => array(
			'type' => 'multi_tab',
			'sub-tabs' => array(
				'settings-general' => array(
					'title' => esc_html_x( 'General options', 'Admin title of tab', YITH_WAPO_LOCALIZE_SLUG ),
				),
				'settings-cart' => array(
					'title' => esc_html_x( 'Cart & Order', 'Admin title of tab', YITH_WAPO_LOCALIZE_SLUG ),
				),
			),
		),
	),

	'settings-general' => array(

		'general-options' => array(
			'id'	=> 'yith-wapo-general-options',
			'title'	=> __( 'General options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'	=> 'title',
			'desc'	=> '',
		),
		'options-position' => array(
			'id'		=> 'yith-wapo-options-position',
			'name'		=> __( 'Options position in product page', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Choose the position for the options blocks.', YITH_WAPO_LOCALIZE_SLUG ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'default'	=> 'before',
			'options'	=> array(
				'before'	=> __( 'Before add to cart', YITH_WAPO_LOCALIZE_SLUG ),
				'after'		=> __( 'After add to cart', YITH_WAPO_LOCALIZE_SLUG ),
			),
		),
		'show-in-shop' => array(
			'id'		=> 'yith-wapo-show-in-shop',
			'name'		=> __( 'Show options in other WooCommerce pages', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to show the options also in shop page, category page and WooCommerce shortcodes', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'no',
		),
		'button-in-shop' => array(
			'id'      => 'yith-wapo-button-in-shop',
			'name'    => __( 'In WooCommerce pages show', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'    => __( 'Choose the position for the options blocks', YITH_WAPO_LOCALIZE_SLUG ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'default'	=> 'before',
			'options'	=> array(
				'select'	=> __( '"Select options" button', YITH_WAPO_LOCALIZE_SLUG ),
				'add'		=> __( '"Add to cart" button', YITH_WAPO_LOCALIZE_SLUG ),
			),
		),
		'select-options-label' => array(
			'id'		=> 'yith-wapo-select-options-label',
			'name'		=> __( 'Label for "Select options" button', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enter the text for the "Select options" button', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'text',
			'default'	=> 'Select options',
		),
		'hide-button-if-required' => array(
			'id'		=> 'yith-wapo-hide-button-if-required',
			'name'		=> __( 'Hide add to cart until required options are selected', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to hide the add to cart button until the user selects required options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),
		'total-price-box' => array(
			'id'      => 'yith-wapo-total-price-box',
			'name'    => __( 'Total price box', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'    => __( 'Choose the informations to add in the total price box', YITH_WAPO_LOCALIZE_SLUG ),
			'type'      => 'select',
			'yith-type' => 'radio',
			'default'	=> 'before',
			'options'	=> array(
				'final'		=> __( 'Show only final total', YITH_WAPO_LOCALIZE_SLUG ),
				'product'	=> __( 'Show product price and total options', YITH_WAPO_LOCALIZE_SLUG ),
				'total'		=> __( 'Show final total and hide total options only if value is 0', YITH_WAPO_LOCALIZE_SLUG ),
			),
		),
		'hide-titles-and-images' => array(
			'id'		=> 'yith-wapo-hide-titles-and-images',
			'name'		=> __( 'Hide titles and images of options groups', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to hide all titles and images setted in "display" tab of the options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'no',
		),
		'hide-images' => array(
			'id'		=> 'yith-wapo-hide-images',
			'name'		=> __( 'Hide images of all singular options', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to hide all the images uploaded in the "populate options" tab of the options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'no',
		),
		'general-options-end' => array(
			'id'   => 'yith-wapo-general-option',
			'type' => 'sectionend',
		),

		'upload-options' => array(
			'id'	=> 'yith-wapo-upload-options',
			'title'	=> __( 'Upload options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'	=> 'title',
			'desc'	=> '',
		),
		'uploads-folder' => array(
			'id'		=> 'yith-wapo-uploads-folder',
			'name'		=> __( 'Uploads folder', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enter the name of the folder used to storage the files uploaded from users', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'text',
			'default'	=> 'yith_advanced_product_options',
		),
		'allowed-file-types' => array(
			'id'		=> 'yith-wapo-allowed-file-types',
			'name'		=> __( 'Allowed file types', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enter which file types can be uploaded by users.', YITH_WAPO_LOCALIZE_SLUG ) . '<br />'
							. __( 'Separate each file type with a comma. Example: .jpg, .png, .pdf', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'text',
			'default'	=> '.jpg, .pdf, .zip, .rar',
		),
		'max-file-size' => array(
			'id'		=> 'yith-wapo-max-file-size',
			'name'		=> __( 'Max file size allowed (MB)', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enter the maximum allowed size for files uploaded by users', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'text',
			'default'	=> '5',
		),
		'attach-file-to-email' => array(
			'id'		=> 'yith-wapo-attach-file-to-email',
			'name'		=> __( 'Attach uploaded files to orders emails', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable if you want to receive the files uploaded by users also in orders emails', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),
		'upload-options-end' => array(
			'id'   => 'yith-wapo-upload-option',
			'type' => 'sectionend',
		),

	),

	'settings-cart' => array(

		'cart-order' => array(
			'id'	=> 'yith-wapo-cart-order',
			'title'	=> __( 'Cart & Order options', YITH_WAPO_LOCALIZE_SLUG ),
			'type'	=> 'title',
			'desc'	=> '',
		),
		'show-options-in-cart-page' => array(
			'id'		=> 'yith-wapo-show-options-in-cart-page',
			'name'		=> __( 'Show options in cart page', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to show options details in cart page', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'no',
		),
		'show-replacement-image-in-cart' => array(
			'id'		=> 'yith-wapo-show-image-in-cart',
			'name'		=> __( 'Show the replacement image in cart', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to replace the product image with option image in cart', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),
		'hide-options-in-order-email' => array(
			'id'		=> 'yith-wapo-hide-options-in-order-email',
			'name'		=> __( 'Hide options in order email', YITH_WAPO_LOCALIZE_SLUG ),
			'desc'		=> __( 'Enable to hide the options in order email', YITH_WAPO_LOCALIZE_SLUG ),
			'type'		=> 'yith-field',
			'yith-type'	=> 'onoff',
			'default'	=> 'yes',
		),
		'cart-order-end' => array(
			'id'   => 'yith-wapo-cart-order',
			'type' => 'sectionend',
		),

	),
);

return apply_filters( 'yith_wapo_panel_settings_options', $settings );
