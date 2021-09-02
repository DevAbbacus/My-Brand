<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Multistep_Checkout
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Multistep_Checkout_Premium' ) ) {
	/**
	 * Class YITH_Multistep_Checkout_Premium
	 *
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	class YITH_Multistep_Checkout_Premium extends YITH_Multistep_Checkout {

        /**
         * @var array|mixed|void The Image Sizes array
         */
        public $sizes = array();

		public function __construct() {
			// init image sizes array
			$this->sizes = array(
				'yith_wcms_timeline_style1' => array(
					'width'  => 39,
					'height' => 39,
					'crop'   => true
				),
				'yith_wcms_timeline_style2' => array(
					'width'  => 18,
					'height' => 18,
					'crop'   => true
				),
				'yith_wcms_timeline_style3' => array(
					'width'  => 25,
					'height' => 25,
					'crop'   => true
				),
				'yith_wcms_timeline_style4_horizontal' => array(
					'width'  => 80,
					'height' => 75,
					'crop'   => true
				),
				'yith_wcms_timeline_style4_vertical' => array(
					'width'  => 45,
					'height' => 40,
					'crop'   => true
				),
			);

            /* === Premium Initialization === */
            add_filter( 'yith_wcms_require_class', array( $this, 'load_premium_classes' ) );
            add_filter( 'after_setup_theme', array( $this, 'add_image_sizes' ) );

			/* === Register plugin to licence/update system === */
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

            parent::__construct();
        }

         /**
		 * Class Initializzation
		 *
		 * Instance the admin or frontend classes
		 *
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 * @since  1.0
		 * @return void
		 * @access protected
		 */
		public function init() {
            if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['context'] ) && $_REQUEST['context'] == 'frontend' ) ) {
				$this->admin = new YITH_Multistep_Checkout_Admin_Premium();
			}

            else {
            	if( apply_filters( 'yith_wcms_plugin_enabled_on_front', true ) ){
		            $this->frontend = new YITH_Multistep_Checkout_Frontend_Premium();
	            }
			}
		}

         /**
         * Main plugin Instance
         *
         * @return YITH_Multistep_Checkout Main instance
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Add premium files to Require array
         *
         * @param $require The require files array
         *
         * @return Array
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         *
         */
        public function load_premium_classes( $require ){
            $require['admin'][]     = 'includes/class.yith-multistep-checkout-admin-premium.php';
            $require['admin'][]     = 'includes/class.yith-multistep-checkout-privacy.php';
            $require['frontend'][]  = 'includes/class.yith-multistep-checkout-frontend-premium.php';
            $require['common'][]    = 'includes/functions.yith-wcms-premium.php';
            return $require;
        }

        /**
         * Add a body class(es)
         *
         * @param $classes The classes array
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array
         */
        public function body_class( $classes ){
            $classes = parent::body_class( $classes );
            $classes[] = 'yith-wcms-pro';
            return $classes;
        }

        /**
         * Add timeline image sizes
         *
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @since   1.0
         * @return  void
         * @fire    yith_wcms_image_sizes filter
         */
        public function add_image_sizes(){
            foreach( $this->sizes as $name => $size ){
                extract($size);
                add_image_size( $name, $width, $height, $crop );
            }
        }

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCMS_PATH . 'plugin-fw/lib/yit-plugin-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_WCMS_INIT, YITH_WCMS_SECRETKEY, YITH_WCMS_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_WCMS_PATH . 'plugin-fw/lib/yit-upgrade.php';
			}

			YIT_Upgrade()->register( YITH_WCMS_SLUG, YITH_WCMS_INIT );
		}
    }
}
