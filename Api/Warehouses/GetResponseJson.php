<?php

namespace WPDesk\WooCommerceFakturownia\Api\Warehouses;

use WPDesk\WooCommerceFakturownia\Api\ResponseJson;

/**
 * Warehouses response.
 */
class GetResponseJson extends ResponseJson {

	/**
	 * Get warehouses.
	 *
	 * @return array
	 */
	public function get_warehouses() {
		return $this->getResponseBody();
	}
}
