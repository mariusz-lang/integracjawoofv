<?php

namespace WPDesk\WooCommerceFakturownia\Data;

/**
 * Class BillData
 *
 * Prepare data for bill document
 */
class BillData extends InvoiceWithoutVatData {

	/**
	 * Prepare data as array
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                          = parent::prepareDataAsArray();
		$data[ parent::DOCUMENT_KIND ] = 'bill';

		return apply_filters( 'fakturownia/bill/data', $data, $this );
	}
}
