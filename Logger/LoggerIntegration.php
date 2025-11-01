<?php

namespace WPDesk\WooCommerceFakturownia\Logger;

use FakturowniaVendor\Psr\Log\LoggerInterface;
use FakturowniaVendor\Psr\Log\NullLogger;
use FakturowniaVendor\WPDesk\Persistence\Adapter\WordPress\WordpressSerializedOptionsContainer;
use FakturowniaVendor\WPDesk\Persistence\PersistentContainer;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Logger Integration
 */
class LoggerIntegration implements Hookable {

	/**
	 * @var string
	 */
	private $source;

	/**
	 * @var WordpressSerializedOptionsContainer
	 */
	private $settings;

	/**
	 * @param string $source
	 * @param string $settings_name
	 */
	public function __construct( string $source, string $settings_name ) {
		$this->source   = $source;
		$this->settings = new WordpressSerializedOptionsContainer( 'woocommerce_integration-' . $settings_name );
	}

	public function hooks() {
		( new LoggerNotices( $this->settings ) )->hooks();
	}

	/**
	 * @return PersistentContainer
	 */
	public function get_settings(): PersistentContainer {
		return $this->settings;
	}

	/**
	 * @return LoggerInterface
	 */
	public function get_logger(): LoggerInterface {
		return $this->settings->get_fallback( 'debug_mode' ) === 'yes' ? ( new PluginLogger( $this->source ) ) : new NullLogger();
	}

	/**
	 * @return bool
	 */
	public function should_exit(): bool {
		return $this->settings->get_fallback( 'debug_mode' ) === 'yes' && $this->settings->get_fallback( 'debug_mode_with_exit' ) === 'yes' && current_user_can( 'manage_options' );
	}
}
