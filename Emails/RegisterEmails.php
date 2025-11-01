<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\WooCommerceFakturownia\Documents\Bill;
use WPDesk\WooCommerceFakturownia\Documents\Invoice;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceCorrection;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceForeign;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProForma;
use WPDesk\WooCommerceFakturownia\Documents\Receipt;
use WPDesk\WooCommerceFakturownia\Plugin;

/**
 * Register document emails.
 */
class RegisterEmails implements Hookable {

	/**
	 * @var string
	 */
	private $plugin_path;

	/**
	 * @var mixed
	 */
	private $plugin_slug;

	/**
	 * @param Plugin $plugin
	 */
	public function __construct( string $plugin_path, $plugin_slug ) {
		$this->plugin_path = $plugin_path;
		$this->plugin_slug = $plugin_slug;
	}

	/**
	 * Fire hooks.
	 */
	public function hooks() {
		add_filter( 'woocommerce_email_classes', [ $this, 'register_emails' ], 11 );
	}

	/**
	 * @param array $emails Emails.
	 *
	 * @return array
	 */
	public function register_emails( array $emails ) {
		$emails[ Invoice::EMAIL_SLUG ]           = new EmailInvoice( $this->plugin_path, $this->plugin_slug );
		$emails[ InvoiceForeign::EMAIL_SLUG ]    = new EmailInvoiceForeign( $this->plugin_path, $this->plugin_slug );
		$emails[ InvoiceProForma::EMAIL_SLUG ]   = new EmailInvoiceProForma( $this->plugin_path, $this->plugin_slug );
		$emails[ Bill::EMAIL_SLUG ]              = new EmailBill( $this->plugin_path, $this->plugin_slug );
		$emails[ Receipt::EMAIL_SLUG ]           = new EmailReceipt( $this->plugin_path, $this->plugin_slug );
		$emails[ InvoiceCorrection::EMAIL_SLUG ] = new EmailCorrection( $this->plugin_path, $this->plugin_slug );

		return $emails;
	}
}
