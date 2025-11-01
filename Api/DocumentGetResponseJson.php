<?php

namespace WPDesk\WooCommerceFakturownia\Api;

/**
 * Class DocumentGetResponseJson
 *
 * Get response data and prepares them for all documents
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class DocumentGetResponseJson extends ResponseJson {

	const DOCUMENT_FULL_NUMBER = 'number';
	const DOCUMENT_PRICE_GROSS = 'price_gross';
	const ROUNDING_PRECISION   = 2;

	/**
	 * Get document full number.
	 *
	 * @return string
	 */
	public function getFullNumber() {
		$response_content = $this->getResponseBody();
		if ( isset( $response_content[ self::DOCUMENT_FULL_NUMBER ] ) ) {
			return $response_content[ self::DOCUMENT_FULL_NUMBER ];
		}
		return null;
	}

	/**
	 * Get document full number.
	 *
	 * @return float
	 */
	public function getDocumentTotal() {
		$document_total   = 0;
		$response_content = $this->getResponseBody();
		if ( isset( $response_content[ self::DOCUMENT_PRICE_GROSS ] ) ) {
			return $response_content[ self::DOCUMENT_PRICE_GROSS ];
		}
		return $document_total;
	}
}
