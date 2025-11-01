<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class InvoiceForeignMetadata
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class InvoiceForeignMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Foreign invoice', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Foreign invoice', 'woocommerce-fakturownia' ) );
	}
}
