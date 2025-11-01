<?php

namespace WPDesk\WooCommerceFakturownia;

/**
 * Display into for admin on incorrect config.
 *
 * The class is from iFirma
 */
class ConfigInfo implements \FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'admin_notices', [ $this, 'maybe_display_config_message' ] );
		add_action( 'admin_head', [ $this, 'maybe_add_custom_css_to_admin_head' ] );
	}

	/**
	 * Maybe display config message.
	 */
	public function maybe_display_config_message() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			if ( wc_tax_enabled() && get_option( 'woocommerce_tax_round_at_subtotal', '0' ) !== 'yes' ) {
				new \FakturowniaVendor\WPDesk\Notice\Notice(
					sprintf(
						// Translators: woocommerce tax settings URL.
						__( 'WooCommerce ma wyłączone zaokrąglanie podatków na podsumach. Do prawidłowego działania wtyczki Fakturownia konieczne jest włączenie zaokrąglania podatków na podsumie, a nie per wiersz. Kliknij %1$stutaj%2$s aby przejść do konfiguracji podatków.', 'woocommerce-fakturownia' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=tax' ) ) . '">',
						'</a>'
					),
					\FakturowniaVendor\WPDesk\Notice\Notice::NOTICE_TYPE_ERROR
				);
			}
		}
		if ( intval( get_option( 'woocommerce_price_num_decimals', '0' ) ) > 2 ) {
			new \FakturowniaVendor\WPDesk\Notice\Notice(
				sprintf(
					// Translators: woocommerce settings URL.
					__( 'Liczba znaków po przecinku jest ustawione na wartość większą od 2. Do prawidłowego działania wtyczki Fakturownia konieczne jest ustawianie tej wartości na 2 lub mniej. Kliknij %1$stutaj%2$s aby przejść do ustawień głównych.', 'woocommerce-fakturownia' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">',
					'</a>'
				),
				\FakturowniaVendor\WPDesk\Notice\Notice::NOTICE_TYPE_ERROR
			);
		}
	}

	/**
	 * Maybe add custom css to admin head.
	 */
	public function maybe_add_custom_css_to_admin_head() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$woocommerce_tax_round_at_subtotal = get_option( 'woocommerce_tax_round_at_subtotal', 'no' );
			$woocommerce_price_num_decimals    = intval( get_option( 'woocommerce_price_num_decimals', '0' ) );
			include 'Views/admin-head-style.php';
		}
	}
}
