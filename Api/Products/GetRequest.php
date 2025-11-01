<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Products get request.
 */
class GetRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/products.{type}?api_token={token}&page={page}';

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
	 * @param int    $page Page.
	 * @param string $type Type.
	 */
	public function __construct(
		$api_url,
		$token,
		$page = 1,
		$type = 'json'
	) {
		$this->endPoint = str_replace( '{page}', strval( $page ), $this->endPoint );
		$this->endPoint = str_replace( '{type}', $type, $this->endPoint );
		$this->endPoint = str_replace( '{token}', $token, $this->endPoint );
		parent::__construct(
			$api_url,
			$token,
			null
		);
	}
}
