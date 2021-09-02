<?php
/**
 *
 * The framework's functions and definitions
 *
 */

/**
 * ------------------------------------------------------------------------------------------------
 * Define constants.
 * ------------------------------------------------------------------------------------------------
 */
define( 'WOODMART_THEME_DIR', 		get_template_directory_uri() );
define( 'WOODMART_THEMEROOT', 		get_template_directory() );
define( 'WOODMART_IMAGES', 			WOODMART_THEME_DIR . '/images' );
define( 'WOODMART_SCRIPTS', 		WOODMART_THEME_DIR . '/js' );
define( 'WOODMART_STYLES', 			WOODMART_THEME_DIR . '/css' );
define( 'WOODMART_FRAMEWORK', 		'/inc' );
define( 'WOODMART_DUMMY', 			WOODMART_THEME_DIR . '/inc/dummy-content' );
define( 'WOODMART_CLASSES', 		WOODMART_THEMEROOT . '/inc/classes' );
define( 'WOODMART_CONFIGS', 		WOODMART_THEMEROOT . '/inc/configs' );
define( 'WOODMART_HEADER_BUILDER',  WOODMART_THEME_DIR . '/inc/header-builder' );
define( 'WOODMART_ASSETS', 			WOODMART_THEME_DIR . '/inc/admin/assets' );
define( 'WOODMART_ASSETS_IMAGES', 	WOODMART_ASSETS    . '/images' );
define( 'WOODMART_API_URL', 		'https://xtemos.com/licenses/api/' );
define( 'WOODMART_DEMO_URL', 		'https://woodmart.xtemos.com/' );
define( 'WOODMART_PLUGINS_URL', 	WOODMART_DEMO_URL . 'plugins/');
define( 'WOODMART_DUMMY_URL', 		WOODMART_DEMO_URL . 'dummy-content/');
define( 'WOODMART_SLUG', 			'woodmart' );
define( 'WOODMART_CORE_VERSION', 	'1.0.20' );
define( 'WOODMART_WPB_CSS_VERSION', '1.0.1' );






/**
 * ------------------------------------------------------------------------------------------------
 * Load all CORE Classes and files
 * ------------------------------------------------------------------------------------------------
 */

if( ! function_exists( 'woodmart_autoload' ) ) {
    function woodmart_autoload($className) {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $className = str_replace('WOODMART_', '', $className);
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fileName = WOODMART_CLASSES . DIRECTORY_SEPARATOR . $fileName;
        if( file_exists( $fileName )) {
            require $fileName;
        }
    }

    spl_autoload_register('woodmart_autoload');
}

$woodmart_theme = new WOODMART_Theme();

/**
 * ------------------------------------------------------------------------------------------------
 * Enqueue styles
 * ------------------------------------------------------------------------------------------------
 */
