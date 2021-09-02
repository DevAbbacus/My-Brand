<?php 
	global $product;
	global $post;

	$allowed_stock_type = get_post_meta($post->ID,'allowed_stock_type',true);
	$allowed_s_type = get_post_meta($post->ID);
	//$meta = get_post_meta($post_id);
	
	//echo "<pre>";print_r($allowed_s_type);exit();


	do_action( 'woocommerce_before_shop_loop_item' ); 
?>

<div class="product-element-top">

	<?php 

	if (!empty($allowed_stock_type)) { ?>
		<span class="allowed_stock_type" style="background: orange;color: white;padding: 2px 5px 5px 5px;border-radius: 5px 5px 5px 5px !important;">

			<?php
			if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == "nl") {
				  if ($allowed_stock_type == "Sale") {
				  	echo "VERKOOP";
				  }else{
				  	echo "VERHUUR";
				  }
				
			} 
			if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == "en") {
				  if ($allowed_stock_type == "Sale") {
				  	echo "Sale";
				  }else{
				  	echo "Rental";
				  }
				
			}
			if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE == "fr") {
				  if ($allowed_stock_type == "Sale") {
				  	echo "ACHAT";
				  }else{
				  	echo "LOCATION";
				  }
				
			} 
			?>
		</span>
	<?php }

	?>

	
	<a href="<?php echo esc_url( get_permalink() ); ?>" class="product-image-link">
		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woodmart_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
		?>
	</a>
	<?php woodmart_hover_image(); ?>
	<div class="woodmart-buttons wd-pos-r-t">
		<?php woodmart_add_to_compare_loop_btn(); ?>
		<?php woodmart_quick_view_btn( get_the_ID() ); ?>
		<?php do_action( 'woodmart_product_action_buttons' ); ?>
	</div>

	<?php woodmart_quick_shop_wrapper(); ?>
</div>

<?php 
	echo woodmart_swatches_list();
?>

<?php
	/**
	 * woocommerce_shop_loop_item_title hook
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	do_action( 'woocommerce_shop_loop_item_title' );
?>

<?php
	woodmart_product_categories();
	woodmart_product_brands_links();
?>

<?php
	/**
	 * woocommerce_after_shop_loop_item_title hook
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );
?>

<div class="woodmart-add-btn wd-add-btn-replace">
	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
</div>

<?php if ( woodmart_loop_prop( 'progress_bar' ) ): ?>
	<?php woodmart_stock_progress_bar(); ?>
<?php endif ?>

<?php if ( woodmart_loop_prop( 'timer' ) ): ?>
	<?php woodmart_product_sale_countdown(); ?>
<?php endif ?>
