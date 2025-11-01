<?php

namespace WPDesk\WooCommerceFakturownia\Block\VatNumber;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use FakturowniaVendor\WPDesk\WooCommerce\EUVAT\Settings\ShopSettings;
use FakturowniaVendor\WPDesk_Plugin_Info;

class BlocksIntegration implements IntegrationInterface {

	private const BLOCK_VERSION = '1.0.0';

	private $plugin_info;

	public const BLOCK_NAME = 'woocommerce-eu-vat-number';

	/**
	 * @var array
	 */
	private $eu_vat_data;

	public function __construct( WPDesk_Plugin_Info $plugin_info, array $eu_vat_data ) {
		$this->plugin_info = $plugin_info;
		$this->eu_vat_data = $eu_vat_data;
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$this->register_block_frontend_scripts();
		$this->register_block_editor_scripts();
	}

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return self::BLOCK_NAME;
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return [
			$this->get_name() . '-scripts-frontend',
		];
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return [
			$this->get_name() . '-scripts-frontend',
			$this->get_name() . '-scripts-index',
		];
	}


	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data(): array {
		return $this->eu_vat_data;
	}

	/**
	 * Register scripts for delivery date block editor.
	 *
	 * @return void
	 */
	public function register_block_editor_scripts() {
		$script_path       = '/assets/js/blocks/vat-number/index.js';
		$script_url        = $this->plugin_info->get_plugin_url() . $script_path;
		$script_asset_path = $this->plugin_info->get_plugin_dir() . '/assets/js/blocks/vat-number/index.asset.php';

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			$this->get_name() . '-scripts-index',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'] . time(),
			true
		);
	}

	/**
	 * Register scripts for frontend block.
	 *
	 * @return void
	 */
	public function register_block_frontend_scripts() {
		$script_path       = '/assets/js/blocks/vat-number/frontend.js';
		$script_url        = $this->plugin_info->get_plugin_url() . $script_path;
		$script_asset_path = $this->plugin_info->get_plugin_dir() . '/assets/js/blocks/vat-number/frontend.asset.php';

		$script_asset = file_exists( $script_asset_path )
			? require $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $this->get_file_version( $script_asset_path ),
			];

		wp_register_script(
			$this->get_name() . '-scripts-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'] . time(),
			true
		);

		$settings = get_option( 'woocommerce_integration-fakturownia_settings' );

		$generate_invoice = $settings['wystawianie_faktur'] ?? '';
		wp_localize_script(
			'fakturownia-block-frontend',
			'fakturownia_settings',
			[
				'generate_invoice' => $generate_invoice,
			]
		);
	}


	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 *
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return self::BLOCK_VERSION;
	}
}
