<?php

namespace WPDesk\WooCommerceFakturownia\Api;

/**
 * Class ResponsePdf
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class Response extends \FakturowniaVendor\WPDesk\ApiClient\Request\BasicRequest {

	/**
	 * API response.
	 *
	 * @var FakturowniaVendor\WPDesk\Invoices\API\ApiResponse
	 */
	private $api_response;

	/**
	 * ResponsePdf constructor.
	 *
	 * @param \FakturowniaVendor\WPDesk\ApiClient\Response\Response $api_response API response.
	 * @throws \FakturowniaVendor\WPDesk\Invoices\API\ApiResponseException Invalid response.
	 */
	public function __construct( \FakturowniaVendor\WPDesk\ApiClient\Response\Response $api_response ) {
		$this->api_response = $api_response;
	}

	/**
	 * @inheritDoc
	 */
	public function getResponseCode() {
		return $this->api_response->getResponseCode();
	}

	/**
	 * @inheritDoc
	 */
	public function getResponseHeaders() {
		return $this->api_response->getResponseHeaders();
	}

	/**
	 * @inheritDoc
	 */
	public function getResponseBody() {
		return $this->api_response->getResponseBody();
	}
}
