<?php

namespace WPDesk\WooCommerceFakturownia\Data;

/**
 * Class InvoiceProFormaData
 *
 * Prepare data for invoice proforma document
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class InvoiceProFormaData extends InvoiceData {

	/**
	 * Prepare data as array
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                          = parent::prepareDataAsArray();
		$data[ parent::DOCUMENT_KIND ] = 'proforma';
		$data[ self::INVOICE_STATUS ]  = self::INVOICE_STATUS_ISSUED;
		$data[ self::ADDITIONAL_INFO ] = 1;
		unset( $data[ self::INVOICE_SELL_DATE ] );
		unset( $data[ self::INVOICE_STATUS_PAID ] );

		return apply_filters( 'fakturownia/invoice_proforma/data', $data, $this );
	}
}
