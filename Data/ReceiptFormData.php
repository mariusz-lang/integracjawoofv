<?php

namespace WPDesk\WooCommerceFakturownia\Data;

/**
 * Class ReceiptFormData
 *
 * Prepare data for receipt document
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class ReceiptFormData extends DocumentData {

	const DOCUMENT_KIND            = 'kind';
	const EXCHANGE_KIND_NAME       = 'exchange_kind';
	const EXCHANGE_KIND_SOURCE_ECB = 'ecb';
	const EXCHANGE_KIND_SOURCE_NBP = 'nbp';
	const EXCHANGE_CURRENCY        = 'exchange_currency';
	const EXCHANGE_CURRENCY_VALUE  = 'PLN';
	const INVOICE_LANG             = 'lang';

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
		$data                          = parent::prepareDataAsArray();
		$data[ parent::DOCUMENT_KIND ] = 'receipt';
		$data[ self::ADDITIONAL_INFO ] = 1;
		$data[ self::COMMENTS ]        = wp_specialchars_decode( $this->getComments() );

		foreach ( $data[ self::DOCUMENT_ITEMS ] as $key => $value ) {
			if ( 0.0 === $value['tax'] && ! wc_tax_enabled() ) {
				$data[ self::DOCUMENT_ITEMS ][ $key ][ self::TAX_RATE ] = self::TAX_RATE_TYPE_DISABLE;
			}
		}

		return apply_filters( 'fakturownia/receipt/data', $data, $this );
	}

	/**
	 * Get client lang based on country.
	 *
	 * @return string
	 */
	protected function get_client_lang() {
		$client_data  = $this->getClientData();
		$billing_lang = strtolower( $client_data->getCountry() );
		if ( $this->invoice_integration->woocommerce_integration->getOptionReceiptLang() === self::CLIENT_COUNTRY_OPTION ) {
			return $billing_lang;
		} elseif ( in_array( $this->invoice_integration->woocommerce_integration->getOptionReceiptLang(), [ 'pl', 'en' ], true ) ) {
			return $this->invoice_integration->woocommerce_integration->getOptionReceiptLang();
		} else {
			return 'pl';
		}
	}
}
