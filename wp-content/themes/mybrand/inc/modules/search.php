<?php
 if ( ! defined( 'WOODMART_THEME_DIR' ) ) {
	exit( 'No direct script access allowed' );
}

// **********************************************************************// 
// Search full screen
// **********************************************************************// 
if( ! function_exists( 'woodmart_search_full_screen' ) ) {
	function woodmart_search_full_screen() {

		if ( ! whb_is_full_screen_search() ) return;

		$search_args = array(
			'type' => 'full-screen'
		);

		$settings = whb_get_settings();
		if( isset( $settings['search'] ) ) {
			$search_args['post_type'] = $settings['search']['post_type'];
			$search_args['ajax'] = $settings['search']['ajax'];
			$search_args['count'] = ( isset( $settings['search']['ajax_result_count'] ) && $settings['search']['ajax_result_count'] ) ? $settings['search']['ajax_result_count'] : 40;
		}

		woodmart_search_form( $search_args );

	}

	add_action( 'wp_footer', 'woodmart_search_full_screen', 1 );
}

// **********************************************************************// 
// Search form
// **********************************************************************// 
if( ! function_exists( 'woodmart_search_form' ) ) {
	function woodmart_search_form( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'ajax' => false,
			'post_type' => false,
			'show_categories' => false,
			'type' => 'form',
			'thumbnail' => true,
			'price' => true,
			'count' => 20,
			'icon_type' => '',
			'search_style' => '',
			'custom_icon' => '',
			'el_classes' => '',
		) );

		extract( $args ); 

		$class = '';
		$data  = '';

		if ( $show_categories && $post_type == 'product' ) {
			$class .= ' has-categories-dropdown';
		} 

		if ( $icon_type == 'custom' ) {
			$class .= ' woodmart-searchform-custom-icon';
		}

		if ( $search_style ) {
			$class .= ' search-style-' . $search_style;
		}

		$ajax_args = array(
			'thumbnail' => $thumbnail,
			'price' => $price,
			'post_type' => $post_type,
			'count' => $count,
			'sku' => woodmart_get_opt( 'show_sku_on_ajax' ) ? '1' : '0',
			'symbols_count' => apply_filters( 'woodmart_ajax_search_symbols_count', 3 ),
		);
			$filter_type = strip_tags( $_REQUEST['filter_type'] );
			$product_start_date = get_query_var('product_start_date');
			$product_end_date = get_query_var('product_end_date');
			$Clang = apply_filters( 'wpml_current_language', NULL );
			// $filter_type = $filter_type;


		if (!empty($product_start_date) && !empty($product_end_date) &&  !empty($filter_type)) {
            
			WC()->session->set( 'product_start_date', $product_start_date ); 				
			WC()->session->set( 'product_end_date', $product_end_date ); 
			WC()->session->set( 'filter_type', $filter_type );	
			WC()->session->set( 'lang', $Clang );			
			
 			
		}
		// $product_start_date = WC()->session->get( 'product_start_date', $product_start_date );
			// echo "<pre>";print_r($Clang);exit();

		if( $ajax ) {

			$class .= ' woodmart-ajax-search';
			woodmart_enqueue_script( 'woodmart-autocomplete' );
			foreach ($ajax_args as $key => $value) {
				$data .= ' data-' . $key . '="' . $value . '"';
			}
		}


		switch ( $post_type ) {
			case 'product':
				$placeholder = esc_attr_x( 'Search for products', 'submit button', 'woodmart' );
				$description = esc_html__( 'Start typing to see products you are looking for.', 'woodmart' );
			break;

			case 'portfolio':
				$placeholder = esc_attr_x( 'Search for projects', 'submit button', 'woodmart' );
				$description = esc_html__( 'Start typing to see projects you are looking for.', 'woodmart' );
			break;
		
			default:
				$placeholder = esc_attr_x( 'Search for posts', 'submit button', 'woodmart' );
				$description = esc_html__( 'Start typing to see posts you are looking for.', 'woodmart' );
			break;
		}

		if ( $el_classes ) {
			$class .= ' ' . $el_classes;
		}

		?>
			<div class="woodmart-search-<?php echo esc_attr( $type ); ?> main_serch_div row">
				<?php if ( $type == 'full-screen' ): ?>
					<span class="woodmart-close-search wd-cross-button"><?php esc_html_e('close', 'woodmart'); ?></span>
				<?php endif ?>
				<div class="search_div col-md-6"> 
					<form role="search" method="get" class="searchform <?php echo esc_attr( $class ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo ! empty( $data ) ? $data : ''; ?>>
						<input type="text" class="s" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
						<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">
						<?php if( $show_categories && $post_type == 'product' ) woodmart_show_categories_dropdown(); ?>
						<button type="submit" class="searchsubmit">
							<?php echo esc_attr_x( 'Search', 'submit button', 'woodmart' ); ?>
							<?php 
								if ( $icon_type == 'custom' ) {
									echo whb_get_custom_icon( $custom_icon );
								}
							?>
						
						</button>
					</form>
				</div>

                <form role="search" method="get" class="searchform <?php echo esc_attr( $class ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo ! empty( $data ) ? $data : ''; ?>>

					<div class="filter_div col-md-12">
						<link rel='stylesheet' id='jquery-ui-style-css'  href='https://my-brand.be/wp-content/plugins/flexible-checkout-fields/assets/css/jquery-ui.min.css?ver=2.9.2.19' type='text/css' media='all' />
						
						<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
						<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
						<!-- filter by date start -->
						<div class="row">
							<input type="hidden" name="s" value="" >
							<input type="hidden" name="lang" value="<?php echo esc_attr( $Clang ); ?>">
							<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">
							
							<div class="filter_option d-flex">							
							    <!-- <span class="woocommerce-input-wrapper"><input type="radio" class="input-radio" name="filter_type" id="one_daye" value="one_daye" <?php //echo ($filter_type=='one_daye')?'checked':'' ?> ></span>
							    <label for="one_daye" class="">One day</label> -->
