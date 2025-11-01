<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use WC_Order;
use WPDesk\WooCommerceFakturownia\Api\Invoice\PostResponseJson;
use WPDesk\WooCommerceFakturownia\Data\BillData;
use WPDesk\WooCommerceFakturownia\Forms\Integration\BillForm;
use WPDesk\WooCommerceFakturownia\Forms\Integration\InvoiceForm;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\DocumentData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;
use WPDesk\WooCommerceFakturownia\API\DocumentGetResponseJson;
use WPDesk\WooCommerceFakturownia\API\InvoicesException;

/**
 * Class DomesticInvoiceCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
class BillCreator extends DocumentTypeCreator {

	/**
	 * Create from order.
	 *
	 * @param WC_Order                                                $order            Order.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Field\VatNumber|null $vat_number_field Vat number field.
	 * @param InvoiceOrderDefaults                                    $defaults         Defaults.
	 * @param InvoicesIntegration                                     $integration      Integration.
	 *
	 * @return BillData
	 */
	protected function createFromOrderAndDefaultsAndSettings(
		WC_Order $order,
		$vat_number_field,
		InvoiceOrderDefaults $defaults,
		InvoicesIntegration $integration
	) {
		return BillData::createFromOrderAndDefaultsAndSettings(
			$order,
			$vat_number_field,
			$defaults,
			$integration
		);
	}

	/**
	 * Create document in API.
	 *
	 * @param DocumentData $bill_data Bill data.
	 *
	 * @return PostResponseJson
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	protected function createDocument( DocumentData $bill_data, $order ) {
		$integration = $this->type->getIntegration();
		if ( ! $this->isAllowedForOrder( $order ) ) {
			$error_info = __( 'Ten typ dokumentu nie jest obsługiwany!', 'woocommerce-fakturownia' );
			throw new \RuntimeException( $error_info );
		}

		return $integration->get_api()->create_document( $bill_data );
	}

	/**
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAutoCreateAllowedForOrder( $order ) {
		$integration     = $this->type->getIntegration();
		$generate_status = $integration->woocommerce_integration->getOptionGenerateBillStatus();
		$generate_type   = $integration->woocommerce_integration->getOptionGenerateBill();
		$document_type   = $integration->woocommerce_integration->getOptionDocumentType();
		$order_status    = 'wc-' . $order->get_status();

		if ( $document_type !== BillForm::OPTION_DOCUMENT_TYPE_BILL ) {
			return false;
		}

		$generate_for_status = in_array( $order_status, $generate_status, true );
		if ( $generate_for_status && InvoiceForm::AUTO_GENERATE === $generate_type ) {
			return true;
		}

		if ( $generate_for_status && InvoiceForm::ASK_AND_AUTO_GENERATE === $generate_type ) {
			if ( '1' === $order->get_meta( '_billing_rachunek', 'true' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function isAllowedForOrder( $order ) {
		if ( wc_tax_enabled() ) {
			return false;
		}

		return ! ( $this->type->getIntegration()->woocommerce_integration->getOptionDocumentType() !== BillForm::OPTION_DOCUMENT_TYPE_BILL );
	}

	/**
	 * Get document from API.
	 *
	 * @param int $bill_id Bill ID.
	 *
	 * @return DocumentGetResponseJson
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	protected function getDocument( $bill_id ) {
		$integration = $this->type->getIntegration();

		return $integration->get_api()->get_bill( $bill_id );
	}
}
