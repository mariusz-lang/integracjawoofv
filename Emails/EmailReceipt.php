<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use WPDesk\WooCommerceFakturownia\Documents\Receipt;

/**
 * Email template for receipt document type.
 *
 * @package WPDesk\WooCommerceFakturownia\Emails
 */
class EmailReceipt extends BaseEmail {

	/**
	 * @param string $plugin_path          Plugin path.
	 * @param string $plugin_template_path Plugin template path.
	 */
	public function __construct( $plugin_path, $plugin_template_path ) {
		$this->id             = Receipt::EMAIL_SLUG;
		$this->title          = __( 'Paragon (Fakturownia)', 'woocommerce-fakturownia' );
		$this->description    = __( 'Email z paragonem.', 'woocommerce-fakturownia' );
		$this->heading        = __( 'Paragon do zamówienia', 'woocommerce-fakturownia' );
		$this->subject        = __( '[{site_title}] Paragon do zamówienia {order_number} - {order_date}', 'woocommerce-fakturownia' );
		$this->template_html  = 'emails/paragon.php';
		$this->template_plain = 'emails/plain/paragon.php';

		parent::__construct( $plugin_path, $plugin_template_path );
	}
}