if( ! function_exists( 'woodmart_enqueue_styles' ) ) {


	add_action( 'wp_enqueue_scripts', 'woodmart_enqueue_styles', 10000 );

	function woodmart_enqueue_styles() {
		$version = woodmart_get_theme_info( 'Version' );
		$minified = woodmart_get_opt( 'minified_css' ) ? '.min' : '';
		$is_rtl = is_rtl() ? '-rtl' : '';
		$style_url = WOODMART_THEME_DIR . '/style' . $minified . '.css';
		if ( woodmart_woocommerce_installed() && is_rtl() ) {
			$style_url = WOODMART_STYLES . '/style-rtl' . $minified . '.css';
		} elseif ( ! woodmart_woocommerce_installed() ) {
			$style_url = WOODMART_STYLES . '/base' . $is_rtl . $minified . '.css';
		}

		// Custom CSS generated from the dashboard.
		
		$file = get_option('woodmart-generated-css-file');
		$file_data = isset( $file['file'] ) ? get_file_data( $file['file'], array( 'Version' => 'Version' ) ) : array();
		$file_version = isset( $file_data['Version'] ) ? $file_data['Version'] : '';
		if( ! empty( $file ) && ! empty( $file['url'] ) && version_compare( $version, $file_version, '==' ) ) {
			$style_url = $file['url'];
		}

		wp_deregister_style( 'dokan-fontawesome' );
		wp_dequeue_style( 'dokan-fontawesome' );

		wp_deregister_style( 'font-awesome' );
		wp_dequeue_style( 'font-awesome' );

		wp_dequeue_style( 'vc_pageable_owl-carousel-css' );
		wp_dequeue_style( 'vc_pageable_owl-carousel-css-theme' );
		
		wp_deregister_style( 'woocommerce_prettyPhoto_css' );
		wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
		
		wp_deregister_style( 'contact-form-7' );
		wp_dequeue_style( 'contact-form-7' );
		wp_deregister_style( 'contact-form-7-rtl' );
		wp_dequeue_style( 'contact-form-7-rtl' );

		$wpbfile = get_option('woodmart-generated-wpbcss-file');
		$wpbfile_data = isset( $wpbfile['file'] ) ? get_file_data( $wpbfile['file'], array( 'Version' => 'Version' ) ) : array();
		$wpbfile_version = isset( $wpbfile_data['Version'] ) ? $wpbfile_data['Version'] : '';
		if( ! empty( $wpbfile ) && ! empty( $wpbfile['url'] ) && version_compare( WOODMART_WPB_CSS_VERSION, $wpbfile_version, '==' ) ) {
			$wpbcssfile_url = $wpbfile['url'];

			$inline_styles = wp_styles()->get_data( 'js_composer_front', 'after' );

			wp_deregister_style( 'js_composer_front' );
			wp_dequeue_style( 'js_composer_front' );
			wp_register_style( 'js_composer_front', $wpbcssfile_url, array(), $version );
			if ( ! empty( $inline_styles ) ) {
				$inline_styles = implode( "\n", $inline_styles );
				wp_add_inline_style( 'js_composer_front', $inline_styles );
			}
		}

		wp_enqueue_style( 'js_composer_front', false, array(), $version );

		if ( ! woodmart_get_opt( 'disable_font_awesome_theme_css' ) ) {
			if ( woodmart_get_opt( 'light_font_awesome_version' ) ) {
				wp_enqueue_style( 'font-awesome-css', WOODMART_STYLES . '/font-awesome-light.min.css', array(), $version );
			} else {
				wp_enqueue_style( 'font-awesome-css', WOODMART_STYLES . '/font-awesome.min.css', array(), $version );
			}
		}

		if ( woodmart_get_opt( 'light_bootstrap_version' ) ) {
			wp_enqueue_style( 'bootstrap', WOODMART_STYLES . '/bootstrap-light.min.css', array(), $version );
		} else {
			wp_enqueue_style( 'bootstrap', WOODMART_STYLES . '/bootstrap.min.css', array(), $version );
		}
		
		if ( woodmart_get_opt( 'disable_gutenberg_css' ) ) {
			wp_deregister_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library' );
			
			wp_deregister_style( 'wc-block-style' );
			wp_dequeue_style( 'wc-block-style' );
		}

		wp_enqueue_style( 'front_style', $style_url . '/css/front_style.css', array(), $version );
		
		wp_enqueue_style( 'woodmart-style', $style_url, array( 'bootstrap' ), $version );

		wp_enqueue_style( 'flatpickr', $style_url . '/css/flatpickr.min.css', array(), $version );
		wp_enqueue_style( 'jquery-ui.min', $style_url . '/css/jquery-ui.min.css', array(), $version );

		// load typekit fonts
		$typekit_id = woodmart_get_opt( 'typekit_id' );

		if ( $typekit_id ) {
			wp_enqueue_style( 'woodmart-typekit', 'https://use.typekit.net/' . esc_attr ( $typekit_id ) . '.css', array(), $version );
		}

		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');

		wp_register_style( 'woodmart-inline-css', false );
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Enqueue scripts
 * ------------------------------------------------------------------------------------------------
 */
 
if( ! function_exists( 'woodmart_enqueue_scripts' ) ) {
	add_action( 'wp_enqueue_scripts', 'woodmart_enqueue_scripts', 10000 );

	function woodmart_enqueue_scripts() {
		
		$version = woodmart_get_theme_info( 'Version' );
		/*
		 * Adds JavaScript to pages with the comment form to support
		 * sites with threaded comments (when in use).
		 */
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply', false, array(), $version );
		}
		if( ! woodmart_woocommerce_installed() ) {
			wp_register_script( 'js-cookie', woodmart_get_script_url( 'js.cookie' ), array( 'jquery' ), $version, true );
		}

		wp_dequeue_script( 'flexslider' );
		wp_dequeue_script( 'photoswipe-ui-default' );
		wp_dequeue_script( 'prettyPhoto-init' );
		wp_dequeue_script( 'prettyPhoto' );
		wp_dequeue_style( 'photoswipe-default-skin' );
		if( woodmart_get_opt( 'image_action' ) != 'zoom' ) {
			wp_dequeue_script( 'zoom' );
		}

		wp_enqueue_script( 'wpb_composer_front_js', false, array(), $version );
		wp_enqueue_script( 'imagesloaded', false, array(), $version );

		if( woodmart_get_opt( 'combined_js' ) ) {
		    wp_enqueue_script( 'isotope', woodmart_get_script_url( 'isotope.pkgd' ), array(), $version, true );
		    wp_enqueue_script( 'woodmart-theme', WOODMART_SCRIPTS . '/theme.min.js', array( 'jquery', 'js-cookie' ), $version, true );
		} else {
			wp_enqueue_script( 'woodmart-owl-carousel', woodmart_get_script_url( 'owl.carousel' ), array(), $version, true );
			wp_enqueue_script( 'woodmart-tooltips', woodmart_get_script_url( 'jquery.tooltips' ), array(), $version, true );
			wp_enqueue_script( 'woodmart-magnific-popup', woodmart_get_script_url( 'jquery.magnific-popup' ), array(), $version, true );
			wp_enqueue_script( 'woodmart-device', woodmart_get_script_url( 'device' ), array( 'jquery' ), $version, true );
			wp_enqueue_script( 'woodmart-waypoints', woodmart_get_script_url( 'waypoints' ), array( 'jquery' ), $version, true );

			if ( woodmart_get_opt( 'disable_nanoscroller' ) != 'disable' ) {
				wp_enqueue_script( 'woodmart-nanoscroller', woodmart_get_script_url( 'jquery.nanoscroller' ), array(), $version, true );
			}

			$minified = woodmart_get_opt( 'minified_js' ) ? '.min' : '';
			$base = ! woodmart_woocommerce_installed() ? '-base' : '';
			wp_enqueue_script( 'woodmart-theme', WOODMART_SCRIPTS . '/functions' . $base . $minified . '.js', array( 'js-cookie' ), $version, true );
			if ( woodmart_get_opt( 'ajax_shop' ) && woodmart_woocommerce_installed() && ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) ) {
				wp_enqueue_script( 'woodmart-pjax', woodmart_get_script_url( 'jquery.pjax' ), array(), $version, true );
			}
		}
 		wp_add_inline_script( 'woodmart-theme', woodmart_settings_js(), 'after' );
		
		wp_register_script( 'woodmart-panr-parallax', woodmart_get_script_url( 'panr-parallax' ), array(), $version, true );
		wp_register_script( 'woodmart-photoswipe', woodmart_get_script_url( 'photoswipe-bundle' ), array(), $version, true );
		wp_register_script( 'woodmart-slick', woodmart_get_script_url( 'slick' ), array(), $version, true );
		wp_register_script( 'woodmart-countdown', woodmart_get_script_url( 'countdown' ), array(), $version, true );
		wp_register_script( 'woodmart-packery-mode', woodmart_get_script_url( 'packery-mode.pkgd' ), array(), $version, true );
		wp_register_script( 'woodmart-vivus', woodmart_get_script_url( 'vivus' ), array(), $version, true );
		wp_register_script( 'woodmart-threesixty', woodmart_get_script_url( 'threesixty' ), array(), $version, true );
		wp_register_script( 'woodmart-justifiedGallery', woodmart_get_script_url( 'jquery.justifiedGallery' ), array(), $version, true );
		wp_register_script( 'woodmart-autocomplete', woodmart_get_script_url( 'jquery.autocomplete' ), array(), $version, true );
		wp_register_script( 'woodmart-sticky-kit', woodmart_get_script_url( 'jquery.sticky-kit' ), array(), $version, true );
		wp_register_script( 'woodmart-parallax', woodmart_get_script_url( 'jquery.parallax' ), array(), $version, true );
		wp_register_script( 'woodmart-parallax-scroll', woodmart_get_script_url( 'parallax-scroll' ), array(), $version, true );
		wp_register_script( 'maplace', woodmart_get_script_url( 'maplace-0.1.3' ), array( 'google.map.api' ), $version, true );
		wp_register_script( 'isotope', woodmart_get_script_url( 'isotope.pkgd' ), array(), $version, true );

		if ( woodmart_woocommerce_installed() ) {
			wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting.min.js', array( 'jquery' ), $version, true );
			wp_register_script( 'wc-jquery-ui-touchpunch', WC()->plugin_url() . '/assets/js/jquery-ui-touch-punch/jquery-ui-touch-punch.min.js', array( 'jquery-ui-slider' ), $version, true );
		}
	
		// Add virations form scripts through the site to make it work on quick view
		if( woodmart_get_opt( 'quick_view_variable' ) || woodmart_get_opt( 'quick_shop_variable' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation', false, array(), $version );
		}

		$translations = array(
			'adding_to_cart' => esc_html__('Processing', 'woodmart'),
			'added_to_cart' => esc_html__('Product was successfully added to your cart.', 'woodmart'),
			'continue_shopping' => esc_html__('Continue shopping', 'woodmart'),
			'view_cart' => esc_html__('View Cart', 'woodmart'),
			'go_to_checkout' => esc_html__('Checkout', 'woodmart'),
			'loading' => esc_html__('Loading...', 'woodmart'),
			'countdown_days' => esc_html__('days', 'woodmart'),
			'countdown_hours' => esc_html__('hr', 'woodmart'),
			'countdown_mins' => esc_html__('min', 'woodmart'),
			'countdown_sec' => esc_html__('sc', 'woodmart'),
			'cart_url' => ( woodmart_woocommerce_installed() ) ?  esc_url( wc_get_cart_url() ) : '',
			'ajaxurl' => admin_url('admin-ajax.php'),
			'add_to_cart_action' => ( woodmart_get_opt( 'add_to_cart_action' ) ) ? esc_js( woodmart_get_opt( 'add_to_cart_action' ) ) : 'widget',
			'added_popup' => ( woodmart_get_opt( 'added_to_cart_popup' ) ) ? 'yes' : 'no',
			'categories_toggle' => ( woodmart_get_opt( 'categories_toggle' ) ) ? 'yes' : 'no',
			'enable_popup' => ( woodmart_get_opt( 'promo_popup' ) ) ? 'yes' : 'no',
			'popup_delay' => ( woodmart_get_opt( 'promo_timeout' ) ) ? (int) woodmart_get_opt( 'promo_timeout' ) : 1000,
			'popup_event' => woodmart_get_opt( 'popup_event' ),
			'popup_scroll' => ( woodmart_get_opt( 'popup_scroll' ) ) ? (int) woodmart_get_opt( 'popup_scroll' ) : 1000,
			'popup_pages' => ( woodmart_get_opt( 'popup_pages' ) ) ? (int) woodmart_get_opt( 'popup_pages' ) : 0,
			'promo_popup_hide_mobile' => ( woodmart_get_opt( 'promo_popup_hide_mobile' ) ) ? 'yes' : 'no',
			'product_images_captions' => ( woodmart_get_opt( 'product_images_captions' ) ) ? 'yes' : 'no',
			'ajax_add_to_cart' => ( apply_filters( 'woodmart_ajax_add_to_cart', true ) ) ? woodmart_get_opt( 'single_ajax_add_to_cart' ) : false,
			'all_results' => esc_html__('View all results', 'woodmart'),
			'product_gallery' => woodmart_get_product_gallery_settings(),
			'zoom_enable' => ( woodmart_get_opt( 'image_action' ) == 'zoom') ? 'yes' : 'no',
			'ajax_scroll' => ( woodmart_get_opt( 'ajax_scroll' ) ) ? 'yes' : 'no',
			'ajax_scroll_class' => apply_filters( 'woodmart_ajax_scroll_class' , '.main-page-wrapper' ),
			'ajax_scroll_offset' => apply_filters( 'woodmart_ajax_scroll_offset' , 100 ),
			'infinit_scroll_offset' => apply_filters( 'woodmart_infinit_scroll_offset' , 300 ),
			'product_slider_auto_height' => ( woodmart_get_opt( 'product_slider_auto_height' ) ) ? 'yes' : 'no',
			'price_filter_action' => ( apply_filters( 'price_filter_action' , 'click' ) == 'submit' ) ? 'submit' : 'click',
			'product_slider_autoplay' => apply_filters( 'woodmart_product_slider_autoplay' , false ),
			'close' => esc_html__( 'Close (Esc)', 'woodmart' ),
			'share_fb' => esc_html__( 'Share on Facebook', 'woodmart' ),
			'pin_it' => esc_html__( 'Pin it', 'woodmart' ),
			'tweet' => esc_html__( 'Tweet', 'woodmart' ),
			'download_image' => esc_html__( 'Download image', 'woodmart' ),
			'cookies_version' => ( woodmart_get_opt( 'cookies_version' ) ) ? (int)woodmart_get_opt( 'cookies_version' ) : 1,
			'header_banner_version' => ( woodmart_get_opt( 'header_banner_version' ) ) ? (int)woodmart_get_opt( 'header_banner_version' ) : 1,
			'promo_version' => ( woodmart_get_opt( 'promo_version' ) ) ? (int)woodmart_get_opt( 'promo_version' ) : 1,
			'header_banner_close_btn' => woodmart_get_opt( 'header_close_btn' ),
			'header_banner_enabled' => woodmart_get_opt( 'header_banner' ),
			'whb_header_clone' => woodmart_get_config( 'header-clone-structure' ),
			'pjax_timeout' => apply_filters( 'woodmart_pjax_timeout' , 5000 ),
			'split_nav_fix' => apply_filters( 'woodmart_split_nav_fix' , false ),
			'shop_filters_close' => woodmart_get_opt( 'shop_filters_close' ) ? 'yes' : 'no',
			'woo_installed' => woodmart_woocommerce_installed(),
			'base_hover_mobile_click' => woodmart_get_opt( 'base_hover_mobile_click' ) ? 'yes' : 'no',
			'centered_gallery_start' => apply_filters( 'woodmart_centered_gallery_start' , 1 ),
			'quickview_in_popup_fix' => apply_filters( 'woodmart_quickview_in_popup_fix', false ),
			'disable_nanoscroller' => woodmart_get_opt( 'disable_nanoscroller' ),
			'one_page_menu_offset' => apply_filters( 'woodmart_one_page_menu_offset', 150 ),
			'hover_width_small' => apply_filters( 'woodmart_hover_width_small', true ),
			'is_multisite' => is_multisite(),
			'current_blog_id' => get_current_blog_id(),
			'swatches_scroll_top_desktop' => woodmart_get_opt( 'swatches_scroll_top_desktop' ),
			'swatches_scroll_top_mobile' => woodmart_get_opt( 'swatches_scroll_top_mobile' ),
			'lazy_loading_offset' => woodmart_get_opt( 'lazy_loading_offset' ),
			'add_to_cart_action_timeout' => woodmart_get_opt( 'add_to_cart_action_timeout' ) ? 'yes' : 'no',
			'add_to_cart_action_timeout_number' => woodmart_get_opt( 'add_to_cart_action_timeout_number' ),
			'single_product_variations_price' => woodmart_get_opt( 'single_product_variations_price' ) ? 'yes' : 'no',
			'google_map_style_text' => esc_html__( 'Custom style', 'woodmart' ),
			'quick_shop' => woodmart_get_opt( 'quick_shop_variable' ) ? 'yes' : 'no',
		);
		
		wp_localize_script( 'woodmart-functions', 'woodmart_settings', $translations );
		wp_localize_script( 'woodmart-theme', 'woodmart_settings', $translations );

	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Get script URL
 * ------------------------------------------------------------------------------------------------
 */
if( ! function_exists( 'woodmart_get_script_url') ) {
	function woodmart_get_script_url( $script_name ) {
	    return WOODMART_SCRIPTS . '/' . $script_name . '.min.js';
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Enqueue style for inline css
 * ------------------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'woodmart_enqueue_inline_style_anchor' ) ) {
	function woodmart_enqueue_inline_style_anchor() {
		wp_enqueue_style( 'woodmart-inline-css' );
	}
	
	add_action( 'wp_footer', 'woodmart_enqueue_inline_style_anchor', 10 );
}

/**
 * This function display product custom meta
 */
add_action( 'woocommerce_single_product_summary', 'display_product_custom_meta', 20 );
function display_product_custom_meta() {
    global $post;
    	
	    $custom_fields = get_post_meta($post->ID,'custom_fields',true);
	    $custom_field = json_decode($custom_fields);

	    $os = array("product_en", "product_fr", "long_description_en", "long_description_fr","long_description_nl");

	    echo "<ol class='custom_meta'>";
	    foreach ($custom_field as $key => $value) {
    	if (!empty($value) && !in_array($key, $os)) {
    		echo "<li><span>".ucfirst(str_replace('_', ' ', $key)).": </span>".$value."</li>";
    	}
    }
    echo "</ol>";
}

/**
 * This function get order data
 */
function get_product_item_from_order( $order_id ) {
    global $wpdb;
    global $woocommerce;
    $order = wc_get_order($order_id);
    $table = $wpdb->prefix . "wc_order_product_lookup";

    $dp = (isset($filter['dp'])) ? intval($filter['dp']) : 2;
    $itemsData = array();
    foreach ($order->get_items() as $item_id => $item) {

    $product = $item->get_product();

    $product_id = null;
    $product_sku = null;
    if (is_object($product)) {
	    $product_id = $product->get_id();
	    $product_sku = $product->get_sku();
    }

    $product_id = (!empty($item->get_variation_id()) && ('product_variation' === $product->post_type )) ? $product->get_parent_id() : $product_id;
    $variation_id = (!empty($item->get_variation_id()) && ('product_variation' === $product->post_type )) ? $product_id : 0;

    $discount_amount = $order->get_item_coupon_amount( $item );

    $itemsData[] = array(
    'id' => (string)$item_id,
    'subtotal' => wc_format_decimal($order->get_line_subtotal($item, false, false), $dp),
    'subtotal_tax' => wc_format_decimal($item['line_subtotal_tax'], $dp),
    'total' => wc_format_decimal($order->get_line_total($item, false, false), $dp),
    'total_tax' => wc_format_decimal($item['line_tax'], $dp),
    'price' => wc_format_decimal($order->get_item_total($item, false, false), $dp),
    'quantity' => wc_stock_amount($item['qty']),
    'tax_class' => (!empty($item['tax_class']) ) ? $item['tax_class'] : null,
    'name' => $item['name'],
    'product_id' => $product_id,
    'variation_id' => $variation_id,
    'sku' => $product_sku,
    'meta' => wc_display_item_meta($item, ['echo' => false]),
    'discount_amount' => round($discount_amount,2),
    );
    }

    $orderitems = $itemsData;

    return $orderitems;
}

/**
 * This function used for synchronize member to crms
 */

function syncMemberTocrms($api_req_data,$country_name){   
    global $wpdb;
    $query = $wpdb->get_results("SELECT * FROM wp_crms_country WHERE `name` LIKE '".$country_name."' OR `mname` LIKE '".$country_name."'");

   		
    $url = "https://api.current-rms.com/api/v1/members";
    $ch = curl_init( $url );
    $payload = '{
                    "member":
                    {
                        "name":"'.$api_req_data['first_name']." ".$api_req_data['last_name'].'",
                        "description":"'.$api_req_data['billing_project_comment'].'",
                        "active":true,
                        "bookable":false,
                        "location_type":1,
                        "locale":"en-GB",
                        "membership_type":"Organisation",
                        "sale_tax_class_id":1,
                        "purchase_tax_class_id":1,
                        "tag_list":[],
                        "custom_fields":{},
                        "membership":{"owned_by": 1},
                        "primary_address":{"name":"'.$api_req_data['first_name'].'","street":"'.$api_req_data['address_1'].'","postcode":"'.$api_req_data['postcode'].'","city":"'.$api_req_data['city'].'","county":"'.$api_req_data['city'].'","country_id":"'.$query[0]->id.'","country_name":"'.$query[0]->name.'","type_id":3001,"address_type_name":"Primary","created_at":"'.$api_req_data['currentdate'].'","updated_at":"'.$api_req_data['currentdate'].'"},
                        "emails":[{"address":"'.$api_req_data['email'].'","type_id":'.$api_req_data['emailid'].',"email_type_name":"'.$api_req_data['emailtype'].'"}],
                        "phones":[{"number":"'.$api_req_data['phone'].'","type_id":'.$api_req_data['billid'].',"phone_type_name":"'.$api_req_data['billtype'].'"}],
                        "links":[],
                        "addresses":[],
                        "service_stock_levels":[],
                        "day_cost":"",
                        "hour_cost":"",
                        "distance_cost":"",
                        "flat_rate_cost":"",
                        "icon":{  },
                        "child_members":[],
                        "parent_members":[]
                    }
                }';
          
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:'.$api_req_data['sub_domain'].'','X-AUTH-TOKEN:'.$api_req_data['api_token'].'','Content-Type:application/json'));
    # Return response instead of printing.
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    # Send request.
    $result = curl_exec($ch);
    return $result = curl_exec($ch);
    exit();
}

/**
 * This function used for store extra custom fields
 */

add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );
function my_custom_checkout_field_update_order_meta( $order_id ) {
    if (! empty($_POST['billing_booking_start_date']) || ! empty($_POST['billing_booking_end_date']) )
    {
        update_post_meta( $order_id, 'billing_booking_start_date', sanitize_text_field( $_POST['billing_booking_start_date'] ) );
        update_post_meta( $order_id, 'billing_booking_end_date', sanitize_text_field( $_POST['billing_booking_end_date'] ) );
        update_post_meta( $order_id, 'billing_order_title', sanitize_text_field( $_POST['billing_order_title'] ) );
    }
}

/**
 * This function used for add extra fields on checkout page
 */

add_action( 'woocommerce_checkout_before_customer_details', 'action_woocommerce_checkout_before_customer_details', 10, 1 ); 
function action_woocommerce_checkout_before_customer_details( $wccm_checkout_text_before ) { 
  		
  	$product_start_date = "";
  	$product_end_date = "";
	if (  WC()->session->__isset( 'product_start_date' ) ){
		$product_start_date = WC()->session->get( 'product_start_date', $product_start_date );
	}
	
	if (  WC()->session->__isset( 'product_end_date' ) ){
		$product_end_date = WC()->session->get( 'product_end_date', $product_end_date );
	}

   ?>
		<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


		<h3><?php esc_html_e( 'ORDER INFORMATIONS', 'woocommerce' ); ?></h3>

	   <p class="form-row form-row-first thwcfd-field-wrapper thwcfd-field-text validate-required" id="billing_order_title_field" >
	   		<label for="billing_order_title" class="">Order Title&nbsp;<abbr class="required" title="required">*</abbr></label>
	   		<span class="woocommerce-input-wrapper">
	   			<input type="text" class="input-text " name="billing_order_title" id="billing_order_title" placeholder="" value="" data-qa-id="Order Title">
	   		</span>
	   	</p>
	   <p class="form-row form-row-last thwcfd-field-wrapper thwcfd-field-text validate-required" id="billing_po_or_payment_refere_field" >
	   		<label for="billing_po_or_payment_refere" class="">PO (or Payment Reference)&nbsp;<abbr class="required" title="required">*</abbr></label>
	   		<span class="woocommerce-input-wrapper">
	   			<input type="text" class="input-text " name="billing_po_or_payment_refere" id="billing_po_or_payment_refere" placeholder="" value="" data-qa-id="PO (or Payment Reference)">
	   		</span>
	   	</p>

   		<p class="form-row form-row-first validate-required" id="billing_booking_start_date_field" data-priority="10">
		    <label for="billing_booking_start_date" class="">Booking Start&nbsp;<abbr class="required" title="required">*</abbr></label>
		    <span class="woocommerce-input-wrapper"><input type="text" class="input-text" name="billing_booking_start_date" id="billing_book_start_date" placeholder="" value="<?php echo $product_start_date; ?>">
		</p>
		<p class="form-row form-row-last validate-required" id="billing_booking_end_date_field" data-priority="10">
		    <label for="billing_booking_end_date" class="">Booking End&nbsp;<abbr class="required" title="required">*</abbr></label>
		    <span class="woocommerce-input-wrapper"><input type="text" class="input-text" name="billing_booking_end_date" id="billing_book_end_date" placeholder="" value="<?php echo $product_end_date; ?>"></span>
		</p>


		<p class="form-row form-row-first" id="billing_first_name_field" data-priority="10">
			<label for="billing_first_name" class="">First name&nbsp;</label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_first_name" id="billing_first_name" placeholder="" value="" autocomplete="given-name">
			</span>
		</p>

		<p class="form-row form-row-last" id="billing_last_name_field" data-priority="20">
			<label for="billing_last_name" class="">Last name&nbsp;</label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_last_name" id="billing_last_name" placeholder="" value="" autocomplete="family-name">
			</span>
		</p>

		<p class="form-row form-row-wide validate-required woocommerce-validated" id="billing_company_field" data-priority="30">
			<label for="billing_company" class="">Company Name&nbsp;<abbr class="required" title="Company Name">*</abbr></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_company" id="billing_company" placeholder="" value="" autocomplete="organization">
			</span>
		</p>

		<p class="form-row form-row-wide address-field update_totals_on_change validate-required woocommerce-validated" id="billing_country_field" data-priority="40">
			<label for="billing_country" class="">Country / Region&nbsp;<abbr class="required" title="required">*</abbr></label>
				<span class="woocommerce-input-wrapper">
					<select name="billing_country" id="billing_country" class="country_to_state country_select select2-hidden-accessible" autocomplete="country" data-placeholder="Select a country / region…" tabindex="-1" aria-hidden="true">
						<option value="">Select a country / region…</option>
						<option value="BE" selected="selected">Belgium</option>
						<option value="FR">France</option>
						<option value="LU">Luxembourg</option>
						<option value="NL">Netherlands</option>
					</select>
				</span>
		</p>
		<p class="form-row form-row-wide address-field validate-required" id="billing_address_1_field" data-priority="50">
			<label for="billing_address_1" class="">Street address&nbsp;<abbr class="required" title="required">*</abbr></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_address_1" id="billing_address_1" placeholder="House number and street name" value="" autocomplete="address-line1" data-placeholder="House number and street name">
			</span>
		</p>
		<p class="form-row form-row-wide address-field validate-required" id="billing_city_field" data-priority="70" data-o_class="form-row form-row-wide address-field validate-required">
			<label for="billing_city" class="">Town / City&nbsp;<abbr class="required" title="required">*</abbr></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_city" id="billing_city" placeholder="" value="" autocomplete="address-level2" data-placeholder="">
			</span>
		</p>
		<p class="form-row form-row-wide address-field validate-required validate-postcode" id="billing_postcode_field" data-priority="90" data-o_class="form-row form-row-wide address-field validate-required validate-postcode">
			<label for="billing_postcode" class="">Postcode / ZIP&nbsp;<abbr class="required" title="required">*</abbr></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_postcode" id="billing_postcode" placeholder="" value="" autocomplete="postal-code" data-placeholder="">
			</span>
		</p>


		<p class="form-row form-row-wide address-field validate-required validate-postcode" id="billing_email_field" data-priority="90" data-o_class="form-row form-row-wide address-field validate-required validate-postcode">
			<label for="billing_email" class="">email&nbsp;<abbr class="required" title="required">*</abbr></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_email" id="billing_email" placeholder="" value="" autocomplete="postal-code" data-placeholder="">
			</span>
		</p>
		<p class="form-row form-row-wide address-field validate-required validate-postcode" id="billing_phone_field" data-priority="90" data-o_class="form-row form-row-wide address-field validate-required validate-postcode">
			<label for="billing_phone" class="">Phone&nbsp;<abbr class="required" title="required">*</abbr></label>
			<span class="woocommerce-input-wrapper">
				<input type="text" class="input-text " name="billing_phone" id="billing_phone" placeholder="" value="" autocomplete="postal-code" data-placeholder="">
			</span>
		</p>
	
		<script>

			jQuery(document).ready(function($){

				jQuery("#billing_book_end_date").prop('disabled', true);
				jQuery("#billing_book_end_date").val('');

				jQuery("#billing_book_start_date").flatpickr({

					minDate: 'today',
					dateFormat: "d-m-Y",
					maxDate: jQuery("#billing_book_end_date").val() ? jQuery("#billing_book_end_date").val() : "31-12-2031",


				    onChange: function(selectedDates, dateStr, instance) {


				        jQuery("#billing_book_end_date").prop('disabled', false);
	
					    jQuery("#billing_book_end_date").flatpickr({ 
					        dateFormat: "d-m-Y", 
					        maxDate: "31-12-2031",
					        minDate: dateStr, 

					        onChange: function(selectedDates, dateStr, instance) {
					          	jQuery("#billing_book_start_date").flatpickr({
								    minDate: 'today',
								    dateFormat: "d-m-Y",
								    maxDate: dateStr,
								})
					        }
					    });
				    }
				});

			});

		</script>
		
    <?php
}

/**
 * This function used for synchronize opportunities to CRMS.
 */

function syncOpportunitiesTocrms($api_req_data,$member,$product_detail,$order_id)
{

    $url = "https://api.current-rms.com/api/v1/opportunities/checkout";
    $ch = curl_init( $url );
    $projectname = $api_req_data['billing_order_title'];
    $description = $api_req_data['billing_project_comment'];
    $street = trim($api_req_data['address_1']);
    $city = trim($api_req_data['city']);
    $postcode = trim($api_req_data['postcode']);
    $county = trim($api_req_data['country']);
    $emails = trim($api_req_data['email']);
    $phones = trim($api_req_data['phone']);
    $startdt = date("Y-m-d", strtotime($api_req_data['billing_booking_start_date']));
    $enddt   = date("Y-m-d", strtotime($api_req_data['billing_booking_end_date']));
    $prodata = $product_detail;
    	
    $startdate = $startdt.'T18:30:00.000Z';
    $enddate = $enddt.'T18:30:00.000Z';
    $currentdate = date("Y-m-d").'T18:30:00.000Z';
    $memid = $member['member']['id'];
    $memuuid = $member['member']['uuid'];
    $memname = $member['member']['name'];

    $billingid = $member['member']['primary_address']['id'];
    $pre = $startdate ;
    $show_starts_at = date('Y-m-d', strtotime( $startdt . " +1 days"));
    $show_ends_at = date('Y-m-d', strtotime( $enddt . " -1 days"));
    $collection = $enddate;
    $items = array();
    foreach ($prodata as $key => $value) {
        $subitem['opportunity_id'] = 1;
        $subitem['item_id'] = $api_req_data['crms_id'];
        $subitem['item_type'] = 1;
        $subitem['opportunity_item_type'] = 1;
        $subitem['name'] = $value['title'];
        $subitem['transaction_type'] = 1;
        $subitem['accessory_inclusion_type'] = 0;
        $subitem['accessory_mode'] = 0;
        $subitem['quantity'] = $value['qty'];
        $subitem['revenue_group_id'] = null;
        $subitem['rate_definition_id'] = 5;
        $subitem['service_rate_type'] = 0;
        $subitem['price'] = $value['price'];
        $subitem['discount_percent'] = 0;
        $subitem['starts_at'] = $startdate;
        $subitem['ends_at'] = $enddate;
        $subitem['use_chargeable_days'] = true;
        $subitem['sub_rent'] = false;
        $subitem['description'] = $description;
        $subitem['replacement_charge'] = 0;
        $subitem['weight'] = 0.0;
        $subitem['custom_fields'] = json_decode('{}');
        $suballitem[] = $subitem;

    }
    $items = $suballitem;
    $itmdata = json_encode($items);

    $payload = '
    {
        "opportunity":
            {
                "store_id":1,
                "project_id":null,
                "member_id":'.$memid.',
                "billing_address_id":'.$billingid.',
                "venue_id":null,
                "tax_class_id":1,
                "subject":"Pending Request – '.$projectname.'",
                "description":"'.$description.'",
                "number":"",
                "starts_at":"'.$startdate.'",
                "ends_at":"'.$enddate.'",
                "charge_starts_at":"'.$startdate.'",
                "charge_ends_at":"'.$enddate.'",
                "ordered_at":"'.$currentdate.'",
                "quote_invalid_at":"",
                "state":1,
                "use_chargeable_days":false,
                "chargeable_days":1,
                "open_ended_rental":false,
                "invoiced":false,
                "rating":4,
                "revenue":"0",
                "customer_collecting":false,
                "customer_returning":false,
                "reference":"",
                "external_description":"",
                "owned_by":1,
                "prep_starts_at":"'.$show_starts_at.'",
                "prep_ends_at":"'.$show_ends_at.'",
                "load_starts_at":"'.$show_starts_at.'",
                "load_ends_at":"'.$show_ends_at.'",
                "deliver_starts_at":"'.$show_starts_at.'",
                "deliver_ends_at":"'.$show_ends_at.'",
                "setup_starts_at":"'.$show_starts_at.'",
                "setup_ends_at":"'.$show_ends_at.'",
                "show_starts_at":"'.$show_starts_at.'",
                "show_ends_at":"'.$show_ends_at.'",
                "takedown_starts_at":"'.$pre.'",
                "takedown_ends_at":"'.$pre.'",
                "collect_starts_at":"'.$collection.'",
                "collect_ends_at":"'.$collection.'",
                "unload_starts_at":"",
                "unload_ends_at":"",
                "deprep_starts_at":"",
                "deprep_ends_at":"",
                "tag_list":[],
                "assigned_surcharge_group_ids":[],
                "custom_fields":{},
                "participants":[{"uuid":"'.$memuuid.'",
                "member_id":"'.$memid.'",
                "mute":false,
                "member_name":"'.$memname.'",
                "created_at":"'.$currentdate.'",
                "updated_at":"'.$currentdate.'",
                "assignment_type":"Activity"}]
            },
                "items":'.$itmdata.'
    }';

    	
    	
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:'.$api_req_data['sub_domain'].'','X-AUTH-TOKEN:'.$api_req_data['api_token'].'','Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $response = curl_exec($ch);

    $response_decode  = json_decode($response);
    $response_decoded = (array)$response_decode;
    if (!empty($response_decoded)) {
        $url = "https://api.current-rms.com/api/v1/opportunities/".$response_decoded['opportunity']->id."/opportunity_items";
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:baseboys','X-AUTH-TOKEN:iBnSjFdaWALAyrsx_-WK','Content-Type:application/json'));

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
    }

    
    $opportunity_items  = $result->opportunity_items;        
    $opportunity_id = $opportunity_items[1]->opportunity_id;
    $desired_opportunity_id = $opportunity_items[1]->id;
    update_post_meta( $order_id, 'crms_opp_id', $opportunity_id );
    update_post_meta( $order_id, 'crms_desired_opp_id', $desired_opportunity_id );

    exit;
    curl_close($ch);
}

/**
 * This function used get opportunities.
 */
function putOpportunities($oppid,$api_req_data)
{
    $name = $api_req_data['first_name'];
    $projectname = $api_req_data['billing_project_name'];
    $description = $api_req_data['billing_project_comment'];
    $street = trim($api_req_data['address_1']);
    $city = trim($api_req_data['city']);
    $postcode = trim($api_req_data['postcode']);
    $county = trim($api_req_data['state']);
    $emails = trim($api_req_data['email']);
    $phones = trim($api_req_data['phone']);
    $startdate = date("Y-m-d", strtotime($api_req_data['billing_booking_start_date']));
    $enddate = date("Y-m-d", strtotime($api_req_data['billing_booking_end_date']));
    $prodata = $product_detail;

    global $wpdb;
    $query = $wpdb->get_results("SELECT * FROM wp_crms_country WHERE `name` LIKE '".$api_req_data['state']."' OR `mname` LIKE '".$api_req_data['state']."'");

    $url = "https://api.current-rms.com/api/v1/opportunities/".$oppid['opportunity']['id'];
    $ch = curl_init( $url );
    $payload = '
            {
                "opportunity":
                    {"destination":
                            {
                            "source_type":"Opportunity",
                            "address":{"name":"'.$name.'",
                            "street":"'.$street.'",
                            "postcode":"'.$postcode.'",
                            "city":"'.$city.'",
                            "county":"'.$county.'",
                            "country_id":"'.$query[0]->id.'",
                            "country_name":"'.$query[0]->name.'",
                            "created_at":"'.date("Y-m-d").'T18:30:00.000Z",
                            "updated_at":"'.date("Y-m-d").'T18:30:00.000Z"}
                            }
                    }
            }';

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:'.$api_req_data['sub_domain'].'','X-AUTH-TOKEN:'.$api_req_data['api_token'].'','Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    $response = curl_exec($ch);
    curl_close($ch);


    if (!empty($response)) {
        $url = "https://api.current-rms.com/api/v1/opportunities/".$oppid['opportunity']['id']."/convert_to_order";
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:'.$api_req_data['sub_domain'].'','X-AUTH-TOKEN:'.$api_req_data['api_token'].'','Content-Type:application/json'));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);
        curl_close($ch);
         
    }
}

/**
 * This function used for get order data on thankyou page.
 */
add_action('woocommerce_thankyou', 'elite_CustomReadOrder');
function elite_CustomReadOrder($order_id)
{

    global $wpdb;
    $order = wc_get_order($order_id);
    $order_data = $order->get_data();
    $items = $order->get_items();
    
    $countries = WC()->countries->countries[ $order->get_billing_country() ];
    $states = WC()->countries->get_states( $order->get_billing_country() );
    $state  = ! empty( $states[ $order->get_billing_country() ] ) ? $states[ $order->get_billing_country() ] : '';

    $product_js = [];
    $api_req_data = array();
    foreach ($order_data['meta_data'] as $key => $meta_data) {
        $api_req_data[$meta_data->key] = $meta_data->value;        
    }
    /*order detail*/
    $api_req_data['first_name'] = $order_data['billing']['first_name'];

    /*billing detail*/
    $api_req_data['order_title'] = $order_data['billing']['billing_order_title'];
    $api_req_data['first_name'] = $order_data['billing']['first_name'];
    $api_req_data['last_name'] = $order_data['billing']['last_name'];
    $api_req_data['company'] = $order_data['billing']['company'];
    $api_req_data['address_1'] = $order_data['billing']['address_1'];
    $api_req_data['address_2'] = $order_data['billing']['address_2'];
    $api_req_data['city'] = $order_data['billing']['city'];
    $api_req_data['state'] = $order_data['billing']['state'];
    $api_req_data['postcode'] = $order_data['billing']['postcode'];
    $api_req_data['country'] = $order_data['billing']['country'];
    $api_req_data['email'] = $order_data['billing']['email'];
    $api_req_data['phone'] = $order_data['billing']['phone'];
    $api_req_data['billing_booking_start_date'] = get_post_meta( $order_id ,'billing_booking_start_date',true);
    $api_req_data['billing_booking_end_date'] = get_post_meta( $order_id ,'billing_booking_end_date',true);
    $api_req_data['billingtype'] = 'Personal';
    $api_req_data['currentdate'] = date("Y-m-d").'T18:30:00.000Z';
    $api_req_data['billid'] = 6005;
    $api_req_data['billtype'] = 'Home';
    $api_req_data['emailid'] = 4002;
    $api_req_data['emailtype'] = 'Home';
    if ($countryCode !== "") {
    $country = WC()->countries->countries[$api_req_data['country']];
    }
    if ($countryCode != "" && $stateId != '') {
    $state = WC()->countries->get_states( $api_req_data['country'] )[$stateId];
    }

    $country_data = explode('(', $country);
    $country_name = trim($country_data[0]);
    
    if ($country_name == "België") {
    	$country_name = "Belgium";
    }

    $order_detail_data = get_product_item_from_order($order_id);

    foreach ($order_detail_data as $key => $order_pro_data) {
        $api_req_data['product_name'] = $order_pro_data['name'];
        $api_req_data['product_id'] = $order_pro_data['product_id'];
        $crms_id = get_post_meta( $order_pro_data['product_id'], 'crms_id', true); 
        $sub_domain = get_post_meta( $order_pro_data['product_id'], 'sub_domain', true);
        $api_token = get_post_meta( $order_pro_data['product_id'], 'api_token', true);

        $api_req_data['sub_domain'] = $sub_domain;
        $api_req_data['api_token'] = $api_token;
        $api_req_data['crms_id'] = $crms_id;
        $api_req_data['product_sku'] = $order_pro_data['sku'];
        $api_req_data['product_meta'] = $order_pro_data['meta'];
        $api_req_data['product_discount_amount'] = $order_pro_data['discount_amount'];
        $api_req_data['product_subtotal'] = $order_pro_data['subtotal'];
        $api_req_data['product_subtotal_tax'] = $order_pro_data['subtotal_tax'];
        $api_req_data['product_total'] = $order_pro_data['total'];
        $api_req_data['product_total_tax'] = $order_pro_data['total_tax'];
        $api_req_data['product_price'] = $order_pro_data['price'];
        $api_req_data['product_quantity'] = $order_pro_data['quantity'];
    }

    global $wpdb;
    $local_wp_crms_member = $wpdb->get_results("SELECT * FROM wp_crms_member WHERE email='".$api_req_data['email']."'");
    if($local_wp_crms_member){
        $url = "https://api.current-rms.com/api/v1/members/".$local_wp_crms_member[0]->id;
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:'.$api_req_data['sub_domain'].'','X-AUTH-TOKEN:'.$api_req_data['api_token'],'Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $memresult = curl_exec($ch);
        curl_close($ch);
        $member  = json_decode($memresult,true);
    }

    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $product_detail = array();
    $product_detail[] = array('product_id' => $api_req_data['product_id'],
                            'title' => $api_req_data['product_name'],
                        'sku' => $api_req_data['product_sku'],
                    'price' => $api_req_data['product_price'],
                'qty' => $api_req_data['product_quantity']);
    $sync_opportunity_detail = "";

    if($local_wp_crms_member){
        syncOpportunitiesTocrms($api_req_data,$member,$product_detail,$order_id);
    }else{
        global $wpdb;
        if($local_wp_crms_member && isset($member['errors'])){
            $sql = "Delete FROM wp_crms_member Where id = ".$local_wp_crms_member[0]->id ;
            $data_deleted = $wpdb->get_results($sql);
        }
    		
        $sync_member_detail = syncMemberTocrms($api_req_data,$country_name);

        $member  = json_decode($sync_member_detail,true);
        if(!isset($member['errors'])){
                $memid = $member['member']['id'];
                $memuuid = $member['member']['uuid'];
                $memname = $member['member']['name'];
                $billingid = $member['member']['primary_address']['id'];
                $email = $member['member']['emails'][0]['address'];
                $sql = "Insert Into wp_crms_member (id, uuid, email, name, billing_id) Values (".$memid.",'".$memuuid."','".$email."','".$memname."',".$billingid.")";
                $Insert_mamber = $wpdb->get_results($sql);
                syncOpportunitiesTocrms($api_req_data,$member,$product_detail,$order_id);
        }
    }

    $oppid  = json_decode($sync_opportunity_detail,true);

    if(!isset($oppid['errors']) && $sync_opportunity_detail != null){        
        putOpportunities($oppid,$api_req_data);
        OrdertoOpportunity($oppid);
    }

    function OrdertoOpportunity($oppid){
        $url = "https://api.current-rms.com/api/v1/opportunities/".$oppid['opportunity']['id']."/convert_to_order";
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
        // curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:'.$api_req_data['sub_domain'].'','X-AUTH-TOKEN:'.$api_req_data['api_token'].'','Content-Type:application/json'));

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
    }
}

/**
 * This function used for Product Date Search filter.
 */
add_action('pre_get_posts', 'o3_filter_product_by_date');	
function o3_filter_product_by_date( $query ) {
	
    $start_date = '';
    $end_date = '';
    
	if ($query->is_search && !is_admin() ) {
		if( isset($_GET['post_type']) && $_GET['post_type'] == 'product' ) {

			if( isset($_GET['product_start_date']) && !empty($_GET['product_start_date'])) {
				
				$start = sanitize_text_field($_GET['product_start_date']);
				$start = str_replace("/","-",$start);
				$start_date = date("Ymd", strtotime($start));
	    		
			}

			if( isset($_GET['product_end_date']) && !empty($_GET['product_end_date']) ) {
	        	$end = sanitize_text_field($_GET['product_end_date']);
				$end = str_replace("/","-",$end);
				$end_date = date("Ymd", strtotime($end));
		    }

			if( !empty($start_date) && !empty($end_date) && isset($_GET['filter_type']) &&  $_GET['filter_type'] == 'period' ) {
		    	$query->set('post_type', 'product' );
                $query->set('meta_key', 'product_start_date');
				$query->set('meta_value', $start_date);
				$query->set('meta_compare', "<=");	
		    }
		    if( !empty($start_date) && !empty($end_date) && isset($_GET['filter_type']) && $_GET['filter_type'] == 'period') {
		    	$query->set('post_type','product');
				$query->set('meta_key', 'product_end_date');
				$query->set('meta_value', $end_date);
				$query->set('meta_compare', ">=");
		    }
		    if(!empty($end_date) && isset($_GET['filter_type']) && $_GET['filter_type'] == 'deadline'){	
	    		$query->set('post_type','product');
				$query->set('meta_key', 'product_end_date');
				$query->set('meta_value', $end_date);
				$query->set('meta_compare', ">=");			    
		    }
		}
	}

	return $query;
}

/**
 * Add a custom product data tab
 */
add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) {
	$tabs['test_tab'] = array(
		'title' 	=> __( 'Long Description', 'woocommerce' ),
		'priority' 	=> 50,
		'callback' 	=> 'woo_new_product_tab_content'
	);
	return $tabs;
}

