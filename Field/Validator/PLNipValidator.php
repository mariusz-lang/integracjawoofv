<?php

namespace WPDesk\WooCommerceFakturownia\Field\Validator;

/**
 * Validate Polish VAT number if the MOSS is disabled.
 *
 * @package WPDesk\WooCommerceFakturownia\Field\Validator
 */
class PLNipValidator {

	const VALID_NIP_LENGTH = 10;

	/**
	 * Is valid when converted to number
	 *
	 * @param string $number
	 *
	 * @return bool
	 */
	public static function is_valid( $number ) {
		$number = preg_replace( '/[^0-9]+/', '', $number );
		if ( strlen( $number ) !== self::VALID_NIP_LENGTH ) {
			return false;
		}

		$arrSteps = [ 6, 5, 7, 2, 3, 4, 5, 6, 7 ];
		$intSum   = 0;

		for ( $i = 0; $i < 9; $i++ ) {
			$intSum += $arrSteps[ $i ] * $number[ $i ];
		}

		$int          = $intSum % 11;
		$intControlNr = $int === 10 ? 0 : $int;

		return $intControlNr === (int) $number[9];
	}
}
