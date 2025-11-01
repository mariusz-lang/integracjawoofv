<?php

namespace WPDesk\WooCommerceFakturownia\Api;

/**
 * Class Request
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class Request extends \FakturowniaVendor\WPDesk\ApiClient\Request\BasicRequest {

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Key.
	 *
	 * @var string
	 */
	protected $api_token;

	/**
	 * Content type.
	 *
	 * @var string
	 */
	protected $content_type = 'json';

	/**
	 * Encoded data.
	 *
	 * @var string
	 */
	private $encoded_data = '';

	/**
	 * Request constructor.
	 *
	 * @param string     $api_url API URL.
	 * @param string     $token Token.
	 * @param array|null $data Data.
	 */
	public function __construct(
		$api_url,
		$token,
		$data = null
	) {
		$this->api_url   = $api_url;
		$this->api_token = $token;
		$this->data      = $data;

		if ( null !== $data ) {
			$serializer = new Serializer();

			$this->encoded_data = $serializer->serialize( $data );
		}
	}

	/**
	 * Get headers.
	 *
	 * @return array
	 */
	public function getHeaders() {
		if ( 'pdf' === $this->content_type ) {
			$headers['Accept']       = 'application/pdf';
			$headers['Content-type'] = 'application/pdf; charset=UTF-8';
		} else {
			$headers['Accept']       = 'application/json';
			$headers['Content-type'] = 'application/json; charset=UTF-8';
		}
		$headers['Accept-Encoding'] = '';
		$headers['User-Agent']      = 'WP Desk WooCommerce Fakturownia';
		return $headers;
	}
}
