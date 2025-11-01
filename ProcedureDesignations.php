<?php

namespace WPDesk\WooCommerceFakturownia;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

class ProcedureDesignations implements Hookable {

	const CODE_KEY = '_fakturownia_procedure_designations';

	public function hooks() {
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_select' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_value' ], 10, 2 );
	}

	public function add_select() {
		global $post;
		echo '<div id="' . esc_attr( self::CODE_KEY ) . '" class="options_group">';
		woocommerce_wp_select(
			[
				'id'          => self::CODE_KEY,
				'value'       => get_post_meta( $post->ID, self::CODE_KEY, true ),
				'label'       => esc_html__( 'Oznaczenie transakcji przychodowej', 'woocommerce-fakturownia' ),
				'desc_tip'    => true,
				'description' => esc_html__( 'Oznaczenie transakcji przychodowej da Fakturowni', 'woocommerce-fakturownia' ),
				'options'     => $this->option_values(),
			]
		);
		echo '</div>';
	}

	/**
	 * @param int $product_id Product ID.
	 */
	public function save_value( $product_id ) {
		if ( isset( $_POST[ self::CODE_KEY ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$gtu_code = sanitize_text_field( wp_unslash( $_POST[ self::CODE_KEY ] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $product_id, self::CODE_KEY, $gtu_code );
		}
	}

	/**
	 * @return array
	 */
	public function option_values() {
		return [
			''               => __( 'brak', 'woocommerce-fakturownia' ),
			'SW'             => 'SW - Dostawa w ramach sprzedaży wysyłkowej z terytorium kraju, o której mowa w art. 23 ustawy',
			'EE'             => 'EE - Świadczenie usług telekomunikacyjnych, nadawczych i elektronicznych, o których mowa w art. 28k ustawy',
			'TP'             => 'TP - Istniejące powiązania między nabywcą a dokonującym dostawy towarów lub usługodawcą, o których mowa w art. 32 ust. 2 pkt 1 ustawy,',
			'TT_WNT'         => 'TT_WNT - Wewnątrzwspólnotowe nabycie towarów dokonane przez drugiego w kolejności podatnika VAT w ramach transakcji trójstronnej w procedurze uproszczonej, o której mowa w dziale XII rozdziale 8 ustawy',
			'TT_D'           => 'TT_D - Dostawa towarów poza terytorium kraju dokonana przez drugiego w kolejności podatnika VAT w ramach transakcji trójstronnej w procedurze uproszczonej, o której mowa w dziale XII rozdziale 8 ustawy',
			'MR_T'           => 'MR_T - Świadczenie usług turystyki opodatkowane na zasadach marży zgodnie z art. 119 ustawy',
			'MR_UZ'          => 'MR_UZ - Dostawa towarów używanych, dzieł sztuki, przedmiotów kolekcjonerskich i antyków, opodatkowana na zasadach marży zgodnie z art. 120 ustawy',
			'I_42'           => 'I_42 - Wewnątrzwspólnotowa dostawa towarów następująca po imporcie tych towarów w ramach procedury celnej 42 (import)',
			'I_63'           => 'I_63 - Wewnątrzwspólnotowa dostawa towarów następująca po imporcie tych towarów w ramach procedury celnej 63 (import)',
			'B_SPV'          => 'B_SPV - Transfer bonu jednego przeznaczenia dokonany przez podatnika działającego we własnym imieniu, opodatkowany zgodnie z art. 8a ust. 1 ustawy',
			'B_SPV_DOSTAWA'  => 'B_SPV_DOSTAWA - Dostawa towarów oraz świadczenie usług, których dotyczy bon jednego przeznaczenia na rzecz podatnika, który wyemitował bon zgodnie z art. 8a ust. 4 ustawy',
			'B_MPV_PROWIZJA' => 'B_MPV_PROWIZJA - Świadczenie usług pośrednictwa oraz innych usług dotyczących transferu bonu różnego przeznaczenia, opodatkowane zgodnie z art. 8b ust. 2 ustawy',
		];
	}
}
