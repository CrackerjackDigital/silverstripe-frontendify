<?php

class FrontendifySelect2ColourPicker extends FrontendifySelect2Field {
	const FrontendifyType = 'Select2ColourPicker';

	private static $colours = [
		'#FBCEB1' => 'Apricot',
		'#FFB347' => 'Pastel Orange',
		'#F0EAD6' => 'Pearl',
		'#E5E4E2' => 'Platinum',
		'#FDFD96' => 'Pastel Yellow',
		'#DFFF00' => 'Chartreuse',
		'#ACE1AF' => 'Celadon',
		'#7FFFD4' => 'Aquamarine',
		'#00FFFF' => 'Cyan',
		'#CCCCFF' => 'Lavender Blue',
		'#F49AC2' => 'Pastel Magenta',
		'#FFD1DC' => 'Pastel Pink',
		'#FFC1CC' => 'Bubble Gum',
		'#FFE5B4' => 'Peach',
	];

	/**
	 * Return an array with a closure which will generate a script to select2ify the
	 * field by passed in id and with array of colours as data.
	 *
	 * @return array
	 */
	public function custom_javascripts() {
		static $colours;

		if (is_null($colours)) {
			$colours = $this->config()->get( 'colours' ) ?: [];
			asort( $colours );
			$colours = json_encode( $colours );
		}

		return [
			function ( $id ) use ($colours) {
				return '
					$(function() {
						$("#' . $id . '").select2ify(' . $colours . ');
					})(jQuery);
				';
			}
		];
	}
}