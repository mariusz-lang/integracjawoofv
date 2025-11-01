<?php

namespace WPDesk\WooCommerceFakturownia\Field;

use FakturowniaVendor\WPDesk\Invoices\Field\VatNumber;

class RequiredVatNumber extends VatNumber {

	/**
	 * Field label.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * @var bool
	 */
	protected $required = false;

	/**
	 * FormField constructor.
	 *
	 * @param string $fieldId     Field ID.
	 * @param string $label       Label.
	 * @param string $placeholder Placeholder.
	 */
	public function __construct( $fieldId, $label, $placeholder ) {
		parent::__construct( $fieldId, $label, $placeholder );
		$this->label       = $label;
		$this->placeholder = $placeholder;
	}

	/**
	 * Prepare checkout field.
	 *
	 * @param null|int $field_priority Field priority
	 *
	 * @return bool|array
	 */
	protected function prepareCheckoutField( $field_priority = null ) {
		return [
			'label'       => $this->label,
			'placeholder' => $this->placeholder,
			'required'    => $this->required,
			'class'       => \is_admin() ? '' : [ 'form-row-wide' ],
			'clear'       => \true,
			'priority'    => $field_priority,
		];
	}

	public function set_required() {
		$this->required = true;
	}
}
