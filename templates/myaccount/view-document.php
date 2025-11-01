<?php
/**
 * Fakturownia document in my account.
 *
 * @package woocommerce-fakturownia
 *
 * @var $type_name_label string
 * @var $get_pdf_url string
 * @var $document_number string
 */

?>
<div class="fakturownia-document">
	<header class="title"><h2><?php echo $type_name_label; ?></h2></header>
	<p><a href="<?php echo $get_pdf_url; ?>" target="_blank"><?php echo $document_number; ?></a></p>
</div>
