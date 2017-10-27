<?php


class FrontendifyDateField extends DateField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'DateField';

	private static $frontendify_requirements = [
		self::FrontendifyType => []
	];

	public function __construct( $name, $title = null, $value = null ) {
		parent::__construct( $name, $title, $value );
		$this->setAttribute( 'type', 'date');
		$this->removeExtraClass( 'hasDatepicker');
	}
}