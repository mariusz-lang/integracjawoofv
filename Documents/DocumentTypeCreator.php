<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;

/**
 * Class InvoiceTypeCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
abstract class DocumentTypeCreator extends DocumentCreator {

	/**
	 * @param array|string $document_statuses
	 *
	 * @return array|string
	 */
	protected function remove_wc_prefix_from_order_status( $document_statuses ) {
		if ( is_array( $document_statuses ) ) {
			$statuses = [];
			foreach ( $document_statuses as $key => $order_status ) {
				$statuses[ $key ] = \str_replace( 'wc-', '', $order_status );
			}

			return $statuses;
		}

		return str_replace( 'wc-', '', $document_statuses );
	}

	/**
	 * Is auto create allowed for order?
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAutoCreateAllowedForOrder( $order ) {
		$integration = $this->type->getIntegration();

		if ( InvoiceForm::AUTO_GENERATE === $integration->woocommerce_integration->getOptionGenerateInvoice() ) {
			return true;
		}

		if ( InvoiceForm::ASK_AND_AUTO_GENERATE === $integration->woocommerce_integration->getOptionGenerateInvoice() ) {
			if ( '1' === $order->get_meta( '_billing_faktura', 'true' ) ) {
				return true;
			}
		}

		return false;
	}
}
