<?php

namespace WPDesk\WooCommerceFakturownia\Helpers;

class Translations {

	public static function eu_vat_translation( $lang ): string {
		$t = [
			'pl' => 'nie podl. UE',
			'en' => 'not subject to the EU',
			'fr' => 'non soumis à l\'UE',
			'cz' => 'nepodléhá EU',
			'de' => 'unterliegt nicht der EU',
			'es' => 'no está sujeto a la UE',
			'et' => 'ei kuulu ELi kohaldamisalasse',
			'hu' => 'nem tartozik az EU hatálya alá',
			'hr' => 'nije predmet EU',
			'it' => 'non soggetto all\'UE',
			'ne' => 'niet onderworpen aan de EU',
			'sk' => 'nepodlieha EÚ',
			'sl' => 'za katere EU ne velja',
		];
		if ( isset( $t[ $lang ] ) ) {
			return $t[ $lang ];
		}

		return $t['en'];
	}
}
