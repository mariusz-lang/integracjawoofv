<?php

namespace WPDesk\WooCommerceFakturownia\Data;

use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;
use FakturowniaVendor\WPDesk\Invoices\Data\Items\LineItem;
use WPDesk\WooCommerceFakturownia\Documents\Receipt;
use WPDesk\WooCommerceFakturownia\GTU;
use WPDesk\WooCommerceFakturownia\Helpers\Translations;
use WPDesk\WooCommerceFakturownia\ProcedureDesignations;

/**
 * Class InvoiceData
 *
 * Prepare data for invoice document
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class InvoiceData extends DocumentData {

	public const DOCUMENT_KIND       = 'kind';
	private const FROM_INVOICE_ID    = 'from_invoice_id';
	private const INVOICE_ID         = 'invoice_id';
	private const ADDITIONAL_PARAMS  = 'additional_params';
	private const EXCLUDE_FROM_STOCK = 'exclude_from_stock_level';
	private const GTU_CODES          = 'gtu_codes';
	private const PROCEDURE_CODES    = 'procedure_designations';

	/**
	 * Get receipt ID.
	 */
	private function get_receipt_id() {
		$receipt_meta = $this->getOrder()->get_meta( Receipt::META_DATA_NAME );

		return isset( $receipt_meta['id'] ) ? $receipt_meta['id'] : null;
	}

	/**
	 * @return string|null
	 */
	private function get_gtu_code( $item ) {
		if ( $item instanceof \WC_Order_Item_Product ) {
			$product_id = $item->get_product_id();
			$code       = get_post_meta( $product_id, GTU::GTU_CODE_KEY, true );
			if ( ! empty( $code ) ) {
				return $code;
			}
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	private function get_transaction_code( $item ) {
		if ( $item instanceof \WC_Order_Item_Product ) {
			$product_id = $item->get_product_id();
			$code       = get_post_meta( $product_id, ProcedureDesignations::CODE_KEY, true );
			if ( ! empty( $code ) ) {
				return $code;
			}
		}

		return null;
	}

	/**
	 * @return array
	 */
	private function get_items_gtu_codes() {
		$codes = [];
		foreach ( $this->getItems() as $item ) {
			if ( $item instanceof LineItem ) {
				$order_item = $item->getOrderItem();
				$code       = $this->get_gtu_code( $order_item );
				if ( $code ) {
					$codes[] = $code;
				}
			}
		}

		return array_unique( $codes );
	}

	/**
	 * @return array
	 */
	private function get_items_procedure_codes() {
		$codes = [];
		foreach ( $this->getItems() as $item ) {
			if ( $item instanceof LineItem ) {
				$order_item = $item->getOrderItem();
				$code       = $this->get_transaction_code( $order_item );
				if ( $code ) {
					$codes[] = $code;
				}
			}
		}

		return array_unique( $codes );
	}


	/**
	 * Prepare data as array
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                        = parent::prepareDataAsArray();
		$data[ self::DOCUMENT_KIND ] = 'vat';
		$gtu_codes                   = $this->get_items_gtu_codes();
		$procedure_codes             = $this->get_items_procedure_codes();

		if ( ! empty( $gtu_codes ) ) {
			$data[ self::GTU_CODES ] = $gtu_codes;
		}

		if ( ! empty( $procedure_codes ) ) {
			$data[ self::PROCEDURE_CODES ] = $procedure_codes;
		}

		$data[ self::ADDITIONAL_INFO ] = 1;

		if ( $this->get_receipt_id() ) {
			$data[ self::FROM_INVOICE_ID ]    = $this->get_receipt_id();
			$data[ self::INVOICE_ID ]         = $this->get_receipt_id();
			$data[ self::ADDITIONAL_PARAMS ]  = 'for_receipt';
			$data[ self::EXCLUDE_FROM_STOCK ] = true;
		}

		return apply_filters( 'fakturownia/invoice/data', $data, $this );
	}
}
