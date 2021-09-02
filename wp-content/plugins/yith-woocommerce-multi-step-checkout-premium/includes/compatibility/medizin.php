<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/* === Astra Theme Support === */
if( class_exists( 'Medizin_Woo' ) ){
	remove_action( 'woocommerce_checkout_after_order_review', array(
		Medizin_Woo::instance(),
		'template_checkout_payment_title',
	), 10 );
	remove_action( 'woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20 );
}
