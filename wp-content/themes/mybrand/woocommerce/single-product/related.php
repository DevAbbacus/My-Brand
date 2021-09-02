<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$related_product_view = woodmart_get_opt( 'related_product_view' );

if ( $related_products ) : ?>

	<div class="related-products">
		
		<h3 class="title slider-title"><?php echo esc_html__( 'Related products', 'woocommerce' ); ?></h3>
		
		<?php 
		
			/*if ( $related_product_view == 'slider' ) {
				$slider_args = array(
					'slides_per_view' => ( woodmart_get_opt( 'related_product_columns' ) ) ? woodmart_get_opt( 'related_product_columns' ) : apply_filters( 'woodmart_related_products_per_view', 4 ),
					'img_size' => 'woocommerce_thumbnail',
					'products_bordered_grid' => woodmart_get_opt( 'products_bordered_grid' ),
					'custom_sizes' => apply_filters( 'woodmart_product_related_custom_sizes', false )
				);
				
				woodmart_set_loop_prop( 'products_view', 'carousel' );

				echo woodmart_generate_posts_slider( $slider_args, false, $related_products );
			}elseif ( $related_product_view == 'grid' ) {
		
				woodmart_set_loop_prop( 'products_columns', woodmart_get_opt( 'related_product_columns' ) );
				woodmart_set_loop_prop( 'products_different_sizes', false );
				woodmart_set_loop_prop( 'products_masonry', false );
				woodmart_set_loop_prop( 'products_view', 'grid' );
				
				woocommerce_product_loop_start();

				foreach ( $related_products as $related_product ) {
					$post_object = get_post( $related_product->get_id() );

					setup_postdata( $GLOBALS['post'] = $post_object );

					wc_get_template_part( 'content', 'product' ); 
				}

				woocommerce_product_loop_end();
				
				woodmart_reset_loop();
				
				if ( function_exists( 'woocommerce_reset_loop' ) ) woocommerce_reset_loop();
			}*/
			
		?>
		<?php $all = get_post_meta( get_the_ID(), 'related_products', true );


			if(!empty($all)){
		?>
		<div id="my-carousel" class="owl-carousel woodmart-carousel-container  slider-type-product woodmart-carousel-spacing-30 products-bordered-grid" >
		<?php 
			$all =explode(",", $all);
			foreach ($all as $a) { 
			$product = wc_get_product( $a ); 

			//echo "<pre>";print_r($product);exit();
			?>
			<div class="owl-item " style="width: 292.938px;">
				<div class="slide-product owl-carousel-item">
					<div class="product-grid-item product woodmart-hover-standard type-product post-<?php echo $product->get_id(); ?> status-publish last instock product_cat-furniture_deco_tables_chairs_stools has-post-thumbnail taxable shipping-taxable purchasable product-type-simple" data-id="<?php echo $product->get_id(); ?>">
						<div class="product-element-top">
							<a href="<?php echo get_permalink( $product->get_id() ); ?>" class="product-image-link">
							<?php 
								$image_id  = $product->get_image_id();
								$image_url = wp_get_attachment_image_url( $image_id, 'full' ); ?>
							<img src="<?php echo ($image_url)? $image_url : site_url('wp-content/uploads/2020/05/product-image-coming-soon.jpg');  ?>" width="600" height="600" class="woocommerce-placeholder wp-post-image" alt="Placeholder"></a>
							<div class="woodmart-buttons wd-pos-r-t">
							   	<div class="quick-view wd-action-btn wd-quick-view-btn wd-style-icon">
							      	<a href="<?php echo get_permalink( $product->get_id() ); ?>" class="open-quick-view woodmart-tltp" data-id="<?php echo $product->get_id(); ?>"><span class="woodmart-tooltip-label" style="margin-left: -46px;">Quick View</span>Quick View</a>
							   	</div>
							   	<div class="woodmart-wishlist-btn wd-action-btn wd-wishlist-btn wd-style-icon">
							      	<a href="<?php echo get_permalink( $product->get_id() ); ?>" data-key="86710680a7" data-product-id="<?php echo $product->get_id(); ?>" data-added-text="Browse Wishlist" class="woodmart-tltp"><span class="woodmart-tooltip-label" style="margin-left: -58px;">Add to wishlist</span>Add to wishlist</a>
							   	</div>
							</div>
							<div class="quick-shop-wrapper">
							   	<div class="quick-shop-close wd-cross-button wd-size-s wd-with-text-left"><span>Close</span></div>
								<div class="quick-shop-form">
								</div>
							</div>
						</div>
						<h3 class="product-title"><a href="<?php echo get_permalink( $product->get_id() ); ?>"><?php echo $product->get_name(); ?></a></h3>
						<span class="price"><?php echo $product->get_price_html(); ?> 
						<div class="woodmart-add-btn wd-add-btn-replace">
							<a href="?add-to-cart=<?php echo $product->get_id(); ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart add-to-cart-loop" data-product_id="<?php echo $product->get_id(); ?>" data-product_sku="bpost-20-30kg---nationaal" aria-label="Add “Bpost 20-30kg - national” to your cart" rel="nofollow"><span>Add to cart</span></a>
						</div>
					</div>
				</div>
	        </div>
		<?php } ?>
		</div>
		<?php }else{echo "<p>There Are No Related Products Available.</p>";} ?>

<?php endif;

wp_reset_postdata();