<?php

namespace WPDesk\WooCommerceFakturownia\Logger;

use FakturowniaVendor\Psr\Log\AbstractLogger;
use FakturowniaVendor\WPDesk\Logger\Settings;
use FakturowniaVendor\WPDesk\Logger\SimpleLoggerFactory;

/**
 * Plugin logger.
 */
class PluginLogger extends AbstractLogger {

	/**
	 * Log name.
	 *
	 * @var string
	 */
	private $source;

	/**
	 * @param string $source
	 */
	public function __construct( string $source ) {
		$this->source = $source;
	}

	/**
	 * @return \FakturowniaVendor\Monolog\Logger
	 */
	private function get_logger(): \FakturowniaVendor\Monolog\Logger {
		return ( new SimpleLoggerFactory( $this->source ) )->getLogger();
	}

	/**
	 * @param       $level
	 * @param       $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = [] ) {
		$data = is_array( $message ) || is_object( $message ) ? print_r( $message, true ) : $message; //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		$this->get_logger()->log( $level, $data, array_merge( $context, [ 'source' => $this->source ] ) );
	}
}