/**
 * This function is callback function for tab content.
 */
function woo_new_product_tab_content() {
	    global $post;

	$custom_fields = get_post_meta($post->ID,'custom_fields',true);
	$custom_field = json_decode($custom_fields);
	 if(ICL_LANGUAGE_CODE == 'en'){
		$os = array("owner","product_en","product_fr","webshop_color_i","storage_location","webshop_color_ii","webshop_category_i","webshop_category_ii","webshop_category_iv","short_description_en","long_description_nl","short_description_fr","short_description_nl","long_description_fr","webshop_category_iii","published_on_my_brand");
	}
	if(ICL_LANGUAGE_CODE == 'nl'){
		$os = array("eigenaar","product_en", "product_fr","webshop_color_i","opslaglocatie","webshop_color_ii","webshop_category_i","webshop_category_ii","webshop_category_iv","short_description_en","long_description_en","short_description_fr","long_description_fr","korte_beschrijving_nl","webshop_category_iii","gepubliceerd_op_mijn_merk");
	}
	if(ICL_LANGUAGE_CODE == 'fr'){
		$os = array("proprietaire","product_en","product_fr","webshop_color_i","emplacement_de_stockage","webshop_color_ii","webshop_category_i","webshop_category_ii","webshop_category_iv","short_description_en","long_description_en","long_description_nl","short_description_fr","short_description_nl","webshop_category_iii","publié_sur_ma_marque");
	}

	foreach ($custom_field as $key => $value) {
    	if (!empty($value) && !in_array($key, $os)) {
    		echo "<li><span>".ucfirst(str_replace('_', ' ', $key)).": </span>".$value."</li>";
    	}

    }
}

