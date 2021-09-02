<?php
defined( 'YITH_WCMBS' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCMBS_Elementor' ) ) {
	/**
	 * Elementor class
	 * handle Elementor widgets
	 *
	 * @since 1.4.0
	 */
	class YITH_WCMBS_Elementor {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCMBS_Elementor
		 */
		private static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCMBS_Elementor
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YITH_WCMBS_Elementor constructor.
		 */
		private function __construct() {
			if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '3.0.0', '>=' ) ) {
				add_action( 'init', array( $this, 'init' ) );
			}
		}

		/**
		 * Let's start with Elementor
		 */
		public function init() {
			$this->load_files();
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
			add_action( 'elementor/elements/categories_registered', array( $this, 'add_yith_category' ) );
		}

		/**
		 * Load files
		 */
		private function load_files() {
			require_once YITH_WCMBS_INCLUDES_PATH . '/builders/elementor/widgets/class.yith-wcmbs-elementor-members-only-content-start-widget.php';
		}

		/**
		 * Register Elementor Widgets
		 */
		public function register_widgets() {
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new YITH_WCMBS_Elementor_Members_Only_Content_Start_Widget() );
		}

		/**
		 * Add "YITH" group for Elementor widgets
		 *
		 * @param Elementor\Elements_Manager $elements_manager
		 */
		public function add_yith_category( $elements_manager ) {
			$elements_manager->add_category(
				'yith',
				array(
					'title' => 'YITH',
					'icon'  => 'fa fa-plug',
				)
			);
		}
	}
}