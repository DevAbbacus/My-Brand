<?php

/**
 *	Blocks Template
 *
 *	@package YITH WooCommerce Product Add-ons
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

$block_id = isset( $_REQUEST['block_id'] ) ? $_REQUEST['block_id'] : false;

if ( $block_id ) {

	include YITH_WAPO_DIR . '/templates/admin/block-editor.php';

} else {

	include YITH_WAPO_DIR . '/templates/admin/blocks-table.php';
	
}
