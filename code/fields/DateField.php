<?php

class FrontendifyDateField extends TextField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'DateField';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/frontendify/css/default.picker.css',
			'/frontendify/js/lib/picker.js',
			'/frontendify/js/lib/picker.date.js',
			'/frontendify/js/DateField.js',
		],
	];

	public function __construct($name, $title = null, $value = null) {
		parent::__construct($name, $title, $value);
		$this->removeExtraClass('datepicker');
		$this->removeExtraClass('hasDatepicker');
	}

	public function setValue($value) {
		if (strpos($value, '/')) {
			$parts = explode('/', $value);
			if (count($parts) == 3) {
				$value = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
			} else {
				$value = '';
			}
		}
		return parent::setValue($value);
	}

	public function Value() {
		if ($value = $this->dataValue()) {
			$value = date( 'd/m/Y', strtotime( $value ) );
		}
		return $value;
	}

}
