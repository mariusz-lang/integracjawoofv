<?php

namespace WPDesk\WooCommerceFakturownia\Metadata;

use FakturowniaVendor\WPDesk\Invoices\Metadata\MetadataContent;

class CorrectionMetadataContent extends MetadataContent {

	public function update( $metaData, $save = \true ) {

		$data = empty( $this->get() ) ? [] : $this->get();

		$data[] = $metaData;

		parent::update( $data, $save );
	}
}
