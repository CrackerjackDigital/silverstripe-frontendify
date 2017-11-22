<?php


class FrontendifyDateField extends TextField {
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
		$this->addExtraClass( 'date');
		$this->removeExtraClass( 'datepicker');
		$this->removeExtraClass( 'hasDatepicker');
	}
}