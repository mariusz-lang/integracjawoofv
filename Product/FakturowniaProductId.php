<?php

namespace WPDesk\WooCommerceFakturownia\Product;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\WooCommerceFakturownia\Api\FakturowniaApi;

/**
 * Add field to product and product variation.
 */
class FakturowniaProductId implements Hookable {

	const FAKTUROWNIA_PRODUCT_ID           = '_fakturownia_product_id';
	const FAKTUROWNIA_PRODUCT_ID_VARIATION = '_fakturownia_product_id_variation';

	/**
	 * API.
	 *
	 * @var FakturowniaApi
	 */
	private $fakturownia_api;

	/**
	 * FakturowniaProductIdAjax constructor.
	 *
	 * @param FakturowniaApi $fakturownia_api .
	 */
	public function __construct( FakturowniaApi $fakturownia_api ) {
		$this->fakturownia_api = $fakturownia_api;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'woocommerce_product_options_sku', [ $this, 'add_field_to_warehouse_tab' ] );
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'add_field_to_variation' ], 10, 3 );
	}

	/**
	 * Display field.
	 * Displays input text field.
	 *
	 * @param string $field_id .
	 * @param string $value .
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws \Exception API Exception.
	 */
	private function display_field( $field_id, $value = '' ) {
		$args = [
			'label'             => __( 'Produkt w Fakturowni', 'woocommerce-fakturownia' ),
			'placeholder'       => __( 'Wprowadź produkt', 'woocommerce-fakturownia' ),
			'id'                => $field_id,
			'desc_tip'          => true,
			'description'       => __( 'W przypadku wybranego magazynu szuka produktu bezpośrednio w nim.', 'woocommerce-fakturownia' ),
			'class'             => 'wc-product-search',
			'style'             => 'width:50%;',
			'custom_attributes' => [
				'data-action'      => 'fakturownia_product_id',
				'data-allow_clear' => '1',
				'data-placeholder' => '',
			],
			'options'           => [],
		];
		if ( ! empty( $value ) && '' !== $value ) {
			$args['value'] = $value;

			try {
				$fakturownia_product_response = $this->fakturownia_api->get_product( $value );
				$fakturownia_product          = $fakturownia_product_response->get_product();
				$options[ $value ]            = sprintf( '%1$s (#%2$s)', $fakturownia_product['name'], $fakturownia_product['id'] );
			} catch ( \Exception $e ) {
				$options = [ $value => $value ];
			}

			$args['options'] = $options;
		}
		woocommerce_wp_select( $args );
	}

	/**
	 * Add field to warehouse tab on product edit page.
	 */
	public function add_field_to_warehouse_tab() {
		global $product_object;
		$this->display_field( self::FAKTUROWNIA_PRODUCT_ID, $product_object->get_meta( self::FAKTUROWNIA_PRODUCT_ID ) );
	}

	/**
	 * Add field after variation dimensions on product page.
	 *
	 * @param int     $loop Position in the loop.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation Post data.
	 */
	public function add_field_to_variation( $loop, $variation_data, $variation ) {
		echo '<div>';
		$this->display_field( self::FAKTUROWNIA_PRODUCT_ID_VARIATION . "[{$loop}]", isset( $variation_data[ self::FAKTUROWNIA_PRODUCT_ID ] ) ? $variation_data[ self::FAKTUROWNIA_PRODUCT_ID ][0] : '' );
		echo '</div>';
	}
}
