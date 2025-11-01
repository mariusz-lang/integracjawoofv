<?php

namespace WPDesk\WooCommerceFakturownia\Metabox;

/**
 * Class ProformaPaymentDateField
 *
 * @package WPDesk\WooCommerceFakturownia\Metabox
 */
class ProformaPaymentDateField extends \FakturowniaVendor\WPDesk\Invoices\Metabox\MetaBoxFieldDate {

	/**
	 * Payment date for COD in days.
	 *
	 * @var int
	 */
	private $payment_date_for_cod = 3;

	/**
	 * MetaBoxField constructor.
	 *
	 * @param string $id    ID.
	 * @param string $name  Name.
	 * @param string $label Label.
	 * @param string $days  Payment date for COD in days.
	 */
	public function __construct( $id, $name, $label, $days ) {
		parent::__construct( $id, $name, $label );
		$this->payment_date_for_cod = intval( $days );
	}

	/**
	 * Prepare value.
	 *
	 * @param \WC_Order                                                   $order            Order.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Meta data.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Data\OrderDefaults       $order_defaults   Order defaults.
	 *
	 * @return string
	 */
	protected function prepareValue(
		\WC_Order $order,
		\FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content,
		\FakturowniaVendor\WPDesk\Invoices\Data\OrderDefaults $order_defaults
	) {
		return $order_defaults->getDefault( 'payment_date' );
	}
}
