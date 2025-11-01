<?php

namespace WPDesk\WooCommerceFakturownia\Data;

use FakturowniaVendor\WPDesk\Invoices\Documents\Type;
use WC_Order;
use FakturowniaVendor\WPDesk\Invoices\Data\OrderDefaults;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProForma;
use WPDesk\WooCommerceFakturownia\Documents\InvoiceProFormaWithoutVat;
use WPDesk\WooCommerceFakturownia\Documents\Receipt;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\WoocommerceIntegration;

/**
 * Class InvoiceOrderDefaults
 *
 * Set default order data for invoices.
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class InvoiceOrderDefaults extends OrderDefaults {

	/**
	 * Integration.
	 *
	 * @var InvoicesIntegration
	 */
	protected $integration;

	/**
	 * Payment method options.
	 *
	 * @var array
	 */
	private $payment_method_options = [];

	/**
	 * Payment method names
	 */
	const PAYMENT_METHOD_TRANSFER = 'transfer';
	const PAYMENT_METHOD_CARD     = 'card';
	const PAYMENT_METHOD_CASH     = 'cash';
	const PAYMENT_METHOD_COD      = 'cod';

	const PAID_STATUSES = [ 'completed', 'processing' ];

	/**
	 * InvoiceOrderDefaults constructor.
	 *
	 * @param WC_Order $order Order.
	 * @param InvoicesIntegration $integration Integration.
	 * @param Type $document_type Document type.
	 */
	public function __construct( WC_Order $order, InvoicesIntegration $integration, $document_type ) {
		parent::__construct( $order, $document_type );

		$type = $document_type->getTypeName();
		$this->initPaymentMethodOptions();
		$this->integration = $integration;
		$payment_method    = $order->get_payment_method();

		$this->addDefault( 'issue_date', date( 'Y-m-d', current_time( 'timestamp' ) ) ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		$this->setPaidAmount( $order );

		if ( 'cod' === $payment_method ) {
			$payment_date_for_cod = date( 'Y-m-d', current_time( 'timestamp' ) + 60 * 60 * 24 * $integration->woocommerce_integration->getOptionPaymentDate() ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$this->addDefault( 'payment_date', $payment_date_for_cod );
			$order_status = $order->get_status();
			if ( $order_status !== 'completed' ) {
				$this->addDefault( 'paid_amount', '0.0' );
			}
		} else {
			$payment_date = date( 'Y-m-d', current_time( 'timestamp' ) ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			if ( $type === InvoiceProForma::TYPE_NAME || $type === InvoiceProFormaWithoutVat::TYPE_NAME ) {
				$payment_date = date( 'Y-m-d', current_time( 'timestamp' ) + 60 * 60 * 24 * $integration->woocommerce_integration->getProformaOptionPaymentDate() ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			}

			$this->addDefault( 'payment_date', $payment_date );
		}

		$this->addDefault( 'payment_method', $payment_method );
		$this->addDefault( 'sale_date', date( 'Y-m-d', current_time( 'timestamp' ) ) ); //phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		$this->addDefault( 'legal_basis', $integration->woocommerce_integration->getOptionLegalBasis() );

		$comment_option = $integration->woocommerce_integration->getOptionCommentContent();
		if ( Receipt::TYPE_NAME === $type ) {
			$comment_option = $integration->woocommerce_integration->getOptionCommentContentForReceipt();
		}

		$this->addDefault(
			'comments',
			str_replace(
				InvoiceForm::SHORTCODE_ORDER_NUMBER,
				$order->get_order_number(),
				$comment_option
			)
		);
	}

	/**
	 * Set paid amount.
	 *
	 * @param \WC_Order $order Order.
	 */
	private function setPaidAmount( $order ) {
		$order_status = $order->get_status();
		if ( in_array( $order_status, self::PAID_STATUSES, true ) ) {
			$this->addDefault( 'paid_amount', $order->get_total() );
		} else {
			$this->addDefault( 'paid_amount', '0.0' );
		}
	}

	/**
	 * Init payment method options.
	 */
	private function initPaymentMethodOptions() {
		$this->payment_method_options = [
			self::PAYMENT_METHOD_TRANSFER => __( 'Przelew', 'woocommerce-fakturownia' ),
			self::PAYMENT_METHOD_CARD     => __( 'Karta płatnicza', 'woocommerce-fakturownia' ),
			self::PAYMENT_METHOD_CASH     => __( 'Gotówka', 'woocommerce-fakturownia' ),
		];
	}

	/**
	 * Get payment method options.
	 *
	 * @return array
	 */
	public function getPaymentMethodOptions() {
		return $this->payment_method_options;
	}
}
