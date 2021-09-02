<?php
/**
 * Plugin menu items class
 *
 * @author  YITH
 * @package YITH WooCommerce Customize My Account Page
 * @version 2.4.0
 */

defined( 'YITH_WCMAP' ) || exit;

if ( ! class_exists( 'YITH_WCMAP_Items' ) ) {
	/**
	 * Items class.
	 * The class manage all plugin endpoints items.
	 *
	 * @since 2.4.0
	 */
	class YITH_WCMAP_Items {

		/**
		 * Item types
		 *
		 * @since 3.0.0
		 * @aconst array
		 */
		const ITEM_TYPES = array(
			'endpoint',
			'group',
			'link'
		);

		/**
		 * Items array
		 *
		 * @since 2.4.0
		 * @var array
		 */
		private $items = array();

		/**
		 * Default items array
		 *
		 * @since 2.4.0
		 * @var array
		 */
		private $default_items = array();

		/**
		 * Plugins items array
		 *
		 * @since 2.4.0
		 * @var array
		 */
		private $plugins_items = array();

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  2.4.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ), 20 );
			add_action( 'init', array( $this, 'add_custom_endpoints' ), 21 );
			add_action( 'init', array( $this, 'rewrite_rules' ), 22 );
		}

		/**
		 * Get items method
		 *
		 * @since  2.4.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_items() {
			return apply_filters( 'yith_wcmap_get_items', $this->items );
		}

		/**
		 * Get default items method
		 *
		 * @since  2.4.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_default_items() {
			return apply_filters( 'yith_wcmap_get_default_items', $this->default_items );
		}

		/**
		 * Get plugins items method
		 *
		 * @since  2.4.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_plugins_items() {
			return apply_filters( 'yith_wcmap_get_plugins_items', $this->plugins_items );
		}

		/**
		 * Get a single item
		 *
		 * @since  3.0.0
		 * @author Francesco Licandro
		 * @param string $key
		 * @return array
		 */
		public function get_single_item( $key, $items = array() ) {

			empty( $items ) && $items = $this->get_items();

			foreach ( $items as $item_key => $options ) {
				if( $item_key === $key ) {
					return $options;
				}

				if( ! empty( $options['children'] ) ) {
					$child = $this->get_single_item( $key, $options['children'] );
					if( ! empty( $child ) ) {
						return $child;
					}
				}
			}

			return array();
		}

		/**
		 * Get items slug
		 *
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_items_slug() {
			// TODO use wp_cache
			$slugs = array();
			foreach ( $this->get_items() as $key => $field ) {
				isset( $field['slug'] ) && $slugs[ $key ] = $field['slug'];
				if ( isset( $field['children'] ) ) {
					foreach ( $field['children'] as $child_key => $child ) {
						isset( $child['slug'] ) && $slugs[ $child_key ] = $child['slug'];
					}
				}
			}

			return $slugs;
		}

		/**
		 * Get items keys
		 *
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @return array
		 */
		public function get_items_keys() {
			$keys = array();
			foreach ( $this->get_items() as $items_key => $item ) {
				$keys[] = $items_key;
				if ( isset( $item['children'] ) ) {
					foreach ( $item['children'] as $child_key => $child ) {
						$keys[] = $child_key;
					}
				}
			}

			return $keys;
		}

		/**
		 * Get plugins items method
		 *
		 * @since  2.4.0
		 * @author Francesco Licandro
		 * @param string $key
		 * @param array  $data
		 */
		public function register_plugin_item( $key, $data ) {
			$this->plugins_items[ $key ] = $data;
		}

		/**
		 * Init items
		 *
		 * @since  2.4.0
		 * @author Francesco Licandro
		 * @param boolean $force Force the init
		 */
		public function init( $force = false ) {

			if ( ! empty( $this->items ) && ! $force ) {
				return;
			}

			// get saved endpoints order
			$items = get_option( 'yith_wcmap_endpoint', '' );
			$items = json_decode( $items, true );

			// set empty array is false or null
			( ! $items || is_null( $items ) ) && $items = array();

			$this->items = array();

			// get default endpoints
			$this->init_default_items();
			$defaults = array_merge( $this->default_items, $this->plugins_items );

			if ( empty( $items ) ) {
				$this->items = $defaults;
			} else {

				foreach ( $items as $id => $item_option ) {

					// build return array
					$this->items[ $id ] = array();

					$options = get_option( 'yith_wcmap_endpoint_' . $id, array() );

					if ( is_array( $options ) ) {

						empty( $item_option['type'] ) && $item_option['type'] = 'endpoint';
						$options_default = call_user_func( "yith_wcmap_get_default_{$item_option['type']}_options", $id );
						// is empty check on default endpoint
						( empty( $options ) && isset( $defaults[ $id ] ) ) && $options = $defaults[ $id ];
						// always merge with default
						$options = array_merge( $options_default, $options );

						if ( isset( $item_option['children'] ) ) {

							$children = array();

							foreach ( $item_option['children'] as $child_id => $child ) {
								$child_options   = get_option( 'yith_wcmap_endpoint_' . $child_id, array() );
								$options_default = call_user_func( "yith_wcmap_get_default_{$child['type']}_options", $child_id );
								// is empty check on default endpoint
								( empty( $child_options ) && isset( $defaults[ $id ] ) ) && $child_options = $defaults[ $id ];
								// always merge with default
								$children[ $child_id ] = is_array( $child_options ) ? array_merge( $options_default, $child_options ) : $options_default;

								// check child on default plugin
								unset( $defaults[ $child_id ] );
							}

							$options['children'] = $children;
						}

						// unset on defaults
						unset( $defaults[ $id ] );

						$this->items[ $id ] = $options;
					}
				}

				// merge with defaults again
				$this->items = array_merge( $this->items, $defaults );
			}
		}

		/**
		 * Init default items
		 *
		 * @since  2.4.0
		 * @access protected
		 * @author Francesco Licandro
		 */
		protected function init_default_items() {

			if ( ! empty( $this->default_items ) ) {
				return;
			}

			$endpoints_slugs = array(
				'orders'          => get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' ),
				'downloads'       => get_option( 'woocommerce_myaccount_downloads_endpoint', 'downloads' ),
				'edit-address'    => get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' ),
				'payment-methods' => get_option( 'woocommerce_myaccount_payment_methods_endpoint', 'payment-methods' ),
				'edit-account'    => get_option( 'woocommerce_myaccount_edit_account_endpoint', 'edit-account' ),
				'customer-logout' => get_option( 'woocommerce_logout_endpoint', 'customer-logout' ),
			);

			$endpoints = array(
				'dashboard'       => __( 'Dashboard', 'yith-woocommerce-customize-myaccount-page' ),
				'orders'          => __( 'My Orders', 'yith-woocommerce-customize-myaccount-page' ),
				'downloads'       => __( 'My Downloads', 'yith-woocommerce-customize-myaccount-page' ),
				'edit-address'    => __( 'Edit Address', 'yith-woocommerce-customize-myaccount-page' ),
				'payment-methods' => __( 'Payment Methods', 'yith-woocommerce-customize-myaccount-page' ),
				'edit-account'    => __( 'Edit Account', 'yith-woocommerce-customize-myaccount-page' ),
				'customer-logout' => __( 'Logout', 'yith-woocommerce-customize-myaccount-page' ),
			);

			$menu_items_endpoint = apply_filters( 'woocommerce_account_menu_items', $endpoints, $endpoints_slugs );
			! is_array( $menu_items_endpoint ) && $menu_items_endpoint = array();
			$endpoints = array_merge( $endpoints, $menu_items_endpoint );

			if ( class_exists( 'WC_Subscriptions' ) ) {
				unset( $endpoints['subscriptions'] );
			}

			$registered_endpoint = WC()->query->get_query_vars();
			$this->default_items = array();

			// populate endpoints array with options
			foreach ( $endpoints as $endpoint_key => $endpoint_label ) {

				// always exclude customer logout or endpoint not in menu if are not wc default
				if ( $endpoint_key == 'customer-logout' || ( $endpoint_key != 'dashboard' && ! array_key_exists( $endpoint_key, $registered_endpoint ) ) ) {
					continue;
				}

				$slug    = isset( $registered_endpoint[ $endpoint_key ] ) ? $registered_endpoint[ $endpoint_key ] : $endpoint_key;
				$options = yith_wcmap_get_default_endpoint_options( $slug );
				// set label
				$options['label'] = $endpoint_label;

				switch ( $endpoint_key ) {
					case 'orders':
						$options['icon'] = 'file-text-o';
						break;
					case 'edit-account':
					case 'edit-address':
						$options['icon'] = 'pencil-square-o';
						break;
					case 'downloads':
						$options['icon'] = 'download';
						break;
					case 'dashboard':
						$options['icon'] = 'tachometer';
						break;
					case 'payment-methods' :
						$options['icon'] = 'money';
						break;
					default:
						break;
				}

				$this->default_items[ $endpoint_key ] = $options;
			}
		}

		/**
		 * Add custom endpoints to main WC array
		 *
		 * @access public
		 * @since  3.0.0
		 * @author Francesco Licandro
		 */
		public function add_custom_endpoints() {
			$slugs = $this->get_items_slug();
			if ( empty( $slugs ) || ! is_array( $slugs ) ) {
				return;
			}

			$mask = WC()->query->get_endpoints_mask();

			foreach ( $slugs as $key => $slug ) {
				if ( $key == 'dashboard' || isset( WC()->query->query_vars[ $key ] ) ) {
					continue;
				}

				WC()->query->query_vars[ $key ] = $slug;
				add_rewrite_endpoint( $slug, $mask );
			}
		}

		/**
		 * Rewrite rules
		 *
		 * @access public
		 * @since  1.0.0
		 * @author Francesco Licandro
		 * @param boolean $force
		 */
		public function rewrite_rules( $force = false ) {
			$do_flush = get_option( 'yith_wcmap_flush_rewrite_rules', 1 );

			if ( $do_flush || $force ) {
				// change option
				update_option( 'yith_wcmap_flush_rewrite_rules', 0 );
				// the flush rewrite rules
				flush_rewrite_rules();
			}
		}

		/**
		 * Change Active status given item
		 *
		 * @access public
		 * @since 3.0.0
		 * @author Francesco Licandro
		 * @param string  $item_key
		 * @param boolean $active
		 * @return boolean
		 */
		public function change_status_item( $item_key, $active = true ) {

			// Check if given item key exists
			if ( ! in_array( $item_key, $this->get_items_keys() ) ) {
				return false;
			}

			$options           = get_option( 'yith_wcmap_endpoint_' . $item_key );
			$options['active'] = $active;
			update_option( 'yith_wcmap_endpoint_' . $item_key, $options );
			// Re-init items
			$this->init( true );

			return true;
		}

		/**
		 * Add a new item
		 *
		 * @access public
		 * @since  3.0.0
		 * @author Francesco Licandro
		 * @param string $item_type
		 * @param array  $options
		 * @return boolean
		 */
		public function add_item( $item_type, $options ) {

			$slug   = ! empty( $options['slug'] ) ? yith_wcmap_sanitize_item_key( $options['slug'] ) : '';
			$label  = ! empty( $options['label'] ) ? stripslashes( $options['label'] ) : '';

			if( ! in_array( $item_type, self::ITEM_TYPES ) || empty( $label ) || ( 'endpoint' === $item_type && empty( $slug ) ) ) {
				return false;
			}

			$item_key_base  = $slug ? $slug : $label;
			$item_key       = $item_key_base ? $this->generate_unique_item_key( $item_key_base ) : '';
			if( ! $item_key ) {
				return false;
			}

			// Merge data with default
			// TODO double check for default
			$default    = call_user_func( "yith_wcmap_get_default_{$item_type}_options", $item_key );
			$options    = array_merge( $default, $options );
			// Save new item data
			$this->save_item( $item_key, $item_type, $options, false );

			// get saved endpoints order
			$items = get_option( 'yith_wcmap_endpoint', '' );
			$items = json_decode( $items, true );

			$items[ $item_key ] = array(
				'type'  => $item_type
			);

			update_option( 'yith_wcmap_endpoint', json_encode( $items ) );
			$this->init( true );

			return $item_key;
		}

		/**
		 * Get and save the endpoint options
		 *
		 * @access public
		 * @since  3.0.0
		 * @author Francesco Licandro
		 * @param string $item_key
		 * @param string $item_type
		 * @param array  $data
		 * @param boolean $init
		 * @return boolean
		 */
		public function save_item( $item_key, $item_type, $data, $init = true ) {

			$item_key       = yith_wcmap_sanitize_item_key( $item_key );
			$data['label']  = ! empty( $data['label'] ) ? stripslashes( $data['label'] ) : '';
			$data['active'] = isset( $data['active'] );

			switch ( $item_type ) {
				case 'group':
					$data['open'] = isset( $data['open'] );
					break;
				case 'link':
					$data['url']          = ! empty( $data['url'] ) ? esc_url_raw( $data['url'] ) : '#';
					$data['target_blank'] = isset( $data['target_blank'] );
					break;
				default:

					$data['slug']    = ! empty( $data['slug'] ) ? yith_wcmap_sanitize_item_key( $data['slug'] ) : $item_key;
					$data['content'] = ! empty( $data['content'] ) ? $data['content'] : '';

					update_option( 'woocommerce_myaccount_' . str_replace( '-', '_', $item_key ) . '_endpoint', $data['slug'] );
					break;
			}

			update_option( 'yith_wcmap_endpoint_' . $item_key, $data );
			// Re-init if needed
			$init && $this->init( true );
			return true;
		}

		/**
		 * Remove given item
		 *
		 * @access public
		 * @since 3.0.0
		 * @author Francesco Licandro
		 * @param string $remove_key
		 * @return boolean
		 */
		public function remove_item( $remove_key ) {

			// Exit if given item is a default one or a plugin one
			if ( array_key_exists( $remove_key, $this->get_default_items() ) || array_key_exists( $remove_key, $this->get_plugins_items() ) ) {
				return false;
			}

			delete_option( 'yith_wcmap_endpoint_' . $remove_key );
			// delete wc options if any
			delete_option( 'woocommerce_myaccount_' . str_replace( '-', '_', $remove_key ) . '_endpoint' );

			// get saved endpoints order
			$items = get_option( 'yith_wcmap_endpoint', '' );
			$items = json_decode( $items, true );

			foreach ( $items as $item_key => &$item ) {
				if ( $item_key === $remove_key ) {
					unset( $items[ $item_key ] );
					break;
				}

				if ( isset( $item['children'] ) ) {
					foreach ( $item['children'] as $child_key => $child ) {
						if ( $child_key === $remove_key ) {
							unset( $item['children'][ $child_key ] );
							break;
						}
					}
				}
			}

			update_option( 'yith_wcmap_endpoint', json_encode( $items ) );
			// Re-init items
			$this->init( true );

			return true;
		}

		/**
		 * Generate an unique item key from a base
		 *
		 * @since 3.0.0
		 * @author Francesco Licandro
		 * @param string $base
		 * @param integer $iteration
		 * @return string
		 */
		public function generate_unique_item_key( $base, $iteration = 0 ) {
			$key = yith_wcmap_sanitize_item_key( $base );
			if( in_array( $key, $this->get_items_keys() ) ) {
				$base .= ' ' . substr( 'abcdefghijklmnopqrstuvwxyz', rand( 0, 26 ), $iteration );
				return $this->generate_unique_item_key( $base, ++$iteration );
			}
			return $key;
		}
	}
}