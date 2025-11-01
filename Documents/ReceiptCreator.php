<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WC_Order;

use WPDesk\WooCommerceFakturownia\Api\Invoice\PostResponseJson;
use WPDesk\WooCommerceFakturownia\Data\ReceiptFormData;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\ReceiptForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\DocumentData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;
use WPDesk\WooCommerceFakturownia\API\DocumentGetResponseJson;
use WPDesk\WooCommerceFakturownia\API\InvoicesException;

/**
 * Class ReceiptCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class ReceiptCreator extends DocumentTypeCreator {

	/**
	 * Create from order.
	 *
	 * @param WC_Order                                                $order            Order.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Field\VatNumber|null $vat_number_field Vat number field.
	 * @param InvoiceOrderDefaults                                    $defaults         Defaults.
	 * @param InvoicesIntegration                                     $integration      Integration.
	 *
	 * @return ReceiptFormData
	 */
	protected function createFromOrderAndDefaultsAndSettings(
		WC_Order $order,
		$vat_number_field,
		InvoiceOrderDefaults $defaults,
		InvoicesIntegration $integration
	) {
		return ReceiptFormData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	/**
	 * Create document in API.
	 *
	 * @param DocumentData $invoice_data Invoice data.
	 *
	 * @return PostResponseJson
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	protected function createDocument( DocumentData $invoice_data, $order ) {
		if ( ! $this->isAllowedForOrder( $order ) ) {
			$error_info = __( 'Ten typ dokumentu nie jest obsługiwany!', 'woocommerce-fakturownia' );
			throw new \RuntimeException( $error_info );
		}
		$integration = $this->type->getIntegration();

		return $integration->get_api()->create_document( $invoice_data, $order );
	}

	/**
	 * Is allowed for order.
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		return true;
	}

	/**
	 * Is auto create allowed for order?
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAutoCreateAllowedForOrder( $order ) {
		$integration     = $this->type->getIntegration();
		$generate_status = $integration->woocommerce_integration->getOptionGenerateReceiptStatus();
		$generate_type   = $integration->woocommerce_integration->getOptionGenerateReceipt();
		$order_status    = 'wc-' . $order->get_status();

		$generate_for_status = in_array( $order_status, $generate_status, true );
		if ( $generate_for_status && ReceiptForm::AUTO_GENERATE === $generate_type ) {
			return true;
		}

		if ( $generate_for_status && ReceiptForm::AUTO_GENERATE_WITHOUT_INVOICE === $generate_type ) {
			if ( '1' !== $order->get_meta( '_billing_faktura', 'true' ) && '1' !== $order->get_meta( '_billing_rachunek', 'true' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get document from API.
	 *
	 * @param int $invoice_id Invoice ID.
	 *
	 * @return DocumentGetResponseJson
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	protected function getDocument( $invoice_id ) {
		$integration = $this->type->getIntegration();

		return $integration->get_api()->get_invoice( $invoice_id );
	}
}
