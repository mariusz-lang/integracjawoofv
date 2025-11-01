<?php
/**
 * @var string $woocommerce_tax_round_at_subtotal
 * @var int    $woocommerce_price_num_decimals
 */
?>
<style type="text/css">
	<?php if ( 'yes' !== $woocommerce_tax_round_at_subtotal ) : ?>
	label[for=woocommerce_tax_round_at_subtotal] {
		border: 2px solid red;
		padding: 5px;
	}

	<?php endif; ?>
	<?php if ( $woocommerce_price_num_decimals > 2 ) : ?>
	input[id=woocommerce_price_num_decimals] {
		border: 2px solid red;
		padding: 1px;
	}
	<?php endif; ?>
</style>
