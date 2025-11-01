<?php

namespace WPDesk\WooCommerceFakturownia\Api;

use FakturowniaVendor\WPDesk\ApiClient\Serializer\JsonSerializer;
/**
 * Json serializer.
 */
class Serializer extends JsonSerializer {

	const INI_PRECISION           = 'precision';
	const INI_SERIALIZE_PRECISION = 'serialize_precision';

	const PRECISION           = 14;
	const SERIALIZE_PRECISION = - 1;

	/**
	 * Convert data to string
	 *
	 * @param array $data Data.
	 * @return string
	 */
	public function serialize( $data ) {
		$precision           = ini_get( self::INI_PRECISION );
		$serialize_precision = ini_get( self::INI_SERIALIZE_PRECISION );

		ini_set( self::INI_PRECISION, self::PRECISION ); // phpcs:ignore WordPress.PHP.IniSet.Risky
		ini_set( self::INI_SERIALIZE_PRECISION, self::SERIALIZE_PRECISION ); // phpcs:ignore WordPress.PHP.IniSet.Risky
		$json = wp_json_encode( $data, JSON_PRETTY_PRINT );
		ini_set( self::INI_PRECISION, $precision ); // phpcs:ignore WordPress.PHP.IniSet.Risky
		ini_set( self::INI_SERIALIZE_PRECISION, $serialize_precision ); // phpcs:ignore WordPress.PHP.IniSet.Risky

		return $json;
	}

	/**
	 * Convert string to php data
	 *
	 * @param string $data Data.
	 * @return mixed
	 * @throws \FakturowniaVendor\WPDesk\ApiClient\Serializer\Exception\CannotUnserializeException Unserialize exception.
	 */
	public function unserialize( $data ) {

		$unserialized_result = json_decode( $data, true );
		if ( null === $unserialized_result ) {
			if ( 0 === strpos( $data, '%PDF' ) ) {
				$unserialized_result = [
					'pdf' => $data,
				];
			} else {
				throw new \FakturowniaVendor\WPDesk\ApiClient\Serializer\Exception\CannotUnserializeException( "Cannot unserialize data: {$data}" );
			}
		}

		return $unserialized_result;
	}
}