/**
 * This function add query vars for filter
 */
add_filter( 'query_vars', 'rj_add_query_vars_filter' );
function rj_add_query_vars_filter( $vars ){
    $vars[] = "product_start_date";
    $vars[] = "product_end_date";
    return $vars;
}

/**
 * This function used for customize email template
 */
add_action( 'woocommerce_email_customer_details', 'action_woocommerce_email_customer_details', 10, 4 ); 
function action_woocommerce_email_customer_details( $order, $sent_to_admin, $plain_text, $email ) { 

	$text_align = is_rtl() ? 'right' : 'left';

	$order_id = $order->get_id();

	// Get an instance of the WC_Order Object from the Order ID (if required)
	$order = wc_get_order( $order_id );


	// Get the Order meta data in an unprotected array
	$data  = $order->get_data(); 
	$billing_company    = $data['billing']['company'];
	$billing_address_1  = $data['billing']['address_1'];
	$billing_address_2  = $data['billing']['address_2'];
	$shipping_company    = $data['shipping']['company'];
	$shipping_address_1  = $data['shipping']['address_1'];
	$shipping_address_2  = $data['shipping']['address_2'];	

	$order_id        = $data['id'];
	$order_parent_id = $data['parent_id'];

	$customer_id     = $data['customer_id'];

	/*--BILLING INFORMATION: --*/

	$billing_email      = $data['billing']['email'];
	$billing_phone      = $order_data['billing']['phone'];

	$billing_first_name = $data['billing']['first_name'];
	$billing_last_name  = $data['billing']['last_name'];
	
	$billing_city       = $data['billing']['city'];
	$billing_state      = $data['billing']['state'];
	$billing_postcode   = $data['billing']['postcode'];
	$billing_country    = $data['billing']['country'];

	/*--SHIPPING INFORMATION: --*/

	$shipping_first_name = $data['shipping']['first_name'];
	$shipping_last_name  = $data['shipping']['last_name'];
	
	$shipping_city       = $data['shipping']['city'];
	$shipping_state      = $data['shipping']['state'];
	$shipping_postcode   = $data['shipping']['postcode'];
	$shipping_country    = $data['shipping']['country'];

	$user_id = get_post_meta( $order_id, '_customer_user', true );

	$order_title= get_post_meta( $order_id, '_billing_order_title', true );

	$customer = new WC_Customer( $user_id );
	$first_name   = $customer->get_first_name();
	$last_name    = $customer->get_last_name();

	$created_date = date('Ymd');


    ?>
	<h2> ORDER <?php echo $created_date.'-'.'Client'."-".$order_title.$data['id']; ?></h2>
	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;border:4px solid #314159" border="1">
			<thead style="background-color: #314159">
				<tr>
					<th class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"></th>				
					<th class="td" style="font-size: 11px;text-transform: uppercase;background: #eeb000;color: #ffffff;width: 30%;text-align: center;height: 20px;font-family: 'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;padding: 6px;" >Value Order Type</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="td" scope="row" style="border:none;border-bottom:1px solid #c2c2c2;text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"> Order Start Date </td>
					<td style="border:none;border-bottom:1px solid #c2c2c2"><?php echo $_POST['billing_booking_start_date'];?></td>
				</tr>
				<tr>
					<td class="td" scope="row" style="border:none;border-bottom:1px solid #c2c2c2;text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"> Order End Date </td>
					<td style="border:none;border-bottom:1px solid #c2c2c2"><?php echo $_POST['billing_booking_end_date'];?></td>
				</tr>
				<tr>
					<td class="td" scope="row" style="border:none;border-bottom:1px solid #c2c2c2;text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"> PO (or Payment Reference) </td>
					<td style="border:none;border-bottom:1px solid #c2c2c2"><?php echo $_POST['billing_po_or_payment_refere'];?></td>
				</tr>
				<tr>
					<td class="td" scope="row" style="border:none;border-bottom:1px solid #c2c2c2;text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"> Billing Company </td>
					<td style="border:none;border-bottom:1px solid #c2c2c2"><?php echo $billing_company.','.$billing_address_1.','.$billing_address_2; ?></td>
				</tr>
				<tr>
					<td class="td" scope="row" style="border:none;border-bottom:1px solid #c2c2c2;text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"> Shipping Detail </td>
					<td style="border:none;border-bottom:1px solid #c2c2c2"><?php echo $shipping_company.','.$shipping_address_1.','.$shipping_address_2; ?></td>
				</tr>
				<tr>
					<td class="td" scope="row" style="border:none;border-bottom:1px solid #c2c2c2;text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"> Contact@Venue </td>
					<td style="border:none;border-bottom:1px solid #c2c2c2"><?php echo $first_name.' '.$last_name; ?></td>
				</tr>
			</tbody>
			
		</table>
	</div>
    <?php
} 
         
