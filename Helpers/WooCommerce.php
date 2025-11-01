<?php

namespace WPDesk\WooCommerceFakturownia\Helpers;

class WooCommerce {

	public static function get_woocommerce_statuses(): array {
		$statuses = wc_get_order_statuses();
		unset( $statuses['wc-pending'], $statuses['wc-checkout-draft'] );

		return $statuses;
	}
}
