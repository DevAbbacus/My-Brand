<?php
/**
 * YITH WooCommerce Subscriptions Compatibility Class
 *
 * @author  YITH
 * @package YITH WooCommerce Customize My Account Page
 * @version 1.0.0
 */


defined( 'YITH_WCMAP' ) || exit;

if ( ! class_exists( 'YITH_WCMAP_Subscriptions_Compatibility' ) ) {
	/**
	 * Class YITH_WCMAP_Membership_Compatibility
	 *
	 * @since 3.0.0
	 */
	class YITH_WCMAP_Subscriptions_Compatibility extends YITH_WCMAP_Compatibility {

		/**
		 * Constructor
		 *
		 * @since 3.0.0
		 * @author Francesco Licandro
		 */
		public function __construct() {
			$this->endpoint_key = 'yith-subscription';
			$this->endpoint     = array(
				'slug'    => 'my-subscription',
				'label'   => __( 'My Subscriptions', 'yith-woocommerce-customize-myaccount-page' ),
				'icon'    => 'pencil',
				'content' => '[ywsbs_my_account_subscriptions]',
			);

			// Register endpoint
			$this->register_endpoint();

			// handle compatibility
			add_action( 'template_redirect', array( $this, 'hooks' ), 5 );
		}

		/**
		 * Compatibility hooks and filters
		 *
		 * @since 3.0.0
		 * @author Francesco Licandro
		 */
		public function hooks() {
			if ( function_exists( 'YWSBS_Subscription_My_Account' ) ) {
				// remove content in my account
				remove_action( 'woocommerce_before_my_account', array( YWSBS_Subscription_My_Account(), 'my_account_subscriptions' ), 10 );
			}

			add_filter( 'yith_wcmap_endpoint_menu_class', array( $this, 'set_active' ), 10, 3 );
		}

		/**
		 * Assign active class to endpoint subscription
		 *
		 * @since  3.0.0
		 * @author Francesco Licandro
		 * @param array  $classes
		 * @param string $endpoint
		 * @param array  $options
		 * @return array
		 */
		public function set_active( $classes, $endpoint, $options ) {

			global $wp;

			if ( $endpoint == 'yith-subscription' && ! in_array( 'active', $classes ) && isset( $wp->query_vars['view-subscription'] ) ) {
				$classes[] = 'active';
			}

			return $classes;
		}
	}
}

new YITH_WCMAP_Subscriptions_Compatibility();