<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use WPDesk\WooCommerceFakturownia\Documents\Invoice;

/**
 * Email template for invoice document type.
 *
 * @package WPDesk\WooCommerceFakturownia\Emails
 */
class EmailCorrection extends BaseEmail {

	/**
	 * @param string $plugin_path          Plugin path.
	 * @param string $plugin_template_path Plugin template path.
	 */
	public function __construct( $plugin_path, $plugin_template_path ) {
		$this->id             = Invoice::EMAIL_SLUG;
		$this->title          = __( 'Faktura korygująca (Fakturownia)', 'woocommerce-fakturownia' );
		$this->description    = __( 'Email z fakturą korygującą (Fakturownia).', 'woocommerce-fakturownia' );
		$this->heading        = __( 'Faktura korygująca do zamówienia', 'woocommerce-fakturownia' );
		$this->subject        = __( '[{site_title}] Faktura korygująca do zamówienia {order_number} - {order_date}', 'woocommerce-fakturownia' );
		$this->template_html  = 'emails/faktura-correction.php';
		$this->template_plain = 'emails/plain/faktura-correction.php';

		parent::__construct( $plugin_path, $plugin_template_path );
	}
}
