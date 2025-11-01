<?php

namespace WPDesk\WooCommerceFakturownia\Api\Warehouses;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Warehouses get request.
 */
class GetRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/warehouses.{type}?api_token={token}';

	/**
	 * Method.
	 *
	 * @var string
	 */
	protected $method = 'GET';

	/**
	 * GetRequest constructor.
	 *
	 * @param string $api_url API URL.
	 * @param string $token Token.
	 * @param string $type Type.
	 */
	public function __construct(
		$api_url,
		$token,
		$type = 'json'
	) {
		$this->endPoint = str_replace( '{type}', $type, $this->endPoint );
		$this->endPoint = str_replace( '{token}', $token, $this->endPoint );
		parent::__construct(
			$api_url,
			$token,
			null
		);
	}
}
