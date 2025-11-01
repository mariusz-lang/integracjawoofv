<?php

namespace WPDesk\WooCommerceFakturownia\Emails;

use FakturowniaVendor\WPDesk\Invoices\Email\DocumentEmail;

/**
 * Base email.
 *
 * @package WPDesk\WooCommerceFakturownia\Emails
 */
class BaseEmail extends DocumentEmail {

	/**
	 * @param string $plugin_path          Plugin path.
	 * @param string $plugin_template_path Plugin template path.
	 */
	public function __construct( $plugin_path, $plugin_template_path ) {
		parent::__construct();
		$this->template_base  = untrailingslashit( $plugin_path ) . '/templates/';
		$this->customer_email = true;
		$this->manual         = true;

		$this->setTemplatePath( $plugin_template_path );
	}

	/**
	 * Trigger.
	 *
	 * @param \WC_Order $order        Order.
	 * @param string    $pdf_document PDF document content.
	 * @param string    $file_name    Attachment file name.
	 * @param string    $download_url Download URL.
	 *
	 * @throws \Exception Exception.
	 */
	public function trigger( $order, $pdf_document, $file_name, $download_url = '' ) {
		try {
			parent::trigger( $order, $pdf_document, $file_name, $download_url );
			$order->add_order_note(
				sprintf(
				// Translators: email title.
					__( 'Fakturownia - wysłano email: %1$s', 'woocommerce-fakturownia' ),
					$this->get_subject()
				)
			);
		} catch ( \Exception $e ) {
			$order->add_order_note(
				sprintf(
				// Translators: type name and document number.
					__( 'Fakturownia - błąd przy wysyłania emaila: %1$s', 'woocommerce-fakturownia' ),
					$this->get_subject()
				)
			);
			throw $e;
		}
	}

	/**
	 * Get email order items.
	 *
	 * @param \WC_Order $order      Order.
	 * @param bool      $plain_text Is plain text.
	 *
	 * @return string
	 */
	public static function get_email_order_items( $order, $plain_text = false ) {
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			return $order->email_order_items_table( [ 'plain_text' => $plain_text ] );
		} else {
			return wc_get_email_order_items( $order, [ 'plain_text' => $plain_text ] );
		}
	}
}
