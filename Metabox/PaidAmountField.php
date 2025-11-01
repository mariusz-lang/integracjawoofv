<?php

namespace WPDesk\WooCommerceFakturownia\Metabox;

use FakturowniaVendor\WPDesk\Invoices\Data\OrderDefaults;
use FakturowniaVendor\WPDesk\Invoices\Metabox\MetaBoxFieldPrice;
use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WC_Order;

/**
 * Class Paid_Amount_Field
 *
 * @package WPDesk\WooCommerceFakturownia\Metabox
 */
class PaidAmountField extends MetaBoxFieldPrice {

	/**
	 * Prepare value.
	 *
	 * @param WC_Order        $order            Order.
	 * @param MetadataContent $metadata_content Meta data.
	 * @param OrderDefaults   $order_defaults   Order defaults.
	 *
	 * @return string
	 */
	protected function prepareValue(
		WC_Order $order,
		MetadataContent $metadata_content,
		OrderDefaults $order_defaults
	): string {
		return $order_defaults->getDefault( 'paid_amount' );
	}
}
