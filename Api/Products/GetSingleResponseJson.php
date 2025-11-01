<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\ResponseJson;

/**
 * Single product response.
 */
class GetSingleResponseJson extends ResponseJson {

	/**
	 * Get product.
	 *
	 * @return array
	 */
	public function get_product() {
		return $this->getResponseBody();
	}
}
