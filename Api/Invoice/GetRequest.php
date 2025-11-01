<?php

namespace WPDesk\WooCommerceFakturownia\Api\Invoice;

use WPDesk\WooCommerceFakturownia\Api\Request;

/**
 * Class GetRequest
 *
 * @package WPDesk\WooCommerceFakturownia\Api\DomesticInvoice
 */
class GetRequest extends Request {

	/**
	 * Endpoint.
	 *
	 * @var string
	 */
	protected $endPoint = '/invoices/{id}.{type}?api_token={token}';

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
	 * @param int    $invoice_id Invoice ID.
	 * @param string $type Type.
	 */
	public function __construct(
		$api_url,
		$token,
		$invoice_id,
		$type = 'json'
	) {
		$this->endPoint = str_replace( '{id}', strval( $invoice_id ), $this->endPoint );
		$this->endPoint = str_replace( '{type}', $type, $this->endPoint );
		$this->endPoint = str_replace( '{token}', $token, $this->endPoint );
		parent::__construct(
			$api_url,
			$token,
			null
		);
	}
}
