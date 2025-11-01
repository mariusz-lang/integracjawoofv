<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class InvoiceWithoutVatMetadata
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class InvoiceWithoutVatMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Invoice without VAT', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Invoice without VAT', 'woocommerce-fakturownia' ) );
	}
}
