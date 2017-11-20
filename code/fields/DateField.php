<?php

class FrontendifyDateField extends TextField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'DateField';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/frontendify/css/default.pickadate.css',
			'/frontendify/js/lib/picker.js',
			'/frontendify/js/lib/picker.date.js',
		],
	];

	public function __construct($name, $title = null, $value = null) {
		parent::__construct($name, $title, $value);
		$this->setAttribute('type', 'date');
		$this->removeExtraClass('datepicker');
		$this->removeExtraClass('hasDatepicker');
	}
}