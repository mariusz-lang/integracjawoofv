<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use WPDesk\WooCommerceFakturownia\Documents\InvoiceProForma;

/**
 * Email template for invoice proforma document type.
 *
 * @package WPDesk\WooCommerceFakturownia\Emails
 */
class EmailInvoiceProForma extends BaseEmail {

	/**
	 * @param string $plugin_path          Plugin path.
	 * @param string $plugin_template_path Plugin template path.
	 */
	public function __construct( $plugin_path, $plugin_template_path ) {
		$this->id             = InvoiceProForma::EMAIL_SLUG;
		$this->title          = __( 'Faktura Pro Forma (Fakturownia)', 'woocommerce-fakturownia' );
		$this->description    = __( 'Email z fakturą pro forma.', 'woocommerce-fakturownia' );
		$this->heading        = __( 'Faktura Pro Forma do zamówienia', 'woocommerce-fakturownia' );
		$this->subject        = __( '[{site_title}] Faktura Pro Forma do zamówienia {order_number} - {order_date}', 'woocommerce-fakturownia' );
		$this->template_html  = 'emails/faktura-proforma.php';
		$this->template_plain = 'emails/plain/faktura-proforma.php';

		parent::__construct( $plugin_path, $plugin_template_path );
	}
}