<?php 

/*echo "dd".$_SESSION["filter"];
echo $_SESSION["sdate"];
echo $_SESSION["edate"];*/
?>
							    <span class="woocommerce-input-wrapper">
							    	<input type="radio" class="input-radio" name="filter_type" id="period" value="period" <?php echo ($filter_type=='period')?'checked':'checked'; echo ($_SESSION["filter"] == "period")? 'checked' : '' ; ?> >
							    	<label for="period" class="">Period</label>
							    </span>

							    <span class="woocommerce-input-wrapper">
							    	<input type="radio" class="input-radio" name="filter_type" id="deadline" value="deadline" <?php echo ($filter_type=='deadline')?'checked':''; ?>>
							    	<label for="deadline" class="">Deadline</label>
							    </span>
							</div>	
							
                            <div class="flt_date">

								<p class="form-row form-row-first validate-required col-md-6" id="billing_booking_start_date_field" data-priority="10" >
								    
								    <span class="woocommerce-input-wrapper"><input type="text" class="input-text" name="product_start_date" id="product_start_date" placeholder="dd/mm/yy" value="<?php echo (!empty(WC()->session->get( 'product_start_date' )))? WC()->session->get( 'product_start_date' ) : $product_start_date; ?>"></span>
								</p>

								<p class="form-row form-row-last validate-required col-md-6" id="billing_booking_end_date_field" data-priority="10" >
								    
								    <span class="woocommerce-input-wrapper"><input type="text" class="input-text" name="product_end_date" id="product_end_date" placeholder="dd/mm/yy" value="<?php echo (!empty(WC()->session->get( 'product_end_date' )))? WC()->session->get( 'product_end_date' ) : $product_end_date; ?>"></span>
								</p>

								<button type="submit" class="btn qbtn">Go</button>
								<button id="reset" class="btn qbtn">Reset</button>
							</div>	
						</div>

					</div>
                
                </form>
                
				<script>


					jQuery(document).ready(function(){

						jQuery("#reset").click(function(e) {
							e.preventDefault();
						  	jQuery('#product_start_date').val('');
						  	jQuery('#product_end_date').val('');
                            var action_data = 'remove_filter_session';
                            var session_remove = 'session_remove';
						  	jQuery.ajax({

							    url:"<?php echo admin_url('admin-ajax.php'); ?>",
				                type:'POST',
				                data: { 'session_remove' : session_remove,'action' : action_data },
				                success: function(data){
							  
							        //location.reload();
							    }
							});
						  	
						});

					  jQuery("#reportCustomDisplay").html('Nothing Selected');

					  // Initially always disabled.
					  

					  jQuery("#product_end_date").prop('disabled', true);
					  
					  // DATE FROM
					  jQuery("#product_start_date").flatpickr({
					    // First Month of year
					    minDate: 'today',

					    dateFormat: "d-m-Y",
					    maxDate: jQuery("#product_end_date").val() ? jQuery("#product_end_date").val() : "31-12-2021",

					    // Last  Month of year
					    //maxDate: "2021-12-31",
					    // Format it to a mySQL datetime friendly format
					    //dateFormat: "Y-m-d",

					    // When this input changes, we set a min start date 
					    // for input2 always equal or greater than this.
					    onChange: function(selectedDates, dateStr, instance) {

					      // Set display from
					      jQuery("#reportFromCustom").html(dateStr);
					      // Enable inputText2
					      jQuery("#product_end_date").prop('disabled', false);
					      // Set display to
					      jQuery("#reportToCustom").html('00-00-0000');
					      // Set display progress
					      jQuery("#reportCustomDisplay").html('..to when?');
					      // jQuery("#product_end_date").set('minDate', selectedDates[0]);
					      	console.log("selectedDates[0]",selectedDates[0],dateStr)
						      // Recreate inputText2 with relative start date
						      jQuery("#product_end_date").flatpickr({ 
						        // inputText1 selected datetime
						        // Last  Month of year
						        // Format it to a mySQL datetime friendly format
						        dateFormat: "d-m-Y", 
						        maxDate: "31-12-2031",
						        minDate: dateStr, 
						        /*maxDate: "2021-12-31",
						        dateFormat: "Y-m-d", */
						        onChange: function(selectedDates, dateStr, instance) {
						          // Set display to
						          jQuery("#reportToCustom").html(dateStr);

						          // Set display progress
						          jQuery("#reportCustomDisplay").html('Click Get report!');

						          	jQuery("#product_start_date").flatpickr({
									    // First Month of year
									    minDate: 'today',

									    dateFormat: "d-m-Y",
									    maxDate: dateStr,
									})
						        }
						      });
					    }


					  });

					});



					jQuery(document).ready(function($) {

						/*var date1 =flatpickr('#product_start_date',{
							dateFormat: "d-m-Y",
							minDate: "today",
							"locale": {
							    "firstDayOfWeek": 1 // start week on Monday
							},
							onChange: function(selectedDates, dateStr, instance) {
							    date2.set('minDate', dateStr)
							}
						});*/
				        //jQuery("#product_start_date").datepicker({format: 'dd/mm/yy'});
				        
						/*var date2 =  flatpickr('#product_end_date',{
						 	dateFormat: "d-m-Y",
						 	"locale": {
							    "firstDayOfWeek": 1 // start week on Monday
							},
							onChange: function(selectedDates, dateStr, instance) {
						    date1.set('maxDate', dateStr)
						  	}*/					    	
						    /*onSelect: function(dateText, inst) {
						        var startDate = jQuery('#product_start_date').val(); 
						        var endDate = dateText
						        getBookingDays(startDate,endDate);
						    }*/
					    /*});*/

							// $('#ReturnRequired').is(':checked')

							if (jQuery('#one_daye').is(':checked')) {
						        jQuery('#billing_booking_start_date_field').css('display', 'block');
						        jQuery('#billing_booking_end_date_field').css('display', 'none');
						        jQuery("#product_end_date").val('');
						    } else if(jQuery('#period').is(':checked')){
						    	jQuery('#billing_booking_start_date_field').css('display', 'block');
						        jQuery('#billing_booking_end_date_field').css('display', 'block');
						        jQuery('#product_end_date').prop("disabled", true);
						        
						    } else if(jQuery('#deadline').is(':checked')){
						    	jQuery('#billing_booking_start_date_field').css('display', 'none');
						        jQuery('#billing_booking_end_date_field').css('display', 'block');
						        jQuery("#product_end_date").prop('disabled', false);
						        jQuery("#product_start_date").val('');
					  	
					  			jQuery("#product_end_date").flatpickr({ 
							        // inputText1 selected datetime
							        // Last  Month of year
							        // Format it to a mySQL datetime friendly format
							        dateFormat: "d-m-Y", 
							        maxDate: "31-12-2031",
							        minDate: 'today', 
							        /*maxDate: "2021-12-31",
							        dateFormat: "Y-m-d", */
							        onChange: function(selectedDates, dateStr, instance) {
							          // Set display to
							          jQuery("#reportToCustom").html(dateStr);

							          // Set display progress
							          jQuery("#reportCustomDisplay").html('Click Get report!');
							        }
							      });
						    } else {
						    	jQuery('#billing_booking_start_date_field').css('display', 'block');
						        jQuery('#billing_booking_end_date_field').css('display', 'block');
						    }


					    jQuery(".filter_option input:radio").change(function () {
						    if (jQuery(this).val() == "one_daye") {
						        jQuery('#billing_booking_start_date_field').css('display', 'block');
						        jQuery('#billing_booking_end_date_field').css('display', 'none');
						        jQuery("#product_end_date").val('');
						    } else if(jQuery(this).val() == "period"){
						    	jQuery('#billing_booking_start_date_field').css('display', 'block');
						        jQuery('#billing_booking_end_date_field').css('display', 'block');
						        jQuery('#product_end_date').prop("disabled", true);
						    } else if(jQuery(this).val() == "deadline"){
						    	jQuery('#billing_booking_start_date_field').css('display', 'none');
						        jQuery('#billing_booking_end_date_field').css('display', 'block');
						        // jQuery('#product_end_date').prop("disabled", false);
						        jQuery("#product_start_date").val('');
						        jQuery("#product_end_date").prop('disabled', false);
					  	
					  			jQuery("#product_end_date").flatpickr({ 
							        // inputText1 selected datetime
							        // Last  Month of year
							        // Format it to a mySQL datetime friendly format
							        dateFormat: "d-m-Y", 
							        maxDate: "31-12-2031",
							        minDate: 'today', 
							        /*maxDate: "2021-12-31",
							        dateFormat: "Y-m-d", */
							        onChange: function(selectedDates, dateStr, instance) {
							          // Set display to
							          jQuery("#reportToCustom").html(dateStr);

							          // Set display progress
							          jQuery("#reportCustomDisplay").html('Click Get report!');
							        }
							      });
						    } else {
						    	jQuery('#billing_booking_start_date_field').css('display', 'block');
						        jQuery('#billing_booking_end_date_field').css('display', 'block');
						    }
						});

				    });
				</script>
				<!-- filter by date start -->

				<?php if ( $type == 'full-screen' ): ?>
					<div class="search-info-text"><span><?php echo esc_html( $description ); ?></span></div>
				<?php endif ?>
				<?php if ( $ajax ): ?>
					<div class="search-results-wrapper"><div class="woodmart-scroll"><div class="woodmart-search-results woodmart-scroll-content"></div></div><div class="woodmart-search-loader wd-fill"></div></div>
				<?php endif ?>
			</div>
		<?php
	}
}

