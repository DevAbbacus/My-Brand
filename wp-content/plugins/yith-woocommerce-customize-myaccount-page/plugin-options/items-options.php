<?php
/**
 * GENERAL ARRAY OPTIONS
 */
if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'items' => array(
		'yith_wcmap_items' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wcmap_admin_items_list',
		),
	),
);