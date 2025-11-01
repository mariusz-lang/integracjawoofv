<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Products query request. Gets products queried by query parameter
 */
class QueryRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/products.{type}?api_token={token}&page={page}&query={query}&warehouse_id={warehouse_id}';

	/**
	 * Method.
	 *
	 * @var string
	 */
	protected $method = 'GET';

	/**
	 * GetRequest constructor.
	 *
	 * @param string $api_url      API URL.
	 * @param string $token        Token.
	 * @param string $warehouse_id Warehouse ID.
	 * @param string $query        Query, ie. product name, product code.
	 * @param int    $page         Page.
	 * @param string $type         Type.
	 */
	public function __construct(
		$api_url,
		$token,
		$warehouse_id,
		$query,
		$page = 1,
		$type = 'json'
	) {
		$this->endPoint = str_replace( '{query}', urlencode( $query ), $this->endPoint ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.urlencode_urlencode
		$this->endPoint = str_replace( '{page}', strval( $page ), $this->endPoint );
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
