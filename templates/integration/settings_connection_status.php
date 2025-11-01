<?php
$is_connected = $params['data']['is_connected'] ?? false;
?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="woocommerce_integration-fakturownia_account_name">Status połączenia</label>
	</th>
	<td class="forminp">
		<?php if ( $is_connected ): ?>
			<span style="color: green; font-weight: 700">Połączono</span>
		<?php else: ?>
			<span style="color: red; font-weight: 700">Nie połączono</span>
		<?php endif; ?>
	</td>
</tr>
