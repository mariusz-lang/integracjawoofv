<?php

namespace WPDesk\WooCommerceFakturownia\Metabox;

use FakturowniaVendor\WPDesk\Invoices\Data\OrderDefaults;
use FakturowniaVendor\WPDesk\Invoices\Metabox\MetaBoxFieldSelect;
use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WC_Order;

/**
 * @package WPDesk\WooCommerceFakturownia\Metabox
 */
class PaymentMethodField extends MetaBoxFieldSelect {


	/**
	 * @param WC_Order                                  $order            Order.
	 * @param \WPDesk\Invoices\Metadata\MetadataContent $metadata_content Meta data.
	 * @param \WPDesk\Invoices\Data\OrderDefaults       $order_defaults   Order defaults.
	 *
	 * @return string
	 */
	protected function prepareValue(
		WC_Order $order,
		MetadataContent $metadata_content,
		OrderDefaults $order_defaults
	) {
		$this->get_order_payment_name( $order );
		return $order_defaults->getDefault( 'payment_method' );
	}

	private function get_order_payment_name( WC_Order $order ) {
		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		} else {
			$payment_gateways = [];
		}

		$payment_method            = $order->get_payment_method();
		$method[ $payment_method ] = $payment_method;
		if ( isset( $payment_gateways[ $payment_method ] ) ) {
			/* translators: %s: payment method */
			$method[ $payment_method ] = $payment_gateways[ $payment_method ]->get_title();
		}
		$options = array_merge( $this->getOptions(), $method );
		$this->setOptions( $options );
	}
}
