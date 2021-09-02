<?php

/**
 *	Block Template
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.


?>

<div id="plugin-fw-wc" class="yit-admin-panel-content-wrap yith-plugin-ui yith-wapo">
	<div id="yith-wapo-panel-block" class="yith-plugin-fw yit-admin-panel-container">
		<div class="yith-plugin-fw-panel-custom-tab-container">

			<a href="admin.php?page=yith_wapo_panel&tab=blocks">< back to blocks list</a>
			<div class="list-table-title">
				<h2><?php echo is_numeric( $block_id ) ? __( 'Edit block', YITH_WAPO_LOCALIZE_SLUG ) : __( 'Add new block', YITH_WAPO_LOCALIZE_SLUG ); ?></h2>
			</div>

			<form>
				<div class="field-wrap">
					<label for="block-name">Block name</label>
					<div class="field">
						<input type="text" name="block_name" id="block-name" value="">
						<span class="description"><?php echo __( 'Enter a name to identify this block of options', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
					</div>
				</div>
				<div class="field-wrap">
					<label for="block-priority">Block priority</label>
					<div class="field">
						<input type="number" name="block_name" id="block-priority" value="0" min="0" max="99">
						<span class="description"><?php echo __( 'Set the priority for this block', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
					</div>
				</div>

				<div id="addons-tabs">
					<a href="#" id="options" class="selected">OPTIONS</a>
					<a href="#" id="rules">RULES</a>
				</div>

				<script type="text/javascript">
					jQuery('#addons-tabs a').click(function(){
						jQuery('#addons-tabs a').removeClass('selected');
						jQuery(this).addClass('selected');
						var tab = jQuery(this).attr('id');
						jQuery( '#addons-tab > div' ).hide();
						jQuery( '#addons-tab #block-' + tab ).show();
					});
				</script>

				<div id="addons-tab">

					<div id="block-options">
						<div id="add-option">
							<p><?php echo __( 'Start to add your options in this block!', YITH_WAPO_LOCALIZE_SLUG ); ?></p>
							<button class="yith-add-button"><?php echo __( 'Add options', YITH_WAPO_LOCALIZE_SLUG ); ?></button>
						</div>
					</div>

					<div id="block-rules" style="display: none;">

						<div class="field-wrap">
							<label for="block-show">Show this block of options:</label>
							<div class="field">
								<?php
									yith_plugin_fw_get_field( array(
										'id' => 'yith-wapo-block-rules-show-' . $block_id,
										'type' => 'radio',
										'value' => 'categories',
										'options'	=> array(
											'all'			=> __( 'All products', YITH_WAPO_LOCALIZE_SLUG ),
											'products'		=> __( 'Specific products', YITH_WAPO_LOCALIZE_SLUG ),
											'categories'	=> __( 'Products of specific categories', YITH_WAPO_LOCALIZE_SLUG ),
										),
									), true );
								?>
								<span class="description"><?php echo __( 'Choose if show this options in all products or only specific products / products categories', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>

						<div class="field-wrap">
							<label for="block-categories">Choose category</label>
							<div class="field">
								<?php
									yith_plugin_fw_get_field( array(
										'id' => 'yith-wapo-block-rules-categories-' . $block_id,
										'type' => 'textarea',
										'value' => '',
									), true );
								?>
								<span class="description"><?php echo __( 'Choose in which product categories to show this options', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>

						<div class="field-wrap">
							<label for="block-exclude-products">Exclude products</label>
							<div class="field">
								<?php
									yith_plugin_fw_get_field( array(
										'id' => 'yith-wapo-block-rules-exclude-products-' . $block_id,
										'type' => 'onoff',
										'value' => 'no',
									), true );
								?>
								<span class="description"><?php echo __( 'Enable if you want to hide this options in some products', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>

						<div class="field-wrap">
							<label for="block-show-to">Show options to:</label>
							<div class="field">
								<?php
									yith_plugin_fw_get_field( array(
										'id' => 'yith-wapo-block-rules-show-to-' . $block_id,
										'type' => 'radio',
										'value' => 'membership',
										'options'	=> array(
											'all'			=> __( 'To all users', YITH_WAPO_LOCALIZE_SLUG ),
											'roles'			=> __( 'Only to specified user roles', YITH_WAPO_LOCALIZE_SLUG ),
											'membership'	=> __( 'Only to users with membership plan', YITH_WAPO_LOCALIZE_SLUG ),
										),
									), true );
								?>
								<span class="description"><?php echo __( 'Choose if show the options to all users with a specified role or membership plan', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>

					</div>

				</div>

				<div id="save-button">
					<button class="yith-save-button"><?php echo __( 'Save', YITH_WAPO_LOCALIZE_SLUG ); ?></button>
				</div>

			</form>

		</div>
	</div>

	<div id="yith-wapo-options-overlay">
		<div id="options-editor">

			<div id="types">
				<!--
				<h3>Add option</h3>
				<div id="types">
					<a class="type" href="#"><span class="icon"></span><br />Heading</a>
					<a class="type" href="#"><span class="icon"></span><br />Text</a>
					<a class="type" href="#"><span class="icon"></span><br />Seperator</a>
					<a class="type" href="#"><span class="icon"></span><br />Radio</a>
					<a class="type" href="#"><span class="icon"></span><br />Number</a>
					<a class="type" href="#"><span class="icon"></span><br />Select</a>
					<a class="type" href="#"><span class="icon"></span><br />Input text</a>
					<a class="type" href="#"><span class="icon"></span><br />Textarea</a>
					<a class="type" href="#"><span class="icon"></span><br />Color swatch</a>
					<a class="type" href="#"><span class="icon"></span><br />Label or image</a>
					<a class="type" href="#"><span class="icon"></span><br />Product</a>
					<a class="type" href="#"><span class="icon"></span><br />Checkbox</a>
					<a class="type" href="#"><span class="icon"></span><br />Date</a>
					<a class="type" href="#"><span class="icon"></span><br />File upload</a>
					<div class="clear"></div>
				</div>
				-->
			</div>

			<form id="options">

				<h3>Color swatch</h3>

				<div id="options-tabs">
					<a href="#" id="options-list" class="selected">Populate options</a>
					<a href="#" id="display-options">Display options</a>
					<a href="#" id="conditional-logic">Conditional logic</a>
					<a href="#" id="advanced-options">Advanced options</a>
				</div>

				<script type="text/javascript">
					jQuery('#options-tabs a').click(function(){
						jQuery('#options-tabs a').removeClass('selected');
						jQuery(this).addClass('selected');
						var tab = jQuery(this).attr('id');
						jQuery( '#options-container > div' ).hide();
						jQuery( '#options-container #tab-' + tab ).show();
						
					});
				</script>

				<div id="options-container">

					<!-- POPULATE OPTIONS -->

					<div id="tab-options-list">
						<div class="option">
							COLOR SWATCH
						</div>
					</div>

					<!-- DISPLAY OPTIONS -->

					<div id="tab-display-options" style="display: none;">

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-title"><?php echo __( 'Option title', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<input type="text" name="block_name" id="option-title" value="">
								<span class="description"><?php echo __( 'Enter a title to show before the options', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-title-image"><?php echo __( 'Show image near title', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-title-image',
									'type' => 'onoff',
									'value' => 'no',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to show an additional image or icon near the title', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-show-as-toggle"><?php echo __( 'Show as toggle', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-show-as-toggle',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to show options in a toggle secrion', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-show-toggle-opened"><?php echo __( 'Show toggle opened by default', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-show-toggle-opened',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to show the toggle opened by default', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-replace-product-image"><?php echo __( 'Replace product image with option image', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-replace-product-image',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to replace the product image when the user select an option', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-hide-options-label"><?php echo __( 'Hide options labels', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-hide-options-label',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to hide the options labels', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-hide-options-prices"><?php echo __( 'Hide options prices', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-hide-options-prices',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to hide the options prices', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-show-in-a-grid"><?php echo __( 'Show options in a grid', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-show-in-a-grid',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to show the options in a grid and not one below the other', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="options-for-row"><?php echo __( 'Options for row', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-options-for-row',
									'type' => 'number',
									'value' => '3',
								), true ); ?>
								<span class="description">
									<?php echo __( 'Enter how many options to display for each row', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="option-show-quantity-selector"><?php echo __( 'Show quantity selector', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-option-show-quantity-selector',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description"><?php echo __( 'Enable if you want to show a quantity selector for this option', YITH_WAPO_LOCALIZE_SLUG ); ?></span>
							</div>
						</div>
						<!-- End option field -->

					</div>

					<!-- CONDITIONAL LOGIC -->

					<div id="tab-conditional-logic" style="display: none;">
						
						<!-- Option field -->
						<div class="field-wrap">
							<label for="enable-conditional-logic"><?php echo __( 'Set conditions to show or hide this set of options', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-enable-conditional-logic',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description">
									<?php echo __( 'Enable if you want to set rules to hide or show the options', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="conditional-logic-rules"><?php echo __( 'Display rules', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field rule">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-conditional-logic-rule-1',
									'type' => 'select',
									'value' => 'show',
									'options'	=> array(
										'show'	=> __( 'Show', YITH_WAPO_LOCALIZE_SLUG ),
									),
								), true ); ?>
								<span>this set of options if</span>
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-conditional-logic-rule-2',
									'type' => 'select',
									'value' => 'show',
									'options'	=> array(
										'show'	=> __( 'Any of this rules', YITH_WAPO_LOCALIZE_SLUG ),
									),
								), true ); ?>
								<span>match:</span>
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-conditional-logic-rule-2',
									'type' => 'select',
									'value' => 'show',
									'options'	=> array(
										'show'	=> __( 'Customize color', YITH_WAPO_LOCALIZE_SLUG ),
									),
								), true ); ?>
								<span>is</span>
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-conditional-logic-rule-2',
									'type' => 'select',
									'value' => 'show',
									'options'	=> array(
										'show'	=> __( 'Yes', YITH_WAPO_LOCALIZE_SLUG ),
									),
								), true ); ?>
							</div>
						</div>
						<!-- End option field -->

					</div>

					<!-- ADVANCED OPTIONS -->

					<div id="tab-advanced-options" style="display: none;">

						<!-- Option field -->
						<div class="field-wrap">
							<label for="first-options-selected"><?php echo __( 'Set the first options selected as free', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-first-options-selected',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description">
									<?php echo __( 'Enable if you want to set a specific number of options as free', YITH_WAPO_LOCALIZE_SLUG ); ?><br />
									<?php echo __( 'Example: the first three "pizza ingredients" are free, included in product price.', YITH_WAPO_LOCALIZE_SLUG ); ?>
									<?php echo __( 'The user will pay from the fourth ingredient.', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="first-free-options"><?php echo __( 'How many options the user can select for free', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-first-free-options',
									'type' => 'number',
									'value' => '3',
								), true ); ?>
								<span class="description">
									<?php echo __( 'Set how many options the user can select for free', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="options-selection-type"><?php echo __( 'Selection type', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-options-selection-type',
									'type' => 'radio',
									'value' => 'single',
									'options'	=> array(
										'single'	=> __( 'Single - User can select only ONE of the options availables', YITH_WAPO_LOCALIZE_SLUG ),
										'multiple'	=> __( 'Multiple - User can select MULTIPLE options', YITH_WAPO_LOCALIZE_SLUG ),
									),
								), true ); ?>
								<span class="description">
									<?php echo __( 'Choose if show this options in all products or only specific products / products categories.', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="enable-min-max-selection-rules"><?php echo __( 'Enable min/max selection rules', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field">
								<?php yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-enable-min-max-selection-rules',
									'type' => 'onoff',
									'value' => 'yes',
								), true ); ?>
								<span class="description">
									<?php echo __( 'Enable if the user has to select a minumum, maximum or exact number of options to proceed with the purchase.', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

						<!-- Option field -->
						<div class="field-wrap">
							<label for="min-max-rules"><?php echo __( 'To proceed to buy, the user has to select:', YITH_WAPO_LOCALIZE_SLUG ); ?></label>
							<div class="field rule">
								<?php
								yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-min-max-rule-1',
									'type' => 'select',
									'value' => 'single',
									'options'	=> array(
										'min'	=> __( 'A minimum of', YITH_WAPO_LOCALIZE_SLUG ),
										'max'	=> __( 'The maximum of', YITH_WAPO_LOCALIZE_SLUG ),
										'exact'	=> __( 'Exactly', YITH_WAPO_LOCALIZE_SLUG ),
									),
								), true );
								yith_plugin_fw_get_field( array(
									'id' => 'yith-wapo-first-free-options',
									'type' => 'number',
									'value' => '3',
								), true );
								?>
								<span class="description">
									<?php echo __( 'options', YITH_WAPO_LOCALIZE_SLUG ); ?>
								</span>
							</div>
						</div>
						<!-- End option field -->

					</div>

				</div>

			</form>
		</div>
	</div>

</div>
