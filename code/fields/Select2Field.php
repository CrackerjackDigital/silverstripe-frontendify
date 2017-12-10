<?php
/**
 * Field which implements Select2 functionality (http://http://ivaynberg.github.io/select2/)
 *
 * By default expects select2 to be installed via composer to component/select2/
 */

class FrontendifySelect2Field extends DropdownField {
	use frontendify_field, frontendify_requirements;

	const FrontendifyType = 'Select2Field';

	private static $frontendify_require = [
		self::FrontendifyType => [
			"js/select2/select2.js",
			"js/select2/select2.css"
		]
	];

	/**
	 * Auto-populate from current controller's model
	 *
	 * @param string $name
	 * @param null   $title
	 * @param null   $source
	 * @param array  $values
	 * @param bool   $isMultiple
	 * @param null   $emptyString
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $name, $title = null, $source = null, $value = null, $form = null, $emptyString = null ) {
		parent::__construct( $name, $title );
		if ($source) {
			$this->setSource( $source );
		}
		if ($value) {
			$this->setValue( $value );
		}
	}

	public function setSource( $source ) {
		parent::setSource( $this->decode_list( $source ) );

		return $this;
	}


	protected static function decode_values( $list ) {
		$decoded = static::decode_list( $list );
		return is_array( $decoded ) ? array_keys( $decoded ) : $decoded;
	}

}