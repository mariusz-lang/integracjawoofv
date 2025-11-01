<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use WPDesk\WooCommerceFakturownia\Documents\Bill;

/**
 * Email template for bill document type.
 *
 * @package WPDesk\WooCommerceFakturownia\Emails
 */
class EmailBill extends BaseEmail {

	/**
	 * @param string $plugin_path          Plugin path.
	 * @param string $plugin_template_path Plugin template path.
	 */
	public function __construct( $plugin_path, $plugin_template_path ) {
		$this->id             = Bill::EMAIL_SLUG;
		$this->title          = __( 'Rachunek (Fakturownia)', 'woocommerce-fakturownia' );
		$this->description    = __( 'Email z rachunkiem.', 'woocommerce-fakturownia' );
		$this->heading        = __( 'Rachunek do zamówienia', 'woocommerce-fakturownia' );
		$this->subject        = __( '[{site_title}] Rachunek do zamówienia {order_number} - {order_date}', 'woocommerce-fakturownia' );
		$this->template_html  = 'emails/rachunek.php';
		$this->template_plain = 'emails/plain/rachunek.php';

		parent::__construct( $plugin_path, $plugin_template_path );
	}
}
