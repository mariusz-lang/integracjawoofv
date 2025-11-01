<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class InvoiceCorrectionMetadata
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class InvoiceCorrectionMetadata extends DocumentMetadata {

	/**
	 * InvoiceMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$this->setTypeName( __( 'Corrective invoice', 'woocommerce-fakturownia' ) );
		$this->setTypeNameLabel( __( 'Corrective invoice', 'woocommerce-fakturownia' ) );
	}
	protected function display_warning_totals_different() {
		//phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		//Do nothing...
	}
}
