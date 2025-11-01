<?php

namespace WPDesk\WooCommerceFakturownia\Api;

/**
 * Class DocumentPostResponseJson
 *
 * Send values and prepares them for all documents
 *
 * @package WPDesk\WooCommerceFakturownia\Api
 */
class DocumentPostResponseJson extends ResponseJson {

	/**
	 * Get ID.
	 *
	 * @return int|null
	 */
	public function getId() {
		$response_content = $this->getResponseBody();
		if ( isset( $response_content['id'] ) ) {
			return intval( $response_content['id'] );
		}
		return null;
	}
}
