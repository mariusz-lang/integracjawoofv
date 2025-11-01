<?php

namespace WPDesk\WooCommerceFakturownia\Data;

use FakturowniaVendor\WPDesk\Invoices\Data\Items\InvoiceItem;
use WPDesk\WooCommerceFakturownia\Api\InvoicesException;
use WPDesk\WooCommerceFakturownia\Api\ParentInvoiceIDNotFoundException;
use WPDesk\WooCommerceFakturownia\Documents\Invoice;
use WPDesk\WooCommerceFakturownia\Integrations\FQIntegration;

/**
 * Class InvoiceCorrectionData
 *
 * Prepare data for invoice document
 *
 * @package WPDesk\WooCommerceFakturownia\Data
 */
class InvoiceCorrectionData extends DocumentData {

	private const CORRECTION_REASON_KEY            = 'correction_reason';
	private const CORRECTED_INVOICE_ID_KEY         = 'invoice_id';
	private const CORRECTED_FROMINVOICE_ID_KEY     = 'from_invoice_id';
	private const CORRECTION_BEFORE_ATTRIBUTES_KEY = 'correction_before_attributes';
	private const CORRECTION_AFTER_ATTRIBUTES_KEY  = 'correction_after_attributes';
	private const CORRECTION_BEFORE_KIND_KEY       = 'correction_before';
	private const CORRECTION_AFTER_KIND_KEY        = 'correction_after';
	public const  KIND                             = 'correction';

	private const REFUND_QTY              = 'refund_qty';
	private const REFUND_TOTAL_NET        = 'refund_total_net';
	private const REFUND_TOTAL_TAX        = 'refund_total_tax';
	private const REFUND_TOTAL_GROSS      = 'refund_total_gross';
	private const REFUND_UNIT_PRICE_NET   = 'refund_unit_price_net';
	private const REFUND_UNIT_PRICE_GROSS = 'refund_unit_price_gross';

	private const LATEST_REFUND_INDEX = 0;


	/**
	 * Prepare data as array
	 *
	 * @return array
	 */
	public function prepareDataAsArray() {
		$data                                = parent::prepareDataAsArray();
		$data[ DocumentData::DOCUMENT_KIND ] = self::KIND;

		$parent_invoice_id                          = $this->get_invoice_id( $this->getOrder() );
		$data[ self::CORRECTED_FROMINVOICE_ID_KEY ] = $parent_invoice_id;
		$data[ self::CORRECTED_INVOICE_ID_KEY ]     = $parent_invoice_id;

		$order_refunds = $this->getOrder()->get_refunds();

		if ( ! isset( $order_refunds[ self::LATEST_REFUND_INDEX ] ) ) {
			throw new InvoicesException( __( 'Refund not found.', 'woocommerce-fakturownia' ) );
		}
		/**
		 * Issue correction only for the last refund.
		 */
		$latest_refund = $order_refunds[ self::LATEST_REFUND_INDEX ];

		$data[ self::CORRECTION_REASON_KEY ] = $this->get_correction_reason( $latest_refund );
		$data[ self::DOCUMENT_ITEMS ]        = $this->prepareCorrectionItemsAsArray( $latest_refund );

		return apply_filters( 'fakturownia/invoice_correction/data', $data, $this );
	}

	/**
	 *
	 * Get correction reasons from WooCommerce Order.
	 *
	 * @param \WC_Order_Refund $refund
	 *
	 * @return string
	 */
	private function get_correction_reason( \WC_Order_Refund $refund ): string {
		return $refund->get_reason();
	}

