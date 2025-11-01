<?php

namespace WPDesk\WooCommerceFakturownia\Helpers;

class WooCommerceSettings {

	private const WOOCOMMERCE_TAXES_ENABLED_OPTION           = 'woocommerce_calc_taxes';
	private const WOOCOMMERCE_TAXES_INCLUDED_IN_PRICE_OPTION = 'woocommerce_prices_include_tax';
	public function taxes_enabled(): bool {
		return filter_var( get_option( self::WOOCOMMERCE_TAXES_ENABLED_OPTION ), FILTER_VALIDATE_BOOLEAN );
	}

	public function prices_include_taxes(): bool {
		return filter_var( get_option( self::WOOCOMMERCE_TAXES_INCLUDED_IN_PRICE_OPTION ), FILTER_VALIDATE_BOOLEAN );
	}
}