if( ! function_exists( 'woodmart_show_categories_dropdown' ) ) {
	function woodmart_show_categories_dropdown() {
		$args = array( 
			'hide_empty' => 1,
			'parent' => 0
		);
		$terms = get_terms('product_cat', $args);
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			?>
			<div class="search-by-category input-dropdown">
				<div class="input-dropdown-inner woodmart-scroll-content">
					<input type="hidden" name="product_cat" value="0">
					<a href="#" data-val="0"><?php esc_html_e('Select category', 'woodmart'); ?></a>
					<div class="list-wrapper woodmart-scroll">
						<ul class="woodmart-scroll-content">
							<li style="display:none;"><a href="#" data-val="0"><?php esc_html_e('Select category', 'woodmart'); ?></a></li>
							<?php
								if( ! apply_filters( 'woodmart_show_only_parent_categories_dropdown', false ) ) {
							        $args = array(
							            'title_li' => false,
										'taxonomy' => 'product_cat',
										'use_desc_for_title' => false,
							            'walker' => new WOODMART_Custom_Walker_Category(),
							        );
							        wp_list_categories($args);
								} else {
								    foreach ( $terms as $term ) {
								    	?>
											<li><a href="#" data-val="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_attr( $term->name ); ?></a></li>
								    	<?php
								    }
								}
							?>
						</ul>
					</div>
				</div>
			</div>
			<?php
		}
	}
}

