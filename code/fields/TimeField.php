<?php


class FrontendifyTimeField extends TextField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'TimeField';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/frontendify/css/default.pickatime.css',
			'/frontendify/js/lib/picker/picker.js',
			'/frontendify/js/lib/picker/picker.time.js',
		]
	];

	public function __construct( $name, $title = null, $value = null ) {
		parent::__construct( $name, $title, $value );
		$this->setAttribute( 'type', 'time');
		$this->addExtraClass( 'time');
		$this->removeExtraClass( 'timepicker');
		$this->removeExtraClass( 'hasTimepicker');
	}
}