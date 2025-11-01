<?php

namespace WPDesk\WooCommerceFakturownia;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use FakturowniaVendor\WPDesk\View\Renderer\Renderer;
use FakturowniaVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use FakturowniaVendor\WPDesk\View\Resolver\ChainResolver;
use FakturowniaVendor\WPDesk\View\Resolver\DirResolver;
use FakturowniaVendor\WPDesk\View\Resolver\WPThemeResolver;
use FakturowniaVendor\WPDesk\Dashboard\DashboardWidget;
use FakturowniaVendor\WPDesk_Plugin_Info;
use WPDesk\WooCommerceFakturownia\Emails\RegisterEmails;
use WPDesk\WooCommerceFakturownia\Product\FakturowniaProductIdSaver;

/**
 * Class Plugin
 *
 * @package WPDesk\WooCommerceFakturownia
 */
class Plugin extends AbstractPlugin implements HookableCollection {
	use HookableParent;

	/**
	 * Scripts version.
	 *
	 * @var string
	 */
	private $scripts_version = WOOCOMMERCE_FAKTUROWNIA_VERSION . '.11';

	/**
	 * Renderer.
	 *
	 * @var Renderer;
	 */
	private $renderer;

	private $plugin_path;

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		parent::__construct( $plugin_info );

		$this->plugin_path  = $this->plugin_info->get_plugin_dir();
		$this->settings_url = admin_url( 'admin.php?page=wc-settings&tab=integration&section=integration-fakturownia' );
		$this->docs_url     = 'https://www.wpdesk.pl/sk/woocommerce-fakturownia-docs/';
		$this->support_url  = 'https://www.wpdesk.pl/sk/woocommerce-fakturownia-support/';
		$this->init_renderer();
	}

	/**
	 * Set renderer.
	 */
	private function init_renderer() {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new WPThemeResolver( $this->plugin_namespace ) );
		$resolver->appendResolver( new DirResolver( trailingslashit( $this->plugin_path ) . 'templates' ) );
		$this->renderer = new SimplePhpRenderer( $resolver );
	}

	/**
	 * Fire hooks.
	 */
	public function hooks() {
		parent::hooks();

		InvoicesIntegration::$plugin_info = $this->plugin_info;
		$this->add_hookable( new RegisterEmails( $this->plugin_path, $this->plugin_namespace ) );
		$this->add_hookable( new ConfigInfo() );
		$this->add_hookable( new FakturowniaProductIdSaver() );

		( new DashboardWidget() )->hooks();
		$this->hooks_on_hookable_objects();

		WoocommerceIntegration::set_renderer( $this->renderer );
		add_filter( 'woocommerce_integrations', [ $this, 'add_woocommerce_integration' ], 20 );
	}

	/**
	 * Add WooCommerce integration.
	 *
	 * @param array $integrations Integrations.
	 *
	 * @return array
	 */
	public function add_woocommerce_integration( array $integrations ): array {
		$integrations[] = WoocommerceIntegration::class;

		return $integrations;
	}

	/**
	 * Admin enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( in_array(
			$screen->id,
			[
				'woocommerce_page_wc-settings',
				'edit-shop_order',
				'shop_order',
				'woocommerce_page_wc-orders',
			],
			true
		) ) {
			wp_register_style(
				'fakturownia_admin_css',
				trailingslashit( $this->get_plugin_assets_url() ) . 'css/admin.css',
				[],
				$this->scripts_version
			);
			wp_enqueue_style( 'fakturownia_admin_css' );
			wp_register_script(
				'fakturownia_admin_js',
				trailingslashit( $this->get_plugin_assets_url() ) . 'js/admin.js',
				[ 'jquery' ],
				$this->scripts_version,
				true
			);
			wp_enqueue_script( 'fakturownia_admin_js' );
		}
	}

	/**
	 * Admin enqueue scripts.
	 */
	public function wp_enqueue_scripts() {
		if ( is_checkout() ) {
			wp_register_script(
				'fakturownia_checkout_js',
				trailingslashit( $this->get_plugin_assets_url() ) . 'js/checkout.js',
				[ 'jquery' ],
				$this->scripts_version,
				true
			);
			wp_enqueue_script( 'fakturownia_checkout_js' );
		}
	}
}