/**
 * This function used for woocommerce login redirect
 */
add_filter( 'woocommerce_login_redirect', 'custome_woocommerce_redirect_after_login', 9999, 2 );
function custome_woocommerce_redirect_after_login( $redirect, $user ) {
     
	$uri_segments = explode('/', $redirect);

    if(isset($uri_segments[1]) && ($uri_segments[1] == 'my-account' || $uri_segments[1] == 'mon-compte' || $uri_segments[1] == 'mijn-account' || $redirect == '/')){

	  $redirect = wc_get_page_permalink( 'shop' );
    }
    return $redirect;
}

/**
 * This function used add woocommerce order request
 */
add_filter( 'woocommerce_order_button_text', 'mybrand_custom_button_text' );
function mybrand_custom_button_text( $button_text ) {
   return 'PLACE ORDER REQUEST'; // new text is here 
}

/**
 * This function used to add admin side style
 */
add_action( 'admin_enqueue_scripts', 'my_admin_style');
function my_admin_style() {
    wp_enqueue_style( 'admin-style', get_stylesheet_directory_uri() . '/admin-style.css' );
}

/**
 * This function used to create a column
 */
add_filter( 'manage_edit-product_columns', 'misha_extra_column', 20 );
function misha_extra_column( $columns_array ) {
	
 	return array_slice( $columns_array, 0, 3, true )
	+ array( 'related_product' => 'Related Product' )
	+ array_slice( $columns_array, 4, NULL, true );
}
 
