<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

/**
 * Class DocumentMetadata
 *
 * The class is from iFirma
 *
 * @package WPDesk\WooCommerceFakturownia\Metadata
 */
class DocumentMetadata extends \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata {

	/**
	 * DocumentMetadata constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		parent::__construct( $metadata_content );
		$metadata = $metadata_content->get();
		$this->setErrorCode( isset( $metadata['kod'] ) ? $metadata['kod'] : 0 );
		$this->setErrorMessage( isset( $metadata['error'] ) ? $metadata['error'] : '' );
		$this->setId( isset( $metadata['id'] ) ? $metadata['id'] : '' );
		$this->setNumber( isset( $metadata['numer'] ) ? $metadata['numer'] : '' );

		if ( isset( $metadata['faktura_total'] ) && floatval( floatval( $metadata_content->getOrder()->get_total() ) !== floatval( $metadata['faktura_total'] ) ) ) {
			$this->display_warning_totals_different();
		}
	}

	protected function display_warning_totals_different() {
		$this->setWarningMessage( __( 'Uwaga! Kwota na fakturze jest różna od kwoty zamówienia. Różnica może wynikać z różnych sposobów wyliczania podatków dla zamówienia i faktury (zaokrąglenia).', 'woocommerce-fakturownia' ) );
	}
}
