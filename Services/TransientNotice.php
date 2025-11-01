<?php

namespace WPDesk\WooCommerceFakturownia\Services;

use FakturowniaVendor\WPDesk\Notice\Notice;

/**
 * Service for creating transient notices.
 * Use this class to create notice that has to be visible after refreshing the page.
 *
 * Use display_admin_notices inside admin_notices hook.
 */
class TransientNotice {

	private const NOTICE_TRANSIENT_KEY = 'fakturownia_admin_notice_';

	public function display_admin_notices() {
		$transient_key = self::NOTICE_TRANSIENT_KEY;
		$notice_data   = get_transient( $transient_key );

		if ( $notice_data ) {
			new Notice( $notice_data['message'], $notice_data['type'] );
			delete_transient( $transient_key );
		}
	}

	public function set_admin_notice( string $message, string $type = 'error' ) {
		$transient_key = self::NOTICE_TRANSIENT_KEY;
		$notice_data   = [
			'message' => $message,
			'type'    => $type,
		];

		set_transient( $transient_key, $notice_data, 60 );
	}
}