/**
 * This function used to adds checkbox to our newly created column
 */
add_action( 'manage_posts_custom_column', 'misha_populate_columns' );
function misha_populate_columns( $column_name ) {
 	 $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );

    $loop = new WP_Query( $args );

	if( $column_name  == 'related_product' ) {
		$all = get_post_meta( get_the_ID(), 'related_products', true );
		echo '<select multiple data-productid="' . get_the_ID() .'" class="select_prod">';
		while ( $loop->have_posts() ) : $loop->the_post();
	        global $product;
	        echo '<option value="'.$product->get_id().'" '. ((in_array($product->get_id(), $all)) ? 'selected': "").'>'.$product->get_title().'</option>';
    	endwhile;
		
		echo '</select><small style="display:block;color:#7ad03a"></small>';
	}
}
 
/**
 * This function add scripts in footer
 */ 
add_action( 'admin_footer', 'misha_jquery_event' );
function misha_jquery_event(){
 
	echo "<script>jQuery(function($){
		$('.select_prod').click(function(){
			$.ajax({
				type: 'POST',
				data: {
					action: 'productmetasave', // wp_ajax_{action} WordPress hook to process AJAX requests
					product: $(this).data('productid'),
					product_id: $(this).val(),
					myajaxnonce : '" . wp_create_nonce( "activatingcheckbox" ) . "'
				},
				url: ajaxurl, // as usual, it is already predefined in /wp-admin
				success: function(data){
					
				}
			});
		});
	});</script>";
 
}

