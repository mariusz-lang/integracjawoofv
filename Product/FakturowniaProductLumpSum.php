<?php

namespace WPDesk\WooCommerceFakturownia\Product;

use FakturowniaVendor\WPDesk\InvoicesLumpSum\Settings\LumpSumProductFields;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

class FakturowniaProductLumpSum implements Hookable {

	private LumpSumProductFields $lump_sum_fields;

	public function __construct( LumpSumProductFields $lump_sum_fields ) {
		$this->lump_sum_fields = $lump_sum_fields;
	}

	public function hooks() {
		$this->lump_sum_fields->hooks();
	}
}
