<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use WPDesk\WooCommerceFakturownia\Documents\InvoiceForeign;

/**
 * Email template for foreign invoice document type.
 *
 * @package WPDesk\WooCommerceFakturownia\Emails
 */
class EmailInvoiceForeign extends BaseEmail {

	/**
	 * @param string $plugin_path          Plugin path.
	 * @param string $plugin_template_path Plugin template path.
	 */
	public function __construct( $plugin_path, $plugin_template_path ) {
		$this->id             = InvoiceForeign::EMAIL_SLUG;
		$this->title          = __( 'Faktura walutowa (Fakturownia)', 'woocommerce-fakturownia' );
		$this->description    = __( 'Email z fakturą walutową (Fakturownia).', 'woocommerce-fakturownia' );
		$this->heading        = __( 'Faktura walutowa do zamówienia', 'woocommerce-fakturownia' );
		$this->subject        = __( '[{site_title}] Faktura walutowa do zamówienia {order_number} - {order_date}', 'woocommerce-fakturownia' );
		$this->template_html  = 'emails/faktura.php';
		$this->template_plain = 'emails/plain/faktura.php';

		parent::__construct( $plugin_path, $plugin_template_path );
	}
}
