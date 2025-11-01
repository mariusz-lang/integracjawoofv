<?php

namespace WPDesk\WooCommerceFakturownia\Api;

/**
 * Class ResponseJson
 *
 * Get response from API
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class ResponseJson extends \FakturowniaVendor\WPDesk\ApiClient\Response\RawResponse {

	const FIELD_RESPONSE = 'response';
	const FIELD_CODE     = 'Kod';
	const FIELD_INFO     = 'Informacja';

	/**
	 * API response.
	 *
	 * @var \FakturowniaVendor\WPDesk\ApiClient\Response\RawResponse
	 */
	private $api_response;

	/**
	 * ResponseJson constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\ApiClient\Response\Response $api_response API response.
	 * @throws \FakturowniaVendor\WPDesk\Invoices\API\ApiResponseException Invalid response.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\ApiClient\Response\Response $api_response ) {
		$this->api_response = $api_response;
	}

	/**
	 * Get response code.
	 *
	 * @return int
	 */
	public function getResponseCode() {
		return $this->api_response->getResponseCode();
	}


	/**
	 * Get response headers.
	 *
	 * @return array
	 */
	public function getResponseHeaders() {
		return $this->api_response->getResponseHeaders();
	}

	/**
	 * Get response body.
	 *
	 * @return array
	 */
	public function getResponseBody() {
		return $this->api_response->getResponseBody();
	}

	/**
	 * Is any error occurred
	 *
	 * @return bool
	 */
	public function isError() {
		$error = parent::isError();

		$response_body = $this->getResponseBody();
		if ( isset( $response_body[ self::FIELD_RESPONSE ] ) ) {
			$response = $response_body[ self::FIELD_RESPONSE ];
			if ( isset( $response[ self::FIELD_CODE ] ) ) {
				$code = intval( $response[ self::FIELD_CODE ] );
				if ( $code ) {
					$error = true;
				}
			}
		}
		return $error;
	}

	/**
	 * Get error info.
	 *
	 * @param string $default_info Default info.
	 * @return string
	 */
	public function get_error_info( $default_info = '' ) {
		$info = $default_info;

		$response_body = $this->getResponseBody();
		if ( isset( $response_body[ self::FIELD_RESPONSE ] ) ) {
			$response = $response_body[ self::FIELD_RESPONSE ];
			if ( isset( $response[ self::FIELD_INFO ] ) ) {
				$info = $response[ self::FIELD_INFO ];
			}
		}
		return $info;
	}
}