	/**
	 *
	 * Get invoice id to correct.
	 *
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	private function get_invoice_id( \WC_Order $order ): string {
		$invoice_data = $order->get_meta( Invoice::META_DATA_NAME );

		if ( isset( $invoice_data['id'] ) ) {
			return (string) $invoice_data['id'];
		}

		throw new ParentInvoiceIDNotFoundException( __( 'Parent invoice ID not found.', 'woocommerce-fakturownia' ) );
	}

	/**
	 *
	 * Get array with correction details. Array contains refund details of each refunded item.
	 *
	 * @param \WC_Order_Refund $refund
	 *
	 * @return array
	 */
	private function get_correction_details( \WC_Order_Refund $refund ): array {
		$details = [];
		$items   = $refund->get_items( [ 'line_item', 'shipping' ] );

		foreach ( $items as $item_id => $item ) {
			$refund_qty = (int) $item->get_quantity();

			if ( $item instanceof \WC_Order_Item_Product ) {
				$index = $item->get_variation_id() ?: $item->get_product_id();

				$refund_total_net   = (float) $item->get_total();
				$refund_total_tax   = (float) $item->get_total_tax();
				$refund_total_gross = $refund_total_net + $refund_total_tax;

			} elseif ( $item instanceof \WC_Order_Item_Shipping ) {
				$index = 'shipping_' . $item->get_instance_id();

				if ( ! $this->isPricesIncludeTax() ) {

					$original_refund_net = (float) $item->get_total();
					$original_refund_tax = (float) $item->get_total_tax();

					$refund_total_net = $original_refund_net + $original_refund_tax;

					$tax_rate = 0.0;
					if ( $original_refund_net !== 0.0 ) {
						$tax_rate = abs( $original_refund_tax / $original_refund_net );
					}

					$refund_total_tax   = $refund_total_net * $tax_rate;
					$refund_total_gross = $refund_total_net + $refund_total_tax;

				} else {
					$refund_total_net   = (float) $item->get_total();
					$refund_total_tax   = (float) $item->get_total_tax();
					$refund_total_gross = $refund_total_net + $refund_total_tax;
				}
			} else {
				continue;
			}

			$unit_price_net   = ( $refund_qty !== 0 ) ? abs( $refund_total_net / $refund_qty ) : 0;
			$unit_price_gross = ( $refund_qty !== 0 ) ? abs( $refund_total_gross / $refund_qty ) : 0;

			$details[ $index ] = [
				self::REFUND_QTY              => $refund_qty,
				self::REFUND_TOTAL_NET        => $refund_total_net,
				self::REFUND_TOTAL_TAX        => $refund_total_tax,
				self::REFUND_TOTAL_GROSS      => $refund_total_gross,
				self::REFUND_UNIT_PRICE_NET   => $unit_price_net,
				self::REFUND_UNIT_PRICE_GROSS => $unit_price_gross,
			];
		}

		return $details;
	}

	/**
	 *
	 * Get "correction_before_attributes" for item. Returns array with values before refund (order values before
	 * refund).
	 *
	 * @param InvoiceItem $item
	 *
	 * @return array
	 */
	private function get_correction_before_attributes_for_item( InvoiceItem $item ): array {
		$item_data = [];

		$item_data                         = $this->prepareItemIdentity( $item_data, $item );
		$item_data[ self::ITEM_FULL_NAME ] = $this->get_item_full_name( $item );
		$item_data[ self::ITEM_QUANTITY ]  = $item->getQuantity();
		$item_data[ self::TAX_RATE ]       = (float) $item->getTaxRate();
		$item_data['kind']                 = self::CORRECTION_BEFORE_KIND_KEY;

		$original_price = $item->getPrice();
		$original_qty   = $item->getQuantity();

		if ( $this->isPricesIncludeTax() ) {
			$item_data[ self::ITEM_PRICE_GROSS ]       = $original_price;
			$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $this->round( $original_price * $original_qty );
		} else {
			$tax_rate    = $item->getTaxRate();
			$total_net   = $this->round( $original_price * $original_qty );
			$total_gross = $this->round( $total_net * ( 1 + ( $tax_rate / 100 ) ) );
			$price_gross = $this->round( $original_price * ( 1 + ( $tax_rate / 100 ) ) );

			$item_data[ self::ITEM_PRICE_NET ]         = $original_price;
			$item_data[ self::ITEM_TOTAL_PRICE_NET ]   = $total_net;
			$item_data[ self::ITEM_PRICE_GROSS ]       = $price_gross;
			$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $total_gross;
		}

		return $item_data;
	}


