<?php

namespace WPDesk\WooCommerceFakturownia\Forms\Integration;

use Exception;
use FakturowniaVendor\WPDesk\Forms\AbstractForm;

/**
 * Class AuthorizationForm
 *
 * @package WPDesk\WooCommerceFakturownia\Forms\Integration
 */
class AuthorizationForm extends AbstractForm {

	/**
	 * Unique form_id.
	 *
	 * @var string
	 */
	protected $form_id = 'auth';

	const OPTION_TOKEN         = 'token';
	const OPTION_ACCOUNT_NAME  = 'account_name';
	const OPTION_DEPARTMENT_ID = 'department_id';
	const CONNECTION_STATUS    = 'connection_status';

	/**
	 * @var string|null
	 */
	protected $api_token;

	/**
	 * @var string|null
	 */
	private $domain;


	public function __construct( $token, $domain ) {
		$this->api_token = $token;
		$this->domain    = $domain;
	}

	/**
	 * Create form data and return an associative array.
	 *
	 * @return array
	 */
	protected function create_form_data() {
		return [
			'autoryzacja'              => [
				'title' => __( 'Autoryzacja', 'woocommerce-fakturownia' ),
				'type'  => 'tab_open',
			],
			self::OPTION_TOKEN         => [
				'title'       => __( 'API Token', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'description' => __( 'Token w serwisie fakturownia.pl znajdziesz w menu Ustawienia &rarr; Ustawienia konta &rarr; Integracja.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => '',
			],
			self::OPTION_ACCOUNT_NAME  => [
				'title'       => __( 'Domena', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'description' => __( 'Domena w serwisie fakturownia.pl to pierwszy człon adresu logowania dla konta, np. dla https://wpdesk.fakturownia.pl/ będzie to wpdesk.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => '',
			],
			self::OPTION_DEPARTMENT_ID => [
				'title'       => __( 'ID Firmy', 'woocommerce-fakturownia' ),
				'type'        => 'text',
				'description' => __( 'Pole opcjonalne. Uzupełnij ID firmy lub działu, z którym chcesz zintegrować wtyczkę. Znajdziesz je w adresie URL na stronie edycji firmy/działu w Ustawienia &rarr; Dane firmy.', 'woocommerce-fakturownia' ),
				'desc_tip'    => false,
				'default'     => '',
			],
			'autoryzacja_end'          => [
				'type' => 'tab_close',
			],
		];
	}

	private function is_connected(): bool {
		try {
			$url      = 'https://' . $this->domain . '.fakturownia.pl/invoices.json?period=all&page=1&per_page=1&api_token=' . $this->api_token;
			$response = wp_remote_get(
				$url,
				[
					'headers' => [
						'Accept' => 'application/json',
					],
				]
			);

			if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {
				return true;
			}
		} catch ( Exception $e ) {
			return false;
		}

		return false;
	}
}
