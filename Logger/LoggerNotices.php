<?php

namespace WPDesk\WooCommerceFakturownia\Logger;

use FakturowniaVendor\WPDesk\Persistence\PersistentContainer;

/**
 * Show notice if logger is enabled.
 */
class LoggerNotices {

	/**
	 * @var bool
	 */
	private $is_debug_mode;

	/**
	 * @var bool
	 */
	private $stop_request;

	/**
	 * @param PersistentContainer $container
	 */
	public function __construct( PersistentContainer $container ) {
		$this->is_debug_mode = $container->get_fallback( 'debug_mode' ) === 'yes';
		$this->stop_request  = $container->get_fallback( 'debug_mode_with_exit' ) === 'yes';
	}

	public function hooks() {
		add_action( 'admin_notices', [ $this, 'show_notice_for_enabled_logger' ] );
		add_action( 'admin_notices', [ $this, 'show_notice_for_blocked_request' ] );
	}

	public function show_notice_for_enabled_logger() {
		if ( $this->is_debug_mode ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					// Translators: link
					echo wp_kses_post( sprintf( __( '<strong>Uwaga!</strong> Tryb debugowania wtyczki Fakturownia jest włączony. Ustawienie debugowania znajdziesz <a href="%s">tutaj &rarr;</a>', 'woocommerce-fakturownia' ), admin_url( 'admin.php?page=wc-settings&tab=integration&section=integration-fakturownia#tryb-debugowania' ) ) );
					?>
				</p>
			</div>
			<?php
		}
	}

	public function show_notice_for_blocked_request() {
		if ( $this->stop_request ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					// Translators: link
					echo wp_kses_post( sprintf( __( '<strong>Uwaga!</strong> Blokowanie żądań API dla wtyczki Fakturownia jest włączone. Możesz je wyłączyć <a href="%s">tutaj &rarr;</a>', 'woocommerce-fakturownia' ), admin_url( 'admin.php?page=wc-settings&tab=integration&section=integration-fakturownia#tryb-debugowania' ) ) );
					?>
				</p>
			</div>
			<?php
		}
	}
}
