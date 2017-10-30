<?php


class FrontendifyDateField extends DateField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'DateField';

	private static $frontendify_require = [
		self::FrontendifyType => [
			'/themes/geeves/css/datepicker3.css',
			'/themes/geeves/js/bootstrap.min.js'
		]
	];

	public function __construct( $name, $title = null, $value = null ) {
		parent::__construct( $name, $title, $value );
		$this->setAttribute( 'type', 'date');
		$this->addExtraClass( 'datepicker');
		$this->removeExtraClass( 'hasDatepicker');
	}
}