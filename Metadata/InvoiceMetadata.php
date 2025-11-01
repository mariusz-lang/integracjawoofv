<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class InvoiceMetadata
 *
 * The class is from iFirma
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class InvoiceMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Invoice', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Invoice', 'woocommerce-fakturownia' ) );
	}
}
