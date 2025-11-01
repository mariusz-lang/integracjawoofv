<?php

namespace WPDesk\WooCommerceFakturownia\Api\Invoice;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Class PostRequest
 *
 * @package WPDesk\WooCommerceFakturownia\Api\DomesticInvoice
 */
class PostRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/invoices.json';

	/**
	 * Method.
	 *
	 * @var string
	 */
	protected $method = 'POST';
}
