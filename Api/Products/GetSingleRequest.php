<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Single product get request.
 */
class GetSingleRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/products/{id}.{type}?api_token={token}&warehouse_id={warehouse_id}';

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
	 * @param string $warehouse_id Warehouse ID.
	 * @param string $id ID.
	 * @param string $type Type.
	 */
	public function __construct(
		$api_url,
		$token,
		$warehouse_id,
		$id,
		$type = 'json'
	) {
		$this->endPoint = str_replace( '{id}', strval( $id ), $this->endPoint );
		$this->endPoint = str_replace( '{type}', $type, $this->endPoint );
		$this->endPoint = str_replace( '{token}', $token, $this->endPoint );
		$this->endPoint = str_replace( '{warehouse_id}', $warehouse_id, $this->endPoint );
		parent::__construct(
			$api_url,
			$token,
			null
		);
	}
}
