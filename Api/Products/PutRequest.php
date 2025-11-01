<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\Request;

class PutRequest extends Request {

	protected $endPoint = '/products/{productId}.json';

	protected $method = 'PUT';

	public function __construct(
		string $api_url,
		string $token,
		string $product_id,
		array $data
	) {
		$this->endPoint = str_replace( '{productId}', $product_id, $this->endPoint );
		parent::__construct(
			$api_url,
			$token,
			$data
		);
	}
}
