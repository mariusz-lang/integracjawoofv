<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class InvoiceProFormaWithoutVatMetadata
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class InvoiceProFormaWithoutVatMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Proforma invoice without VAT', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Proforma invoice without VAT', 'woocommerce-fakturownia' ) );
	}
}
