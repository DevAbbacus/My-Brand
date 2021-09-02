<?php
/**
 * STYLE ARRAY OPTIONS
 */
if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

$position = get_option( 'yith_wcmap_menu_position', 'vertical-left' );
'horizontal' !== $position && $position = 'vertical';
$options = array(
	'no-borders' => array(
		'vertical'   => YITH_WCMAP_ASSETS_URL . '/images/admin/vertic-no-borders.svg',
		'horizontal' => YITH_WCMAP_ASSETS_URL . '/images/admin/horiz-no-borders.svg',
	),
	'modern'     => array(
		'vertical'   => YITH_WCMAP_ASSETS_URL . '/images/admin/vertic-modern.svg',
		'horizontal' => YITH_WCMAP_ASSETS_URL . '/images/admin/horiz-modern.svg',
	),
	'simple'     => array(
		'vertical'   => YITH_WCMAP_ASSETS_URL . '/images/admin/vertic-simple.svg',
		'horizontal' => YITH_WCMAP_ASSETS_URL . '/images/admin/horiz-simple.svg',
	),
);

$style = array(

	'style' => array(

		array(
			'title' => __( 'Style Options', 'yith-woocommerce-customize-myaccount-page' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith-wcmap-style-options',
		),

		array(
			'id'        => 'yith_wcmap_menu_position',
			'type'      => 'yith-field',
			'yith-type' => 'select-images',
			'title'     => esc_html_x( 'Menu position', 'Option: title', 'yith-woocommerce-customize-myaccount-page' ),
			'desc'      => esc_html_x( 'Choose the position of the links to be displayed in a horizontal or vertical sidebar.', 'Option: description', 'yith-woocommerce-customize-myaccount-page' ),
			'default'   => 'vertical-left',
			'options'   => array(
				'vertical-left'  => array(
					'label' => esc_html_x( 'Vertical Left', 'Option: Menu position', 'yith-woocommerce-customize-myaccount-page' ),
					'image' => YITH_WCMAP_ASSETS_URL . '/images/admin/vertical-left.svg',
				),
				'vertical-right' => array(
					'label' => esc_html_x( 'Vertical Right', 'Option: Menu position', 'yith-woocommerce-customize-myaccount-page' ),
					'image' => YITH_WCMAP_ASSETS_URL . '/images/admin/vertical-right.svg',
				),
				'horizontal'     => array(
					'label' => esc_html_x( 'Horizontal', 'Option: Menu position', 'yith-woocommerce-customize-myaccount-page' ),
					'image' => YITH_WCMAP_ASSETS_URL . '/images/admin/horizontal.svg',
				),
			),
		),

		array(
			'id'        => 'yith_wcmap_menu_layout',
			'type'      => 'yith-field',
			'yith-type' => 'select-images',
			'title'     => esc_html_x( 'Menu layout', 'Option: title', 'yith-woocommerce-customize-myaccount-page' ),
			'desc'      => esc_html_x( 'Choose the menu layout style.', 'Option: description', 'yith-woocommerce-customize-myaccount-page' ),
			'default'   => 'simple',
			'options'   => array(
				'no-borders' => array(
					'label' => esc_html_x( 'No borders', 'Option: Menu position', 'yith-woocommerce-customize-myaccount-page' ),
					'image' => $options['no-borders'][ $position ],
					'data'  => $options['no-borders'],
				),
				'modern'     => array(
					'label' => esc_html_x( 'Modern banners', 'Option: Menu position', 'yith-woocommerce-customize-myaccount-page' ),
					'image' => $options['modern'][ $position ],
					'data'  => $options['modern'],
				),
				'simple'     => array(
					'label' => esc_html_x( 'Simple tabs', 'Option: Menu position', 'yith-woocommerce-customize-myaccount-page' ),
					'image' => $options['simple'][ $position ],
					'data'  => $options['simple'],
				),
			),
		),

		array(
			'title'     => _x( 'Menu background', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'      => 'yith-field',
			'yith-type' => 'colorpicker',
			'id'        => 'yith_wcmap_menu_background_color',
			'default'   => '#f4f4f4',
			'deps'      => array(
				'id'     => 'yith_wcmap_menu_layout',
				'values' => 'no-borders',
			),
		),

		array(
			'title'     => _x( 'Border color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'      => 'yith-field',
			'yith-type' => 'colorpicker',
			'id'        => 'yith_wcmap_menu_border_color',
			'default'   => '#e0e0e0',
			'deps'      => array(
				'id'     => 'yith_wcmap_menu_layout',
				'values' => 'simple',
			),
		),

		array(
			'title'     => _x( 'Border color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'      => 'yith-field',
			'yith-type' => 'multi-colorpicker',
			'id'        => 'yith_wcmap_menu_item_border_color',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Default border color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'normal',
					'default' => '#eaeaea',
				),
				array(
					'name'    => _x( 'Hover border color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'hover',
					'default' => '#cceae9',
				),
				array(
					'name'    => _x( 'Active border color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'active',
					'default' => '#cceae9',
				),
			),
			'deps'      => array(
				'id'     => 'yith_wcmap_menu_layout',
				'values' => 'modern',
			),
		),

		array(
			'title'     => _x( 'Shadow color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'      => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'id'        => 'yith_wcmap_menu_item_shadow_color',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Default shadow color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'normal',
					'default' => 'rgba(114, 114, 114, 0.16)',
				),
				array(
					'name'    => _x( 'Hover shadow color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'hover',
					'default' => 'rgba(3,163,151,0.16)',
				),
				array(
					'name'    => _x( 'Active shadow color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'active',
					'default' => 'rgba(3,163,151,0.16)',
				),
			),
			'deps'      => array(
				'id'     => 'yith_wcmap_menu_layout',
				'values' => 'modern',
			),
		),

		array(
			'title'        => _x( 'Items background color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'id'           => 'yith_wcmap_background_color',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Default background color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'normal',
					'default' => '#ffffff',
				),
				array(
					'name'    => _x( 'Hover background color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'hover',
					'default' => '#ffffff',
				),
				array(
					'name'    => _x( 'Active background color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'active',
					'default' => '#ffffff',
				),
			),
			'deps'      => array(
				'id'     => 'yith_wcmap_menu_layout',
				'values' => 'modern,simple',
			),
		),

		array(
			'title'        => _x( 'Items text color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'id'           => 'yith_wcmap_text_color',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Default text color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'normal',
					'default' => '#777777',
				),
				array(
					'name'    => _x( 'Hover text color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'hover',
					'default' => '#000000',
				),
				array(
					'name'    => _x( 'Active text color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'active',
					'default' => '#000000',
				),
			),
		),

		array(
			'title'     => _x( 'Text size (px)', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'id'        => 'yith_wcmap_font_size',
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'default'   => 16,
			'min'       => 1,
			'step'      => 1
		),

		array(
			'title'        => __( 'Padding of menu items', 'yith-woocommerce-customize-myaccount-page' ),
			'id'           => 'yith_wcmap_items_padding',
			'type'         => 'yith-field',
			'yith-type'    => 'dimensions',
			'allow_linked' => false,
			'default'      => array(
				'unit'       => 'px',
				'dimensions' => array(
					'top'    => 12,
					'right'  => 5,
					'bottom' => 12,
					'left'   => 0,
				),
			),
		),

		array(
			'title'        => _x( 'Logout button colors', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'id'           => 'yith_wcmap_logout_button_color',
			'colorpickers' => array(
				array(
					'name'    => _x( 'Text color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'text_normal',
					'default' => '#ffffff',
				),
				array(
					'name'    => _x( 'Hover text color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'text_hover',
					'default' => '#ffffff',
				),
				array(
					'name'    => _x( 'Background color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'background_normal',
					'default' => '#c0c0c0',
				),
				array(
					'name'    => _x( 'Hover background color', '[admin]Plugin option label', 'yith-woocommerce-customize-myaccount-page' ),
					'id'      => 'background_hover',
					'default' => '#333333',
				),
			),
		),

		array(
			'type' => 'sectionend',
			'id'   => 'yith-wcmap-end-style-options',
		),
	),
);

return apply_filters( 'yith_wcmap_panel_style_options', $style );