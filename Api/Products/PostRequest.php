<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Product Post Request
 */
class PostRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/products.json';

	/**
	 * Method.
	 *
	 * @var string
	 */
	protected $method = 'POST';
}