/**
 * ------------------------------------------------------------------------------------------------
 * Blog results on search page
 * ------------------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'woodmart_show_blog_results_on_search_page' ) ) {
	function woodmart_show_blog_results_on_search_page() {
		if ( ! is_search() || ! woodmart_get_opt( 'enqueue_posts_results' ) ) {
			return;
		}

		$search_query = get_search_query();
		$column = woodmart_get_opt( 'search_posts_results_column' );

		ob_start();

		?>
		<div class="wd-blog-search-results">
			<h4 class="slider-title">
				<?php esc_html_e( 'Results from blog', 'woodmart' ); ?>
			</h4>
		
			<?php echo do_shortcode( '[woodmart_blog slides_per_view="' . $column . '" blog_design="carousel" search="' . $search_query . '" items_per_page="10"]' ); ?>
		
			<div class="wd-search-show-all">
				<a href="<?php echo esc_url( home_url() ) ?>?s=<?php echo esc_attr( $search_query ); ?>&post_type=post" class="button">
					<?php esc_html_e( 'Show all blog results', 'woodmart' ); ?>
				</a>
			</div>
		</div>
		<?php
		
		echo ob_get_clean();
	}
	
	add_action( 'woocommerce_after_shop_loop', 'woodmart_show_blog_results_on_search_page', 100 );
	add_action( 'woodmart_after_portfolio_loop', 'woodmart_show_blog_results_on_search_page', 100 );
	add_action( 'woodmart_after_no_product_found', 'woodmart_show_blog_results_on_search_page', 100 );
}

/**
 * ------------------------------------------------------------------------------------------------
 * Ajax search
 * ------------------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'woodmart_init_search_by_sku' ) ) {
	function woodmart_init_search_by_sku() {
		if ( apply_filters( 'woodmart_search_by_sku', woodmart_get_opt( 'search_by_sku' ) ) && woodmart_woocommerce_installed() ) {
			add_filter( 'posts_search', 'woodmart_product_search_sku', 9 );
		}
	}

	add_action( 'init', 'woodmart_init_search_by_sku', 10 );
}

if ( ! function_exists( 'woodmart_ajax_suggestions' ) ) {
	function woodmart_ajax_suggestions() {

		$allowed_types = array( 'post', 'product', 'portfolio' );
		$post_type = 'product';

		if ( apply_filters( 'woodmart_search_by_sku', woodmart_get_opt( 'search_by_sku' ) ) && woodmart_woocommerce_installed() ) {
			add_filter( 'posts_search', 'woodmart_product_ajax_search_sku', 10 );
		}
		
		$query_args = array(
			'posts_per_page' => 5,
			'post_status'    => 'publish',
			'post_type'      => $post_type,
			'no_found_rows'  => 1,
		);

		if ( ! empty( $_REQUEST['post_type'] ) && in_array( $_REQUEST['post_type'], $allowed_types ) ) {
			$post_type = strip_tags( $_REQUEST['post_type'] );
			$query_args['post_type'] = $post_type;
		}

		if ( $post_type == 'product' && woodmart_woocommerce_installed() ) {
			
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['exclude-from-search'],
				'operator' => 'NOT IN',
			);

			if ( ! empty( $_REQUEST['product_cat'] ) ) {
				$query_args['product_cat'] = strip_tags( $_REQUEST['product_cat'] );
			}
		}

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && $post_type == 'product' ) {
			$query_args['meta_query'][] = array( 'key' => '_stock_status', 'value' => 'outofstock', 'compare' => 'NOT IN' );
		}

		if ( ! empty( $_REQUEST['query'] ) ) {
			$query_args['s'] = sanitize_text_field( $_REQUEST['query'] );
		}

		if ( ! empty( $_REQUEST['number'] ) ) {
			$query_args['posts_per_page'] = (int) $_REQUEST['number'];
		}

		$results = new WP_Query( apply_filters( 'woodmart_ajax_search_args', $query_args ) );

		if ( woodmart_get_opt( 'relevanssi_search' ) && function_exists( 'relevanssi_do_query' ) ) {
			relevanssi_do_query( $results );
		}

		$suggestions = array();

		if ( $results->have_posts() ) {

			if ( $post_type == 'product' && woodmart_woocommerce_installed() ) {
				$factory = new WC_Product_Factory();
			}

			while ( $results->have_posts() ) {
				$results->the_post();

				if ( $post_type == 'product' && woodmart_woocommerce_installed() ) {
					$product = $factory->get_product( get_the_ID() );

					$suggestions[] = array(
						'value' => get_the_title(),
						'permalink' => get_the_permalink(),
						'price' => $product->get_price_html(),
						'thumbnail' => $product->get_image(),
						'sku' => $product->get_sku() ? esc_html__( 'SKU:', 'woodmart' ) . ' ' . $product->get_sku() : '',
					);
				} else {
					$suggestions[] = array(
						'value' => get_the_title(),
						'permalink' => get_the_permalink(),
						'thumbnail' => get_the_post_thumbnail( null, 'medium', '' ),
					);
				}
			}

			wp_reset_postdata();
		} else {
			$suggestions[] = array(
				'value' => ( $post_type == 'product' ) ? esc_html__( 'No products found', 'woodmart' ) : esc_html__( 'No posts found', 'woodmart' ),
				'no_found' => true,
				'permalink' => ''
			);
		}

		if ( woodmart_get_opt( 'enqueue_posts_results' ) && 'post' !== $post_type ) {
			$post_suggestions = woodmart_get_post_suggestions();
			$suggestions = array_merge( $suggestions, $post_suggestions );
		}

		echo json_encode( array(
			'suggestions' => $suggestions,
		) );

		die();
	}

	add_action( 'wp_ajax_woodmart_ajax_search', 'woodmart_ajax_suggestions', 10 );
	add_action( 'wp_ajax_nopriv_woodmart_ajax_search', 'woodmart_ajax_suggestions', 10 );
}

if ( ! function_exists( 'woodmart_get_post_suggestions' ) ) {
	function woodmart_get_post_suggestions() {
		$query_args = array(
			'posts_per_page' => 5,
			'post_status'    => 'publish',
			'post_type'      => 'post',
			'no_found_rows'  => 1,
		);
		
		if ( ! empty( $_REQUEST['query'] ) ) {
			$query_args['s'] = sanitize_text_field( $_REQUEST['query'] );
		}
		
		if ( ! empty( $_REQUEST['number'] ) ) {
			$query_args['posts_per_page'] = (int) $_REQUEST['number'];
		}
		
		$results = new WP_Query( $query_args );
		$suggestions = array();

		if ( $results->have_posts() ) {

			$suggestions[] = array(
				'value' => '',
				'divider' => esc_html__( 'Results from blog', 'woodmart' ),
			);

			while ( $results->have_posts() ) {
				$results->the_post();
			
				$suggestions[] = array(
					'value' => get_the_title(),
					'permalink' => get_the_permalink(),
					'thumbnail' => get_the_post_thumbnail( null, 'medium', '' ),
				);
			}
			
			wp_reset_postdata();
		}
		
		return $suggestions;
	}
}

if ( ! function_exists( 'woodmart_product_search_sku' ) ) {
	function woodmart_product_search_sku( $where, $class = false ) {
		global $pagenow, $wpdb, $wp;

		$type = array('product', 'jam');
		
		if ( ( is_admin() ) //if ((is_admin() && 'edit.php' != $pagenow) 
				|| !is_search()  
				|| !isset( $wp->query_vars['s'] ) 
				//post_types can also be arrays..
				|| (isset( $wp->query_vars['post_type'] ) && 'product' != $wp->query_vars['post_type'] )
				|| (isset( $wp->query_vars['post_type'] ) && is_array( $wp->query_vars['post_type'] ) && !in_array( 'product', $wp->query_vars['post_type'] ) ) 
				) {
			return $where;
		}

		$s = $wp->query_vars['s'];

		//WC 3.6.0
		if ( function_exists( 'WC' ) && version_compare( WC()->version, '3.6.0', '<' ) ) {
			return woodmart_sku_search_query( $where, $s );
		} else {
			return woodmart_sku_search_query_new( $where, $s );
		}
	}
}

if ( ! function_exists( 'woodmart_product_ajax_search_sku' ) ) {
	function woodmart_product_ajax_search_sku( $where ) {
		if ( ! empty( $_REQUEST['query'] ) ) {
			$s = sanitize_text_field( $_REQUEST['query'] );

			//WC 3.6.0
			if ( function_exists( 'WC' ) && version_compare( WC()->version, '3.6.0', '<' ) ) {
				return woodmart_sku_search_query( $where, $s );
			} else {
				return woodmart_sku_search_query_new( $where, $s );
			}
		}

		return $where;
	}
}

if ( ! function_exists( 'woodmart_sku_search_query' ) ) {
	function woodmart_sku_search_query( $where, $s ) {
		global $wpdb;

		$search_ids = array();
		$terms = explode( ',', $s );

		foreach ( $terms as $term ) {
			//Include the search by id if admin area.
			if ( is_admin() && is_numeric( $term ) ) {
				$search_ids[] = $term;
			}
			// search for variations with a matching sku and return the parent.

			$sku_to_parent_id = $wpdb->get_col( $wpdb->prepare( "SELECT p.post_parent as post_id FROM {$wpdb->posts} as p join {$wpdb->postmeta} pm on p.ID = pm.post_id and pm.meta_key='_sku' and pm.meta_value LIKE '%%%s%%' where p.post_parent <> 0 group by p.post_parent", wc_clean( $term ) ) );

			//Search for a regular product that matches the sku.
			$sku_to_id = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE '%%%s%%';", wc_clean( $term ) ) );

			$search_ids = array_merge( $search_ids, $sku_to_id, $sku_to_parent_id );
		}

		$search_ids = array_filter( array_map( 'absint', $search_ids ) );

		if ( sizeof( $search_ids ) > 0 ) {
			$where = str_replace( ')))', ") OR ({$wpdb->posts}.ID IN (" . implode( ',', $search_ids ) . "))))", $where );
		}
		
		#remove_filters_for_anonymous_class('posts_search', 'WC_Admin_Post_Types', 'product_search', 10);
		return $where;
	}
}

if ( ! function_exists( 'woodmart_sku_search_query_new' ) ) {
	function woodmart_sku_search_query_new( $where, $s ) {
		global $wpdb;

		$search_ids = array();
		$terms = explode( ',', $s );

		foreach ( $terms as $term ) {
			//Include the search by id if admin area.
			if ( is_admin() && is_numeric( $term ) ) {
				$search_ids[] = $term;
			}
			// search for variations with a matching sku and return the parent.

			$sku_to_parent_id = $wpdb->get_col( $wpdb->prepare( "SELECT p.post_parent as post_id FROM {$wpdb->posts} as p join {$wpdb->wc_product_meta_lookup} ml on p.ID = ml.product_id and ml.sku LIKE '%%%s%%' where p.post_parent <> 0 group by p.post_parent", wc_clean( $term ) ) );

			//Search for a regular product that matches the sku.
			$sku_to_id = $wpdb->get_col( $wpdb->prepare( "SELECT product_id FROM {$wpdb->wc_product_meta_lookup} WHERE sku LIKE '%%%s%%';", wc_clean( $term ) ) );

			$search_ids = array_merge( $search_ids, $sku_to_id, $sku_to_parent_id );
		}

		$search_ids = array_filter( array_map( 'absint', $search_ids ) );

		if ( sizeof( $search_ids ) > 0 ) {
			$where = str_replace( ')))', ") OR ({$wpdb->posts}.ID IN (" . implode( ',', $search_ids ) . "))))", $where );
		}
		
		#remove_filters_for_anonymous_class('posts_search', 'WC_Admin_Post_Types', 'product_search', 10);
		return $where;
	}
}

if ( ! function_exists( 'woodmart_rlv_index_variation_skus' ) ) {
	function woodmart_rlv_index_variation_skus( $content, $post ) {
		if ( ! woodmart_get_opt( 'search_by_sku' ) || ! woodmart_get_opt( 'relevanssi_search' ) || ! function_exists( 'relevanssi_do_query' ) ) {
			return $content;
		}

		if ( $post->post_type == 'product' ) {
			
			$args = array( 'post_parent' => $post->ID, 'post_type' => 'product_variation', 'posts_per_page' => -1 );
			$variations = get_posts( $args );
			if ( !empty( $variations)) {
				foreach ( $variations as $variation ) {
					$sku = get_post_meta( $variation->ID, '_sku', true );
					$content .= " $sku";
				}
			}
		}
		
		return $content;
	}
	
	add_filter( 'relevanssi_content_to_index', 'woodmart_rlv_index_variation_skus', 10, 2 );
}
