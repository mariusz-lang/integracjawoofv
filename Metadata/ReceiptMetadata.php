<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class BillMetadata
 *
 * The class is from iFirma
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class ReceiptMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Receipt', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Receipt', 'woocommerce-fakturownia' ) );
	}
}
