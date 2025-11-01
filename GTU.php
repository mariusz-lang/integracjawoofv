<?php

namespace WPDesk\WooCommerceFakturownia;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

class GTU implements Hookable {

	const GTU_CODE_KEY = '_fakturownia_gtu_code';

	public function hooks() {
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_gtu_select' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_gtu_value' ], 10, 2 );
	}

	public function add_gtu_select() {
		global $post;
		echo '<div id="gtu_code_fakturownia" class="options_group">';
		$options = [
			'0' => __( 'brak', 'woocommerce-fakturownia' ),
		];
		foreach ( $this->gtu_data() as $codes ) {
			$options[ $codes['name'] ] = $codes['name'] . ' - ' . $codes['short_description'];
		}
		woocommerce_wp_select(
			[
				'id'          => self::GTU_CODE_KEY,
				'value'       => get_post_meta( $post->ID, self::GTU_CODE_KEY, true ),
				'label'       => esc_html__( 'Kod GTU Fakturownia', 'woocommerce-fakturownia' ),
				'desc_tip'    => true,
				'description' => esc_html__( 'Oznaczenie grup towarów i usług dla Fakturowni', 'woocommerce-fakturownia' ),
				'options'     => $options,
			]
		);
		echo '</div>';
	}

	/**
	 * @param int $product_id Product ID.
	 */
	public function save_gtu_value( $product_id ) {
		if ( isset( $_POST[ self::GTU_CODE_KEY ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$gtu_code = sanitize_text_field( wp_unslash( $_POST[ self::GTU_CODE_KEY ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $product_id, self::GTU_CODE_KEY, $gtu_code );
		}
	}

	public function gtu_data() {
		return [
			[
				'id'                => '01',
				'name'              => 'GTU_01',
				'short_description' => 'Dostawa napojów alkoholowych',
				'description'       => 'Dostawa napojów alkoholowych - alkoholu etylowego, piwa, wina, napojów fermentowanych i wyrobów pośrednich, w rozumieniu przepisów o podatku akcyzowym.',
			],
			[
				'id'                => '02',
				'name'              => 'GTU_02',
				'short_description' => 'Dostawa paliw silnikowych',
				'description'       => 'Dostawa towarów, o których mowa w art. 103 ust. 5aa ustawy.',
			],
			[
				'id'                => '03',
				'name'              => 'GTU_03',
				'short_description' => 'Dostawa oleju opałowego, olejów smarownych oraz smarów plastycznych itp.',
				'description'       => 'Dostawa oleju opałowego w rozumieniu przepisów o podatku akcyzowym oraz olejów smarowych, pozostałych olejów o kodach CN od 2710 19 71 do 2710 19 99, z wyłączeniem wyrobów o kodzie CN 2710 19 85 (oleje białe, parafina ciekła) oraz smarów plastycznych zaliczanych do kodu CN 2710 19 99, olejów smarowych o kodzie CN 2710 20 90, preparatów smarowych objętych pozycją CN 3403, z wyłączeniem smarów plastycznych objętych tą pozycją.',
			],
			[
				'id'                => '04',
				'name'              => 'GTU_04',
				'short_description' => 'Dostawa wyrobów tytoniowych oraz wyborów nowatorskich',
				'description'       => 'Dostawa wyrobów tytoniowych, suszu tytoniowego, płynu do papierosów elektronicznych i wyrobów nowatorskich, w rozumieniu przepisów o podatku akcyzowym.',
			],
			[
				'id'                => '05',
				'name'              => 'GTU_05',
				'short_description' => 'Dostawa odpadów',
				'description'       => 'Dostawa odpadów - wyłącznie określonych w poz. 79-91 załącznika nr 15 do ustawy.',
			],
			[
				'id'                => '06',
				'name'              => 'GTU_06',
				'short_description' => 'Dostawa urządzeń elektronicznych oraz części i materiałów do nich',
				'description'       => 'Dostawa urządzeń elektronicznych oraz części i materiałów do nich, wyłącznie określonych w poz. 7-9, 59-63, 65, 66, 69 i 94-96 załącznika nr 15 do ustawy.',
			],
			[
				'id'                => '07',
				'name'              => 'GTU_07',
				'short_description' => 'Dostawa pojazdów oraz części samochodowych',
				'description'       => 'Dostawa pojazdów oraz części samochodowych o kodach wyłącznie CN 8701 - 8708 oraz CN 8708 10.',
			],
			[
				'id'                => '08',
				'name'              => 'GTU_08',
				'short_description' => 'Dostawa metali szlachetnych oraz nieszlachetnych',
				'description'       => 'Dostawa metali szlachetnych oraz nieszlachetnych - wyłącznie określonych w poz. 1-3 załącznika nr 12 do ustawy oraz w poz. 12-25, 33-40, 45, 46, 56 i 78 załącznika nr 15 do ustawy.',
			],
			[
				'id'                => '09',
				'name'              => 'GTU_09',
				'short_description' => 'Dostawa leków oraz wyrobów medycznych',
				'description'       => 'Dostawa leków oraz wyrobów medycznych - produktów leczniczych, środków spożywczych specjalnego przeznaczenia żywieniowego oraz wyrobów medycznych, objętych obowiązkiem zgłoszenia, o którym mowa w art. 37av ust. 1 ustawy z dnia 6 września 2001 r. - Prawo farmaceutyczne (Dz. U. z 2019 r. poz. 499, z późn. zm.).',
			],
			[
				'id'                => '10',
				'name'              => 'GTU_10',
				'short_description' => 'Dostawa budynków, budowli i gruntów.',
				'description'       => 'Dostawa budynków, budowli i gruntów.',
			],
			[
				'id'                => '11',
				'name'              => 'GTU_11',
				'short_description' => 'Świadczenie usług w zakresie przenoszenia uprawnień do emisji gazów cieplarnianych',
				'description'       => 'Dostawa o których mowa w ustawie z dnia 12 czerwca 2015 r. o systemie handlu uprawnieniami do emisji gazów cieplarnianych (Dz. U. z 2018 r. poz. 1201 i 2538 oraz z 2019 r. poz. 730, 1501 i 1532)..',
			],
			[
				'id'                => '12',
				'name'              => 'GTU_12',
				'short_description' => 'Świadczenie usług o charakterze niematerialnym',
				'description'       => 'Świadczenie usług o charakterze niematerialnym - wyłącznie: doradczych, księgowych, prawnych, zarządczych, szkoleniowych, marketingowych, firm centralnych (head offices), reklamowych, badania rynku i opinii publicznej, w zakresie badań naukowych i prac rozwojowych.',
			],
			[
				'id'                => '13',
				'name'              => 'GTU_13',
				'short_description' => 'Świadczenie usług transportowych i gospodarki magazynowej',
				'description'       => 'Świadczenie usług transportowych i gospodarki magazynowej - Sekcja H PKWiU 2015 symbol ex 49.4, ex 52.1.',
			],
		];
	}
}
