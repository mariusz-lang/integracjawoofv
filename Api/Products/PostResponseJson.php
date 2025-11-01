<?php

namespace WPDesk\WooCommerceFakturownia\Api\Products;

use WPDesk\WooCommerceFakturownia\Api\ResponseJson;

/**
 * Products Post Response Json.
 */
class PostResponseJson extends ResponseJson {

	/**
	 * Get product.
	 *
	 * @return array
	 */
	public function get_product() {
		return $this->getResponseBody();
	}
}