	/**
	 *
	 * Get "correction_after_attributes" for item. Returns array with values after refund (new values in order).
	 *
	 * @param InvoiceItem $item
	 * @param array       $correction_details
	 *
	 * @return array
	 */
	private function get_correction_after_attributes_for_item( InvoiceItem $item, array $correction_details ): array {
		$item_data = [];

		$item_data                         = $this->prepareItemIdentity( $item_data, $item );
		$item_data[ self::ITEM_FULL_NAME ] = $this->get_item_full_name( $item );
		$item_data[ self::TAX_RATE ]       = $item->getTaxRate();
		$item_data['kind']                 = self::CORRECTION_AFTER_KIND_KEY;

		$new_quantity                     = $item->getQuantity() + $correction_details[ self::REFUND_QTY ];
		$item_data[ self::ITEM_QUANTITY ] = $new_quantity;

		$original_price = $item->getPrice();

		if ( $this->isPricesIncludeTax() ) {
			$item_data[ self::ITEM_PRICE_GROSS ]       = $original_price;
			$original_total_gross                      = $this->round( $original_price * $item->getQuantity() );
			$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $original_total_gross + $correction_details[ self::REFUND_TOTAL_GROSS ];
		} else {
			$tax_rate           = $item->getTaxRate();
			$price_gross        = $this->round( $original_price * ( 1 + ( $tax_rate / 100 ) ) );
			$original_total_net = $this->round( $original_price * $item->getQuantity() );
			$total_net_after    = $original_total_net + $correction_details[ self::REFUND_TOTAL_NET ];
			$total_gross_after  = $this->round( $total_net_after * ( 1 + ( $tax_rate / 100 ) ) );

			$item_data[ self::ITEM_PRICE_NET ]         = $original_price;
			$item_data[ self::ITEM_TOTAL_PRICE_NET ]   = $total_net_after;
			$item_data[ self::ITEM_PRICE_GROSS ]       = $price_gross;
			$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $total_gross_after;
		}

		return $item_data;
	}

