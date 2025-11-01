<?php
/**
 * VAT Validator.
 *
 * @package WPDesk\WooCommerceFakturownia\Field\Validator
 */

namespace WPDesk\WooCommerceFakturownia\Field\Validator;

use WP_Error;

/**
 * Validate VAT number
 *
 * @package WPDesk\WooCommerceFakturownia\Field\Validator
 */
class ValidateVatNumber {

	const INVALID_NIP_ERROR_CODE = 'error';

	/**
	 * List of Countries to validate.
	 *
	 * @var string[]
	 */
	private $validation_countries = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ];

	/**
	 * Return true if supplied tax ID is valid for supplied country.
	 *
	 * @param string $vat_number Vat number.
	 * @param string $country    Country.
	 *
	 * @return bool
	 */
	private function is_valid_number( $vat_number, $country ): bool { //phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded
		$vat_number = trim( strtoupper( $vat_number ) );
		$country    = strtoupper( $country );
		$slug       = substr( $vat_number, 0, 2 );

		if ( strtoupper( $slug ) !== $country ) {
			$vat_number = $country . $vat_number;
		}

		switch ( $country ) {
			case 'AT': // AUSTRIA
				return preg_match( '/^(AT)U(\d{8})$/', $vat_number );
			case 'BE': // BELGIUM
				return preg_match( '/^(BE)(0\d{9}|1\d{9})$/', $vat_number );
			case 'BG': // BULGARIA
				return preg_match( '/(BG)(\d{9,10})$/', $vat_number );
			case 'CHE': // Switzerland
				return preg_match( '/(CHE)(\d{9})(MWST)?$/', $vat_number );
			case 'CY': // CYPRUS
				return preg_match( '/^(CY)([0-5|9]\d{7}[A-Z])$/', $vat_number );
			case 'CZ': // CZECH REPUBLIC
				return preg_match( '/^(CZ)(\d{8,10})(\d{3})?$/', $vat_number );
			case 'DE': // GERMANY
				return preg_match( '/^(DE)([1-9]\d{8})$/', $vat_number );
			case 'DK': // DENMARK
				return preg_match( '/^(DK)(\d{8})$/', $vat_number );
			case 'EE': // ESTONIA
				return preg_match( '/^(EE)(10\d{7})$/', $vat_number );
			case 'EL': // GREECE
				return preg_match( '/^(EL)(\d{9})$/', $vat_number );
			case 'ES': // SPAIN
				return preg_match( '/^(ES)([A-Z]\d{8})$/', $vat_number ) || preg_match( '/^(ES)([A-H|N-S|W]\d{7}[A-J])$/', $vat_number ) || preg_match( '/^(ES)([0-9|Y|Z]\d{7}[A-Z])$/', $vat_number ) || preg_match( '/^(ES)([K|L|M|X]\d{7}[A-Z])$/', $vat_number );
			case 'EU': // EU type
				return preg_match( '/^(EU)(\d{9})$/', $vat_number );
			case 'FI': // FINLAND
				return preg_match( '/^(FI)(\d{8})$/', $vat_number );
			case 'FR': // FRANCE
				return preg_match( '/^(FR)(\d{11})$/', $vat_number ) || preg_match( '/^(FR)([(A-H)|(J-N)|(P-Z)]\d{10})$/', $vat_number ) || preg_match( '/^(FR)(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/', $vat_number ) || preg_match( '/^(FR)([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $vat_number );
			case 'GB': // GREAT BRITAIN
				return preg_match( '/^(GB)?(\d{9})$/', $vat_number ) || preg_match( '/^(GB)?(\d{12})$/', $vat_number ) || preg_match( '/^(GB)?(GD\d{3})$/', $vat_number ) || preg_match( '/^(GB)?(HA\d{3})$/', $vat_number );
			case 'GR': // GREECE
				return preg_match( '/^(GR)(\d{8,9})$/', $vat_number );
			case 'HR': // CROATIA
				return preg_match( '/^(HR)(\d{11})$/', $vat_number );
			case 'HU': // HUNGARY
				return preg_match( '/^(HU)(\d{8})$/', $vat_number );
			case 'IE': // IRELAND
				return preg_match( '/^(IE)(\d{7}[A-W])$/', $vat_number ) || preg_match( '/^(IE)([7-9][A-Z\*\+)]\d{5}[A-W])$/', $vat_number ) || preg_match( '/^(IE)(\d{7}[A-W][AH])$/', $vat_number );
			case 'IT': // ITALY
				return preg_match( '/^(IT)(\d{11})$/', $vat_number );
			case 'LV': // LATVIA
				return preg_match( '/^(LV)(\d{11})$/', $vat_number );
			case 'LT': // LITHUNIA
				return preg_match( '/^(LT)(\d{9}|\d{12})$/', $vat_number );
			case 'LU': // LUXEMBOURG
				return preg_match( '/^(LU)(\d{8})$/', $vat_number );
			case 'MT': // MALTA
				return preg_match( '/^(MT)([1-9]\d{7})$/', $vat_number );
			case 'NL': // NETHERLAND
				return preg_match( '/^(NL)(\d{9})B\d{2}$/', $vat_number );
			case 'NO': // NORWAY
				return preg_match( '/^(NO)(\d{9})$/', $vat_number );
			case 'PL': // POLAND
				return PLNipValidator::is_valid( $vat_number );
			case 'PT': // PORTUGAL
				return preg_match( '/^(PT)(\d{9})$/', $vat_number );
			case 'RO': // ROMANIA
				return preg_match( '/^(RO)([1-9]\d{1,9})$/', $vat_number );
			case 'RS': // SERBIA
				return preg_match( '/^(RS)(\d{9})$/', $vat_number );
			case 'SI': // SLOVENIA
				return preg_match( '/^(SI)([1-9]\d{7})$/', $vat_number );
			case 'SK': // SLOVAK REPUBLIC
				return preg_match( '/^(SK)([1-9]\d[(2-4)|(6-9)]\d{7})$/', $vat_number );
			case 'SE': // SWEDEN
				return preg_match( '/^(SE)(\d{10}01)$/', $vat_number );
			default:
				return false;
		}
	}

	/**
	 * Hook to checkout to ensure valid NIP
	 *
	 * @return void
	 */
	public function validate_sanitize( string $field_name ) {

		add_action(
			'woocommerce_after_checkout_validation',
			function ( array $data, WP_Error $errors ) use ( $field_name ) {
				$vat_number = $data[ 'billing_' . $field_name ] ?? '';
				if (
				$vat_number &&
				in_array( $data['billing_country'], $this->validation_countries, true ) &&
				! $this->is_valid_number( $vat_number, $data['billing_country'] )
				) {
					// translators: VAT number
					$errors->add( self::INVALID_NIP_ERROR_CODE, sprintf( __( 'The entered VAT number (%s) is incorrect. Use only letters and numbers (for example: DE109025001).', 'woocommerce-fakturownia' ), $vat_number ) );
				}
			},
			10,
			2
		);
	}
}
