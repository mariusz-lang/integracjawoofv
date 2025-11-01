<?php
/**
 * Fakturownia settings template.
 *
 * @package WPDesk\WooCommerceFakturownia
 *
 * @var $form_fields array
 * @var $rendered_settings string
 * @var $text_domain string
 * @var $module_title string
 */

?>
<div>
	<div class="nav-tab-wrapper js-nav-tab-wrapper">
		<?php
		$active = 'nav-tab-active';
		foreach ( $form_fields as $field ) {
			if ( $field['type'] == 'tab_open' ) {
				?>
				<a class="nav-tab nav-tab-<?php echo sanitize_title( $field['title'] ); ?> <?php echo $active; ?>"
				   href="#<?php echo sanitize_title( $field['title'] ); ?>"><?php echo $field['title']; ?></a>
				<?php
				$active = '';
			}
		}
		?>
	</div>
</div>
<?php
echo $rendered_settings;
?>
<hr/>
<p><?php echo sprintf( __( '<b>Uwaga!</b> Kliknięcie <b>Zapisz zmiany</b> spowoduje zapisanie ustawień z wszystkich zakładek ustawień integracji %s.', $text_domain ), $module_title ); ?></p>

