<?php
/**
 * @var bool   $can_download_without_credits Can the user download the product without spending credits?
 * @var int    $credits_after                User credits after downloading the product.
 * @var int    $credits_before               User credits before downloading the product.
 * @var int    $credits                      Credits needed to download the product.
 * @var string $links_html                   The links.
 */
defined( 'ABSPATH' ) || exit;

$extra_class = $can_download_without_credits ? 'yith-wcmbs-product-download-box--can-download' : 'yith-wcmbs-product-download-box--needs-credits';

?>
<div class='yith-wcmbs-product-download-box <?php echo $extra_class ?>'>
	<div class='yith-wcmbs-product-download-box__heading'>
		<?php
		if ( $can_download_without_credits ) {
			echo esc_html__( 'Download this product FOR FREE!', 'yith-woocommerce-membership' );
		} else {
			echo esc_html( sprintf( _n( 'Download this product for 1 credit', 'Download this product for %s credits', $credits, 'yith-woocommerce-membership' ), $credits ) );
		}
		?>
	</div>

	<?php if ( ! $can_download_without_credits ) : ?>

		<div class='yith-wcmbs-product-download-box__credits-before'>
			<span class='yith-wcmbs-product-download-box__label'><?php echo esc_html__( 'Your credits', 'yith-woocommerce-membership' ); ?></span>
			<span class='yith-wcmbs-product-download-box__value'><?php echo $credits_before ?></span>
		</div>

		<?php if ( $credits_after >= 0 ) : ?>
			<div class='yith-wcmbs-product-download-box__credits-after'>
				<span class='yith-wcmbs-product-download-box__label'><?php echo esc_html__( 'Credits after this download', 'yith-woocommerce-membership' ); ?></span>
				<span class='yith-wcmbs-product-download-box__value'><?php echo $credits_after ?></span>
			</div>
		<?php else: ?>
			<div class='yith-wcmbs-product-download-box__non-sufficient-credits'><?php echo esc_html__( "You don't have enough credits to download this product!", 'yith-woocommerce-membership' ) ?></div>
		<?php endif; ?>

	<?php endif; ?>

	<?php if ( $credits_after >= 0 && $links_html ) : ?>
		<div class='yith-wcmbs-product-download-box__downloads'><?php echo $links_html ?></div>
	<?php endif; ?>
</div>