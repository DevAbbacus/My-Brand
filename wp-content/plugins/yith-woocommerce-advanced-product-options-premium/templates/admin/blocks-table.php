<?php

/**
 *	Blocks Table Template
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

global $wpdb;

$query = "SELECT * FROM {$wpdb->prefix}yith_wapo_groups WHERE del='0' ORDER BY priority, name ASC";
$blocks_array = $wpdb->get_results( $query );

?>

<div id="plugin-fw-wc" class="yit-admin-panel-content-wrap yith-plugin-ui yith-wapo">
	<div id="yith_wapo_panel_blocks" class="yith-plugin-fw yit-admin-panel-container">
		<div class="yith-plugin-fw-panel-custom-tab-container">

			<div class="list-table-title">
				<h2><?php echo __( 'Blocks list', YITH_WAPO_LOCALIZE_SLUG ); ?></h2>
				<a href="admin.php?page=yith_wapo_panel&tab=blocks&block_id=new" class="yith-add-button"><?php echo __( 'Add block', YITH_WAPO_LOCALIZE_SLUG ); ?></a>
			</div>

			<table class="form-table wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr class="list-table">
						<th class="name"><?php echo __( 'Name', YITH_WAPO_LOCALIZE_SLUG ); ?></th>
						<th class="priority"><?php echo __( 'Priority', YITH_WAPO_LOCALIZE_SLUG ); ?></th>
						<th class="products"><?php echo __( 'Show on products:', YITH_WAPO_LOCALIZE_SLUG ); ?></th>
						<th class="categories"><?php echo __( 'Show on categories:', YITH_WAPO_LOCALIZE_SLUG ); ?></th>
						<th class="active"><?php echo __( 'Active', YITH_WAPO_LOCALIZE_SLUG ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $blocks_array as $key => $block ) : ?>
						<tr>
							<td class="name"><?php echo empty( $block->name ) ? '-' : $block->name; ?></td>
							<td class="priority"><?php echo $block->priority; ?></td>
							<td class="products"><?php echo empty( $block->products_id ) ? '-' : $block->products_id; ?></td>
							<td class="categories"><?php echo empty( $block->categories_id ) ? '-' : $block->categories_id; ?></td>
							<td class="active">
								<div class="actions" style="display: none;">
									<a href="admin.php?page=yith_wapo_panel&tab=blocks&block_id=<?php echo $block->products_id; ?>">E</a>
									<a href="#">D</a>
									<a href="#">R</a>
									<a href="#">M</a>
								</div>
								<?php
									yith_plugin_fw_get_field( array(
										'id' => 'yith-wapo-active-block-' . $block->id,
										'type' => 'onoff',
										'value' => 'yes',
									), true );
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		
		</div>
	</div>
</div>
