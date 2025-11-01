<?php

namespace WPDesk\WooCommerceFakturownia\Documents;

use FakturowniaVendor\WPDesk\Invoices\Documents\Type;
use WPDesk\WooCommerceFakturownia\InvoicesIntegration;
use WPDesk\WooCommerceFakturownia\Metabox\MetaBoxFields;
use WPDesk\WooCommerceFakturownia\Metadata\InvoiceMetadata;

/**
 * @package WPDesk\WooCommerceFakturownia\Documents
 */
abstract class DocumentType extends Type {

	const META_DATA_TYPE = 'not set';

	/**
	 * Meta data type.
	 *
	 * @var string
	 */
	private $meta_data_type;

	/**
	 * Integration.
	 *
	 * @var InvoicesIntegration
	 */
	protected $integration;

	/**
	 * DocumentType constructor.
	 *
	 * @param string              $type_name                   Type name.
	 * @param string              $meta_data_name              Meta data name.
	 * @param string              $meta_data_type              Meta data type.
	 * @param string              $metabox_create_button_label Metabox create button label.
	 * @param string              $parameters_label            Parameters label.
	 * @param MetaBoxFields       $metabox_fields              Metabox fields.
	 * @param InvoicesIntegration $integration                 Integration.
	 */
	public function __construct(
		$type_name,
		$meta_data_name,
		$meta_data_type,
		$metabox_create_button_label,
		$parameters_label,
		$metabox_fields,
		InvoicesIntegration $integration
	) {
		parent::__construct( $type_name, $meta_data_name, $metabox_create_button_label, $parameters_label, $metabox_fields );
		$this->meta_data_type = $meta_data_type;
		$this->integration    = $integration;
	}

	/**
	 * Get meta data type.
	 *
	 * @return string
	 */
	public function get_meta_data_type() {
		return $this->meta_data_type;
	}


	/**
	 * Get integration.
	 *
	 * @return InvoicesIntegration
	 */
	public function getIntegration() {
		return $this->integration;
	}

	/**
	 * Prepare Document Metadata.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Metadata content.
	 * @return \FakturowniaVendor\WPDesk\Invoices\Metadata\DocumentMetadata
	 */
	public function prepareDocumentMetadata( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		return new InvoiceMetadata( $metadata_content );
	}

	/**
	 * Is meta data content valid for document type.
	 *
	 * @param \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content Meta data content.
	 *
	 * @return bool
	 */
	public function isMetadataContentValidForDocumentType( \FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent $metadata_content ) {
		$content = $metadata_content->get();
		$valid   = '' !== $content;
		if ( $valid && is_array( $content ) && isset( $content['typ'] ) ) {
			$valid = $content['typ'] === $this->get_meta_data_type();
		}
		return $valid;
	}

	/**
	 * Get metadata type.
	 *
	 * @return string
	 */
	public function getMetaDataType() {
		return static::META_DATA_TYPE;
	}
}
