<?php

/**
 *	Main class
 *
 *	@author  Corrado Porzio <corradoporzio@gmail.com>
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO' ) ) {
	/**
	 * YITH_WAPO class
	 */
	class YITH_WAPO {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WAPO
		 */
		protected static $instance;

		/**
		 * Admin object
		 *
		 * @var YITH_WAPO_Admin|YITH_WAPO_Admin_Premium
		 */
		public $admin;

		/**
		 * Frontend object
		 *
		 * @var YITH_WAPO_Frontend|YITH_WAPO_Frontend_Premium
		 */
		public $frontend;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WAPO|YITH_WAPO_Premium
		 */
		public static function get_instance() {
			$self = __CLASS__ . ( class_exists( __CLASS__ . '_Premium' ) ? '_Premium' : '' );

			return ! is_null( $self::$instance ) ? $self::$instance : $self::$instance = new $self();
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			// Load Plugin Framework
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// Admin
			if ( is_admin() && ( ! isset( $_REQUEST['action'] ) || ( isset( $_REQUEST['action'] ) && 'yith_load_product_quick_view' !== $_REQUEST['action'] ) ) ) {
				$this->admin = yith_wapo_admin();
			}

			/*
			// Frontend
			$is_ajax_request = defined( 'DOING_AJAX' ) && DOING_AJAX;
			if ( ! is_admin() || $is_ajax_request ) {
				$this->frontend = yith_wapo_frontend();
			}
			*/

			// yith_wapo_compatibility();
		}

		/**
		 * Load Plugin Framework
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( function_exists( 'YIT_Plugin_Licence' ) ) {
				YIT_Plugin_Licence()->register( YITH_WAPO_INIT, YITH_WAPO_SECRET_KEY, YITH_WAPO_SLUG );
			}
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( function_exists( 'YIT_Upgrade' ) ) {
				YIT_Upgrade()->register( YITH_WAPO_SLUG, YITH_WAPO_INIT );
			}
		}

	}
}

/**
 * Unique access to instance of YITH_WAPO class
 *
 * @return YITH_WAPO|YITH_WAPO_Premium
 * @since 1.0.0
 */
function YITH_WAPO() {
	return YITH_WAPO::get_instance();
}