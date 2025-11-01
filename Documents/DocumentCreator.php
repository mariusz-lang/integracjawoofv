<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use FakturowniaVendor\WPDesk\Invoices\Metadata\CustomMetadata;
use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use WC_Order;
use FakturowniaVendor\WPDesk\Invoices\Documents\AbstractCreator;
use WPDesk\WooCommerceFakturownia\Api\DocumentGetResponseJson;
use WPDesk\WooCommerceFakturownia\Api\DocumentPostResponseJson;
use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Api\ParentInvoiceIDNotFoundException;
use WPDesk\WooCommerceFakturownia\Emails\EmailAutoSend;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Data\DocumentData;
use WPDesk\WooCommerceFakturownia\Data\InvoiceOrderDefaults;
use WPDesk\WooCommerceFakturownia\Logger\LoggerException;
use WPDesk\WooCommerceFakturownia\Metadata\CorrectionMetadataContent;

/**
 * Class DocumentCreator
 *
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
abstract class DocumentCreator extends AbstractCreator {

	/**
	 * Document type.
	 *
	 * @var DocumentType
	 */
	protected $type;

	public function get_type() {
		return $this->type;
	}

	/**
	 * Create from order.
	 *
	 * @param WC_Order                                                $order            Order.
	 * @param \FakturowniaVendor\WPDesk\Invoices\Field\VatNumber|null $vat_number_field Vat number field.
	 * @param InvoiceOrderDefaults                                    $defaults         Defaults.
	 * @param InvoicesIntegration                                     $integration      Integration.
	 *
	 * @return \WPDesk\WooCommerceFakturownia\Data\DocumentData
	 */
	abstract protected function createFromOrderAndDefaultsAndSettings(
		WC_Order $order,
		$vat_number_field,
		InvoiceOrderDefaults $defaults,
		InvoicesIntegration $integration
	);

	/**
	 * Create document in API.
	 *
	 * @param DocumentData $invoice_data Invoice data.
	 *
	 * @return DocumentPostResponseJson
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	abstract protected function createDocument( DocumentData $invoice_data, $order );

	/**
	 * Get document from API.
	 *
	 * @param int $invoice_id Invoice ID.
	 *
	 * @return DocumentGetResponseJson
	 * @throws \WPDesk\HttpClient\HttpClientRequestException HTTP Exception.
	 * @throws InvoicesException API Exception.
	 */
	abstract protected function getDocument( $invoice_id );


	/**
	 * Create document for order.
	 *
	 * @param WC_Order $order              Order.
	 * @param array    $data               Posted data.
	 * @param bool     $overwrite_existing Overwrite already created.
	 *
	 * @throws \WPDesk\HttpClient\HttpClientRequestException Exception.
	 * @throws InvoicesException Exception.
	 * @throws \Exception Exception.
	 */
	public function createDocumentForOrder( $order, array $data, $overwrite_existing = true ) {
		$this->maybe_throw_document_already_exists_exception( $order, $overwrite_existing );

		$integration = $this->type->getIntegration();
		$vat_number  = $integration->get_vat_number_field();

		$defaults = new InvoiceOrderDefaults( $order, $integration, $this->type );

		$defaults->setFromData( $data );

		if ( $this->type->getMetaDataName() === InvoiceCorrection::META_DATA_NAME ) {
			$metadata = new CorrectionMetadataContent( $this->type->getMetaDataName(), $order );
			$metahash = new CorrectionMetadataContent( $this->type->getMetaDataName() . '_hash', $order );
		} else {
			$metadata = new MetadataContent( $this->type->getMetaDataName(), $order );
			$metahash = new MetadataContent( $this->type->getMetaDataName() . '_hash', $order );
		}

		try {

			$invoice_data = $this->createFromOrderAndDefaultsAndSettings(
				$order,
				$vat_number,
				$defaults,
				$integration
			);

			if ( $integration->woocommerce_integration->isDebugEnabled() ) {
				$order->update_meta_data( $this->type->getMetaDataName() . '_log', $invoice_data->toJsonString() );
			}

			$create_invoice_response = $this->createDocument( $invoice_data, $order );

			$invoice_id = $create_invoice_response->getId();

			$get_invoice_response = $this->getDocument( $invoice_id );

			$document_meta = [
				'id'            => $invoice_id,
				'numer'         => $get_invoice_response->getFullNumber(),
				'data'          => $get_invoice_response->getResponseBody(),
				'typ'           => $this->type->get_meta_data_type(),
				'faktura_total' => $get_invoice_response->getDocumentTotal(),
			];
			$metadata->update( $document_meta, true );

			$download_hash = md5( NONCE_SALT . $order->get_id() . $order->get_billing_email() . $order->get_date_created() );
			$metahash->update( $download_hash, true );

			$document_metadata = $this->type->prepareDocumentMetadata( $metadata );

			$order->add_order_note(
				sprintf(
				// Translators: type name and document number.
					__( 'Fakturownia - wystawiono dokument: %1$s %2$s', 'woocommerce-fakturownia' ),
					$document_metadata->getTypeName(),
					$get_invoice_response->getFullNumber()
				)
			);

			$this->maybeSendEmail( $order, $download_hash );

		} catch ( ParentInvoiceIDNotFoundException $e ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// do nothing
		} catch ( LoggerException $e ) { //phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// do nothing
		} catch ( \Exception $e ) {
			$metadata->update( [ 'error' => $e->getMessage() ], true );
			$document_metadata = $this->type->prepareDocumentMetadata( $metadata );
			$order->add_order_note(
				sprintf(
				// Translators: type name and document number.
					__( 'Fakturownia - błąd przy wystawianiu dokumentu %1$s: %2$s', 'woocommerce-fakturownia' ),
					$document_metadata->getTypeName(),
					$e->getMessage()
				)
			);
			throw $e;
		}
	}

	/**
	 * Maybe send email after document create.
	 *
	 * @param WC_Order $order Order.
	 */
	protected function maybeSendEmail( $order, $download_hash ) {
		$email_auto_send = new EmailAutoSend( $this->type );
		$auto_send       = apply_filters( 'fakturownia/email/auto_send', true, $order, $this->type );
		if ( $auto_send ) {
			$email_auto_send->maybe_send_email( $order, $download_hash );
		}
	}

	/**
	 * Validate currency.
	 *
	 * @param DocumentData $data Data.
	 *
	 * @throws \FakturowniaVendor\WPDesk\Invoices\Exception\InvalidInvoiceDataException Exception.
	 */
	protected function validate_currency( DocumentData $data ) {
		if ( 'PLN' !== $data->getCurrency() ) {
			throw new \FakturowniaVendor\WPDesk\Invoices\Exception\InvalidInvoiceDataException(
			// Translators: currency.
				sprintf( __( 'Błędna waluta: %1$s', 'woocommerce-fakturownia' ), $data->getCurrency() )
			);
		}
	}

	/**
	 * Maybe throw DocumentAlreadyExistsException.
	 *
	 * @param WC_Order $order              Order.
	 * @param bool     $overwrite_existing Overwrite existing document.
	 *
	 * @throws \FakturowniaVendor\WPDesk\Invoices\Exception\DocumentAlreadyExistsException Exception.
	 */
	protected function maybe_throw_document_already_exists_exception( $order, $overwrite_existing ) {
		if ( ! $overwrite_existing && $this->areDocumentExistsForOrder( $order ) ) {
			throw new \FakturowniaVendor\WPDesk\Invoices\Exception\DocumentAlreadyExistsException();
		}
	}

	/**
	 * Are document exists for order?
	 *
	 * @param \WC_Order $order Order.
	 *
	 * @return bool
	 */
	public function areDocumentExistsForOrder( $order ) {
		$meta_data_name   = $this->type->getMetaDataName();
		$metadata_content = new \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent( $meta_data_name, $order );
		$order_metas      = $metadata_content->get();

		// Library declares that $metadata_content->get(); returns array which is not always the case therefore we need to check and ignore phpstan error
		if ( ! is_array( $order_metas ) || empty( $order_metas ) ) { //@phpstan-ignore-line
			return false;
		}

		foreach ( $order_metas as $meta ) {
			$meta_data = ( new CustomMetadata( $meta, $meta_data_name, $order ) )->get();
			if ( isset( $meta_data['id'] ) ) {
				return true;
			}
		}

		return false;
	}
}
