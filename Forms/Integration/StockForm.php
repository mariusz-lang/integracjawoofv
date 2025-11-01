<?php

namespace WPDesk\WooCommerceFakturownia\Forms\Integration;

use FakturowniaVendor\WPDesk\Forms\AbstractForm;
use WPDesk\WooCommerceFakturownia\Webhoks\ProductUpdateListener;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;

/**
 * Class StockForm
 *
 * @package WPDesk\WooCommerceFakturownia\Forms\Integration
 */
class StockForm extends AbstractForm {


	private const STOCK_OPTIONS_CLASS        = 'fakturownia-synchronization';
	private const WEBHOOK_VISIBILITY_CHANGER = 'fakturownia-webhook-visibility';
	private const WEBHOOK_CONFIGS_CLASS      = 'fakturownia-webhook-configs';

	protected $form_id = 'stock';

	const OPTION_WAREHOUSE_WEBHOOK_TOKEN = 'warehouse_webhook_token';
	const OPTION_WAREHOUSE_ID            = 'warehouse_id';
	const OPTION_WAREHOUSE_WEBHOOK       = 'warehouse_webhook';
	const OPTION_SYNCHRONIZATION         = 'synchronization';
	const OPTION_CREATE_PRODUCTS         = 'create_products';
	const OPTION_SYNC_WOO_PRICES         = 'sync_woocommerce_to_fakturownia_prices';

	/**
	 * @var InvoicesIntegration
	 */
	private $invoices_integration;

	public function set_integration( InvoicesIntegration $invoices_integration ) {
		$this->invoices_integration = $invoices_integration;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function warehouses_options() {
		$warehouses = [];
		if ( ! $this->invoices_integration->has_account_name() ) {
			return $warehouses;
		}

		try {
			$warehouses_api = $this->invoices_integration->get_api()->get_warehouses()->get_warehouses();
		} catch ( \Exception $e ) {
			$warehouses_api = [];
		}
		if ( ! empty( $warehouses_api ) ) {
			foreach ( $warehouses_api as $warehouse ) {
				$name = $warehouse['name'];
				if ( $warehouse['kind'] === 'main' ) {
					$general_warehouse = [ $warehouse['id'] => $warehouse['name'] . ' - Główny' ];
				} else {
					$warehouses[ $warehouse['id'] ] = $name;
				}
			}

			if ( isset( $general_warehouse ) ) {
				$warehouses = $general_warehouse + $warehouses;
			}
		}

		return $warehouses;
	}

	/**
	 * Create form data and return an associative array.
	 *
	 * @return array
	 */
	protected function create_form_data() {
		$warehouses = $this->warehouses_options();

		return [
			'magazyn'                            => [
				'title'       => __( 'Magazyn', 'woocommerce-fakturownia' ),
				'type'        => 'tab_open',
				'description' => '',
			],
			self::OPTION_SYNCHRONIZATION         => [
				'title'       => __( 'Synchronizacja', 'woocommerce-fakturownia' ),
				'label'       => __( 'Włącz synchronizację stanów magazynowych', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'desc_tip'    => false,
				'description' => sprintf(
				// translators: %1$s link, %2$s close link.
					__( 'Wymagana jest konfiguracja w fakturownia.pl. %1$sZapoznaj się z dokumentacją%2$s', 'woocommerce-fakturownia' ),
					sprintf( '<a href="%1$s" target="blank">', esc_url( 'https://wpdesk.pl/sk/woocommerce-fakturownia-sync' ) ),
					'</a>'
				),
				'class'       => self::WEBHOOK_VISIBILITY_CHANGER,
				'default'     => 'no',
			],
			self::OPTION_SYNC_WOO_PRICES         => [
				'title'       => __( 'Synchronizacja cen', 'woocommerce-fakturownia' ),
				'label'       => __( 'Włącz synchronizację cen', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'desc_tip'    => false,
				'description' => __( 'Zaznacz tą opcję, aby włączyć synchronizację cen z Fakturownią. Włączenie tej opcji sprawi, że aktualizacja cen w sklepie WooCommerce lub w magazynie Fakturownii zsynchronizuje zmiany.', 'woocommerce-fakturownia' ),
				'default'     => 'no',
				'class'       => self::WEBHOOK_VISIBILITY_CHANGER,
			],
			self::OPTION_WAREHOUSE_ID            => [
				'title'       => __( 'ID magazynu', 'woocommerce-fakturownia' ),
				'label'       => __( 'Wybierz magazyn z którym chcesz się połączyć', 'woocommerce-fakturownia' ),
				'type'        => 'select',
				'options'     => $warehouses,
				'class'       => self::STOCK_OPTIONS_CLASS,
			],
			self::OPTION_CREATE_PRODUCTS         => [
				'title'       => __( 'Nowe produkty', 'woocommerce-fakturownia' ),
				'label'       => __( 'Włącz dodawanie produktów do magazynu Fakturowni', 'woocommerce-fakturownia' ),
				'type'        => 'checkbox',
				'desc_tip'    => false,
				'description' => __( 'Uwaga! Dodawanie produktu nie ustawia ilości produktu w serwisie fakturownia.pl. Opcja umożliwia dodanie nowego produktu podczas wystawiania faktury. Produkt zostanie dodany jeśli nie został odnaleziony produkt po ID produktu lub kodzie produktu.', 'woocommerce-fakturownia' ),
				'default'     => 'no',
				'class'       => self::STOCK_OPTIONS_CLASS,
			],
			self::OPTION_WAREHOUSE_WEBHOOK       => [
				'title'             => __( 'Adres webhooka', 'woocommerce-fakturownia' ),
				'type'              => 'text',
				'desc_tip'          => false,
				'default'           => site_url( '?' . ProductUpdateListener::URL_PARAMETER . '=1' ),
				'custom_attributes' => [
					'readonly' => 'readonly',
				],
				'class'             => self::WEBHOOK_CONFIGS_CLASS,
			],
			self::OPTION_WAREHOUSE_WEBHOOK_TOKEN => [
				'title'             => __( 'Token webhooka', 'woocommerce-fakturownia' ),
				'type'              => 'text',
				'desc_tip'          => false,
				'custom_attributes' => [
					'readonly' => 'readonly',
				],
				'class'             => self::WEBHOOK_CONFIGS_CLASS,
			],
			'magazyn_end'                        => [
				'type' => 'tab_close',
			],
		];
	}
}
