<?php

namespace WPDesk\WooCommerceFakturownia\Data;

use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;

/**
 * Class InvoiceData
 *
 * Prepare data for invoice document
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class InvoiceForeignData extends DocumentData {

	const DOCUMENT_KIND            = 'kind';
	const EXCHANGE_KIND_NAME       = 'exchange_kind';
	const EXCHANGE_KIND_SOURCE_ECB = 'ecb';
	const EXCHANGE_KIND_SOURCE_NBP = 'nbp';
	const EXCHANGE_CURRENCY        = 'exchange_currency';
	const EXCHANGE_CURRENCY_VALUE  = 'PLN';

	/**
	 * Get exchange source.
	 *
	 * @return string
	 */
	private function get_exchange_source() {
		$eu_countries    = WC()->countries->get_european_union_countries();
		$billing_country = $this->getClientData()->getCountry();
		if ( in_array( $billing_country, $eu_countries, true ) ) {
			return self::EXCHANGE_KIND_SOURCE_ECB;
		}

		return self::EXCHANGE_KIND_SOURCE_NBP;
	}

	/**
	 * Prepare data as array
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                             = parent::prepareDataAsArray();
		$data[ self::DOCUMENT_KIND ]      = 'vat';
		$data[ self::ADDITIONAL_INFO ]    = 1;
		$data[ self::EXCHANGE_KIND_NAME ] = $this->get_exchange_source();
		$data[ self::EXCHANGE_CURRENCY ]  = self::EXCHANGE_CURRENCY_VALUE;

		return apply_filters( 'fakturownia/invoice_foreign/data', $data, $this );
	}
}
