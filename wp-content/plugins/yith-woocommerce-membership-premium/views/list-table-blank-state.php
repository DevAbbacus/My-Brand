<?php
/**
 * @var string $icon    The icon URL.
 * @var string $message The message to be shown.
 * @var string $cta     The Call To Action message.
 * @var string $cta_url The CTA URL.
 */
?>

<div class="yith-wcmbs-list-table-blank-state">
	<?php if ( $icon ): ?>
		<div class="yith-wcmbs-list-table-blank-state__icon">
			<?php include $icon; ?>
		</div>
	<?php endif; ?>
	<div class="yith-wcmbs-list-table-blank-state__message"><?php echo $message; ?></div>
	<?php if ( $cta && $cta_url ): ?>
		<div class="yith-wcmbs-list-table-blank-state__cta-wrapper">
			<a href="<?php echo $cta_url; ?>" class="yith-wcmbs-list-table-blank-state__cta"><?php echo $cta; ?></a>
		</div>
	<?php endif; ?>
</div>