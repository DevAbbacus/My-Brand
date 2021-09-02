<?php
/**
 * YITH WooCommerce Points and Rewards Premium Compatibility Class
 *
 * @author  YITH
 * @package YITH WooCommerce Customize My Account Page
 * @version 1.0.0
 */


defined( 'YITH_WCMAP' ) || exit;

if ( ! class_exists( 'YITH_WCMAP_Points_Compatibility' ) ) {
	/**
	 * Class YITH_WCMAP_Points_Compatibility
	 *
	 * @since 3.0.0
	 */
	class YITH_WCMAP_Points_Compatibility extends YITH_WCMAP_Compatibility {

		/**
		 * Constructor
		 *
		 * @since 3.0.0
		 * @author Francesco Licandro
		 */
		public function __construct() {
			// Banner options
			add_filter( 'yith_wcmap_banner_counter_type_options', array( $this, 'add_counter_type' ), 10 );
			add_filter( 'yith_wcmap_banner_points_counter_value', array( $this, 'count_customer_points' ), 10, 2 );
		}

		/**
		 * Add gift card count option to available counter types
		 *
		 * @since 3.0.0
		 * @author Francesco Licandro
		 * @param array $options
		 * @return array
		 */
		public function add_counter_type( $options ) {
			$options['points'] = _x( 'Customer points', 'Banner counter option', 'yith-woocommerce-customize-myaccount-page' );
			return $options;
		}

		/**
		 * Return the number of customer gift cards
		 *
		 * @since 3.0.0
		 * @author Francesco Licandro
		 * @param integer $value
		 * @param integer $customer_id
		 * @return integer
		 */
		public function count_customer_points( $value, $customer_id = 0 ) {
			! $customer_id && $customer_id = get_current_user_id();
			$points = get_user_meta( $customer_id, '_ywpar_user_total_points', true );
			return intval( $points );
		}
	}
}

new YITH_WCMAP_Points_Compatibility();