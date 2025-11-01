<?php

namespace WPDesk\WooCommerceFakturownia;

use WP_User;
use FakturowniaVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Add Vat field in user account.
 *
 * @package WPDesk\Library\FlexibleInvoicesCore\WordPress
 */
class User implements Hookable {

	const FIELD_NAME = 'billing_nip';

	/**
	 * Fires hooks
	 */
	public function hooks() {
		add_action( 'show_user_profile', [ $this, 'add_vat_user_field' ] );
		add_action( 'edit_user_profile', [ $this, 'add_vat_user_field' ] );
		add_action( 'personal_options_update', [ $this, 'save_vat_user_field' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_vat_user_field' ] );
		add_action( 'woocommerce_checkout_update_user_meta', [ $this, 'save_customer_vat_field' ], 10, 2 );
		add_filter( 'woocommerce_ajax_get_customer_details', [ $this, 'get_customer_details' ], 10, 3 );
	}

	/**
	 * @param WP_User $user
	 *
	 * @internal You should not use this directly from another application
	 */
	public function add_vat_user_field( WP_User $user ) {
		?>
		<script id="fakturownia_vat_number_row" type="template/text">
			<tr>
				<th>
					<label
						for="<?php echo esc_attr( self::FIELD_NAME ); ?>"><?php esc_html_e( 'Numer VAT', 'woocommerce-fakturownia' ); ?></label>
				</th>
				<td>
					<input
						type="text"
						id="<?php echo esc_attr( self::FIELD_NAME ); ?>"
						name="<?php echo esc_attr( self::FIELD_NAME ); ?>"
						value="<?php echo esc_attr( get_the_author_meta( self::FIELD_NAME, $user->ID ) ); ?>"
						class="regular-text"
					/>
					<br/>
					<span class="description"></span>
				</td>
			</tr>
		</script>
		<script>
			/**
			 * Adds a VAT Number field after the company or name field.
			 */
			jQuery(function ($) {
				let vat_number_field = $('#fakturownia_vat_number_row').html();
				let billing_company_field = $('#billing_company');
				let billing_last_name_field = $('#billing_last_name');
				if (billing_company_field.length) {
					billing_company_field.closest('tr').after(vat_number_field);
				} else {
					if (billing_last_name_field.length) {
						billing_last_name_field.closest('tr').after(vat_number_field);
					}
				}
			})
		</script>
		<?php
	}

	/**
	 * @param int $user_id
	 *
	 * @internal You should not use this directly from another application
	 */
	public function save_vat_user_field( $user_id ) {
		check_admin_referer( 'update-user_' . $user_id );
		if ( isset( $_POST[ self::FIELD_NAME ] ) && current_user_can( 'edit_user', $user_id ) ) {
			update_user_meta( $user_id, self::FIELD_NAME, sanitize_text_field( wp_unslash( $_POST[ self::FIELD_NAME ] ) ) );
		}
	}

	/**
	 * Update customer vat number.
	 *
	 * @param int   $user_id   User ID.
	 * @param array $post_data Post data.
	 *
	 * @internal You should not use this directly from another application
	 */
	public function save_customer_vat_field( $user_id, $post_data ) {
		if ( $user_id ) {
			update_user_meta( $user_id, self::FIELD_NAME, sanitize_text_field( $post_data[ self::FIELD_NAME ] ) );
		}
	}

	/**
	 * Get VAT number for customer details in order
	 *
	 * @param array $data Customer details.
	 *
	 * @return array
	 *
	 * @internal You should not use this directly from another application
	 */
	public function get_customer_details( $data, $customer, $user_id ) {
		$vat_number_value = '';

		if ( isset( $data['meta_data'] ) ) {
			foreach ( $data['meta_data'] as $meta_data ) {
				$meta = $meta_data->get_data();
				if ( 'billing_nip' === $meta['key'] ) {
					$vat_number_value = $meta['value'];
				}
			}
		} else {
			$vat_number_value = get_user_meta( $user_id, self::FIELD_NAME, true );
		}
		$data['billing']['nip'] = $vat_number_value;

		return $data;
	}
}
