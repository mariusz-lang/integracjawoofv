<?php
/**
 * Plugin. Download document.
 */

namespace WPDesk\WooCommerceFakturownia;

use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Download document from URL.
 *
 * @package WPDesk\WooCommerceFakturownia
 */
class DownloadDocument implements Hookable {

	/**
	 * @var InvoicesIntegration
	 */
	private $integration;

	/**
	 * @param InvoicesIntegration $integration
	 */
	public function __construct( InvoicesIntegration $integration ) {
		$this->integration = $integration;
	}

	/**
	 * Fires hooks.
	 */
	public function hooks() {
		add_action( 'wp', [ $this, 'download_invoice' ] );
	}

	/**
	 * Download invoice with URL.
	 */
	public function download_invoice() {
		$meta_data_name         = $this->get_request( 'type' );
		$order_id               = $this->get_request( 'order_id' );
		$invoice_download_value = $this->get_request( 'invoice_download' );
		if ( $invoice_download_value && $order_id && $meta_data_name ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				wp_die( 'Zamówienie już nie istnieje. Nie można pobrać faktury!' );
			}
			$hash              = new MetadataContent( $meta_data_name . '_hash', $order );
			$document_meta     = new MetadataContent( $meta_data_name, $order );
			$meta_array        = $document_meta->get();
			$document_metadata = end( $meta_array );
			$document_type     = $this->integration->get_document_type_by_metadata_name_and_type(
				$meta_data_name . $document_metadata['typ']
			);

			if ( $document_type && $hash->get() === $invoice_download_value && $document_meta->get() ) {
				$document_pdf = $this->integration->getDocumentPdf( $document_meta );
				$filename     = $this->get_filename( $document_meta->get() );
				header( 'Content-type: application/pdf' );
				if ( ! isset( $request['view'] ) ) {
					header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
				}
				// Don't scape PDF content.
				echo $document_pdf; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				die();
			}
		}
	}

	/**
	 * @param array $document_metadata Document order meta [ numer, typ, invoice, order_id ].
	 *
	 * @return mixed|string|string[]
	 */
	private function get_filename( array $document_metadata ) {
		$number                   = isset( $document_metadata['numer'] ) ? $document_metadata['numer'] : date( 'ymdhis' );
		$document_metadata['typ'] = isset( $document_metadata['typ'] ) ? $document_metadata['typ'] : 'faktura';
		$file_name                = str_replace(
			[ ' ', '/' ],
			'_',
			sprintf( '%1$s_%2$s.pdf', $document_metadata['typ'], $number )
		);

		return $file_name;
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private function get_request( $key ) {
		return isset( $_GET[ $key ] ) ? $_GET[ $key ] : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}
}
