<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class BillMetadata
 *
 * The class is from iFirma
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class BillMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Bill', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Bill', 'woocommerce-fakturownia' ) );
	}
}
