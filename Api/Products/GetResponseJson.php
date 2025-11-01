<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\ResponseJson;

/**
 * Products response.
 */
class GetResponseJson extends ResponseJson {

	/**
	 * Get products.
	 *
	 * @return array
	 */
	public function get_products() {
		return $this->getResponseBody();
	}
}