/**
 * This function used for save product meta
 */  
add_action( 'wp_ajax_productmetasave', 'misha_process_ajax' );
function misha_process_ajax(){
 
	check_ajax_referer( 'activatingcheckbox', 'myajaxnonce' );
 
	if( update_post_meta( $_POST[ 'product'] , 'related_products', $_POST['product_id'] ) ) {
		echo 'Saved';
	}	
 
	die();
}

/**
 * This function used for unset shipping fields
 */  
add_filter( 'woocommerce_checkout_fields' , 'ncydesign_remove_woocommerce_checkout_fields' );
function ncydesign_remove_woocommerce_checkout_fields( $fields ) {
	unset($fields['shipping']['shipping_first_name']); 
	unset($fields['shipping']['shipping_last_name']); 
	unset($fields['shipping']['shipping_company']);  
	unset($fields['shipping']['shipping_address_1']);  
	unset($fields['shipping']['shipping_address_2']);  
	unset($fields['shipping']['shipping_city']);  
	unset($fields['shipping']['shipping_postcode']); 
	unset($fields['shipping']['shipping_country']);  
    return $fields;
}

/**
 * This function used for register new endpoints for My Account page
 */  
add_action( 'init', 'my_account_new_endpoints' );
function my_account_new_endpoints() {
    add_rewrite_endpoint( 'awards', EP_ROOT | EP_PAGES );
}

/**
 * This function used for get new endpoint content
 */  
add_action( 'woocommerce_account_awards_endpoint', 'awards_endpoint_content' );
function awards_endpoint_content() {
    get_template_part('my-account-awards');
}

/**
 * Edit my account menu order
 */ 
add_filter ( 'woocommerce_account_menu_items', 'my_account_menu_order' ); 
function my_account_menu_order() {
    $menuOrder = array(
        'dashboard'          => __( 'Dashboard', 'woocommerce' ),
        'memberships'             => __( 'Memberships', 'woocommerce' ),
        'orders'             => __( 'Orders', 'woocommerce' ),
        'downloads'          => __( 'Download', 'woocommerce' ),
        'edit-address'       => __( 'Addresses', 'woocommerce' ),
        'edit-account'      => __( 'Account Details', 'woocommerce' ),
        'awards'             => __( 'RMS Invoices', 'woocommerce' ),
        'customer-logout'    => __( 'Logout', 'woocommerce' )
        
    );
    return $menuOrder;
}

/**
 * This function used for add new endpoint
 */ 
add_action( 'init', 'my_custom_endpoints' ); 
function my_custom_endpoints() {
    add_rewrite_endpoint( 'my-custom-endpoint', EP_ROOT | EP_PAGES );
}

/**
 * This function used for register endpoint quer vars
 */ 
add_filter( 'query_vars', 'my_custom_query_vars', 0 );
function my_custom_query_vars( $vars ) {
	$vars[] = 'my-custom-endpoint';
	return $vars;
}

/**
 * This function used for get new endpoint content
 */ 
add_action( 'woocommerce_account_my-custom-endpoint_endpoint', 'my_custom_endpoint_content' );
function my_custom_endpoint_content($id) {
	$url = "https://api.current-rms.com/api/v1/opportunities/".$id."/opportunity_items";    
	$ch = curl_init( $url );
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:mossrentals','X-AUTH-TOKEN:XC4erz8Bos-dos44yNvX','Content-Type:application/json'));

	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$result = curl_exec($ch);
	curl_close($ch);

	$result = json_decode($result);
	$opp_items_array[] = $result->opportunity_items[1];
	$table = "<h2 class='heading'>Opportunity Items</h2><table class='display item_table table table-bordered'><thead><tr><th>ID</th><th>Product Name</th><th>Charge Total</th><th>Charge Including Tax</th></tr><thead><tbody>";
	    
	foreach ($opp_items_array as $key_data => $opp_items) {

	    $url = "https://api.current-rms.com/api/v1/opportunities/".$id."/opportunity_items";    
	    $ch = curl_init( $url );
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
	    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:o3group','X-AUTH-TOKEN:PDW-dNfDiZd5RbLLbHYT','Content-Type:application/json'));

	    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	    
	    $result = curl_exec($ch);
	    curl_close($ch);

	    $result = json_decode($result);

	    $opp_items_array_data = $result->opportunity_items[1];
	    $totoal_item_array[] = array('id' => $opp_items_array_data->id,'name' => $opp_items_array_data->name,'charge_total' => $opp_items_array_data->charge_total,'charge_including_tax_total' => $opp_items_array_data->charge_including_tax_total );
	}
   
    foreach ($totoal_item_array as $key => $totoal_item) {
                
        $table.= "<tr><td>".$totoal_item['id']."</td><td>".$totoal_item['name']."</td><td>".$totoal_item['charge_total']."</td><td>".$totoal_item['charge_including_tax_total']."</td></tr>";
    }
    $table.= "</tbody><tfoot><tr><th>ID</th><th>Product Name</th><th>Charge Total</th><th>Charge Including Tax</th></tr></tfoot></table>";
    echo $table;
}