	protected function prepareCorrectionItemsAsArray( \WC_Order_Refund $refund ): array {
		$items_data = [];

		$client_data        = $this->getClientData();
		$correction_details = $this->get_correction_details( $refund );

		foreach ( $this->getItems() as $item ) {
			$order_item = $item->getOrderItem();

			if ( $order_item instanceof \WC_Order_Item_Product ) {
				$product_id = $order_item->get_variation_id() ?: $order_item->get_product_id();

				if ( ! isset( $correction_details[ $product_id ] ) ) {
					continue;
				}
				$item_correction_details = $correction_details[ $product_id ];

				$fq = new FQIntegration( $order_item );

				$item_data                             = [];
				$item_data                             = $this->prepareItemIdentity( $item_data, $item );
				$item_data[ self::ITEM_FULL_NAME ]     = $this->get_item_full_name( $item );
				$item_data[ self::ITEM_QUANTITY ]      = $item_correction_details[ self::REFUND_QTY ];
				$item_data[ self::ITEM_QUANTITY_UNIT ] = $fq->get_item_unit( self::ITEM_QUANTITY_UNIT_VALUE );
				$item_data[ self::TAX_RATE ]           = $item->getTaxRate();
				$item_data['kind']                     = self::KIND;

				if ( $this->isPricesIncludeTax() ) {
					$item_data[ self::ITEM_PRICE_GROSS ]       = $item_correction_details[ self::REFUND_UNIT_PRICE_GROSS ];
					$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $item_correction_details[ self::REFUND_TOTAL_GROSS ];
				} else {
					$item_data[ self::ITEM_PRICE_NET ]         = $item_correction_details[ self::REFUND_UNIT_PRICE_NET ];
					$item_data[ self::ITEM_TOTAL_PRICE_NET ]   = $item_correction_details[ self::REFUND_TOTAL_NET ];
					$item_data[ self::ITEM_PRICE_GROSS ]       = $item_correction_details[ self::REFUND_UNIT_PRICE_GROSS ];
					$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $item_correction_details[ self::REFUND_TOTAL_GROSS ];
				}

				$item_data[ self::CORRECTION_BEFORE_ATTRIBUTES_KEY ] = $this->get_correction_before_attributes_for_item( $item );
				$item_data[ self::CORRECTION_AFTER_ATTRIBUTES_KEY ]  = $this->get_correction_after_attributes_for_item( $item, $item_correction_details );

				if ( 0.0 === $item->getTaxRate() && 'none' !== $this->getTaxRateExempt() ) {
					if ( $this->getTaxRateExempt() === $item->getTaxClass() ) {
						$item_data[ self::TAX_RATE ] = self::TAX_RATE_TYPE_ZW;
						$this->has_zw                = true;

						$pkwiu                                   = $item->getAdditionalDataByName( self::ADDITIONAL_DATA_PKWIU );
						$item_data[ self::ITEM_ADDITIONAL_INFO ] = $pkwiu ?? '';
					}
				}
				$eu_vat_countries = $this->get_eu_countries();
				if ( ! in_array( $client_data->getCountry(), $eu_vat_countries, true ) ) {
					if ( (int) $item->getTaxRate() > 0 ) {
						$item_data[ self::TAX_RATE ] = $item->getTaxRate();
					} else {
						$item_data[ self::TAX_RATE ] = self::TAX_RATE_TYPE_NP;
					}
				}

				$items_data[] = $item_data;

			} elseif ( $order_item instanceof \WC_Order_Item_Shipping ) {

				$shipping_id = 'shipping_' . $order_item->get_instance_id();

				if ( ! isset( $correction_details[ $shipping_id ] ) ) {
					continue;
				}
				$item_correction_details = $correction_details[ $shipping_id ];

				$item_data                         = [];
				$item_data[ self::ITEM_FULL_NAME ] = $item->getName();
				$item_data[ self::ITEM_QUANTITY ]  = $item_correction_details[ self::REFUND_QTY ];
				$item_data[ self::TAX_RATE ]       = $item->getTaxRate();
				$item_data['kind']                 = self::KIND;

				if ( $this->isPricesIncludeTax() ) {
					$item_data[ self::ITEM_PRICE_GROSS ]       = $item_correction_details[ self::REFUND_UNIT_PRICE_GROSS ];
					$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $item_correction_details[ self::REFUND_TOTAL_GROSS ];
				} else {
					$item_data[ self::ITEM_PRICE_NET ]         = $item_correction_details[ self::REFUND_UNIT_PRICE_NET ];
					$item_data[ self::ITEM_TOTAL_PRICE_NET ]   = $item_correction_details[ self::REFUND_TOTAL_NET ];
					$item_data[ self::ITEM_PRICE_GROSS ]       = $item_correction_details[ self::REFUND_UNIT_PRICE_GROSS ];
					$item_data[ self::ITEM_TOTAL_PRICE_GROSS ] = $item_correction_details[ self::REFUND_TOTAL_GROSS ];
				}

				$total_net_before   = (float) $order_item->get_total();
				$total_gross_before = $total_net_before + (float) $order_item->get_total_tax();

				$item_data[ self::CORRECTION_BEFORE_ATTRIBUTES_KEY ] = [
					'name'              => $item->getName(),
					'quantity'          => 1,
					'tax'               => $item->getTaxRate(),
					'total_price_gross' => $this->round( $total_gross_before ),
					'total_price_net'   => $this->round( $total_net_before ),
					'kind'              => self::CORRECTION_BEFORE_KIND_KEY,
				];

				$total_net_after   = $total_net_before + $item_correction_details[ self::REFUND_TOTAL_NET ];
				$total_gross_after = $total_gross_before + $item_correction_details[ self::REFUND_TOTAL_GROSS ];

				$item_data[ self::CORRECTION_AFTER_ATTRIBUTES_KEY ] = [
					'name'              => $item->getName(),
					'quantity'          => $total_gross_after > 0 ? 1 : 0,
					'tax'               => $item->getTaxRate(),
					'total_price_gross' => $this->round( $total_gross_after ),
					'total_price_net'   => $this->round( $total_net_after ),
					'kind'              => self::CORRECTION_AFTER_KIND_KEY,
				];

				$items_data[] = $item_data;
			}
		}

		return $items_data;
	}
}
