<?php

namespace WPDesk\WooCommerceFakturownia\Data;

/**
 * Class InvoiceDataWithoutVat
 *
 * Prepare data for invoice document without VAT
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class InvoiceWithoutVatData extends InvoiceData {

	/**
	 * Prepare data as array.
	 *
	 * @return array
	 */

	const DOCUMENT_KIND = 'kind';

	/**
	 * Prepare data as array
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                        = parent::prepareDataAsArray();
		$data[ self::DOCUMENT_KIND ] = 'vat';

		$data[ self::ADDITIONAL_INFO ] = 0;

		foreach ( $data[ self::DOCUMENT_ITEMS ] as $key => $value ) {
			unset( $data[ self::DOCUMENT_ITEMS ][ $key ][ self::PKWIU ] );
			unset( $data[ self::DOCUMENT_ITEMS ][ $key ][ self::ADDITIONAL_INFO ] );

			$data[ self::DOCUMENT_ITEMS ][ $key ][ self::TAX_RATE ]             = self::TAX_RATE_TYPE_DISABLE;
			$data[ self::DOCUMENT_ITEMS ][ $key ][ self::ITEM_ADDITIONAL_INFO ] = $this->getLegalBasis();

		}
		$data[ self::OPTION_EXEMPT_TAX_KIND ] = $this->getLegalBasis();

		return apply_filters( 'fakturownia/invoice_without_vat/data', $data, $this );
	}
}
