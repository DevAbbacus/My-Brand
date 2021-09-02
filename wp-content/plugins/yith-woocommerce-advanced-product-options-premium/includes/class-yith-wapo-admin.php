<?php

/**
 *	Admin class
 *
 *	@author  Corrado Porzio <corradoporzio@gmail.com>
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WAPO_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WAPO_Admin
		 */
		protected static $instance;

		/**
		 * Plugin options
		 *
		 * @var array
		 */
		public $options = array();

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = YITH_WAPO_VERSION;

		/**
		 * The plugin panel
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel;

		/**
		 * Premium version landing link
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/';

		/**
		 * Panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wapo_panel';

		/**
		 * Documentation URL
		 *
		 * @var string
		 */
		public $doc_url = 'https://docs.yithemes.com/yith-woocommerce-product-add-ons/';

		/**
		 * Demo URL
		 *
		 * @var string
		 */
		public $demo_url = 'https://plugins.yithemes.com/yith-woocommerce-product-add-ons/product/custom-post/';

		/**
		 * YITH Site URL
		 *
		 * @var string
		 */
		public $yith_url = 'https://www.yithemes.com';

		/**
		 * Landing URL
		 *
		 * @var string
		 */
		public $plugin_url = 'https://yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/';

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WAPO_Admin | YITH_WAPO_Admin_Premium
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );
			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			// Add action links.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WAPO_DIR . '/' . basename( YITH_WAPO_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			// Add Capabilities to Administrator and Shop Manager.
			// add_action( 'admin_init', array( $this, 'add_capabilities' ) );

			// Enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			// Blocks Settings
			add_action( 'yith_wapo_show_block_tab', array( $this, 'show_block_tab' ) );
			add_action( 'yith_wapo_show_blocks_tab', array( $this, 'show_blocks_tab' ) );

			// Premium Tabs
			add_action( 'yith_wapo_premium_tab', array( $this, 'show_premium_tab' ) );
		}

		/**
		 * Return an array of links for the YITH Sidebar
		 *
		 * @return array
		 */
		public function get_panel_sidebar_links() {
			$links = array(
				array(
					'url'   => $this->yith_url,
					'title' => __( 'Your Inspiration Themes', YITH_WAPO_LOCALIZE_SLUG ),
				),
				array(
					'url'   => $this->doc_url,
					'title' => __( 'Plugin Documentation', YITH_WAPO_LOCALIZE_SLUG ),
				),
				array(
					'url'   => $this->plugin_url,
					'title' => __( 'Plugin Site', YITH_WAPO_LOCALIZE_SLUG ),
				),
				array(
					'url'   => $this->demo_url,
					'title' => __( 'Live Demo', YITH_WAPO_LOCALIZE_SLUG ),
				),
			);

			return $links;
		}

		/**
		 * Action Links
		 * add the action links to plugin admin page
		 *
		 * @param array $links Action links.
		 *
		 * @use		plugin_action_links_{$plugin_file_name}
		 * @Return	array
		 * @author	Leanza Francesco <leanzafrancesco@gmail.com>
		 */
		public function action_links( $links ) {
			return yith_add_action_links( $links, $this->panel_page, defined( 'YITH_WAPO_PREMIUM' ), YITH_WAPO_SLUG );
		}

		/**
		 * Adds action links to plugin admin page
		 *
		 * @param array    $row_meta_args Row meta arguments.
		 * @param string[] $plugin_meta   An array of the plugin's metadata,
		 *                                including the version, author,
		 *                                author URI, and plugin URI.
		 * @param string   $plugin_file   Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data   An array of plugin data.
		 * @param string   $status        Status of the plugin. Defaults are 'All', 'Active',
		 *                                'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
		 *                                'Drop-ins', 'Search', 'Paused'.
		 *
		 * @return array
		 */
		public function plugin_row_meta( $row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( YITH_WAPO_INIT === $plugin_file ) {
				$row_meta_args['slug']       = YITH_WAPO_SLUG;
				$row_meta_args['is_premium'] = true;
			}
			return $row_meta_args;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Leanza Francesco <leanzafrancesco@gmail.com>
		 * @use      YIT_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = array(
				'blocks' => __( 'Options Blocks', YITH_WAPO_LOCALIZE_SLUG ),
				'settings' => __( 'General Settings', YITH_WAPO_LOCALIZE_SLUG ),
				'style' => __( 'Style', YITH_WAPO_LOCALIZE_SLUG ),
				'help' => __( 'Help', YITH_WAPO_LOCALIZE_SLUG ),
				// 'premium'  => __( 'Premium Version', YITH_WAPO_LOCALIZE_SLUG ),
			);

			$args = array(
				'create_menu_page' => true,
				'class'            => yith_set_wrapper_class(),
				'parent_slug'      => '',
				'plugin_slug'      => YITH_WAPO_SLUG,
				'page_title'       => 'WooCommerce Product Add-ons',
				'menu_title'       => 'Product Add-ons 2.0',
				'capability'       => 'manage_options',
				'parent'           => YITH_WAPO_SLUG,
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->panel_page,
				'links'            => $this->get_panel_sidebar_links(),
				'admin-tabs'       => $admin_tabs,
				'plugin-url'       => YITH_WAPO_DIR,
				'options-path'     => YITH_WAPO_DIR . 'plugin-options',
			);

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Admin enqueue scripts
		 */
		public function admin_enqueue_scripts() {
			$screen = get_current_screen();
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'wapo-admin', YITH_WAPO_URL . 'assets/css/_new_yith-wapo-admin.css' );
			
		}

		/**
		 * Add management capabilities to Admin and Shop Manager
		 *
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
		public function add_capabilities() {
			$caps = yith_wapo_create_capabilities( array( 'addon', 'addons' ) );

			$admin        = get_role( 'administrator' );
			$shop_manager = get_role( 'shop_manager' );

			foreach ( $caps as $cap => $value ) {
				if ( $admin ) {
					$admin->add_cap( $cap );
				}

				if ( $shop_manager ) {
					$shop_manager->add_cap( $cap );
				}
			}
		}

		/**
		 *	Show block tab
		 *
		 * @return	void
		 * @since	2.0
		 * @author	Corrado Porzio <corradoporzio@gmail.com>
		 */
		public function show_block_tab() {
			$template = YITH_WAPO_TEMPLATE_PATH . '/admin/block.php';
			file_exists( $template ) && require $template;
		}

		/**
		 *	Show blocks tab
		 *
		 * @return	void
		 * @since	2.0
		 * @author	Corrado Porzio <corradoporzio@gmail.com>
		 */
		public function show_blocks_tab() {
			$template = YITH_WAPO_TEMPLATE_PATH . '/admin/blocks.php';
			file_exists( $template ) && require $template;
		}

		/**
		 *	Show premium landing tab
		 *
		 *	@return	void
		 *	@since	2.0
		 *	@author	Corrado Porzio <corradoporzio@gmail.com>
		 */
		public function show_premium_tab() {
			$template = YITH_WAPO_TEMPLATE_PATH . '/premium.php';
			file_exists( $template ) && require $template;
		}

		/**
		 *	Get the premium landing uri
		 *
		 *	@return	string The premium landing link
		 *	@since	2.0
		 *	@author	Corrado Porzio <corradoporzio@gmail.com>
		 */
		public function get_premium_landing_uri() {
			return $this->premium_landing;
		}
	}
}

/**
 * Unique access to instance of YITH_WAPO_Admin class
 *
 * @return YITH_WAPO_Admin
 */
function yith_wapo_admin() {
	return YITH_WAPO_Admin::get_instance();
}
