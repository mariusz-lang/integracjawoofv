<?php

namespace WPDesk\WooCommerceFakturownia\Forms\Integration;

use FakturowniaVendor\WPDesk\Forms\AbstractForm;

/**
 * Class DebugForm
 *
 * @package WPDesk\WooCommerceFakturownia\Forms\Integration
 */
class DebugForm extends AbstractForm {

	/**
	 * Unique form_id.
	 *
	 * @var string
	 */
	protected $form_id = 'debug';

	const OPTION_DEBUG_MODE           = 'debug_mode';
	const OPTION_DEBUG_MODE_WITH_EXIT = 'debug_mode_with_exit';

	/**
	 * Create form data and return an associative array.
	 *
	 * @return array
	 */
	protected function create_form_data() {
		return [
			'debug' => [
				'title'       => __( 'Tryb debugowania', 'woocommerce-fakturownia' ),
				'type'        => 'tab_open',
				'description' => __( 'Zapis komunikatów do pliku', 'woocommerce-fakturownia' ),
			],
			self::OPTION_DEBUG_MODE => [
				'title'       => __( 'Tryb debugowania', 'woocommerce-fakturownia' ),
				'label'       => __( 'Włącz tryb debugowania', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'description' => sprintf(
				// Translators: woocommerce logs
					__( 'Włączenie spowoduje utworzenie pliku fakturownia.log. Zapis logów można sprawdzić <a href="%s">tutaj &rarr;</a><br/><a href="https://wpdesk.pl/sk/woocommerce-fakturownia-debug" target="_blank">Przeczytaj jak przeprowadzić proces debugowania &rarr;</a>', 'woocommerce-fakturownia' ),
					admin_url( 'admin.php?page=wc-status&tab=logs' )
				),
				'class'       => 'debug-tab',
			],
			self::OPTION_DEBUG_MODE_WITH_EXIT => [
				'title'       => __( 'Zablokuj żądanie API', 'woocommerce-fakturownia' ),
				'label'       => __( 'Włącz', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'description' => __( 'Zablokuj tworzenie dokumentów, gdy włączony jest tryb debugowania. Ustawienie to będzie włączone jedynie dla konta administratora.', 'woocommerce-fakturownia' ),
				'class'       => 'fakturownia-debug-mode debug-tab',
			],
			'debug_end' => [
				'type' => 'tab_close',
			],

		];
	}
}