/**
 * This function used for add extra user fields
 */ 
add_action( 'user_new_form', 'extra_profile_fields', 15 );
add_action( 'show_user_profile', 'extra_profile_fields', 15 );
add_action( 'edit_user_profile', 'extra_profile_fields', 15 );
function extra_profile_fields( $user ) { ?>
   
    <h3><?php _e('Add CRMS User Id and Address Id '); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="user_crms_id">CRMS User Id</label></th>
            <td>
            <input type="text" name="user_crms_id" id="user_crms_id" value="<?php echo esc_attr( get_the_author_meta( 'user_crms_id', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description">Enter crms user id here.</span>
            </td>
        </tr> 
        <tr>
            <th><label for="user_crms_address">Add CRMS Id Billing Address</label></th>
            <td>
            <input type="text" name="user_crms_address" id="user_crms_address" value="<?php echo esc_attr( get_the_author_meta( 'user_crms_address', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description">Enter crms Address id here.</span>
            </td>
        </tr>

        <tr>
            <th><label for="user_crms_venue">Add CRMS Id Shipping Address</label></th>
            <td>
            <input type="text" name="user_crms_venue" id="user_crms_venue" value="<?php echo esc_attr( get_the_author_meta( 'user_crms_venue', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description">Enter crms Venue id here.</span>
            </td>
        </tr>       
    </table>
    <?php
}

/**
 * This function used for save extra user fields
 */ 
add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );
add_action( 'user_register', 'save_extra_profile_fields' );
function save_extra_profile_fields( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    if (isset($_POST['user_crms_id'])) {

	    $user_crms_id = $_POST['user_crms_id'];
	    $email = $_POST['email'];
	    $billing_phone = $_POST['billing_phone'];

	    $url = "https://api.current-rms.com/api/v1/members/".$user_crms_id."";
	    $ch = curl_init( $url );
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
	    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:o3group','X-AUTH-TOKEN:PDW-dNfDiZd5RbLLbHYT','Content-Type:application/json'));

	    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	    $result = curl_exec($ch);
	    curl_close($ch);
	    $result = json_decode($result);
	    $member_data_rms = $result->member;
	    $phones = $member_data_rms->phones[0];
	    $emails = $member_data_rms->emails[0];
	    $name = $member_data_rms->name;
	    $description = $description->description;
	    $phones_number = $phones->number;
	    $emails_address = $emails->address;

	    if (!empty($member_data_rms)) {
	        if ($emails_address == $email) {
	        
	        update_user_meta($user_id, 'billing_phone', sanitize_text_field($phones_number));
	        update_user_meta($user_id, 'nickname', sanitize_text_field($name));
	        update_user_meta($user_id, 'description', sanitize_text_field($description));
	        }
	    }
    }
	if (!empty($member_data_rms->parent_members)) {

	    $parent_members_add_data = $member_data_rms->parent_members;

		foreach ($parent_members_add_data as $key => $member_add_data) {
			if ($member_add_data->relatable_membership_type == "Venue" && $member_add_data->relatable_id == $_POST['user_crms_venue'] ) {

				$url = "https://api.current-rms.com/api/v1/members/".$_POST['user_crms_venue']."";
			    $ch = curl_init( $url );
			    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
			    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:o3group','X-AUTH-TOKEN:PDW-dNfDiZd5RbLLbHYT','Content-Type:application/json'));

			    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			    $result = curl_exec($ch);
			    curl_close($ch);

			    $result = json_decode($result);
			    $member_data = $result->member;

			    $primary_address = $member_data->primary_address;

		    	$address_internal_name = "Address Internal Name ".$primary_address->name;
		    	$shipping_first_name = $primary_address->name;
		    	$shipping_last_name = $primary_address->name;
		    	$shipping_company = $shipping_first_name." company";
		    	$address_id = $primary_address->id;
		    	$shipping_state = $primary_address->city;
		    	$shipping_address_1 = $primary_address->street;
		    	$postcode = $primary_address->postcode;
		    	$c_code = $primary_address->country;
				$code = $c_code->code;
				$address_type_name = "shipping";

				$final_add_array[] = array('type' => $address_type_name,'address_id' => $address_id,'user_id' => $user_id,'shipping_first_name' => $shipping_first_name,'shipping_last_name' => $shipping_last_name,'shipping_company' => $shipping_company,'shipping_country' => $code,'shipping_state' => $shipping_state,'shipping_address_1' => $shipping_address_1,'shipping_address_2' => '','shipping_city' => $shipping_state,'shipping_postcode' => $postcode,'shipping_phone' => '','shipping_email' => 'admin@123.com','address_internal_name' => $address_internal_name);	
			}

			if ($member_add_data->relatable_membership_type == "Organisation" && $member_add_data->relatable_id == $_POST['user_crms_address'] ) {

				$url = "https://api.current-rms.com/api/v1/members/".$_POST['user_crms_address']."";
			    $ch = curl_init( $url );
			    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
			    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:o3group','X-AUTH-TOKEN:PDW-dNfDiZd5RbLLbHYT','Content-Type:application/json'));
			   
			    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			
			    $result = curl_exec($ch);
			    curl_close($ch);

			    $result = json_decode($result);
			    $member_data = $result->member;

			    global $wpdb;
		    	$crms_member_exist = $wpdb->get_results("SELECT * FROM wp_crms_member WHERE email='".$emails_address."'");
		        if (empty($crms_member_exist)) {
		            $memid = $member_data->id;
		            $memuuid = $member_data->uuid;
		            $memname = $member_data->name;
		            $billingid_primary_address = $member_data->primary_address;
		            $billingid = $billingid_primary_address->id;
		            
		            $email = $emails_address;
		            
		            $sql = "Insert Into wp_crms_member (id, uuid, email, name, billing_id) Values (".$memid.",'".$memuuid."','".$email."','".$memname."',".$billingid.")";
		            $Insert_mamber = $wpdb->get_results($sql);
		        }


			    $addresses = $member_data->addresses;

			    foreach ($addresses as $key => $address) {

			    	if ($address->address_type_name == "Billing") {
			    		$address_internal_name = "Address Internal Name ".$address->name;
				    	$billing_first_name = $address->name;
				    	$billing_last_name = $address->name;
				    	$billing_company = $billing_first_name." company";
				    	$address_id = $address->id;
				    	$billing_state = $address->city;
				    	$billing_address_1 = $address->street;
				    	$postcode = $address->postcode;
				    	$c_code = $address->country;
	    				$code = $c_code->code;
	    				$address_type_name = "billing";

	    				$final_add_array[] = array('type' => $address_type_name,'address_id' => $address_id,'user_id' => $user_id,'billing_first_name' => $billing_first_name,'billing_last_name' => $billing_last_name,'billing_company' => $billing_company,'billing_country' => $code,'billing_state' => $billing_state,'billing_address_1' => $billing_address_1,'billing_address_2' => '','billing_city' => $billing_state,'billing_postcode' => $postcode,'billing_phone' => '','billing_email' => 'admin@123.com','address_internal_name' => $address_internal_name);
	    				
			    	}
			    }
			}
		}
		$serialize_final_add_array = serialize($final_add_array);
	}

    $data = serialize(array("test"=>"1"));

    /* Edit the following lines according to your set fields */
    update_usermeta( $user_id, '_wcmca_additional_addresses', $data  );

  	global $wpdb;
    $sql = "update www0_usermeta
        set
        meta_value = case meta_key
                        when '_wcmca_additional_addresses' then '$serialize_final_add_array'
                        else meta_value
                  end 
              where
  			user_id = $user_id";

    update_usermeta( $user_id, 'user_crms_id', $_POST['user_crms_id'] );
    update_usermeta( $user_id, 'user_crms_address', $_POST['user_crms_address'] );
    update_usermeta( $user_id, 'user_crms_venue', $_POST['user_crms_venue'] );
    
}

/**
 * This function used for unset filter session on reset
 */ 
add_action( 'wp_ajax_nopriv_remove_filter_session', 'product_fileter_session_remove' );
add_action( 'wp_ajax_remove_filter_session', 'product_fileter_session_remove' );
function product_fileter_session_remove(){
	WC()->session->__unset( 'product_start_date' );
	WC()->session->__unset( 'product_end_date' );
	WC()->session->__unset( 'filter_type' );
	WC()->session->__unset( 'lang' );
}
