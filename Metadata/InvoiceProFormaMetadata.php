<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class InvoiceProFormaMetadata
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class InvoiceProFormaMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Proforma invoice', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Proforma invoice', 'woocommerce-fakturownia' ) );
	}
}
